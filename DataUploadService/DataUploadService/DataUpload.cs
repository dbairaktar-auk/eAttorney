using DataUploadService.Lib;
using DataUploadService.Lib.Emailing;
using DataUploadService.Lib.Logging;
using Microsoft.Extensions.Configuration;
using System;
using System.Collections.Generic;
using System.IO;
using System.Text;
using System.Timers;

namespace DataUploadService
{
    public class DataUpload
    {
        private IConfigurationRoot config;
        private readonly Timer timer;
        private readonly Timer heartbeatTimer;
        private static bool isIterationRunning;
        private static bool isStopRequested;

        public DataUpload(IConfigurationRoot config)
        {
            this.config = config;
            timer = new Timer(Convert.ToDouble(config["IterationTimerSeconds"]) * 1000) { AutoReset = true };
            timer.Elapsed += TimerElapsed;

            heartbeatTimer = new Timer(Convert.ToDouble(config["HeartbeatTimerMinutes"]) * 60 * 1000) { AutoReset = true };
            heartbeatTimer.Elapsed += HeartbeatTimerElapsed;

            isIterationRunning = false;
            isStopRequested = false;
            Logger.MainLogger = new Logger(config["DatabaseConnectionString"]);

            var smtpSettings = config.GetSection("Smtp");
            EmailSender.Configure(smtpSettings["Host"],
                                  Convert.ToInt32(smtpSettings["Port"]),
                                  Convert.ToBoolean(smtpSettings["EnableSsl"]),
                                  smtpSettings["UserName"],
                                  smtpSettings["Password"],
                                  smtpSettings["From"],
                                  config["EmailTo"]);
        }

        private void HeartbeatTimerElapsed(object sender, ElapsedEventArgs e)
        {
            try
            {
                Logger heartbeatLogger = new Logger(config["DatabaseConnectionString"]);
                heartbeatLogger.LogInformation("Heartbeat. All works ok", true);
                heartbeatLogger.StopLogger();
            }
            catch (Exception ex)
            {
                Logger.MainLogger.LogError($"Error logging heartbeat. Message: {ExceptionHelper.GetExceptionInfo(ex)}");
            }
        }

        private void TimerElapsed(object sender, ElapsedEventArgs e)
        {
            try
            {
                if (isIterationRunning)
                {
                    Logger.MainLogger.PingConnection();
                    return;
                }

                if (isStopRequested)
                {
                    Logger.MainLogger.PingConnection();
                    return;
                }

                isIterationRunning = true;
                Logger.MainLogger.RefreshExecutionId();
                foreach (string folder in Directory.GetDirectories(config["SourceFilesRootFolder"]))
                {
                    ProcessFolder(folder);
                }
                isIterationRunning = false;
            }
            catch (Exception ex)
            {
                Logger.MainLogger.LogError($"An error occurred while processing folder. Exception message: {ExceptionHelper.GetExceptionInfo(ex)}", true);
                isIterationRunning = false;
            }
        }


        private void ProcessFolder(string folder)
        {
            // check if we have trigger file
            string[] flags = Directory.GetFiles(folder, "flag.*");
            if (flags.Length == 0)
                return;

            // get user id and delete flags
            int userId = 0;
            foreach (string flag in flags) 
            {
                int.TryParse(File.ReadAllText(flag), out userId);
                File.Delete(flag); 
            }

            Logger.MainLogger.LogInformation($"{folder} directory processing started");

            string companyName = new DirectoryInfo(folder).Name;
            string[] files = Directory.GetFiles(folder, "*.xlsx");

            if (files.Length == 0)
            {
                Logger.MainLogger.LogInformation("There are no files to process");
                return;
            }

            ExcelUploadManager.ConfigureConnections(config["ExcelConnectionString"], config["DatabaseConnectionString"]);
            ETLManager.ConfigureConnection(config["DatabaseConnectionString"]);
            int filesProcessed = 0;
            bool noInvalidFiles = true;

            foreach (var excelFile in files)
            {
                var uploadResult = new ExcelUploadManager(excelFile, userId).ProcessFile();
                Logger.MainLogger.LogInformation(uploadResult.Message);

                if (uploadResult.IsSuccessful && uploadResult.RowsUploaded > 0)
                {
                    new ETLManager(uploadResult.StagingTableName).PrepareETLs();
                }

                if (!uploadResult.IsSuccessful)
                {
                    noInvalidFiles = false;
                }
                filesProcessed++;
            }

            if (noInvalidFiles && filesProcessed > 0)
            {
                // call master etl for a company
                ETLRunner.ConfigureConnection(config["DatabaseConnectionString"]);
                new ETLRunner().RunMasterETL(companyName);
            }

            Logger.MainLogger.LogInformation($"{folder} directory processing ended. {filesProcessed} file(s) processed", true);
        }

        public void Start()
        {
            try
            {
                timer.Start();
                heartbeatTimer.Start();
                Logger.MainLogger.LogInformation("Service started", true);
            }
            catch (Exception ex)
            {
                Logger.MainLogger.LogError($"Error while trying to start service... {ExceptionHelper.GetExceptionInfo(ex)}", true);
                Logger.MainLogger.StopLogger();
                throw;
            }
        }

        public void Stop()
        {
            isStopRequested = true;
            timer.Stop();
            heartbeatTimer.Stop();

            while (isIterationRunning)
            {
                System.Threading.Thread.Sleep(1000);
            }
            Logger.MainLogger.LogInformation("Service stopped", true);
            Logger.MainLogger.StopLogger();
        }
    }
}

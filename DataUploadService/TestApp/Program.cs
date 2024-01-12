using DataUploadService.Lib;
using DataUploadService.Lib.Logging;
using Microsoft.Extensions.Configuration;
using System;
using System.IO;

namespace TestApp
{
    class Program
    {
        static void Main(string[] args)
        {
            var env = Environment.GetEnvironmentVariable("ASPNETCORE_ENVIRONMENT");
            var builder = new ConfigurationBuilder()
                .AddJsonFile($"appsettings.json", false, true)
                .AddJsonFile($"appsettings.{env}.json", true, true)
                .AddEnvironmentVariables();

            var config = builder.Build();

            string folderPath = @"C:\Temp\ExcelUpload\EVA";
            string excelConnStringPattern = config["ExcelConnectionString"];
            string databaseConnString = config["DatabaseConnectionString"];
            string companyName = new DirectoryInfo(folderPath).Name;

            ExcelUploadManager.ConfigureConnections(excelConnStringPattern, databaseConnString);
            ETLManager.ConfigureConnection(databaseConnString);

            Logger.StartLogger(databaseConnString);

            Logger.LogInformation($"{folderPath} directory processing started");
            foreach (var excelFile in Directory.GetFiles(folderPath, "*.xlsx"))
            {
                var uploadResult = new ExcelUploadManager(excelFile).ProcessFile();

                if (uploadResult.IsSuccessful && uploadResult.RowsUploaded > 0)
                {
                    new ETLManager(uploadResult.StagingTableName).PrepareETLs();
                }
            }
            // call master etl for a company
            ETLRunner.ConfigureConnection(databaseConnString);
            new ETLRunner().RunMasterETL(companyName);

            Logger.LogInformation($"{folderPath} directory processing ended");
            Logger.StopLogger();
        }
    }
}

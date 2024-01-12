using DataUploadService.Lib.Emailing;
using MySql.Data.MySqlClient;
using System;
using System.Collections.Generic;
using System.Data;
using System.Text;

namespace DataUploadService.Lib.Logging
{
    public class Logger
    {
        private MySqlConnection _dbConnection;
        private string _databaseConnString;
        private long _executionId;
        private MySqlCommand _log_command;
        public static Logger MainLogger;

        public Logger(string databaseConnString)
        {
            _databaseConnString = databaseConnString;
            _executionId = DateTime.Now.Ticks;

            ConnectToDatabase();
        }

        private void ConnectToDatabase()
        {
            _dbConnection = new MySqlConnection(_databaseConnString);
            _dbConnection.Open();
        }

        private void InitializeCommand()
        {
            _log_command = new MySqlCommand("log_service_action", _dbConnection);
            _log_command.CommandType = CommandType.StoredProcedure;
            _log_command.Parameters.Add(new MySqlParameter("_execution_id", MySqlDbType.Int64) { Value = _executionId });
            _log_command.Parameters.Add(new MySqlParameter("_logged_info_type", MySqlDbType.VarChar));
            _log_command.Parameters.Add(new MySqlParameter("_log_message", MySqlDbType.VarChar));
        }

        public void StopLogger()
        {
            if (_dbConnection != null && _dbConnection.State != ConnectionState.Closed)
            {
                _dbConnection.Dispose();
            }
        }

        public void RefreshExecutionId()
        {
            _executionId = DateTime.Now.Ticks;
            PingConnection();
        }

        public void PingConnection()
        {
            _dbConnection.Ping();
        }

        public void LogInformation(string logMessage, bool sendEmail = false)
        {
            Log(LoggedInfoType.Information, logMessage);
            if (sendEmail)
            {
                EmailSender.SendEmail($"[{LoggedInfoType.Information.ToString().ToUpper()}]", logMessage);
            }
        }

        public void LogError(string logMessage, bool sendEmail = true)
        {
            Log(LoggedInfoType.Error, logMessage);
            if (sendEmail)
            {
                EmailSender.SendEmail($"[{LoggedInfoType.Error.ToString().ToUpper()}]", logMessage);
            }
        }

        private void Log(LoggedInfoType loggedInfoType, string logMessage)
        {
            if (_dbConnection == null || _dbConnection.State != ConnectionState.Open)
            {
                _dbConnection.Dispose();
                ConnectToDatabase();
            }

            StringBuilder logMessageTruncated = new StringBuilder(logMessage);
            logMessageTruncated.Length = 1024;

            InitializeCommand();
            _log_command.Parameters["_execution_id"].Value = _executionId;
            _log_command.Parameters["_logged_info_type"].Value = loggedInfoType;
            _log_command.Parameters["_log_message"].Value = logMessageTruncated;
            _log_command.ExecuteNonQuery();
            Console.WriteLine($"{DateTime.Now.ToString("yyyy-MM-dd hh:mm:ss.fff")} {loggedInfoType.ToString().ToUpper()}: {logMessage}");
        }
    }
}

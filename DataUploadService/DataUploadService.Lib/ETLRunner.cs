using DataUploadService.Lib.Logging;
using MySql.Data.MySqlClient;
using System.Data;

namespace DataUploadService.Lib
{
    public class ETLRunner
    {
        private static MySqlConnection _dbConnection;

        public static void ConfigureConnection(string databaseConnString)
        {
            _dbConnection = new MySqlConnection(databaseConnString);
        }
        public void RunMasterETL(string companyName)
        {
            using (_dbConnection)
            {
                _dbConnection.Open();
                MySqlCommand command = new MySqlCommand($"etl_{companyName}_master", _dbConnection);
                command.CommandType = CommandType.StoredProcedure;
                command.ExecuteNonQuery();
            }
            Logger.MainLogger.LogInformation($"Master ETL for {companyName} executed successfully");
        }
    }
}

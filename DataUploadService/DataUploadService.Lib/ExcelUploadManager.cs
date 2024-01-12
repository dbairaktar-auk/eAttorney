using DataUploadService.Lib.Emailing;
using DataUploadService.Lib.Logging;
using MySql.Data.MySqlClient;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Data;
using System.Data.Common;
using System.Data.OleDb;
using System.IO;
using System.Linq;
using System.Text;

namespace DataUploadService.Lib
{
    public class ExcelUploadManager
    {
        private static string _excelConnStringPattern;
        private static MySqlConnection _dbConnection;

        private readonly string _excelConnString;
        private readonly FileInfo _excelFileInfo;
        private readonly string _companyName;
        private string _stagingTableName;
        private DataTable _fileData;
        private List<MappingInfo> _mappingInfo;
        private List<string> _actualFieldsMapped;
        private string _archiveFilePath;
        private ExcelUploadResult _excelUploadResult;
        private int _userId;
        public ExcelUploadManager(string filePath, int userId)
        {
            _excelFileInfo = new FileInfo(filePath);
            _excelConnString = string.Format(_excelConnStringPattern, filePath);
            _fileData = new DataTable();
            _companyName = _excelFileInfo.Directory.Name;
            _actualFieldsMapped = new List<string>();
            _excelUploadResult = new ExcelUploadResult();
            _userId = userId;
        }

        public static void ConfigureConnections(string excelConnStringPattern, string databaseConnString)
        {
            _excelConnStringPattern = excelConnStringPattern;
            _dbConnection = new MySqlConnection(databaseConnString);
        }

        public ExcelUploadResult ProcessFile()
        {
            using (_dbConnection)
            {
                _dbConnection.Open();
                if (!StagingTableExistsAndHasMapping())
                {
                    _excelUploadResult.IsSuccessful = false;
                    MoveProcessedFile(_excelUploadResult.IsSuccessful);
                    SendUploadReport();
                    return _excelUploadResult;
                }
                TruncateStagingTable();

                // fill file data
                FillDataToMemory();
                AddCompanyName();
                AddUserId();

                // perform validation
                // insert data to staging table
                FillStagingTable();

                // logging and notification
                _excelUploadResult.IsSuccessful = _excelUploadResult.InvalidRecordsDetails.Count == 0 ? true : false;
                MoveProcessedFile(_excelUploadResult.IsSuccessful);
                SendUploadReport();
                InsertUploadLog();
            }
            _excelUploadResult.Message = $"File {_excelFileInfo.FullName} processed. {_excelUploadResult.RowsUploaded} rows uploaded to staging ({_excelUploadResult.InvalidRecordsDetails.Count} invalid)";
            return _excelUploadResult;
        }

        private void SendUploadReport()
        {
            string status;
            StringBuilder reportBody = new StringBuilder();
            reportBody.AppendLine($"File was archived to {_archiveFilePath}");

            if (_excelUploadResult.IsSuccessful)
            {
                status = "SUCCESS";
                reportBody.AppendLine($"File {_excelFileInfo.Name} processed successfully. {_excelUploadResult.RowsUploaded} rows uploaded");
            }
            else if (_excelUploadResult.InvalidRecordsDetails.Count > 0)
            {
                status = "ERROR";
                reportBody.AppendLine($"There were {_excelUploadResult.InvalidRecordsDetails.Count} invalid records in processed file:");
                foreach (string line in _excelUploadResult.InvalidRecordsDetails)
                {
                    reportBody.AppendLine(line);
                }
            }
            else
            {
                status = "ERROR";
                reportBody.AppendLine($"There were following errors processing the file {_excelFileInfo.Name}: {_excelUploadResult.Message}");
            }
            string reportSubject = $"[{status}] {_excelFileInfo.Name} upload";
            reportBody.Length = 4096;

            EmailSender.SendEmail(reportSubject, reportBody.ToString());
        }

        private void FillStagingTable()
        {
            using (MySqlDataAdapter dataAdapter = new MySqlDataAdapter($"SELECT * FROM {_stagingTableName} LIMIT 0;", _dbConnection))
            {
                DataTable dt = new DataTable();
                dataAdapter.Fill(dt);
                _mappingInfo = GetMappingInfo();

                foreach (var fieldMappingInfo in _mappingInfo)
                {
                    if (_fileData.Columns.Contains(fieldMappingInfo.SourceFieldName) && dt.Columns.Contains(fieldMappingInfo.TargetFieldName))
                    {
                        _actualFieldsMapped.Add(fieldMappingInfo.TargetFieldName);
                    }
                }
                if (_actualFieldsMapped.Count == 0)
                    return;

                foreach (DataRow row in _fileData.Rows)
                {
                    DataRow newRow = dt.NewRow();
                    foreach (var mappedTargetField in _actualFieldsMapped)
                    {
                        MappingInfo mappedFieldInfo = _mappingInfo.Where(m => m.TargetFieldName == mappedTargetField).First();
                        newRow[mappedTargetField] = row[mappedFieldInfo.SourceFieldName];
                    }

                    new ValidationManager().ValidateRow(ref newRow, _actualFieldsMapped, _mappingInfo, _excelUploadResult.RowsUploaded + 1);
                    if (Convert.ToBoolean(newRow["is_valid"]) == false)
                    {
                        _excelUploadResult.InvalidRecordsDetails.Add(newRow["validation_info"].ToString());
                    }
                    dt.Rows.Add(newRow);
                    _excelUploadResult.RowsUploaded++;
                }
                using (MySqlCommandBuilder cb = new MySqlCommandBuilder(dataAdapter))
                {
                    dataAdapter.Update(dt);
                }
            }
        }

        private List<MappingInfo> GetMappingInfo()
        {
            var mappingInfo = new List<MappingInfo>();
            string commandText = $"SELECT source_field_name, target_field_name, is_key_field, target_data_type, " +
                                 $"min_length, max_lenght, min_value, max_value, datetime_format, regex_pattern " +
                                 $"FROM excel_mapping " +
                                 $"WHERE source_file_name = '{_excelFileInfo.Name}' " +
                                 $"AND target_table_name = '{_stagingTableName}' " +
                                 $"AND company_name = '{_companyName}';";

            MySqlCommand command = new MySqlCommand(commandText, _dbConnection);
            using (var reader = command.ExecuteReader())
            {
                while (reader.Read())
                {
                    mappingInfo.Add(new MappingInfo()
                    {
                        SourceFieldName = reader[0].ToString(),
                        TargetFieldName = reader[1].ToString(),
                        IsKeyField = Convert.ToBoolean(reader[2]),
                        TargetDataType = reader[3] is DBNull ? string.Empty : reader[3].ToString(),
                        MinLength = reader[4] is DBNull ? 0 : Convert.ToInt32(reader[4]),
                        MaxLength = reader[5] is DBNull ? 0 : Convert.ToInt32(reader[5]),
                        MinValue = reader[6] is DBNull ? int.MinValue : Convert.ToInt32(reader[6]),
                        MaxValue = reader[7] is DBNull ? int.MaxValue : Convert.ToInt32(reader[7]),
                        DateTimeFormat = reader[8] is DBNull ? string.Empty : reader[8].ToString(),
                        RegexPattern = reader[9] is DBNull ? string.Empty : reader[9].ToString()
                    });
                }
            }
            mappingInfo.Add(new MappingInfo()
            {
                SourceFieldName = "company",
                TargetFieldName = "company",
                IsKeyField = true,
                TargetDataType = "string"
            });

            mappingInfo.Add(new MappingInfo()
            {
                SourceFieldName = "user_id",
                TargetFieldName = "user_id",
                IsKeyField = false,
                TargetDataType = "int",
                MinValue = int.MinValue,
                MaxValue = int.MaxValue
            });
            return mappingInfo;
        }

        private void TruncateStagingTable()
        {
            string commandText = $"TRUNCATE TABLE {_stagingTableName};";
            MySqlCommand command = new MySqlCommand(commandText, _dbConnection);
            command.ExecuteNonQuery();
        }

        private bool StagingTableExistsAndHasMapping()
        {
            string cmdCheckTableName = "SELECT DISTINCT target_table_name FROM excel_mapping " +
                                      $"WHERE company_name = '{_companyName}'" +
                                      $"AND source_file_name = '{_excelFileInfo.Name}'";
            MySqlCommand command = new MySqlCommand(cmdCheckTableName, _dbConnection);

            using (var reader = command.ExecuteReader())
            {
                int rowsCount = 0;
                if (!reader.HasRows)
                {
                    _excelUploadResult.Message = $"There is no staging table mapping defined for company_name = '{_companyName}' and source_file_name = '{_excelFileInfo.Name}";
                    return false;
                }

                while (reader.Read())
                {
                    rowsCount++;
                    _stagingTableName = reader["target_table_name"].ToString();
                }

                if (rowsCount > 1)
                {
                    _excelUploadResult.Message = $"There is more than 1 staging table mapping defined for company_name = '{_companyName}' and source_file_name = '{_excelFileInfo.Name}";
                    return false;
                }

                _excelUploadResult.StagingTableName = _stagingTableName;
                return true;
            }
        }

        private void AddCompanyName()
        {
            DataColumn companyCol = new DataColumn("company", typeof(string)) { DefaultValue = _companyName };
            _fileData.Columns.Add(companyCol);
        }

        private void AddUserId()
        {
            DataColumn userCol = new DataColumn("user_id", typeof(int));
            if (_userId > 0)
            {
                userCol.DefaultValue = _userId;
            }
            _fileData.Columns.Add(userCol);
        }

        private void FillDataToMemory()
        {
            DataTable dtExcelDataString = new DataTable(); // intermediate table, accepts all data as string values

            using (OleDbConnection excel_con = new OleDbConnection(_excelConnString))
            {
                excel_con.Open();
                string sheet1 = excel_con.GetOleDbSchemaTable(OleDbSchemaGuid.Tables, null).Rows[0]["TABLE_NAME"].ToString();
                DataTable schemaTable = excel_con.GetOleDbSchemaTable(OleDbSchemaGuid.Columns, new object[] { null, null, sheet1, null });
                string query = "SELECT * FROM [" + sheet1 + "]";

                using (OleDbDataAdapter oda = new OleDbDataAdapter(query, excel_con))
                {
                    oda.Fill(dtExcelDataString);
                }
                excel_con.Close();
            }

            for (int i = 0; i < dtExcelDataString.Columns.Count; i++) // create final data table with correct column names and string type for each column
            {
                string columnNameFromExcel = dtExcelDataString.Rows[0][i].ToString();
                string columnNameAuto = dtExcelDataString.Columns[i].ColumnName;
                _fileData.Columns.Add(string.IsNullOrEmpty(columnNameFromExcel) ? columnNameAuto : columnNameFromExcel,
                                        typeof(string));
            }
            dtExcelDataString.Rows.RemoveAt(0); // remove first row which is just a header names

            foreach (DataRow dr in dtExcelDataString.Rows) // move data to the final table
            {
                _fileData.Rows.Add(dr.ItemArray);
            }
        }

        private void MoveProcessedFile(bool success)
        {
            string processedFolder = Path.Combine(_excelFileInfo.Directory.FullName, "Processed");
            if (!Directory.Exists(processedFolder)) Directory.CreateDirectory(processedFolder);

            string okSubfolder = Path.Combine(processedFolder, "Ok");
            if (!Directory.Exists(okSubfolder)) Directory.CreateDirectory(okSubfolder);

            string errorSubfolder = Path.Combine(processedFolder, "Error");
            if (!Directory.Exists(errorSubfolder)) Directory.CreateDirectory(errorSubfolder);

            string actualSubfolder = success ? okSubfolder : errorSubfolder;
            string archiveFileSubfolder = Path.Combine(actualSubfolder, DateTime.Now.ToString("yyyy-MM-ddTHHmmss"));
            if (!Directory.Exists(archiveFileSubfolder)) Directory.CreateDirectory(archiveFileSubfolder);

            _archiveFilePath = Path.Combine(archiveFileSubfolder, _excelFileInfo.Name);
            File.Move(_excelFileInfo.FullName, _archiveFilePath);
        }

        private void InsertUploadLog()
        {
            List<string> targetFieds = _actualFieldsMapped.FindAll(f => f != "company");
            string fieldsJson = JsonConvert.SerializeObject(targetFieds);

            MySqlCommand command = new MySqlCommand("insert_upload_log", _dbConnection);
            command.CommandType = CommandType.StoredProcedure;
            command.Parameters.Add(new MySqlParameter("source_file", _excelFileInfo.Name));
            command.Parameters.Add(new MySqlParameter("target_table", _stagingTableName));
            command.Parameters.Add(new MySqlParameter("fields_json", fieldsJson));
            command.Parameters.Add(new MySqlParameter("archive_path", _archiveFilePath));
            command.ExecuteNonQuery();
        }
    }
}

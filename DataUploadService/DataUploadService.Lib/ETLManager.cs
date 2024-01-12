using DataUploadService.Lib.Logging;
using MySql.Data.MySqlClient;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace DataUploadService.Lib
{
    public class ETLManager
    {
        private static MySqlConnection _dbConnection;

        private readonly string _stagingTableName;
        private string _fullETLName;
        private string _shortETLName;
        private string _fullETLText;
        private string _shortETLText;
        private string[] _fieldsProvided;

        public ETLManager(string stagingTableName)
        {
            if (stagingTableName.StartsWith("staging_"))
            {
                _stagingTableName = stagingTableName;
            };
        }
        public static void ConfigureConnection(string databaseConnString)
        {
            _dbConnection = new MySqlConnection(databaseConnString);
        }

        public void PrepareETLs()
        {
            using (_dbConnection)
            {
                _dbConnection.Open();
                foreach (string fullETLName in GetETLsList())
                {
                    _fullETLName = fullETLName;
                    _shortETLName = _fullETLName.Substring(0, _fullETLName.Length - 5);
                    _fullETLText = GetFullETLText();
                    _fieldsProvided = GetProvidedFields();
                    CreateShortETL();
                }
            }
        }

        private List<string> GetETLsList()
        {
            List<string> resultList = new List<string>();
            string commandText = $"SELECT full_etl_name FROM etl_settings " +
                                 $"WHERE staging_table_name = '{_stagingTableName}'";

            MySqlCommand command = new MySqlCommand(commandText, _dbConnection);
            using (var reader = command.ExecuteReader())
            {
                while (reader.Read())
                {
                    resultList.Add(reader[0].ToString());
                }
            }
            return resultList;
        }

        private string GetFullETLText()
        {
            string commandText = "SELECT ROUTINE_DEFINITION " +
                                 "FROM information_schema.ROUTINES " +
                                 "WHERE ROUTINE_SCHEMA = 'eattorney_crm' " +
                                 "AND ROUTINE_TYPE = 'PROCEDURE' " +
                                $"AND ROUTINE_NAME = '{_fullETLName}';";

            MySqlCommand command = new MySqlCommand(commandText, _dbConnection);
            return command.ExecuteScalar().ToString();
        }

        private string[] GetProvidedFields()
        {
            string commandText = "SELECT ul.fields_provided " +
                                $"FROM {_stagingTableName} st " +
                                "JOIN upload_log ul ON st.upload_id = ul.upload_id LIMIT 1;";
            MySqlCommand command = new MySqlCommand(commandText, _dbConnection);

            JArray json = (JArray)JsonConvert.DeserializeObject(command.ExecuteScalar().ToString());
            return json.ToObject<string[]>();
        }

        private void CreateShortETL()
        {
            const string INSERT_BEGIN = "/*insert begin*/";
            const string INSERT_END = "/*insert end*/";
            const string SELECT_BEGIN = "/*select begin*/";
            const string SELECT_END = "/*select end*/";
            const string UPDATE_BEGIN = "/*update begin*/";
            const string UPDATE_END = "/*update end*/";

            //string[] insertParts = GetPartsArray(INSERT_BEGIN, INSERT_END);
            //string[] selectParts = GetPartsArray(SELECT_BEGIN, SELECT_END);
            string[] updateParts = GetPartsArray(UPDATE_BEGIN, UPDATE_END);

            //CleanInsertParts(ref insertParts, ref selectParts);
            CleanUpdateParts(ref updateParts);

            _shortETLText = _fullETLText;
            //ReplaceETLPart(INSERT_BEGIN, INSERT_END, insertParts);
            //ReplaceETLPart(SELECT_BEGIN, SELECT_END, selectParts);
            ReplaceETLPart(UPDATE_BEGIN, UPDATE_END, updateParts);
            ReplaceProcedureName();

            PushToDatabase();
            Logger.MainLogger.LogInformation($"ETL {_shortETLName} for table {_stagingTableName} prepared");
        }

        private string[] GetPartsArray(string beginMarker, string endMarker)
        {
            int beginIndex = _fullETLText.IndexOf(beginMarker, 0) + beginMarker.Length;
            int endIndex = _fullETLText.IndexOf(endMarker, 0);
            int length = endIndex - beginIndex - 1;
            string[] partsArray = _fullETLText.Substring(beginIndex, length).Split(new string[] { "\r\n" }, StringSplitOptions.RemoveEmptyEntries);

            for (int i = 0; i < partsArray.Length; i++)
            {
                partsArray[i] = partsArray[i].Trim();
                if (partsArray[i].EndsWith(","))
                {
                    partsArray[i] = partsArray[i].Remove(partsArray[i].Length - 1).Trim();
                }
            }
            return partsArray;
        }

        private void CleanUpdateParts(ref string[] updateParts)
        {
            for (int i = 0; i < updateParts.Length; i++)
            {
                bool remove = true;
                foreach (string field in _fieldsProvided)
                {
                    if (updateParts[i].Contains($"src.{field}"))
                    {
                        remove = false;
                        break;
                    }
                }

                if (remove)
                {
                    updateParts[i] = string.Empty;
                }
            }
            updateParts = updateParts.Where(v => v != string.Empty).ToArray();
        }

        private void CleanInsertParts(ref string[] insertParts, ref string[] selectParts)
        {
            for (int i = 0; i < selectParts.Length; i++)
            {
                bool remove = true;
                foreach (string field in _fieldsProvided)
                {
                    if (selectParts[i].Contains($"src.{field}"))
                    {
                        remove = false;
                        break;
                    }
                }

                if (remove)
                {
                    selectParts[i] = string.Empty;
                    insertParts[i] = string.Empty;
                }
            }
            selectParts = selectParts.Where(v => v != string.Empty).ToArray();
            insertParts = insertParts.Where(v => v != string.Empty).ToArray();
        }

        private void ReplaceETLPart(string beginMarker, string endMarker, string[] parts)
        {

            int beginIndex = _shortETLText.IndexOf(beginMarker, 0);
            int endIndex = _shortETLText.IndexOf(endMarker, 0) + endMarker.Length;
            int length = endIndex - beginIndex;

            _shortETLText = _shortETLText.Remove(beginIndex, length);
            _shortETLText = _shortETLText.Insert(beginIndex, string.Join(", \r\n", parts));
        }

        private void ReplaceProcedureName()
        {
            string oldStatement = $"SET @procedure_name = '{_fullETLName}'";
            string newStatement = $"SET @procedure_name = '{_shortETLName}'";
            _shortETLText = _shortETLText.Replace(oldStatement, newStatement);
        }
        private void PushToDatabase()
        {
            string scriptText = $"CREATE OR REPLACE PROCEDURE {_shortETLName}()\r\n{_shortETLText}";
            MySqlScript script = new MySqlScript(_dbConnection, scriptText);
            script.Delimiter = "$$";
            script.Execute();
        }
    }
}

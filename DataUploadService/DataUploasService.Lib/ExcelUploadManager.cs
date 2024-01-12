using System;
using System.Collections.Generic;
using System.Data;
using System.Data.OleDb;
using System.Text;

namespace DataUploadService.Lib
{
    class ExcelUploadManager
    {
        private readonly string _filePath;
        private DataTable _fileData;
        private string _connString;
        public ExcelUploadManager(string filePath, string connStringPattern)
        {
            _filePath = filePath;
            _connString = string.Format(connStringPattern, filePath);
        }

        public void ProcessFile()
        {
            // check for a staging table, truncate staging

            // fill file data
            FillDataToMemory();

            // perform validation
            // insert data to staging table
            // logging and notification
        }

        private void FillDataToMemory()
        {
            DataTable dtExcelDataString = new DataTable(); // intermediate table, accepts all data as string values

            using (OleDbConnection excel_con = new OleDbConnection(_connString))
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
    }
}

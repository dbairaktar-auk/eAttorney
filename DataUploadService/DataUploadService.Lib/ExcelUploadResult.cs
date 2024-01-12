using System;
using System.Collections.Generic;
using System.Text;

namespace DataUploadService.Lib
{
    public class ExcelUploadResult
    {
        public string StagingTableName { get; set; }
        public bool IsSuccessful { get; set; }
        public string Message { get; set; }
        public int RowsUploaded { get; set; }
        public List<string> InvalidRecordsDetails { get; set; } = new List<string>();
    }
}

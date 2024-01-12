using System;
using System.Collections.Generic;
using System.Text;

namespace DataUploadService.Lib
{
    public class MappingInfo
    {
        public string SourceFieldName { get; set; }
        public string TargetFieldName { get; set; }
        public bool IsKeyField { get; set; }
        public string TargetDataType { get; set; }
        public int MinLength { get; set; }
        public int MaxLength { get; set; }
        public int MinValue { get; set; }
        public int MaxValue { get; set; }
        public string DateTimeFormat { get; set; }
        public string RegexPattern { get; set; }
    }
}

using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;

namespace DataUploadService.Lib
{
    public class ValidationManager
    {
        public void ValidateRow(ref DataRow row, List<string> actualFieldsMapped, List<MappingInfo> mappingInfo, int rowNumber)
        {
            bool rowIsValid = true;
            StringBuilder rowValidationInfo = new StringBuilder($"Row number {rowNumber}: ");

            foreach (DataColumn column in row.Table.Columns)
            {
                MappingInfo fieldInfo = mappingInfo.Where(m => m.TargetFieldName == column.ColumnName).FirstOrDefault();
                bool isMapped = fieldInfo is null ? false : actualFieldsMapped.Contains(fieldInfo.TargetFieldName);

                FieldValidationResult fieldResult = ValidateField(row[column.ColumnName].ToString(), fieldInfo, isMapped);
                if (!fieldResult.IsValid)
                {
                    rowIsValid = false;
                    rowValidationInfo.Append($"{fieldResult.ValidationMessage};");
                }
            }

            rowValidationInfo.Length = 1000;
            row["is_valid"] = rowIsValid;
            row["validation_info"] = rowIsValid ? "Row is valid" : rowValidationInfo.ToString();
        }

        private FieldValidationResult ValidateField(string value, MappingInfo fieldInfo, bool isMapped)
        {
            try
            {
                if (fieldInfo is null)
                {
                    return new FieldValidationResult() { IsValid = true };
                }

                if (fieldInfo.IsKeyField && string.IsNullOrEmpty(value))
                {
                    return new FieldValidationResult()
                    {
                        IsValid = false,
                        ValidationMessage = $"Value for key field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}') was not provided"
                    };
                }

                if (isMapped && !string.IsNullOrEmpty(fieldInfo.TargetDataType) && !string.IsNullOrEmpty(value))
                {
                    switch (fieldInfo.TargetDataType.ToLower())
                    {
                        case "string":
                            return ValidateString(value, fieldInfo);
                        case "datetime":
                            return ValidateDateTime(value, fieldInfo);
                        case "int":
                            return ValidateInt(value, fieldInfo);
                        case "decimal":
                            return ValidateDecimal(value, fieldInfo);
                        default:
                            return new FieldValidationResult()
                            {
                                IsValid = false,
                                ValidationMessage = $"Data type '{fieldInfo.TargetDataType}' not recognized for field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}')"
                            };
                    }
                }

                return new FieldValidationResult() { IsValid = true };
            }
            catch (Exception ex)
            {
                return new FieldValidationResult()
                {
                    IsValid = false,
                    ValidationMessage = $"Error while trying to validate the value '{value}' for field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}')'.\n" +
                                        $"Exception message: {ExceptionHelper.GetExceptionInfo(ex)}"
                };
            }
        }

        private FieldValidationResult ValidateDecimal(string value, MappingInfo fieldInfo)
        {
            FieldValidationResult result = new FieldValidationResult();
            if (decimal.TryParse(value, out decimal res) && res >= fieldInfo.MinValue && res <= fieldInfo.MaxValue)
            {
                result.IsValid = true;
            }
            else
            {
                result.IsValid = false;
                result.ValidationMessage = $"Invalid decimal value in field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}')";
            }
            return result;
        }

        private FieldValidationResult ValidateInt(string value, MappingInfo fieldInfo)
        {
            FieldValidationResult result = new FieldValidationResult();
            if (int.TryParse(value, out int res) && res >= fieldInfo.MinValue && res <= fieldInfo.MaxValue)
            {
                result.IsValid = true;
            }
            else
            {
                result.IsValid = false;
                result.ValidationMessage = $"Invalid int value in field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}')";
            }
            return result;
        }

        private FieldValidationResult ValidateDateTime(string value, MappingInfo fieldInfo)
        {
            FieldValidationResult result = new FieldValidationResult();
            string format = string.IsNullOrEmpty(fieldInfo.DateTimeFormat) ? "M/d/yyyy" : fieldInfo.DateTimeFormat;
            if (DateTime.TryParseExact(value, format, CultureInfo.CurrentCulture, DateTimeStyles.None, out _))
            {
                result.IsValid = true;
            }
            else
            {
                result.IsValid = false;
                result.ValidationMessage = $"Invalid datetime value in field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}')";
            }
            return result;
        }

        private FieldValidationResult ValidateString(string value, MappingInfo fieldInfo)
        {
            FieldValidationResult result = new FieldValidationResult();
            if (fieldInfo.MinLength <= fieldInfo.MaxLength && fieldInfo.MaxLength > 0 && !string.IsNullOrEmpty(value))
            {
                if (!(value.Length >= fieldInfo.MinLength && value.Length <= fieldInfo.MaxLength))
                {
                    result.IsValid = false;
                    result.ValidationMessage = $"Invalid length in field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}')";
                    return result;
                }
                if (!string.IsNullOrEmpty(fieldInfo.RegexPattern) && !Regex.IsMatch(value, fieldInfo.RegexPattern))
                {
                    result.IsValid = false;
                    result.ValidationMessage = $"Value does not match pattern in field '{fieldInfo.TargetFieldName}' ('{fieldInfo.SourceFieldName}')";
                    return result;
                }
            }

            result.IsValid = true;
            return result;
        }

        private class FieldValidationResult
        {
            public bool IsValid { get; set; }
            public string ValidationMessage { get; set; } = string.Empty;
        }
    }
}
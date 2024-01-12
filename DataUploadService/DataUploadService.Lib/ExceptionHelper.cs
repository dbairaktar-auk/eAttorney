using System;
using System.Collections.Generic;
using System.Text;

namespace DataUploadService.Lib
{
    public static class ExceptionHelper
    {
        public static string GetExceptionInfo(Exception ex)
        {
            var res = new StringBuilder(ex.Message);
            var inner = ex.InnerException;
            while (inner != null)
            {
                res.AppendLine(inner.Message);
                inner = inner.InnerException;
            }

            res.AppendLine("StackTrace: ");
            res.AppendLine(ex.StackTrace);

            return res.ToString();
        }
    }
}

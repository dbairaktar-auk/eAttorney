using DataUploadService.Lib.Logging;
using FluentEmail.Core;
using FluentEmail.Smtp;
using System;
using System.Collections.Generic;
using System.Net;
using System.Net.Mail;
using System.Text;

namespace DataUploadService.Lib.Emailing
{
    public class EmailSender
    {
        private static string _host;
        private static int _port;
        private static bool _enableSsl;
        private static string _userName;
        private static string _password;
        private static string _from;
        private static string _emailTo;
        public static void Configure(string host, int port, bool enableSsl, string userName, string password, string from, string emailTo)
        {
            _host = host;
            _port = port;
            _enableSsl = enableSsl;
            _userName = userName;
            _password = password;
            _from = from;
            _emailTo = emailTo;

            SmtpClient client = new SmtpClient();
            client.Host = _host;
            client.Port = _port;
            client.EnableSsl = _enableSsl;
            client.DeliveryMethod = SmtpDeliveryMethod.Network;
            client.UseDefaultCredentials = false;
            client.Credentials = new NetworkCredential(_userName, _password);

            var sender = new SmtpSender(client);

            Email.DefaultSender = sender;
        }

        internal static void SendEmail(string subject, string body)
        {
            try
            {
                var email = Email.From(_userName, _from)
                                 .To(_emailTo)
                                 .Subject($"[{Environment.MachineName}] {subject}")
                                 .Body(body)
                                 .Send();
            }
            catch (Exception ex)
            {
                Logger.MainLogger.LogError($"Error sending email. Message: {ExceptionHelper.GetExceptionInfo(ex)}", false);
            }
        }
    }
}

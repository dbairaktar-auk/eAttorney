using Microsoft.Extensions.Configuration;
using System;
using Topshelf;

namespace DataUploadService
{
    class Program
    {
        static void Main(string[] args)
        {
            var env = Environment.GetEnvironmentVariable("ASPNETCORE_ENVIRONMENT");
            var builder = new ConfigurationBuilder()
                .AddJsonFile($"appsettings.json", false, true)
                .AddJsonFile($"appsettings.{env}.json", true, true)
                .AddEnvironmentVariables();

            var config = builder.Build();

            var rc = HostFactory.Run(x =>
            {
                x.Service<DataUpload>(s =>
                {
                    s.ConstructUsing(dataUpload => new DataUpload(config));
                    s.WhenStarted(dataUpload => dataUpload.Start());
                    s.WhenStopped(dataUpload => dataUpload.Stop());
                });
                x.RunAsLocalSystem();

                x.SetServiceName("DataUploadService");
                x.SetDisplayName("Data Upload Service");
                x.SetDescription("Processes source Excel files to the database");
            });

            var exitCode = (int)Convert.ChangeType(rc, rc.GetTypeCode());
            Environment.ExitCode = exitCode;
        }
    }
}

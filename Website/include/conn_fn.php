<?php
if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
    header( 'HTTP/1.0 404 Not Found', TRUE, 404 );
    die();
}

function getDBConnection(){
    $con=mysqli_connect("localhost","web_app","<password>","eattorney_crm");
    // Check connection
    if (mysqli_connect_errno())
    {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    return null;
    }
    return $con;
}

function getFilesUploadDir()
{
    return 'E:\ftp\SourceFiles';
}
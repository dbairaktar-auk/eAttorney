<?php
include_once('include\functions.php');
session_start();
updateSessionLifetime();
session_unset();     // unset $_SESSION variable for the run-time 
session_destroy();   // destroy session data in storage
redirect('index.php');
?>
<?php
    // Add mysqli connection details below...
    $mySqlGridConnection = new mysqli('localhost', 'UserName', 'Password','Database');
    if($mySqlGridConnection->connect_error) die('Connect Error (' . $mySqlGridConnection->connect_errno . ') '. $mySqlGridConnection->connect_error);
?>
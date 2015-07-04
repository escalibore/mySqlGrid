<?php
    // Add mysqli connection details below...
    $mySqlGridConnection = new mysqli('localhost', 'UserName', 'Password','Database');
    if($mysqli->connect_error) die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
?>
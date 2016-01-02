<?php
// Add mysqli connection details below...

echo "You have not entered database connection details...<br>";  // Remove this after entering db connection details.

//Sample:
//$mySqlGridConnection = new mysqli('localhost', 'UserName', 'Password', 'Database');
//if($mySqlGridConnection->connect_error) die('Connect Error (' . $mySqlGridConnection->connect_errno . ') '. $mySqlGridConnection->connect_error);

//Below can be used if you need to specify the database dynamically.  In this case you would include parameter: "database".
//if($optionsArray['database']) mysqli_select_db($mySqlGridConnection, $optionsArray['database']);
?>

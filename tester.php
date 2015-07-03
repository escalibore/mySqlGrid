<?php
    $mysqli = new mysqli('localhost', 'xxxx', 'xxxx','xxxxx');
    if($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);    
    }
    $mySqlGridArray = array(
        'sql' => "
        SELECT t.Name Track, al.Title Album, a.Name Artist, Composer, g.Name Genre FROM Track t
        LEFT JOIN Album al ON al.AlbumId = t.AlbumId
        LEFT JOIN Artist a ON a.ArtistId = al.ArtistId
        LEFT JOIN Genre g ON g.GenreId = t.GenreId",
        'connection' => $mysqli,
        'lineCount' => 20
    );
    include 'mysqlgrid/mysqlgrid.php';
?>
<body style='margin:0.5%;'>
<div style='text-align:center;'><h2>Demo of <span style='color:#337AB7;'>MySqlGrid</span></h2></div>
<div id="mySqlGridTable" style='text-align: center;'></div>
<div id="mySqlGridPagination" style='text-align: center;'></div>
<div style="text-align: center;"><a href="https://github.com/escalibore/mySqlGrid">Download MySqlGrid from GitHub</a></div>
</body> 
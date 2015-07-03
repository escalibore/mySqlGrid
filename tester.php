<?php
    $mysqli = new mysqli('localhost', 'tberthol_chinook', 'password','tberthol_chinook');
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
    );
    include 'mysqlgrid/mysqlgrid.php';
?>
<body>
<div id="mySqlGridTable"></div>
<div id="mySqlGridPagination"></div>
</body>
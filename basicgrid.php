<html>
    <head> 
        <?php
            $mySqlGridOptions = array(
                'sql' => "
                SELECT TrackId, t.Name Track, al.Title Album, a.Name Artist, Composer, g.Name Genre FROM Track t
                LEFT JOIN Album al ON al.AlbumId = t.AlbumId
                LEFT JOIN Artist a ON a.ArtistId = al.ArtistId
                LEFT JOIN Genre g ON g.GenreId = t.GenreId
                //'lineCount' => 20,
                //'hideColumns' => array('TrackId'),
                //'hideSelects' => array('Composer'),
                //'noSelects' => true,
                //'noPaginate' => true,
                //'alwaysPaginate' => true,
                //'gridControlKey' => 'Track',
                //'gridControlHtml' => "<img onClick=\"view('gridControlKey');\" src='mysqlgrid/view.png'><img onClick=\"edit('gridControlKey');\" src='mysqlgrid/update.png'><img onClick=\"kill('gridControlKey');\" src='mysqlgrid/delete.png'>",
            );
            include ($mySqlGridOptions['includePath'] ? $mySqlGridOptions['includePath'] : 'mysqlgrid/') ."mysqlgrid.php";
        ?> 
    </head>
    <body style='margin:0.5%;'><br>
        <div id="mySqlGridTable" style='text-align: center;'></div>
        <div id="mySqlGridPagination" style='text-align: center;'></div>
    </body>
</html>






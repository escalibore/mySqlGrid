<html>
    <head> 
    <script>window.jQuery || document.write('<script src="mysqlgrid/jquery-2.1.3.min.js">\x3C/script>')</script>
        <?php
            $mySqlGridOptions = array(
                'sql' => "
                SELECT TrackId, t.Name Track, al.Title Album, a.Name Artist, Composer, g.Name Genre FROM Track t
                LEFT JOIN Album al ON al.AlbumId = t.AlbumId
                LEFT JOIN Artist a ON a.ArtistId = al.ArtistId
                LEFT JOIN Genre g ON g.GenreId = t.GenreId
                ORDER BY Track DESC",
                'lineCount' => 20,
                'hideColumns' => array('TrackId'),
                //  'hideSelects' => array('Composer'),
                // 'noSelects' => true,
                //'noPaginate' => true,
                //'alwaysPaginate' => true,
                'includePath' => 'mysqlgrid/',
                'gridPrimaryKey' => 'Track',
                'controlHtml' => "<img onClick=\"view('gridPrimaryKey');\" src='mysqlgrid/view.png'><img onClick=\"edit('gridPrimaryKey');\" src='mysqlgrid/update.png'><img onClick=\"kill('gridPrimaryKey');\" src='mysqlgrid/delete.png'>",
                //   'includePath' => '../tcs/mysqlgrid'
            );
            
            include "$mySqlGridOptions[includePath]mysqlgrid.php";
        ?> 
    </head>
    <body style='margin:0.5%;'><br>
        <div style='text-align:center;'><h2>Demo of <span style='color:#337AB7;'>MySqlGrid</span></h2></div>
        <div id="mySqlGridTable" style='text-align: center;'></div>
        <div id="mySqlGridPagination" style='text-align: center;'></div>
        <div style="text-align: center;"><a href="https://github.com/escalibore/mySqlGrid">Download MySqlGrid from GitHub</a></div>
        <div id="dialog" title="mySqlGrid Control Dialog"></div>
 
 
 
 
 
   
    </body>
    
    <script xsrc="mysqlgrid/jquery-2.1.3.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script xsrc="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script>
        function view(trackTitle) {
            document.getElementById("dialog").innerHTML = "<p>This is a demonstration of mySqlGrid's ability to process rows selected by a user.</p><p>You selected to <b>VIEW</b> the row where Track = <b>\"" + trackTitle + "\"</b></p>";
            $( "#dialog" ).dialog( "open" );
        }  
        function edit(trackTitle) {
            document.getElementById("dialog").innerHTML = "<p>This is a demonstration of mySqlGrid's ability to process rows selected by a user.</p><p>You selected to <b>EDIT</b> the row where Track = <b>\"" + trackTitle + "\"</b></p><p>Instead of displaying this popup, you as a web developer can present the user with an editable form, link to another page, etc.</p>";
            $( "#dialog" ).dialog( "open" );
        }  
        function kill(trackTitle) {
            document.getElementById("dialog").innerHTML = "<p>This is a demonstration of mySqlGrid's ability to process rows selected by a user.</p><p>You selected to <b>DELETE</b> the row where Track = <b>\"" + trackTitle + "\"</b></p><p>(We won't actually delete this row for the demo.)</p>";
            $( "#dialog" ).dialog( "open" );
        }
        $(function() {
            $( "#dialog" ).dialog({
                autoOpen: false,
                minWidth: 800
            });

            $( "#opener" ).click(function() {
                document.getElementById("dialog").innerHTML = "<p>Paragraph changed!</p>";   
                $( "#dialog" ).dialog( "open" );
            });
        });  
    </script>
</html>






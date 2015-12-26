<?php
namespace mysqlgridspace;

class Mysqlgridmain
{
    public function __construct($mySqlGridOptions)
    {
        if (isset($mySqlGridOptions['includePath'])) $mySqlGridPath = $mySqlGridOptions['includePath']; else $mySqlGridPath = 'vendor/mysqlgrid/mysqlgrid/src/';
        if (empty($mySqlGridOptions['gridId'])) $mySqlGridOptions['gridId'] = 'mySqlGridTable'; // default if not specified
        if (empty($mySqlGridOptions['paginationId'])) $mySqlGridOptions['paginationId'] = 'mySqlGridPagination'; // default if not specified
        $lineCount = $mySqlGridOptions['lineCount'] ? $mySqlGridOptions['lineCount'] : 25; // default if not specified
        echo "<div style='margin-left:30%' id='mySqlGridLoading$mySqlGridOptions[gridId]'>Loading... <img class='mySqlGridSpinner' src='{$mySqlGridPath}images/725.GIF'></div>";
        $mySqlGridOptionsEncode = rawurlencode(base64_encode(serialize($mySqlGridOptions)));
        $mySqlGridData = 'mySqlGridOptions=' . $mySqlGridOptionsEncode;
        $postString = "'mySqlGridData':'$mySqlGridData'";
        include_once "$mySqlGridPath" . 'mysqlgridscript.inc';
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#<?php echo $mySqlGridOptions['paginationId']; ?>").css("display", "none"); // prevents pagination bar from appearing before table.
                $("#<?php echo $mySqlGridOptions['gridId']; ?>").load('<?php echo $mySqlGridPath; ?>mysqlgridajax.php', {<?php echo $postString; ?>});  //initial page number to load
                $("#<?php echo $mySqlGridOptions['paginationId']; ?>").bootpag({
                    total: 1, // total number of pages
                    page: 1, //initial page
                    maxVisible: 10 //maximum visible links
                }).on("page", function (e, num) {
                    $('#mySqlGridSpinner<?php echo $mySqlGridOptions['gridId']; ?>').show();
                    e.preventDefault();
                    $("#<?php echo $mySqlGridOptions['gridId']; ?>").load('<?php echo $mySqlGridPath; ?>mysqlgridajax.php', {'page': num, <?php echo $postString; ?>});
                });
            });
        </script>
        <?php
    }
}

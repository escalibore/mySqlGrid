<?php
require __DIR__ . '/vendor/autoload.php';

$mysqlgrid = new mysqlgridspace\Mysqlgridmain([
    'sql' => "select * from Mytable",
    'lineCount' => 15,
]);
?>
<div style='text-align:center;'>
    <div style="display: inline-block; text-align: center;">
        <div id="mySqlGridTable"></div>
        <div id="mySqlGridPagination" style='text-align: center;'></div>
    </div>
</div>







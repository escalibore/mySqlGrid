<?php
require __DIR__ . '/vendor/autoload.php';

$mysqlgrid1 = new mysqlgridspace\Mysqlgridmain([
    'sql' => "select * from Mytable1",
    'gridId' => 'grid1';
    'paginationId' => 'pagination1';
]);

$mysqlgrid2 = new mysqlgridspace\Mysqlgridmain([
    'sql' => "select * from Mytable2",
    'gridId' => 'grid2';
    'paginationId' => 'pagination2';
]);
?>

<div style='text-align:center;'>
    <div style="display: inline-block; text-align: center;">
        <div id="grid1"></div>
        <div id="pagination1" style='text-align: center;'></div>
    </div>
</div>

<div style='text-align:center;'>
    <div style="display: inline-block; text-align: center;">
        <div id="grid2"></div>
        <div id="pagination2" style='text-align: center;'></div>
    </div>
</div>

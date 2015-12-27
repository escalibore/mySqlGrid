<?php
if (!empty($_POST['page'])) $page_number = $_POST['page']; else $page_number = 1;
$mySqlGridParams = array();
parse_str($_POST['mySqlGridData'], $mySqlGridParams);
$optionsArray = unserialize(base64_decode(rawurldecode($mySqlGridParams['mySqlGridOptions'])));
$gridId = $optionsArray['gridId'];
$paginationId = $optionsArray['paginationId'];
if (empty($mySqlGridConnection)) require 'dbconnect.php';
$repack = rawurlencode(base64_encode(serialize($optionsArray)));
$lineCount = $optionsArray['lineCount'] ? $optionsArray['lineCount'] : 25;
if (!empty($optionsArray['includePath'])) $mySqlGridPath = $optionsArray['includePath']; else $mySqlGridPath = 'vendor/mysqlgrid/mysqlgrid/src/';
$baseSql = str_replace(PHP_EOL, ' ', $optionsArray['sql']);
$position = (($page_number - 1) * $lineCount);
$mySqlGridSql = "SELECT * FROM ( " . $baseSql . " ) AS fullSet ";
if (empty($mySqlGridParams["mySqlGridReset$gridId"])) { // Ignore filters if user selected "Reset"
    $mySqlGridSql .= " WHERE 1=1 ";
    foreach ($mySqlGridParams as $key => $val) {
        if (strstr($key, "mySqlGridFilter$gridId") && $val && $val != '(Filtered)') {
            $columnNameArray = explode("mySqlGridFilter$gridId", $key);
            if ($columnNameArray[1]) {
                $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection, $columnNameArray[1]);
                $columnNameArray[1] = str_replace('mySqlGridSpace', ' ', $columnNameArray[1]); // work around because parse_str sets blanks to underscore
                $val = trim(mysqli_real_escape_string($mySqlGridConnection, $val));
                if (strpos($val, '=') === 0) { // if preceded by =, >, or < sign we don't do a "LIKE".
                    $stripArray = explode('=', $val);
                    $mySqlGridSql .= " AND `$columnNameArray[1]` = '$stripArray[1]' ";
                } elseif (strpos($val, '>=') === 0) {
                    $stripArray = explode('>=', $val);
                    $mySqlGridSql .= " AND `$columnNameArray[1]` >= '$stripArray[1]' ";
                } elseif (strpos($val, '<=') === 0) {
                    $stripArray = explode('<=', $val);
                    $mySqlGridSql .= " AND `$columnNameArray[1]` <= '$stripArray[1]' ";
                } elseif (strpos($val, '>') === 0) {
                    $stripArray = explode('>', $val);
                    $mySqlGridSql .= " AND `$columnNameArray[1]` > '$stripArray[1]' ";
                } elseif (strpos($val, '<') === 0) {
                    $stripArray = explode('<', $val);
                    $mySqlGridSql .= " AND `$columnNameArray[1]` < '$stripArray[1]' ";
                } else $mySqlGridSql .= " AND `$columnNameArray[1]` LIKE '%$val%' ";
            }
        }
        if ($val && strstr($key, "mySqlGridDateFilterGe$gridId")) {
            $columnNameArray = explode("mySqlGridDateFilterGe$gridId", $key);
            if ($columnNameArray[1]) {
                $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection, $columnNameArray[1]);
                $columnNameArray[1] = str_replace('mySqlGridSpace', ' ', $columnNameArray[1]); // work around because parse_str sets blanks to underscore
                $val = mysqli_real_escape_string($mySqlGridConnection, $val);
                $mySqlGridSql .= " AND `$columnNameArray[1]` >= '$val' ";
            }
        }
        if ($val && strstr($key, "mySqlGridDateFilterLe$gridId")) {
            $columnNameArray = explode("mySqlGridDateFilterLe$gridId", $key);
            if ($columnNameArray[1]) {
                $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection, $columnNameArray[1]);
                $columnNameArray[1] = str_replace('mySqlGridSpace', ' ', $columnNameArray[1]); // work around because parse_str sets blanks to underscore
                $val = mysqli_real_escape_string($mySqlGridConnection, $val);
                $mySqlGridSql .= " AND `$columnNameArray[1]` <= '$val' ";
            }
        }
    }
    if (!empty($mySqlGridParams["mySqlGridYesPages$gridId"])) {
        $mySqlGridParams["mySqlGridNoPages$gridId"] = 0;
        ?>
        <script>
            $("#mySqlGridPagination<?php echo $gridId; ?>").show();
        </script>
        <?php
    }
} else {
    $mySqlGridParams['mySqlGridSort'] = '';
    $mySqlGridParams['mySqlGridDesc'] = '';
    if ($mySqlGridParams["mySqlGridNoPages$gridId"]) {
        $mySqlGridParams["mySqlGridNoPages$gridId"] = '';
        ?>
        <script>
            $("#<?php echo $paginationId; ?>").show();
        </script>
        <?php
    }
}
if (!empty($mySqlGridParams["mySqlGridSelect$gridId"])) {
    $selectArray = array();
    $selectColumn = str_replace('mySqlGridSpace', ' ', mysqli_real_escape_string($mySqlGridConnection, $mySqlGridParams["mySqlGridSelect$gridId"]));
    $selectSql = "SELECT DISTINCT `$selectColumn` AS SelectVal FROM ( " . $mySqlGridSql . " ) AS fullSet2 WHERE `$selectColumn` IS NOT NULL ORDER BY `$selectColumn` ";
    $selectResults = $mySqlGridConnection->query($selectSql) or die($mySqlGridConnection->error . " line:" . __LINE__ . " sql:$selectSql");
    while ($selectRow = $selectResults->fetch_array(MYSQLI_ASSOC)) {
        $selectArray[] = htmlspecialchars($selectRow['SelectVal']);
    }
}
if (!empty($mySqlGridParams['mySqlGridSort']) && !$mySqlGridParams["mySqlGridReset$gridId"]) $mySqlGridSql .= " ORDER BY `$mySqlGridParams[mySqlGridSort]` $mySqlGridParams[mySqlGridDesc] ";
elseif (!empty($optionsArray['defaultOrderBy'])) $mySqlGridSql .= " $optionsArray[defaultOrderBy] ";
if (empty($mySqlGridParams["mySqlGridNoPages$gridId"]) && !(isset($optionsArray['noPaginate']) && $optionsArray['noPaginate'] == true)) $mySqlGridSql .= " LIMIT $position, $lineCount ";
else {
    ?>
    <script>
        $("#<?php echo $paginationId; ?>").hide();
    </script>
    <?php
}
$mySqlGridSql = preg_replace("/SELECT/i", "SELECT SQL_CALC_FOUND_ROWS ", $mySqlGridSql, 1);
$mySqlGridSql = htmlspecialchars_decode($mySqlGridSql); // for the case where & character is part of a column value
$results = $mySqlGridConnection->query($mySqlGridSql) or die($mySqlGridConnection->error . " line:" . __LINE__ . " sql:$mySqlGridSql");
$resultsRowCount = $mySqlGridConnection->query("SELECT FOUND_ROWS()") or die($mySqlGridConnection->error . " line:" . __LINE__ . " sql:'SELECT FOUND_ROWS()'");
$foundRowsArray = $resultsRowCount->fetch_row();
$totalRows = $foundRowsArray[0];
$pages = ceil($totalRows / $lineCount);
$rowCount = $results->num_rows;
$startRow = $position + 1;
$offSet = $position + $rowCount;
echo "
    <div class='mySqlGridWrapper'>
    <div class='mySqlGridTop' id='mySqlGridTop'>
    $totalRows Rows Found";
if ($totalRows > 2) echo " (showing: $startRow - $offSet)";
echo "&nbsp;&nbsp; <img class='mySqlGridSpinner$gridId' id='mySqlGridSpinner$gridId' src='{$mySqlGridPath}images/725.GIF'>";
if (empty($optionsArray['noToolTip'])) {
    ?>
    <div class='mySqlGridbuttonArea' style="position: relative; top:3px; margin-left: 14px;">
        <a href="#" class="tooltip">
            <img src="<?php echo "$mySqlGridPath"; ?>images/questionmark2.png"/>
            <span>
                Click a column header to change the sort order.<br>Type a value into an input area to perform a "LIKE" search.<br>Preceding an input value with "=" performs an exact match.<br>You can also precede an input value with &gt;, &lt;, &gt;=, or &lt;=.
            </span>
        </a>
    </div>
    <?php
}
if (empty($optionsArray['noReport'])) {
    ?>
    <div class='mySqlGridbuttonArea'>
        <button onclick="printableView()">Printable</button>
    </div>
    <?php
}
if (!empty($mySqlGridParams["mySqlGridNoPages$gridId"]) && $pages > 1)
    echo "<div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridYesPages$gridId\").value=\"1\"; mySqlGridUpdate(\"$gridId\",\"$paginationId\");'>Paginate</button>&nbsp</div>";
elseif ($pages > 1 && !($optionsArray['noPaginate'] == true) && !($optionsArray['alwaysPaginate'] == true)) echo "
        <div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridNoPages$gridId\").value=\"1\";  mySqlGridUpdate(\"$gridId\",\"$paginationId\");'>No Pagination</button>&nbsp</div>";
if (empty($optionsArray['noReset'])) echo "<div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridReset$gridId\").value=\"1\";  mySqlGridUpdate(\"$gridId\",\"$paginationId\");'>Reset</button>&nbsp;</div>";
echo "</div>";
$columns = array();
if (!empty($optionsArray['hideColumns'])) {
    while ($finfo = $results->fetch_field()) if (!in_array($finfo->name, $optionsArray['hideColumns'])) $columns[$finfo->name] = $finfo->type;
} else {
    while ($finfo = $results->fetch_field()) $columns[$finfo->name] = $finfo->type;
}

echo "
    <form name='mySqlGridForm$gridId' id='mySqlGridForm$gridId' method='post' onSubmit='return false'>
    <input type='hidden' name='mySqlGridSort' value=\"" . (!empty($mySqlGridParams['mySqlGridSort']) ? $mySqlGridParams['mySqlGridSort'] : '') . "\">
    <input type='hidden' name='mySqlGridDesc' value=\"" . (!empty($mySqlGridParams['mySqlGridDesc']) ? $mySqlGridParams['mySqlGridDesc'] : '') . "\">
    <input type='hidden' name='sqlGridBaseSql' value=\"" . (!empty($sqlGridBaseSql) ? $sqlGridBaseSql : '') . "\">
    <input type='hidden' name='mySqlGridOptions' value=\"" . $repack . "\">
    <input type='hidden' name='mySqlGridSelect$gridId' id='mySqlGridSelect$gridId' value=\"\">
    <input type='hidden' name='mySqlGridPageCnt$gridId' id='mySqlGridPageCnt$gridId' value=\"$pages\">
    <input type='hidden' name='mySqlGridReset$gridId' id='mySqlGridReset$gridId' value=\"\">
    <input type='hidden' name='mySqlGridNoPages$gridId' id='mySqlGridNoPages$gridId' value=\"" . (!empty($mySqlGridParams["mySqlGridNoPages$gridId"]) ? $mySqlGridParams["mySqlGridNoPages$gridId"] : '') . "\">
    <input type='hidden' name='mySqlGridYesPages$gridId' id='mySqlGridYesPages$gridId' value=\"\">
    <input type='hidden' name='mySqlGridFormCalled$gridId' id='mySqlGridFormCalled$gridId' value=\"1\">";
if ($totalRows || $mySqlGridParams["mySqlGridFormCalled$gridId"]) {
    echo "
        <table class='mySqlGridTable'>
        <tr>";
    foreach ($columns as $column => $type) {  // build header row
        echo "
        <th colspan='2' class='mySqlGridHeader'
        onclick=\"document.mySqlGridForm$gridId.mySqlGridSort.value='$column'; document.mySqlGridForm$gridId.mySqlGridDesc.value='" .
            ((!empty($mySqlGridParams['mySqlGridSort']) && $mySqlGridParams['mySqlGridSort'] == $column && !$mySqlGridParams['mySqlGridDesc']) ? "desc" : "") . "'; mySqlGridUpdate('$gridId','$paginationId'); return false;\">";
        if (!empty($mySqlGridParams['mySqlGridSort']) && $column == $mySqlGridParams['mySqlGridSort']) { //special styling for sort icons
            echo "
                <div style='display:table; margin:auto;'>
                <div style='display:table-cell; vertical-align:middle;'>$column</div>
                <div style='display:table-cell; vertical-align:middle;'>";
            if ($mySqlGridParams['mySqlGridDesc']) echo "<div class='mySqlGridSortIcon'><span class='mySqlGridIconNorth ui-icon ui-icon-triangle-1-n ui-state-disabled'></span><span class='mySqlGridIconSouth ui-icon ui-icon-triangle-1-s'></span></div>";
            else echo "<div class='mySqlGridSortIcon'><span class='mySqlGridIconNorth ui-icon ui-icon-triangle-1-n '></span><span class='mySqlGridIconSouth ui-icon ui-icon-triangle-1-s ui-state-disabled'></span></div>";
            echo "</div></div>";
        } else echo "$column";
        echo "</th>";
    }
    echo "</tr>";
    echo "<tr class='mySqlGridSearchRow'>";
    // echo "<tr id='mySqlGridSearchRow' class='mySqlGridSearchRow'>";
    if (empty($optionsArray['noSearch'])) {
        //  if (!$optionsArray['noSearch'] == true) {
        foreach ($columns as $column => $type) { // build search row
            $column = str_replace(' ', 'mySqlGridSpace', htmlspecialchars($column)); // have to convert blanks to 'mySqlGridSpace' because parse_str changes blanks to underscores.  We convert back in code above.
            if (!empty($mySqlGridParams["mySqlGridSelect$gridId"]) && $mySqlGridParams["mySqlGridSelect$gridId"] == $column) { // User requested a drop down list
                $selectId = "mySqlGridFilter$gridId{$column}";
                echo "<td colspan='2' onChange='mySqlGridUpdate(\"$gridId\",\"$paginationId\");'><select name='mySqlGridFilter$gridId{$column}' id='mySqlGridFilter$gridId{$column}'><option value=''></option>";
                foreach ($selectArray as $optionVal) {
                    echo "<option value=\"=$optionVal\">$optionVal</option>";
                }
                echo "</select></td>";
            } else {
                $val = ''; //initialize
                if (empty($mySqlGridParams["mySqlGridReset$gridId"])) {
                    if (!empty($mySqlGridParams["mySqlGridDateFilterGe$gridId{$column}"]) || !empty($mySqlGridParams["mySqlGridDateFilterLe$gridId{$column}"])) $val = '(Filtered)';
                    else {
                        $postVal = "mySqlGridFilter$gridId$column";
                        //$val = htmlspecialchars($mySqlGridParams[$postVal]);
                        //$postVal = "mySqlGridFilter$gridId$column";
                        if (!empty($mySqlGridParams[$postVal])) $val = htmlspecialchars($mySqlGridParams[$postVal]);
                    }
                }
                echo "
                    <td class='mySqlGridSearchCol'><input class='mySqlGridSearchInput' value=\"$val\" type='text' name='mySqlGridFilter$gridId{$column}' id='mySqlGridFilter$gridId{$column}' onBlur='mySqlGridUpdate(\"$gridId\",\"$paginationId\");' onKeyPress='if(event.keyCode == 13) mySqlGridUpdate(\"$gridId\",\"$paginationId\");'></td>";
                if ($type == 12 || $type == 10) {
                    echo "<td class='mySqlGridSelectCol' onClick=\"mySqlGridDate$gridId$column();\"><img src='{$mySqlGridPath}images/calendar3.gif'></td>";
                } elseif ((empty($optionsArray['hideSelects']) || !in_array(str_replace('mySqlGridSpace', ' ', $column), $optionsArray['hideSelects'])) && empty($optionsArray['noSelects']))
                    //         } elseif (!in_array(str_replace('mySqlGridSpace', ' ', $column), $optionsArray['hideSelects']) && !($optionsArray['noSelects'] == true))
                    echo "<td class='mySqlGridSelectCol' onClick='document.getElementById(\"mySqlGridSelect$gridId\").value=\"$column\";  mySqlGridUpdate(\"$gridId\",\"$paginationId\");'><img src='{$mySqlGridPath}images/icon_dropdown2.gif'></td>";
                else echo "<td></td>";
            }
        }
    }
    echo "</tr>";
    while ($row = $results->fetch_array(MYSQLI_ASSOC)) {
        echo "<tr>";
        foreach ($columns as $column => $type) {
            if ($row[$column] && !stripos($row[$column], ' href') && !stripos($row[$column], 'img')) $row[$column] = htmlspecialchars($row[$column]); // allow html for anchor or img.
            echo "<td class='mySqlGridDataCell' colspan='2'>$row[$column]</td>";
        }
        if (!empty($optionsArray['gridControlHtml'])) {
            $replacementString = str_replace('gridControlKey', rawurlencode(addslashes($row[$optionsArray['gridControlKey']])), $optionsArray['gridControlHtml']); //Replaces primary key as specified in $mySqlGridOptions with the gridControlHtml.
            //                $replacementString = str_replace('gridControlKey',addslashes($row[$optionsArray['gridControlKey']]),$optionsArray['gridControlHtml']); //Replaces primary key as specified in $mySqlGridOptions with the gridControlHtml.
            echo "<td class='mySqlGridControlCell'>$replacementString</td>";
        }
        echo "</tr>";
    }
    echo "
        </table>";
}
echo "<input type='submit' class='mySqlGridSubmit'>";
foreach ($columns as $column => $type) {
    if (($type == 12 || $type == 10) && !$optionsArray['noSearch']) {
        $column = str_replace(' ', 'mySqlGridSpace', htmlspecialchars($column));
        if (!$mySqlGridParams["mySqlGridReset$gridId"]) {
            $postVal = "mySqlGridDateFilterGe$gridId$column";
            $geVal = htmlspecialchars($mySqlGridParams[$postVal]);
            $postVal = "mySqlGridDateFilterLe$gridId$column";
            $leVal = htmlspecialchars($mySqlGridParams[$postVal]);
        }
        $visualName = str_replace('mySqlGridSpace', ' ', $column);
        echo "<div id='mySqlGridDate$gridId$column' title='$visualName Filter'>
            <input type='text' style='width: 0; height: 0; top: -100px; position: absolute;'/>
            <p>$visualName From: <input type='text' name='mySqlGridDateFilterGe$gridId{$column}' id='mySqlGridDateFilterGe$gridId{$column}' value=\"$geVal\" ></p>
            <p>$visualName To: <input type='text' name='mySqlGridDateFilterLe$gridId{$column}' id='mySqlGridDateFilterLe$gridId{$column}' value=\"$leVal\" ></p>
            <p>
            <input type='button' name='mySqlGridDateFilterButton' value='Apply' onclick='document.getElementById(\"mySqlGridFilter$gridId{$column}\").value=\"\"; mySqlGridUpdate(\"$gridId\",\"$paginationId\");'>
            <input type='button' name='mySqlGridDateFilterButton' value='Clear' onclick='document.getElementById(\"mySqlGridDateFilterGe$gridId{$column}\").value=\"\"; document.getElementById(\"mySqlGridDateFilterLe$gridId{$column}\").value=\"\"; document.getElementById(\"mySqlGridFilter$gridId{$column}\").value=\"\"; mySqlGridUpdate(\"$gridId\",\"$paginationId\");'></p>
            </div>";
        ?>
        <script>
            <?php echo "function mySqlGridDate$gridId$column() "; ?>
            {
                $("#<?php echo "mySqlGridDate$gridId$column"; ?>").dialog("open");
            }
            $(function () {
                $("#<?php echo "mySqlGridDate$gridId$column"; ?>").dialog({
                    // position:{my:"top",at:"top+300", of:"body"},
                    // position:{my:"right top",at:"right-100 top+100", of:"body"},
                    autoOpen: false,
                    minWidth: 600,
                    appendTo: '#mySqlGridForm<?php echo $gridId; ?>'
                });
                $("#<?php echo "mySqlGridDateFilterGe$gridId{$column}"; ?>").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd'
                });
                $("#<?php echo "mySqlGridDateFilterLe$gridId{$column}"; ?>").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd'
                });
            });
        </script>
        <?php
    }
}
echo "
    </form>
    </div>";
?>
<script type="text/javascript">
    $('#mySqlGridLoading<?php echo $gridId; ?>').hide();
    $(document).ready(function () {
        var mySqlGridPageCnt = document.getElementById('mySqlGridPageCnt<?php echo $gridId; ?>').value;
        if (mySqlGridPageCnt < 2 <?php if($optionsArray['noPaginate'] || $mySqlGridParams["mySqlGridNoPages$gridId"]) echo ' || true '; ?>) $("#<?php echo $paginationId; ?>").hide();
        else {
            $("#<?php echo $paginationId; ?>").show();
            $("#<?php echo $paginationId; ?>").bootpag({
                total: mySqlGridPageCnt
            })
        }
        $('#mySqlGridSpinner<?php echo $gridId; ?>').hide();
        <?php
            if($selectId) echo "ExpandSelect(\"$gridId\",\"$paginationId\",\"$selectId\");";
        ?>
    });
</script>

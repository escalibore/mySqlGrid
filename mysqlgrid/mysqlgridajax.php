<?php 
    include 'dbconnect.php';
    //sanitize post value
    if($_POST['page']) $page_number = $_POST['page']; else $page_number = 1; 
    $mySqlGridParams = array();
    parse_str($_POST['mySqlGridData'],$mySqlGridParams);
    $optionsArray = unserialize(urldecode($mySqlGridParams['mySqlGridOptions']));
    $repack = urlencode(serialize($optionsArray));
    $lineCount = $optionsArray['lineCount'] ? $optionsArray['lineCount'] : 25;
    $baseSql = str_replace(PHP_EOL, '', $optionsArray['sql']);
    $position = (($page_number-1) * $lineCount);
    $mySqlGridSql = "SELECT * FROM ( ". $baseSql ." ) AS fullSet ";
    if(!$mySqlGridParams['mySqlGridReset']) { // Ignore filters if user selected "Reset"
        $mySqlGridSql .= " WHERE 1=1 ";
        foreach($mySqlGridParams as $key => $val) {
            if($val && strstr($key,'mySqlGridFilter')) {
                $columnNameArray = explode('mySqlGridFilter',$key);
                if($columnNameArray[1]) {
                    $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection,$columnNameArray[1]);
                    $val = mysqli_real_escape_string($mySqlGridConnection,$val);
                    $mySqlGridSql .= " AND $columnNameArray[1] LIKE '%$val%' ";
                }
            }
        } 
    }
    if($mySqlGridParams['mySqlGridSelect']) {
        $selectArray = array(); 
        $mySqlGridParams['mySqlGridSelect'] = mysqli_real_escape_string($mySqlGridConnection,$mySqlGridParams['mySqlGridSelect']); 
        $selectSql = "SELECT DISTINCT $mySqlGridParams[mySqlGridSelect] AS SelectVal FROM ( ". $mySqlGridSql ." ) AS fullSet WHERE $mySqlGridParams[mySqlGridSelect] IS NOT NULL ORDER BY $mySqlGridParams[mySqlGridSelect] ";
        $selectResults = $mySqlGridConnection->query($selectSql) or die($mySqlGridConnection->error." line:".__LINE__." sql:$selectSql");
        while($selectRow = $selectResults->fetch_array(MYSQLI_ASSOC)) {
            $selectArray[] = htmlspecialchars($selectRow['SelectVal']);
        }
    } 
    if($_POST['mySqlGridRows']) $totalRows = $_POST['mySqlGridRows']; // We got the count on original page load 
    else {
        $countSql = "SELECT COUNT(*) rowCount FROM ( ". $mySqlGridSql ." ) AS fullSet "; // get row count    
        $results = $mySqlGridConnection->query($countSql) or die($mySqlGridConnection->error." line:".__LINE__." sql:$countSql");
        $get_total_rows = $results->fetch_array(MYSQLI_ASSOC);
        $totalRows = $get_total_rows['rowCount'];
    } 
    $pages = ceil($totalRows/$lineCount);
    if($mySqlGridParams['sort'] && !$mySqlGridParams['mySqlGridReset']) $mySqlGridSql.=" ORDER BY $mySqlGridParams[sort] $mySqlGridParams[desc] ";
    $mySqlGridSql .= " LIMIT $position, $lineCount ";
    $results = $mySqlGridConnection->query($mySqlGridSql) or die($mySqlGridConnection->error." line:".__LINE__." sql:$mySqlGridSql");
    $rowCount = $results->num_rows;
    $startRow = $position + 1;
    $offSet = $position + $rowCount;
    echo "
    <div class='mySqlGridWrapper'>
    <div class='mySqlGridTop'>
    $totalRows Rows Found (showing: $startRow - $offSet)&nbsp;&nbsp; <img class='mySqlGridSpinner' id='mySqlGridSpinner' src='mysqlgrid/725.GIF'>
    <div class='mySqlGridReset'><button onClick='document.getElementById(\"mySqlGridReset\").value=\"1\";  mySqlGridUpdate();'>Reset</button></div>
    </div>";
    $columns = array();
    while($finfo = $results->fetch_field()) $columns[] = $finfo->name;   
    echo " 
    <form name='mySqlGridForm' id='mySqlGridForm' method='post' onSubmit='return false'>
    <input type='hidden' name='sort' value=\"". $mySqlGridParams['sort'] ."\">
    <input type='hidden' name='desc' value=\"". $mySqlGridParams['desc'] ."\">
    <input type='hidden' name='sqlGridBaseSql' value=\"". $sqlGridBaseSql ."\"> 
    <input type='hidden' name='mySqlGridOptions' value=\"". $repack ."\"> 
    <input type='hidden' name='mySqlGridSelect' id='mySqlGridSelect' value=\"\">
    <input type='hidden' name='pageCnt' id='pageCnt' value=\"$pages\">
    <input type='hidden' name='mySqlGridReset' id='mySqlGridReset' value=\"\">
    <table class='mySqlGridTable'>
    <tr>";
    foreach($columns as $column) {  // build header row
        echo "<th colspan='2' class='mySqlGridHeader' onclick=\"document.mySqlGridForm.sort.value='$column'; document.mySqlGridForm.desc.value='" . (($mySqlGridParams['sort'] == $column && !$mySqlGridParams['desc']) ? "desc" : "") . "'; mySqlGridUpdate(); return false;\">$column</th>";
    }
    echo "</tr>";
    echo "<tr>"; 
    foreach($columns as $column) { // build search row
        $column = htmlspecialchars($column);
        if($mySqlGridParams['mySqlGridSelect'] == $column) { // User requested a drop down list
            $selectId = "mySqlGridFilter{$column}";
            echo "<td colspan='2' onChange='mySqlGridUpdate();'><select name='mySqlGridFilter{$column}' id='mySqlGridFilter{$column}'><option value=''></option>";  
            foreach($selectArray as $optionVal) {
                echo "<option value=\"$optionVal\">$optionVal</option>";
            }
            echo "</select></td>";
        } else {
            if(!$mySqlGridParams['mySqlGridReset']) {
                $postVal = "mySqlGridFilter$column";
                $val = htmlspecialchars($mySqlGridParams[$postVal]);
            }
            echo "
            <td class='mySqlGridSearchCol'><input class='mySqlGridSearchInput' value=\"$val\" type='text' name='mySqlGridFilter{$column}' id='mySqlGridFilter{$column}' onBlur='mySqlGridUpdate();' onKeyPress='if(event.keyCode == 13) mySqlGridUpdate();'></td>
            <td class='mySqlGridSelectCol' onClick='document.getElementById(\"mySqlGridSelect\").value=\"$column\";  mySqlGridUpdate();'><img src='mysqlgrid/icon_dropdown2.gif'></td>";
        }
    }
    echo "</tr>";
    while($row = $results->fetch_array(MYSQLI_ASSOC)) {
        echo "<tr>";
        foreach($columns as $column) {
            if($row[$column]) $row[$column] = htmlspecialchars($row[$column]); 
            echo "<td colspan='2'>$row[$column]</td>";   
        }
        //   echo "<td>Custom Controls</td>";
        echo "</tr>";
    }
    echo "
    </table>
    <input type='submit' class='mySqlGridSubmit'>
    </form>
    </div>";
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#mySqlGridSpinner').show();
        var pageCnt = document.getElementById('pageCnt').value;
        $("#mySqlGridPagination").bootpag({
            total: pageCnt
        })
        $('#mySqlGridSpinner').hide();
        <?php
            if($selectId) echo "ExpandSelect(\"$selectId\");";
        ?>
    });
</script>

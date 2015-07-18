<?php
    if($_POST['page']) $page_number = $_POST['page']; else $page_number = 1; 
    $mySqlGridParams = array();
    parse_str($_POST['mySqlGridData'],$mySqlGridParams);
    $optionsArray = unserialize(urldecode($mySqlGridParams['mySqlGridOptions']));
    if(!$mySqlGridConnection) require 'dbconnect.php';
    $repack = urlencode(serialize($optionsArray));
    $lineCount = $optionsArray['lineCount'] ? $optionsArray['lineCount'] : 25;
    if($optionsArray['includePath']) $mySqlGridPath = $optionsArray['includePath']; else $mySqlGridPath = 'mysqlgrid/';
    $baseSql = str_replace(PHP_EOL, '', $optionsArray['sql']);
    $position = (($page_number-1) * $lineCount);
    $mySqlGridSql = "SELECT * FROM ( ". $baseSql ." ) AS fullSet ";
    if(!$mySqlGridParams['mySqlGridReset']) { // Ignore filters if user selected "Reset"
        $mySqlGridSql .= " WHERE 1=1 ";
        foreach($mySqlGridParams as $key => $val) {
            if(strstr($key,'mySqlGridFilter') && $val && $val != '(Filtered)') {
                $columnNameArray = explode('mySqlGridFilter',$key);
                if($columnNameArray[1]) {
                    $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection,$columnNameArray[1]);
                    $val = mysqli_real_escape_string($mySqlGridConnection,$val);
                    $mySqlGridSql .= " AND $columnNameArray[1] LIKE '%$val%' ";
                }
            }
            if($val && strstr($key,'mySqlGridDateFilterGe')) {
                $columnNameArray = explode('mySqlGridDateFilterGe',$key);
                if($columnNameArray[1]) {
                    $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection,$columnNameArray[1]);
                    $val = mysqli_real_escape_string($mySqlGridConnection,$val);
                    $mySqlGridSql .= " AND $columnNameArray[1] >= '$val' ";
                }
            }
            if($val && strstr($key,'mySqlGridDateFilterLe')) {
                $columnNameArray = explode('mySqlGridDateFilterLe',$key);
                if($columnNameArray[1]) {
                    $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection,$columnNameArray[1]);
                    $val = mysqli_real_escape_string($mySqlGridConnection,$val);
                    $mySqlGridSql .= " AND $columnNameArray[1] <= '$val' ";
                }
            }
        } 
        if($mySqlGridParams['mySqlGridYesPages']) {
            $mySqlGridParams['mySqlGridNoPages'] = 0;
        ?>
        <script>
            $("#mySqlGridPagination").show(); 
        </script>
        <?php        
        }    
    } else {
        if($mySqlGridParams['mySqlGridNoPages']) {
            $mySqlGridParams['mySqlGridNoPages'] = '';
        ?>
        <script>
            $("#mySqlGridPagination").show(); 
        </script>
        <?php
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
    if(!$mySqlGridParams['mySqlGridNoPages'] && !($optionsArray['noPaginate'] == true) ) $mySqlGridSql .= " LIMIT $position, $lineCount ";
    else {  
    ?>
    <script>
        $("#mySqlGridPagination").hide();
    </script>
    <?php   
    }
    $results = $mySqlGridConnection->query($mySqlGridSql) or die($mySqlGridConnection->error." line:".__LINE__." sql:$mySqlGridSql");
    $rowCount = $results->num_rows;
    $startRow = $position + 1;
    $offSet = $position + $rowCount;
    echo "
    <div class='mySqlGridWrapper'>
    <div class='mySqlGridTop'>
    $totalRows Rows Found (showing: $startRow - $offSet)&nbsp;&nbsp; <img class='mySqlGridSpinner' id='mySqlGridSpinner' src='{$mySqlGridPath}images/725.GIF'>";
    if($mySqlGridParams['mySqlGridNoPages'] && $pages > 1) 
        echo "<div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridYesPages\").value=\"1\"; mySqlGridUpdate();'>Paginate</button></div>";
    elseif($pages > 1 && !($optionsArray['noPaginate'] == true) && !($optionsArray['alwaysPaginate'] == true)) echo "
        <div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridNoPages\").value=\"1\";  mySqlGridUpdate();'>No Pagination</button></div>";
    if(!$optionsArray['noReset']) echo "<div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridReset\").value=\"1\";  mySqlGridUpdate();'>Reset</button>&nbsp;</div>";
    echo "</div>";
    $columns = array();
    while($finfo = $results->fetch_field()) if(!in_array($finfo->name, $optionsArray['hideColumns'])) $columns[$finfo->name] = $finfo->type;   
        echo " 
    <form name='mySqlGridForm' id='mySqlGridForm' method='post' onSubmit='return false'>
    <input type='hidden' name='sort' value=\"". $mySqlGridParams['sort'] ."\">
    <input type='hidden' name='desc' value=\"". $mySqlGridParams['desc'] ."\">
    <input type='hidden' name='sqlGridBaseSql' value=\"". $sqlGridBaseSql ."\"> 
    <input type='hidden' name='mySqlGridOptions' value=\"". $repack ."\"> 
    <input type='hidden' name='mySqlGridSelect' id='mySqlGridSelect' value=\"\">
    <input type='hidden' name='pageCnt' id='pageCnt' value=\"$pages\">
    <input type='hidden' name='mySqlGridReset' id='mySqlGridReset' value=\"\">
    <input type='hidden' name='mySqlGridNoPages' id='mySqlGridNoPages' value=\"". $mySqlGridParams['mySqlGridNoPages'] ."\">
    <input type='hidden' name='mySqlGridYesPages' id='mySqlGridYesPages' value=\"\">
    <table class='mySqlGridTable'>
    <tr>";
    foreach($columns as $column => $type) {  // build header row
        echo "<th colspan='2' class='mySqlGridHeader' onclick=\"document.mySqlGridForm.sort.value='$column'; document.mySqlGridForm.desc.value='" . (($mySqlGridParams['sort'] == $column && !$mySqlGridParams['desc']) ? "desc" : "") . "'; mySqlGridUpdate(); return false;\">$column</th>";
    }
    echo "</tr>";
    echo "<tr>";
    if(!$optionsArray['noSearch'] == true) { 
        foreach($columns as $column => $type) { // build search row
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
                    if($mySqlGridParams["mySqlGridDateFilterGe{$column}"] || $mySqlGridParams["mySqlGridDateFilterLe{$column}"]) $val = '(Filtered)';
                    else {
                        $postVal = "mySqlGridFilter$column";
                        $val = htmlspecialchars($mySqlGridParams[$postVal]);
                    }
                }
                echo "
                <td class='mySqlGridSearchCol'><input class='mySqlGridSearchInput' value=\"$val\" type='text' name='mySqlGridFilter{$column}' id='mySqlGridFilter{$column}' onBlur='mySqlGridUpdate();' onKeyPress='if(event.keyCode == 13) mySqlGridUpdate();'></td>";
                if($type == 12 || $type == 10) {
                    echo "<td class='mySqlGridSelectCol' onClick=\"mySqlGridDate$column();\"><img src='{$mySqlGridPath}images/calendar3.gif'></td>";
                }
                elseif(!in_array($column,$optionsArray['hideSelects']) && !($optionsArray['noSelects'] == true))
                    echo "<td class='mySqlGridSelectCol' onClick='document.getElementById(\"mySqlGridSelect\").value=\"$column\";  mySqlGridUpdate();'><img src='{$mySqlGridPath}images/icon_dropdown2.gif'></td>";
                else echo "<td></td>";
            }
        }
    }
    echo "</tr>";
    while($row = $results->fetch_array(MYSQLI_ASSOC)) {
        echo "<tr>";
        foreach($columns as $column => $type) {
            if($row[$column]) $row[$column] = htmlspecialchars($row[$column]); 
            echo "<td class='mySqlGridDataCell' colspan='2'>$row[$column]</td>";   
        }
        if($optionsArray['gridControlHtml']) {
            $replacementString = str_replace('gridControlKey',addslashes($row[$optionsArray['gridControlKey']]),$optionsArray['gridControlHtml']); //Replaces primary key as specified in $mySqlGridOptions with the gridControlHtml.   
            echo "<td>$replacementString</td>";
        }
        echo "</tr>";
    }
    echo "
    </table>
    <input type='submit' class='mySqlGridSubmit'>";
    foreach($columns as $column => $type) {
        if($type == 12) {
            if(!$mySqlGridParams['mySqlGridReset']) {
                $postVal = "mySqlGridDateFilterGe$column";
                $geVal = htmlspecialchars($mySqlGridParams[$postVal]);
                $postVal = "mySqlGridDateFilterLe$column";
                $leVal = htmlspecialchars($mySqlGridParams[$postVal]);
            }
            echo "<div id='mySqlGridDate$column' title='$column Filters'>
            <input type='text' style='width: 0; height: 0; top: -100px; position: absolute;'/>
            <p>$column >= <input type='text' name='mySqlGridDateFilterGe{$column}' id='mySqlGridDateFilterGe{$column}' value=\"$geVal\" ></p>
            <p>$column <= <input type='text' name='mySqlGridDateFilterLe{$column}' id='mySqlGridDateFilterLe{$column}' value=\"$leVal\" ></p>
            <p>
            <input type='button' name='mySqlGridDateFilterButton' value='Apply' onclick='document.getElementById(\"mySqlGridFilter{$column}\").value=\"\"; mySqlGridUpdate();'>
            <input type='button' name='mySqlGridDateFilterButton' value='Clear' onclick='document.getElementById(\"mySqlGridDateFilterGe{$column}\").value=\"\"; document.getElementById(\"mySqlGridDateFilterLe{$column}\").value=\"\"; document.getElementById(\"mySqlGridFilter{$column}\").value=\"\"; mySqlGridUpdate();'></p>
            </div>";   
        ?>  
        <script>
            <?php echo "function mySqlGridDate$column() "; ?> {
                $( "#<?php echo "mySqlGridDate$column"; ?>" ).dialog( "open" );
            }  
            $(function() {
                $( "#<?php echo "mySqlGridDate$column"; ?>" ).dialog({
                    autoOpen: false,
                    minWidth: 600,
                    appendTo: '#mySqlGridForm'
                });
                $( "#<?php echo "mySqlGridDateFilterGe{$column}"; ?>" ).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd'
                });
                $( "#<?php echo "mySqlGridDateFilterLe{$column}"; ?>" ).datepicker({
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
    $(document).ready(function() {
        //      $('#mySqlGridSpinner').show();
        var pageCnt = document.getElementById('pageCnt').value;
        if(pageCnt < 2 <?php if($optionsArray['noPaginate'] || $mySqlGridParams['mySqlGridNoPages']) echo ' || true '; ?>) $("#mySqlGridPagination").hide();
        else {
            $("#mySqlGridPagination").show();
            $("#mySqlGridPagination").bootpag({
                total: pageCnt
            })
        }
        $('#mySqlGridSpinner').hide();
        <?php
            if($selectId) echo "ExpandSelect(\"$selectId\");";
        ?>
    });
</script>

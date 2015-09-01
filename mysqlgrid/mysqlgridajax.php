<?php
    if($_POST['page']) $page_number = $_POST['page']; else $page_number = 1; 
    $mySqlGridParams = array();
    parse_str($_POST['mySqlGridData'],$mySqlGridParams);
    $optionsArray = unserialize(rawurldecode($mySqlGridParams['mySqlGridOptions']));
    if(!$mySqlGridConnection) require 'dbconnect.php';
    $repack = rawurlencode(serialize($optionsArray));
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
                    $columnNameArray[1] = str_replace('mySqlGridSpace',' ',$columnNameArray[1]); // work around because parse_str sets blanks to underscore 
                    $val = trim(mysqli_real_escape_string($mySqlGridConnection,$val));
                    if(strpos($val,'=') === 0){ // if preceded by =, >, or < sign we don't do a "LIKE".
                        $stripArray = explode('=',$val);
                        $mySqlGridSql .= " AND `$columnNameArray[1]` = '$stripArray[1]' ";
                    } elseif(strpos($val,'>=') === 0){ 
                        $stripArray = explode('>=',$val);
                        $mySqlGridSql .= " AND `$columnNameArray[1]` >= '$stripArray[1]' ";
                    } elseif(strpos($val,'<=') === 0){ 
                        $stripArray = explode('<=',$val);
                        $mySqlGridSql .= " AND `$columnNameArray[1]` <= '$stripArray[1]' ";
                    } elseif(strpos($val,'>') === 0){ 
                        $stripArray = explode('>',$val);
                        $mySqlGridSql .= " AND `$columnNameArray[1]` > '$stripArray[1]' ";
                    } elseif(strpos($val,'<') === 0){ 
                        $stripArray = explode('<',$val);
                        $mySqlGridSql .= " AND `$columnNameArray[1]` < '$stripArray[1]' ";
                    }
                    else $mySqlGridSql .= " AND `$columnNameArray[1]` LIKE '%$val%' ";
                }
            }
            if($val && strstr($key,'mySqlGridDateFilterGe')) {
                $columnNameArray = explode('mySqlGridDateFilterGe',$key);
                if($columnNameArray[1]) {
                    $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection,$columnNameArray[1]);
                    $columnNameArray[1] = str_replace('mySqlGridSpace',' ',$columnNameArray[1]); // work around because parse_str sets blanks to underscore
                    $val = mysqli_real_escape_string($mySqlGridConnection,$val);
                    $mySqlGridSql .= " AND `$columnNameArray[1]` >= '$val' ";
                }
            }
            if($val && strstr($key,'mySqlGridDateFilterLe')) {
                $columnNameArray = explode('mySqlGridDateFilterLe',$key);
                if($columnNameArray[1]) {
                    $columnNameArray[1] = mysqli_real_escape_string($mySqlGridConnection,$columnNameArray[1]);
                    $columnNameArray[1] = str_replace('mySqlGridSpace',' ',$columnNameArray[1]); // work around because parse_str sets blanks to underscore
                    $val = mysqli_real_escape_string($mySqlGridConnection,$val);
                    $mySqlGridSql .= " AND `$columnNameArray[1]` <= '$val' ";
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
        $mySqlGridParams['mySqlGridSort'] = '';
        $mySqlGridParams['mySqlGridDesc'] = '';
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
        $selectColumn = str_replace('mySqlGridSpace',' ', mysqli_real_escape_string($mySqlGridConnection,$mySqlGridParams['mySqlGridSelect']));
        $selectSql = "SELECT DISTINCT `$selectColumn` AS SelectVal FROM ( ". $mySqlGridSql ." ) AS fullSet2 WHERE `$selectColumn` IS NOT NULL ORDER BY `$selectColumn` ";
        $selectResults = $mySqlGridConnection->query($selectSql) or die($mySqlGridConnection->error." line:".__LINE__." sql:$selectSql");
        while($selectRow = $selectResults->fetch_array(MYSQLI_ASSOC)) { 
            $selectArray[] = htmlspecialchars($selectRow['SelectVal']);
        }
    }
    if($mySqlGridParams['mySqlGridSort'] && !$mySqlGridParams['mySqlGridReset']) $mySqlGridSql.=" ORDER BY `$mySqlGridParams[mySqlGridSort]` $mySqlGridParams[mySqlGridDesc] ";
    elseif($optionsArray['defaultOrderBy']) $mySqlGridSql .= " $optionsArray[defaultOrderBy] ";
    if(!$mySqlGridParams['mySqlGridNoPages'] && !($optionsArray['noPaginate'] == true) ) $mySqlGridSql .= " LIMIT $position, $lineCount ";
    else {  
    ?>
    <script>
        $("#mySqlGridPagination").hide();
    </script>
    <?php   
    }
    $mySqlGridSql = preg_replace("/SELECT/i","SELECT SQL_CALC_FOUND_ROWS ",$mySqlGridSql, 1);
    $results = $mySqlGridConnection->query($mySqlGridSql) or die($mySqlGridConnection->error." line:".__LINE__." sql:$mySqlGridSql");
    $resultsRowCount = $mySqlGridConnection->query("SELECT FOUND_ROWS()") or die($mySqlGridConnection->error." line:".__LINE__." sql:'SELECT FOUND_ROWS()'");
    $foundRowsArray = $resultsRowCount->fetch_row();
    $totalRows = $foundRowsArray[0];
    $pages = ceil($totalRows/$lineCount);
    $rowCount = $results->num_rows;
    $startRow = $position + 1;
    $offSet = $position + $rowCount;
    echo "
    <div class='mySqlGridWrapper'>
    <div class='mySqlGridTop' id='mySqlGridTop'>
    $totalRows Rows Found";
    if($totalRows > 2) echo " (showing: $startRow - $offSet)";
    echo "&nbsp;&nbsp; <img class='mySqlGridSpinner' id='mySqlGridSpinner' src='{$mySqlGridPath}images/725.GIF'>";
    if($optionsArray['noToolTip'] != true) {
    ?>
    <div class='mySqlGridbuttonArea' style="position: relative; top:3px; margin-left: 14px;">
        <a href="#" class="tooltip">
            <img src="<?php echo "$mySqlGridPath"; ?>images/questionmark2.png" />
            <span>
                Click a column header to change the sort order.<br>Type a value into an input area to perform a "LIKE" search.<br>Preceding an input value with "=" performs an exact match.<br>You can also precede an input value with &gt;, &lt;, &gt;=, or &lt;=.    
            </span>
        </a>
    </div>
    <?php
    }
    if($optionsArray['noReport'] != true) {
    ?>
    <div class='mySqlGridbuttonArea'><button onclick="printableView()">Report View</button></div>
    <?php
    }
    if($mySqlGridParams['mySqlGridNoPages'] && $pages > 1) 
        echo "<div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridYesPages\").value=\"1\"; mySqlGridUpdate();'>Paginate</button>&nbsp</div>";
    elseif($pages > 1 && !($optionsArray['noPaginate'] == true) && !($optionsArray['alwaysPaginate'] == true)) echo "
        <div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridNoPages\").value=\"1\";  mySqlGridUpdate();'>No Pagination</button>&nbsp</div>";
    if(!$optionsArray['noReset']) echo "<div class='mySqlGridbuttonArea'><button onClick='document.getElementById(\"mySqlGridReset\").value=\"1\";  mySqlGridUpdate();'>Reset</button>&nbsp;</div>";
    echo "</div>";
    $columns = array();
    while($finfo = $results->fetch_field()) if(!in_array($finfo->name, $optionsArray['hideColumns'])) $columns[$finfo->name] = $finfo->type;   
        echo " 
    <form name='mySqlGridForm' id='mySqlGridForm' method='post' onSubmit='return false'>
    <input type='hidden' name='mySqlGridSort' value=\"". $mySqlGridParams['mySqlGridSort'] ."\">
    <input type='hidden' name='mySqlGridDesc' value=\"". $mySqlGridParams['mySqlGridDesc'] ."\">
    <input type='hidden' name='sqlGridBaseSql' value=\"". $sqlGridBaseSql ."\"> 
    <input type='hidden' name='mySqlGridOptions' value=\"". $repack ."\"> 
    <input type='hidden' name='mySqlGridSelect' id='mySqlGridSelect' value=\"\">
    <input type='hidden' name='mySqlGridPageCnt' id='mySqlGridPageCnt' value=\"$pages\">
    <input type='hidden' name='mySqlGridReset' id='mySqlGridReset' value=\"\">
    <input type='hidden' name='mySqlGridNoPages' id='mySqlGridNoPages' value=\"". $mySqlGridParams['mySqlGridNoPages'] ."\">
    <input type='hidden' name='mySqlGridYesPages' id='mySqlGridYesPages' value=\"\">
    <input type='hidden' name='mySqlGridFormCalled' id='mySqlGridFormCalled' value=\"1\">";
    if($totalRows || $mySqlGridParams['mySqlGridFormCalled']) {
        echo "
        <table class='mySqlGridTable'>
        <tr>";
        foreach($columns as $column => $type) {  // build header row
            echo "<th colspan='2' class='mySqlGridHeader' onclick=\"document.mySqlGridForm.mySqlGridSort.value='$column'; document.mySqlGridForm.mySqlGridDesc.value='" . (($mySqlGridParams['mySqlGridSort'] == $column && !$mySqlGridParams['mySqlGridDesc']) ? "desc" : "") . "'; mySqlGridUpdate(); return false;\">";
            if($column == $mySqlGridParams['mySqlGridSort']) { //special styling for sort icons 
                echo" 
                <div style='display:table; margin:auto;'>
                <div style='display:table-cell; vertical-align:middle;'>$column</div>
                <div style='display:table-cell; vertical-align:middle;'>";
                if($mySqlGridParams['mySqlGridDesc']) echo "<div class='mySqlGridSortIcon'><span class='mySqlGridIconNorth ui-icon ui-icon-triangle-1-n ui-state-disabled'></span><span class='mySqlGridIconSouth ui-icon ui-icon-triangle-1-s'></span></div>";
                else echo "<div class='mySqlGridSortIcon'><span class='mySqlGridIconNorth ui-icon ui-icon-triangle-1-n '></span><span class='mySqlGridIconSouth ui-icon ui-icon-triangle-1-s ui-state-disabled'></span></div>";
                echo "</div></div>";
            } else echo "$column";
            echo "</th>";
        }     
        echo "</tr>";
        echo "<tr id='mySqlGridSearchRow'>";
        if(!$optionsArray['noSearch'] == true) { 
            foreach($columns as $column => $type) { // build search row
                $column = str_replace(' ','mySqlGridSpace',htmlspecialchars($column)); // have to convert blanks to 'mySqlGridSpace' because parse_str changes blanks to underscores.  We convert back in code above.
                if($mySqlGridParams['mySqlGridSelect'] == $column) { // User requested a drop down list
                    $selectId = "mySqlGridFilter{$column}";
                    echo "<td colspan='2' onChange='mySqlGridUpdate();'><select name='mySqlGridFilter{$column}' id='mySqlGridFilter{$column}'><option value=''></option>";  
                    foreach($selectArray as $optionVal) {
                        echo "<option value=\"=$optionVal\">$optionVal</option>";
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
                    elseif(!in_array(str_replace('mySqlGridSpace',' ',$column),$optionsArray['hideSelects']) && !($optionsArray['noSelects'] == true))
                        echo "<td class='mySqlGridSelectCol' onClick='document.getElementById(\"mySqlGridSelect\").value=\"$column\";  mySqlGridUpdate();'><img src='{$mySqlGridPath}images/icon_dropdown2.gif'></td>";
                    else echo "<td></td>";
                }
            }
        }
        echo "</tr>";
        while($row = $results->fetch_array(MYSQLI_ASSOC)) {
            echo "<tr>";
            foreach($columns as $column => $type) {
                if($row[$column] && !stripos($row[$column], ' href') && !stripos($row[$column], 'img')) $row[$column] = htmlspecialchars($row[$column]); // allow html for anchor or img.
                echo "<td class='mySqlGridDataCell' colspan='2'>$row[$column]</td>";   
            }
            if($optionsArray['gridControlHtml']) {
                $replacementString = str_replace('gridControlKey',addslashes($row[$optionsArray['gridControlKey']]),$optionsArray['gridControlHtml']); //Replaces primary key as specified in $mySqlGridOptions with the gridControlHtml.   
                echo "<td>$replacementString</td>";
            }
            echo "</tr>";
        }
        echo "
        </table>";
    }
    echo "
    <input type='submit' class='mySqlGridSubmit'>";
    foreach($columns as $column => $type) {
        if(($type == 12 || $type == 10) && !$optionsArray['noSearch']) {
            $column = str_replace(' ','mySqlGridSpace',htmlspecialchars($column));  
            if(!$mySqlGridParams['mySqlGridReset']) {
                $postVal = "mySqlGridDateFilterGe$column";
                $geVal = htmlspecialchars($mySqlGridParams[$postVal]);
                $postVal = "mySqlGridDateFilterLe$column";
                $leVal = htmlspecialchars($mySqlGridParams[$postVal]);
            } 
            $visualName = str_replace('mySqlGridSpace',' ',$column);
            echo "<div id='mySqlGridDate$column' title='$visualName Filter'>
            <input type='text' style='width: 0; height: 0; top: -100px; position: absolute;'/>
            <p>$visualName From: <input type='text' name='mySqlGridDateFilterGe{$column}' id='mySqlGridDateFilterGe{$column}' value=\"$geVal\" ></p>
            <p>$visualName To: <input type='text' name='mySqlGridDateFilterLe{$column}' id='mySqlGridDateFilterLe{$column}' value=\"$leVal\" ></p>
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
                    // position:{my:"top",at:"top+300", of:"body"},
                    // position:{my:"right top",at:"right-100 top+100", of:"body"},
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
    $('#mySqlGridLoading').hide();
    $(document).ready(function() {
        var mySqlGridPageCnt = document.getElementById('mySqlGridPageCnt').value;
        if(mySqlGridPageCnt < 2 <?php if($optionsArray['noPaginate'] || $mySqlGridParams['mySqlGridNoPages']) echo ' || true '; ?>) $("#mySqlGridPagination").hide();
        else {
            $("#mySqlGridPagination").show();
            $("#mySqlGridPagination").bootpag({
                total: mySqlGridPageCnt
            })
        }
        $('#mySqlGridSpinner').hide();
        <?php
            if($selectId) echo "ExpandSelect(\"$selectId\");";
        ?>
    });
</script>

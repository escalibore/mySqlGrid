<?php 
    //echo str_replace('  ', '&nbsp; ', nl2br(print_r($_POST, true))); echo '</br>'. str_replace('  ', '&nbsp; ', nl2br(print_r($_GET, true)));  // For debugging.
    //echo "<h3> PHP List All Session Variables</h3>"; foreach ($_SESSION as $key=>$val) echo $key." ".$val."<br/>"; // For debugging.

    $mySqlGridConnection = $mySqlGridArray['connection'];
    $lineCount = $mySqlGridArray['lineCount'] ? $mySqlGridArray['lineCount'] : 25;

    if($_POST['mySqlGridData']) { 
        //  if($_POST['sqlGridSql']) { 
    ?>
    <script type='text/javascript'> // $('#mySqlGridSpinner').show(); </script>
    <?php 
        //sanitize post value
        if(isset($_POST["page"])){
            $page_number = filter_var($_POST["page"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);
            if(!is_numeric($page_number)){die('Invalid page number!');} //incase of invalid page number
        } else {
            $page_number = 1;
        }
        $mySqlGridParams = array();
        parse_str($_POST['mySqlGridData'],$mySqlGridParams);
        $sqlGridBaseSql = $mySqlGridParams['sqlGridBaseSql']; 

        //get current starting point of records
        $position = (($page_number-1) * $lineCount);

        $mySqlGridSql = "SELECT * FROM ( ". $sqlGridBaseSql ." ) AS fullSet ";

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
            $selectSql = "SELECT DISTINCT $mySqlGridParams[mySqlGridSelect] AS SelectVal FROM ( ". $mySqlGridSql ." ) AS fullSet WHERE $mySqlGridParams[mySqlGridSelect] IS NOT NULL ";
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
        //break total records into pages
        $pages = ceil($totalRows/$lineCount);

        if($mySqlGridParams['sort'] && !$mySqlGridParams['mySqlGridReset']) {
            $mySqlGridSql.=" ORDER BY $mySqlGridParams[sort] $mySqlGridParams[desc] ";
        } 

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
        //output results from database
        echo " 
        <form name='mySqlGridForm' id='mySqlGridForm' method='post' onSubmit='return false'>
        <input type='hidden' name='sort' value=\"". $mySqlGridParams['sort'] ."\">
        <input type='hidden' name='desc' value=\"". $mySqlGridParams['desc'] ."\">
        <input type='hidden' name='sqlGridBaseSql' value=\"". $sqlGridBaseSql ."\"> 
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
    <?php
        exit;
    } else { // Gets here on original page load.
        $mySqlGridSql =  $mySqlGridArray['sql'];
        $mySqlGridSql = str_replace(PHP_EOL, '', $mySqlGridSql); // get rid of eol characters
        $countSql = "SELECT COUNT(*) rowCount FROM ( ". $mySqlGridSql ." ) AS fullSet "; //echo "did count1<br>"; // get row count     
        $results = $mySqlGridConnection->query($countSql) or die($mySqlGridConnection->error." line:".__LINE__." sql:$countSql");
        $get_total_rows = $results->fetch_array(MYSQLI_ASSOC);
        //break total records into pages
        $pages = ceil($get_total_rows['rowCount']/$lineCount); 
        $mySqlGridSql = 'sqlGridBaseSql=' . urlencode($mySqlGridSql);
        $postString = "'mySqlGridData':'$mySqlGridSql', 'mySqlGridRows':'$get_total_rows[rowCount]'";
    ?>
    <link rel="stylesheet" type="text/css" href="mysqlgrid/style.css" />
    <script src="mysqlgrid/jquery-2.1.3.min.js"></script>
    <script src="mysqlgrid/jquery.bootpag.min.js"></script>

    <script type="text/javascript">
        function ExpandSelect(select, maxOptionsVisible) {
            //
            // ExpandSelect 1.00
            // Copyright (c) Czarek Tomczak. All rights reserved.
            //
            // License:
            //      New BSD License (free for any use, read more at http://www.opensource.org/licenses/bsd-license.php)
            //
            // Project's website:
            //      http://code.google.com/p/expandselect/
            //

            if (typeof maxOptionsVisible == "undefined") {
                maxOptionsVisible = 20;
            }
            if (typeof select == "string") {
                select = document.getElementById(select);
            }
            if (typeof window["ExpandSelect_tempID"] == "undefined") {
                window["ExpandSelect_tempID"] = 0;
            }
            window["ExpandSelect_tempID"]++;

            var rects = select.getClientRects();

            // ie: cannot populate options using innerHTML.
            function PopulateOptions(select, select2)
            {
                select2.options.length = 0; // clear out existing items
                for (var i = 0; i < select.options.length; i++) {
                    var d = select.options[i];
                    select2.options.add(new Option(d.text, i))
                }
            }

            var select2 = document.createElement("SELECT");
            //select2.innerHTML = select.innerHTML;
            PopulateOptions(select, select2);
            select2.style.cssText = "visibility: hidden;";
            if (select.style.width) {
                select2.style.width = select.style.width;
            }
            if (select.style.height) {
                select2.style.height = select.style.height;
            }
            select2.id = "ExpandSelect_" + window.ExpandSelect_tempID;

            select.parentNode.insertBefore(select2, select.nextSibling);
            select = select.parentNode.removeChild(select);

            if (select.length > maxOptionsVisible) {
                select.size = maxOptionsVisible;
            } else {
                select.size = select.length;
            }

            if ("pageXOffset" in window) {
                var scrollLeft = window.pageXOffset;
                var scrollTop = window.pageYOffset;
            } else {
                // ie <= 8
                // Function taken from here: http://help.dottoro.com/ljafodvj.php
                function GetZoomFactor()
                {
                    var factor = 1;
                    if (document.body.getBoundingClientRect) {
                        var rect = document.body.getBoundingClientRect ();
                        var physicalW = rect.right - rect.left;
                        var logicalW = document.body.offsetWidth;
                        factor = Math.round ((physicalW / logicalW) * 100) / 100;
                    }
                    return factor;
                }
                var zoomFactor = GetZoomFactor();
                var scrollLeft = Math.round(document.documentElement.scrollLeft / zoomFactor);
                var scrollTop = Math.round(document.documentElement.scrollTop / zoomFactor);
            }

            select.style.position = "absolute";
            select.style.left = (rects[0].left + scrollLeft) + "px";
            select.style.top = (rects[0].top + scrollTop) + "px";
            select.style.zIndex = "1000000";

            var keydownFunc = function(e){
                e = e ? e : window.event;
                // Need to implement hiding select on "Escape" and "Enter".
                if (e.altKey || e.ctrlKey || e.shiftKey || e.metaKey) {
                    return 1;
                }
                // Escape, Enter.
                if (27 == e.keyCode || 13 == e.keyCode) {
                    select.blur();
                    return 0;
                }
                return 1;
            };

            if (select.addEventListener) {
                select.addEventListener("keydown", keydownFunc, false);
            } else {
                select.attachEvent("onkeydown", keydownFunc);
            }

            var tempID = window["ExpandSelect_tempID"];

            var clickFunc = function(e){
                window.focus();
                e = e ? e : window.event;
                if (e.target) {
                    if (e.target.tagName == "OPTION") {
                        select.blur();
                    }
                } else {
                    // IE case.
                    if (e.srcElement.tagName == "SELECT" || e.srcElement.tagName == "OPTION") {
                        select.blur();
                    }
                } 

            };

            if (select.addEventListener) {
                select.addEventListener("click", clickFunc, false);
            } else {
                select.attachEvent("onclick", clickFunc);
            }

            var blurFunc = function(){
                if (select.removeEventListener) {
                    select.removeEventListener("blur", arguments.callee, false);
                    select.removeEventListener("click", clickFunc, false);
                    select.removeEventListener("keydown", keydownFunc, false);
                } else {
                    select.detachEvent("onblur", arguments.callee);
                    select.detachEvent("onclick", clickFunc);
                    select.detachEvent("onkeydown", keydownFunc);
                }
                select.size = 1;
                select.style.position = "static";
                select = select.parentNode.removeChild(select);
                var select2 = document.getElementById("ExpandSelect_"+tempID);
                select2.parentNode.insertBefore(select, select2);
                select2.parentNode.removeChild(select2); 
                mySqlGridUpdate();  // added by Berthold for mySqlGrid
            };

            if (select.addEventListener) {
                select.addEventListener("blur", blurFunc, false);
            } else {
                select.attachEvent("onblur", blurFunc);
            }

            document.body.appendChild(select);
            select.focus();
        }
        function mySqlGridUpdate(){
            $('#mySqlGridSpinner').show();
            var pageCnt = document.getElementById('pageCnt').value;
            var formData = $('#mySqlGridForm').serialize();
            $("#mySqlGridTable").load(window.location.pathname, {'mySqlGridData':formData});
            $('#mySqlGridPagination').unbind('page')  
            $("#mySqlGridPagination").bootpag({
                total: pageCnt, // total number of pages
                page: 1, //initial page
                maxVisible: 5 //maximum visible links
            }).on("page", function(e, num){
                e.preventDefault();
                $('#mySqlGridSpinner').show();
                $("#mySqlGridTable").load(window.location.pathname, {'page':num, 'mySqlGridData':formData });
            });
        }
        $(document).ready(function() {
            $('#mySqlGridSpinner').show();
            $("#mySqlGridTable").load(window.location.pathname, {<?php echo $postString; ?>});  //initial page number to load
            $("#mySqlGridPagination").bootpag({
                <?php echo "total: $pages,";?> // total number of pages
                page: 1, //initial page
                maxVisible: 5 //maximum visible links
            }).on("page", function(e, num){
                $('#mySqlGridSpinner').show();
                e.preventDefault();
                $("#mySqlGridTable").load(window.location.pathname, {'page':num, <?php echo $postString; ?> });
            });
        });
    </script> 
    <?php 
    }
?>

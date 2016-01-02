# mySqlGrid
A powerful and easy to use Ajax data grid for PHP/MySQL

Demo: http://mysqlgrid.com/demo/demo1.php

MySqlGrid lets you quickly create sortable, searchable, and paginated data grids from *ANY* MySQL SELECT statement including multi-table joins.  

* Automatically creates input fields in which a substring can be entered to filter the results. 
* Automatically creates dynamically generated drop-down selects.
* Detects date fields and automatically provides pop-up date picker dialogs so users can enter date filters.
* Optionally add controls to edit, view, or delete rows.

MySqlGrid was created with a focus on security, reliability, and simplicity. The learning curve is very minimal. Once you have a valid SQL query, you are minutes away from providing a flexible and beautiful datagrid that will allow your users to quickly find the information they need.

To use mySqlGrid follow these steps:<br><br>
1. Install via Composer/Packagist. https://packagist.org/packages/mysqlgrid/mysqlgrid<br> 
2. Specify your mysqli database connection in <a href="https://github.com/escalibore/mySqlGrid/blob/master/src/dbconnect.php">dbconnect.php</a><br>
3. In your script add "<b>require __DIR__ . '/vendor/autoload.php';</b>" per Composer standards.<br> 
4. Instantiate class <b>Mysqlgridmain</b>, passing in parameters for your SQL Select statement (and any other optional parameters).<br>
5. In the body of your html you will need to create two div elements - one for the datagrid, and one for the pagination area.  These two divs must be given ids of "mySqlGridTable" and "mySqlGridPagination", respectively.<br>  

For a basic example see <a href="https://github.com/escalibore/mySqlGrid/blob/master/basicgrid.php">basicgrid.php</a>.

<h4>MySqlGrid works "out of the box" but provides many configuration options. Options are passed as an array into the Mysqlgridmain object.  The options are as follows:</h4>
<table>
<tr><th>Option Name</th><th>Type</th><th>Description</th></tr>
<tr><td><b>sql</b></td><td>String</td><td>A standard mySQL SELECT statement.  Any statement that returns rows from your mySQL database will be transformed into a grid.  <b>sql</b> is actually the only option that is required to generate a grid.</td></tr>
<tr><td><b>includePath</b></td><td>String</td><td>This is the path to the mysqlgrid "src" directory.  This is not needed if you do a standard installation via Composer, in which case mySqlGrid assumes the default Composer installation directory (vendor/mysqlgrid/mysqlgrid/src/).  <b>includePath</b> needs to have a "/" as the last character.</td></tr>

<tr><td><b>gridId</b></td><td>String</td><td>This is needed if you want to have more than one grid on your page.  If this paramater and its sister parameter "paginationId" are not set, MySqlGrid assumes the divs in your page will be "mySqlGridTable" and "mySqlGridPagination" respectively. For example:<a href="https://github.com/escalibore/mySqlGrid/blob/master/basicgrid.php">basicgrid.php</a>. However, to include multiple grids on one page, each grid needs to have uniquely identified divs for both the grid itself and for the pagination area.  In such a case you will need to specify paramaters <b>gridId</b> and <b>paginationId</b>.  Here is an example: <a href="https://github.com/escalibore/mySqlGrid/blob/master/multigridSample.php">multigridSample.php</a></td></tr>

<tr><td><b>paginationId</b></td><td>String</td><td>See <b>gridId</b> above.</td></tr>

<tr><td><b>lineCount</b></td><td>Integer</td><td>The number of rows in each paginated grid.  If <b>lineCount</b> is not specified mySqlGrid will display 25 rows per page.</td></tr>

<tr><td><b>database</b></td><td>String</td><td>In installations with multiple databases you can specify the database here.  Then, in dbconnect.php you would add something like: <b>if($optionsArray['database']) mysqli_select_db($mySqlGridConnection, $optionsArray['database']);</b></td></tr>

<tr><td><b>hideColumns</b></td><td>Array</td><td>Specifies columns in the mySQL result set that will not be displayed on the grid.  This is handy when you want to get a table's primary key value that means nothing to the user, but will be used to perform an action on a selected row. (See options <b>gridControlHtml</b> and <b>gridControlKey</b>)</td></tr>
<tr><td><b>hideSelects</b></td><td>Array</td><td>Specifies columns that will not be given dynamic drop-down select capability.  They will still be searchable by substring.</td></tr>
<tr><td><b>noSelects</b></td><td>Boolean</td><td>When set to true this option will remove dynamic drop-down select capability from all columns.</td></tr>

<tr><td><b>noReport</b></td><td>Boolean</td><td>Removes the "Report View" button. When the user selects "Report View" button they see a simplified view of the grid table without any controls.</td></tr>


<tr><td><b>defaultOrderBy</b></td><td>String</td><td>You can include a default "ORDER BY" clause in your SQL, but for performance reasons it's better to specify this as a MySqlGrid option. Typically this might look something like: <b>'ORDER BY Last_Name DESC'</b></td></tr>


<tr><td><b>noSearch</b></td><td>Boolean</td><td>Removes entire search row.</td></tr>
<tr><td><b>noToolTip</b></td><td>Boolean</td><td>Removes the question mark that pops up a tooltip.</td></tr>
<tr><td><b>noReset</b></td><td>Boolean</td><td>Removes the "Reset" button.</td></tr>


<tr><td><b>noPaginate</b></td><td>Boolean</td><td>When set to true this option will remove pagination capability. Use with caution: activating <b>noPaginate</b> will cause all rows to be downloaded to the browser at once. If the SQL result set consists of many thousands of rows this might not be wanted.</td></tr>
<tr><td><b>alwaysPaginate</b></td><td>Boolean</td><td>Removes the button: "No Pagination"</td></tr>
<tr><td><b>gridControlKey</b></td><td>String</td><td>Used in conjuction with <b>gridControlHtml</b>. This will be the name of the column that represents the unique identifier of the returned result set. Typically this would be a Primary Key field that was included in the SELECT statement but was hidden from the user using <b>hideColumns</b>.</td></tr>
<tr><td><b>gridControlHtml</b></td><td>String</td><td>Used in conjuction with <b>gridControlKey</b>. This represents the html that creates controls for individual rows in the grid.  You can style the controls as buttons for "view" "update", "delete" for example.  Or you can use img tags to make clickable icons that perform actions on the selected row. <i>The key thing to understand is that wherever you place the string: "gridControlKey" it will be replaced with the value found in the column designated by</i> <b>gridControlKey</b>.<br><br>Here are some examples of what this might look like:<br><br>
<table border="1"><tr><td>
'gridControlHtml' =&gt; &quot;&lt;a href='myprocesspage.php?editkey=gridControlKey'&gt;Edit&lt;/a&gt; &lt;a href='myprocesspage.php?deletekey=gridControlKey'&gt;Delete&lt;/a&gt;&quot;<br><br>
</td></tr>
<tr><td>
'gridControlHtml' =&gt; &quot;&lt;input type='button' onClick='location.href=\&quot;?showUpdateForm=gridControlKey\&quot;' value=\&quot;Edit\&quot;&gt;&quot;<br><br>
</td></tr>
<tr><td>
'gridControlHtml' =&gt; &quot;&lt;img onClick=\&quot;view('gridControlKey');\&quot; src='mysqlgrid/view.png'&gt;&lt;img onClick=\&quot;edit('gridControlKey');\&quot; src='mysqlgrid/update.png'&gt;&lt;img onClick=\&quot;kill('gridControlKey');\&quot; src='mysqlgrid/delete.png'&gt;&quot;<br><br>
</td></tr>
</table>
</td></tr>
</table>
<h4>Other Notes:</h4>
MySqlGrid checks to see if jQuery is present, and if not, automatically loads it from a local copy. If you otherwise need to load jQuery in your script it is recommended to add it in the head section prior to instantiating the Mysqlgridmain object.

<h4>Acknowledgments</h4>
A huge thank you to Czarek Tomczak for <a href="https://code.google.com/p/expandselect/">ExpandSelect.js</a> and to botmonster for <a href="http://botmonster.com/jquery-bootpag/#.VZqNtvlViko">bootpag</a>.  You guys are JavaScript geniuses!

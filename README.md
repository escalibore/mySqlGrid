# mySqlGrid
A powerful yet easy to use Ajax datagrid for PHP/MySQL

Demo: http://tberthold.com/mySqlGrid/tester.php

MySqlGrid lets you easily create a sortable, searchable, and paginated data grid from ANY valid MySQL SELECT statement.  Each column will automatically have an input field where the user can enter a substring to filter the results.  If the user selects the drop-down icon for a column MySqlGrid dynamically builds an html select element from the unique values for that column.

I created mySqlGrid with a focus on security and simplicity.  The learning curve is very minimal, provided you understand your database and SQL.  Once you have a valid SQL query, you are minutes away from providing a flexible and beautiful data grid that will allow your users to quickly find the information they need.

To use mySqlGrid follow these steps:<br>
1. Copy directory "mysqlgrid" to a directory on your web server.<br> 
2. Specify your mysqli database connection in <a href="https://github.com/escalibore/mySqlGrid/blob/master/mysqlgrid/dbconnect.php">dbconnect.php</a><br>
3. Include the file "mysqlgrid.php" in your PHP script.<br> 
4. Specify your SQL Select statement (and any other optional parameters) in the "$mySqlGridOptions" array.  
5. In the body of your html you will need to create two div elements - one for the datagrid, and one for the pagination area.  These two divs must be given ids of "mySqlGridTable" and "mySqlGridPagination", respectively.<br>  

For a basic example see <a href="https://github.com/escalibore/mySqlGrid/blob/master/basicgrid.php">basicgrid.php</a>.

<h4>MySqlGrid Options are specified in the header of your html, in a PHP array called "$mySqlGridOptions".  The options are as follows:</h4>
<table>
<tr><th>Option Name</th><th>Type</th><th>Description</th></tr>
<tr><td><b>sql</b></td><td>String</td><td>A standard mySQL SELECT statement.  Any statement that returns rows from your mySQL database will be transformed into a grid.  <b>sql</b> is actually the only option that is required to generate a grid.</td></tr>
<tr><td><b>includePath</b></td><td>String</td><td>This is the path to the "mysqlgrid" directory. If you do not specify an <b>includePath</b> mySqlGrid assumes the same directory as your php script.  <b>includePath</b> needs to have a "/" as the last character.  It is recommended to use relative paths.  Example: "../somedirectory/anotherdirectory/"</td></tr>
<tr><td><b>lineCount</b></td><td>Integer</td><td>The number of rows in each paginated grid.  If <b>lineCount</b> is not specified mySqlGrid will display 25 rows per page.</td></tr>
<tr><td><b>hideColumns</b></td><td>Array</td><td>Specifies columns in the mySQL result set that will not be displayed on the grid.  This is handy when you want to get a table's primary key value that means nothing to the user, but will be used to perform an action on a selected row. (See options <b>gridControlHtml</b> and <b>gridControlKey</b>)</td></tr>
<tr><td><b>hideSelects</b></td><td>Array</td><td>Specifies columns that will not be given dynamic drop-down select capability.  They will still be searchable by substring.</td></tr>
<tr><td><b>noSelects</b></td><td>Boolean</td><td>When set to true this option will remove dynamic drop-down select capability from all columns.</td></tr>
<tr><td><b>noPaginate</b></td><td>Boolean</td><td>When set to true this option will remove pagination capability. Use with caution: activating <b>noPaginate</b> will cause all rows to be downloaded to the browser at once. If the SQL result set consists of many thousands of rows this might not be wanted.</td></tr>
<tr><td><b>alwaysPaginate</b></td><td>Boolean</td><td>Removes the button: "No Pagination"</td></tr>
<tr><td><b>gridControlKey</b></td><td>String</td><td>Used in conjuction with <b>gridControlHtml</b>. This will be the name of the column that represents the unique identifier of the returned result set. Typically this would be a Primary Key field that was included in the SELECT statement but was hidden from the user using <b>hideColumns</b>.</td></tr>
<tr><td><b>gridControlHtml</b></td><td>String</td><td>Used in conjuction with <b>gridControlKey</b>. This represents the html that creates controls for individual rows in the grid.  You can style the controls as buttons for "view" "update", "delete" for example.  Or you can use img tags to make clickable icons that perform actions on the selected row.  The key thing to understand is that in your html wherever you place the string: "gridControlKey" it will be replaced with the value found in the column designated by <b>gridControlKey</b>.<br><br>Here are some examples of what this might look like:<br><br>
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
MySqlGrid checks to see if jQuery is present, and if not, automatically loads it from a local copy stored in directory "mysqlgrid". If you otherwise need to load jQuery in your script it is recommended to add it in the head section above the $mySqlOptions array.
<h4>Acknowledgments</h4>
A huge thank you goes out to Czarek Tomczak for <a href="https://code.google.com/p/expandselect/">ExpandSelect.js</a> and to botmonster for <a href="http://botmonster.com/jquery-bootpag/#.VZqNtvlViko">bootpag</a>.  You guys are JavaScript geniuses and mySqlGrid would not have been possible without your generous open source contributions.








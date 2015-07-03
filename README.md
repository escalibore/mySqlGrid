# mySqlGrid
A powerful yet easy to use datagrid for PHP/MySQL

Demo: http://tberthold.com/mySqlGrid/tester.php

MySqlGrid generates a sortable, searchable, paginated datagrid from ANY valid MySQL "Select" statement.  Each column has an input field where the user can enter a substring that forms a filter on the rows returned.  If the user selects the drop down icon for a column MySqlGrid dyanamically builds a select element from the unique values for that column for the current result set.

To use mySqlGrid simply copy the directory "mysqlgrid" into you web folder. In your php file, before the <body> tag create a mysqli connection object. Include the file "mysqlgrid.php" into your script. Specify your SQL Select statement.  In the body of your file you will need to create two div elements - one for the datagrid and one for the pagination area.  These will be given ids of "mySqlGridTable" and "mySqlGridPagination", respectively.  See the file "tester.php" for a basic example.







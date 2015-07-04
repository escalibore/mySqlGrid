# mySqlGrid
A powerful yet easy to use datagrid for PHP/MySQL

Demo: http://tberthold.com/mySqlGrid/tester.php

MySqlGrid generates a sortable, searchable, paginated datagrid from ANY valid MySQL "Select" statement.  Each column will have an input field where the user can enter a substring that forms a filter on the rows returned.  If the user selects the drop down icon for a column MySqlGrid dyanamically builds a select element from the unique values for that column for the current result set.

To use mySqlGrid simply follow these steps:
1. Copy the directory "mysqlgrid" into your web directory. 
2. Specify mysqli database connection object in "dbconnect.php" (see example provided)
3. Include the file "mysqlgrid.php" into your script. 
4. Specify your SQL Select statement (and other optional parameters).  
5. In the body of your html you will need to create two div elements - one for the datagrid and one for the pagination area.  These divs must be given ids of "mySqlGridTable" and "mySqlGridPagination", respectively.  

See file "tester.php" for a basic example.







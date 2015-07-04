# mySqlGrid
A powerful yet easy to use datagrid for PHP/MySQL

Demo: http://tberthold.com/mySqlGrid/tester.php

MySqlGrid produces a sortable, searchable, and paginated data grid from ANY valid MySQL "Select" statement.  Each column will have an input field where the user can enter a substring to filter the results.  If the user selects the drop down icon for a column MySqlGrid dyanamically builds a select element from the unique values for that column for the current result set.

I created mySqlGrid with a focus on security and simplicity.  The learning curve is very minimal, provided you understand your database and SQL.  Once you have a valid SQL query, you are minutes away from providing a flexible and beautiful data grid that will allow your users to quickly find the information they need.

To use mySqlGrid follow these steps:
1. Copy directory "mysqlgrid" into your web directory. 
2. Specify your mysqli database connection in "dbconnect.php" (see example provided)
3. Include the file "mysqlgrid.php" in your PHP script. 
4. Specify your SQL Select statement (and any other optional parameters) in the "$mySqlGridOptions" array.  
5. In the body of your html you will need to create two div elements - one for the datagrid, and one for the pagination area.  These two divs must be given ids of "mySqlGridTable" and "mySqlGridPagination", respectively.  

For a basic example of this, see file "tester.php".







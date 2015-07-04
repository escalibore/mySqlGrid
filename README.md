# mySqlGrid
A powerful yet easy to use datagrid for PHP/MySQL

Demo: http://tberthold.com/mySqlGrid/tester.php

MySqlGrid generates a sortable, searchable, paginated datagrid from ANY valid MySQL "Select" statement.  Each column will have an input field where the user can enter a substring that becomes a filter on the rows returned.  If the user selects the drop down icon for a column MySqlGrid dyanamically builds a select element from the unique values for that column for the current result set.

I created mySqlGrid with a focus on simplicity.  The learning curve is almost non-existent, provided you understand your database and SQL.  Once you have a valid SQL query, you are minutes away from providing a powerful and beautiful data grid that will allow your users to quickly and easily find the information they need.

To use mySqlGrid follow these steps:
1. Copy the directory "mysqlgrid" into your web directory. 
2. Specify your mysqli database connection in "dbconnect.php" (see example provided)
3. Include the file "mysqlgrid.php" in your PHP script. 
4. Specify your SQL Select statement (and any other optional parameters).  
5. In the body of your html you will need to create two div elements - one for the datagrid, and one for the pagination area.  These divs must be given ids of "mySqlGridTable" and "mySqlGridPagination", respectively.  

See file "tester.php" for a basic example.







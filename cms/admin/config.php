<?php
define('DB_SERVER','160.153.133.116');
define('DB_USER','cms');
define('DB_PASS' ,'Pnm!969#');
define('DB_NAME', 'cms');
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);

// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
 }

?>

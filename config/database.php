<?php
define('DB_SERVER','160.153.133.116');
define('DB_USER','cobol1962');
define('DB_PASS' ,'150620071010');
define('DB_NAME', 'samyretail');
$mysqli = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
$sql_details = array(
    'user' => "cobol1962",
    'pass' => "150620071010",
    'db'   => "samyretail",
    'host' => '160.153.133.116'
);
// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
 }

?>

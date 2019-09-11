<?php

// DB table to use
session_start();

include "../config/database.php";
$table =  "products";


// indexes
$columns = array(
	array(
    'db' => 'id',
    'dt' => 'id'
  ),
	array(
		'db' => 'category',
		'dt' => 'category'),
  array(
    'db' => 'name',
    'dt' => 'name'),

);

// SQL server connection information


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
require($_SERVER["DOCUMENT_ROOT"] . "/classes/ssp.class.php");
echo json_encode(
    SSP::simple( $_REQUEST, $sql_details, $table, "id", $columns )
);

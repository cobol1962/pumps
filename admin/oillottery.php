<?php

// DB table to use
session_start();

include "../config/database.php";
$table =  "oil_lottery";


// indexes
$columns = array(
	array(
    'db' => 'oilid',
    'dt' => 'oilid'
  ),
	array(
		'db' => 'name',
		'dt' => 'name'),
  array(
    'db' => 'type',
    'dt' => 'type'),
	array(
		'db' => 'price',
		'dt' => 'price'),
  array(
		'db' => 'discount',
		'dt' => 'discount'),
  array(
  	'db' => 'active',
  	'dt' => 'active'),
  array(
    'db' => 'date_added',
    'dt' => 'date_added'),

);

// SQL server connection information


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
require($_SERVER["DOCUMENT_ROOT"] . "/classes/ssp.class.php");
echo json_encode(
    SSP::simple( $_REQUEST, $sql_details, $table, "oilid", $columns )
);

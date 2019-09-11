<?php

// DB table to use
session_start();

include "../config/database.php";
$table =  "users";


// indexes
$columns = array(
	array(
    'db' => 'id',
    'dt' => 'id'
  ),
	array(
		'db' => 'site',
		'dt' => 'site'),
	array(
		'db' => 'address',
		'dt' => 'address'),
  array(
		'db' => 'email',
		'dt' => 'email'),
  array(
  	'db' => 'contactno',
  	'dt' => 'contactno'),
  array(
    'db' => 'posting_date',
    'dt' => 'posting_date'),
	array(
    'db' => 'active',
    'dt' => 'active'),
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

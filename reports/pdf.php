<?php
// Load autoloader (using Composer)
require __DIR__ . '/vendor/autoload.php';
$pdf = new TCPDF();                 // create TCPDF object with default constructor args
$pdf->AddPage();                    // pretty self-explanatory
$pdf->Write(1, 'Revenue');      // 1 is line height

$pdf->Output('revenue.pdf');    // send the file inline to the browser (default).

?>

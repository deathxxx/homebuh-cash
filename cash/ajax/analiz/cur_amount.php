<?php
require_once("../../lib/init.php");
require_once("../../lib/analiz.php");

$ch_analiz = new CashAnaliz($db, $usr, $lng);
echo json_encode( $ch_analiz->getCurAmount($_GET['from'], $_GET['to'], $_GET['in'], $_GET['usr']) );
?>


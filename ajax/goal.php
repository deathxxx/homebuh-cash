<?
require_once("../lib/init.php");
require_once("../lib/goal.php");

$goal = new Goal($db, $usr, $lng);
echo json_encode( $goal->getList($_GET['uid'], $_GET['show_fact']) );
?>


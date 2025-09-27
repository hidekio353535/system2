<?php
require_once("../php/db_connect.php");

$q = strtolower($_GET["q"]);
if (!$q) return;

$sql = "SELECT hinban, hinmei1, color FROM te_product_master WHERE (hinban like '".$q."%') ORDER BY hinban";
$query = mysql_query($sql);

$items = array();

while ($row = mysql_fetch_object($query)) {
	echo $row->hinban . ":" . $row->hinmei1 . ":" . $row->color . "\n";
}
?>

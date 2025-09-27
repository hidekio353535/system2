<?php
require_once("db_connect.php");
require_once("common.php");

$parm = $_REQUEST['parm'];

$flag = $parm[0];
$id = 	$parm[1];
$table =$parm[2];

if($flag == "ON") {
	$sql_up = "UPDATE {$table} SET s_rep_print_flag = 1 WHERE s_id = '{$id}' ";
	$query_up = mysql_query($sql_up);
	if($query_up) {
		echo "<a href='#' style='color:green;text-decoration: none' class='rep_icon' data-id='{$id}' data-table='{$table}'>印済</a>";
	}
	else {
		echo "";
	}
}
else if($flag == "TOGGLE") {
	//引数
	$sql = "SELECT * FROM {$table} WHERE s_id = '{$id}' ";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_object($query)) {
		if($row->s_rep_print_flag) {
			$sql_up = "UPDATE {$table} SET s_rep_print_flag = 0 WHERE s_id = '{$id}' ";
			$query_up = mysql_query($sql_up);
			if($query_up) {
				echo "<a href='#' style='color:red;text-decoration: none' class='rep_icon' data-id='{$id}' data-table='{$table}'>印未</a>";
			}
			else {
				echo "";
			}
		}
		else {
			$sql_up = "UPDATE {$table} SET s_rep_print_flag = 1 WHERE s_id = '{$id}' ";
			$query_up = mysql_query($sql_up);
			if($query_up) {
				echo "<a href='#' style='color:green;text-decoration: none' class='rep_icon' data-id='{$id}' data-table='{$table}'>印済</a>";
			}
			else {
				echo "";
			}
		}
	}
}
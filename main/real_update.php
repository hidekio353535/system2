<?php

require_once("../php/db_connect.php");

if( isset($_REQUEST['id']) && isset($_REQUEST['st_key'])&& isset($_REQUEST['st_val']) ) {

	$id = $_REQUEST['id'];
	$st_key = $_REQUEST['st_key'];
	$st_val = $_REQUEST['st_val'];

	$sql = "SELECT * FROM matsushima_sheet WHERE st_s_id = '{$id}' AND st_key = '{$st_key}' ";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

//echo $sql;	

	if($num) {
		$sql = "UPDATE matsushima_sheet SET st_val = '{$st_val}' WHERE st_s_id = '{$id}' AND st_key = '{$st_key}' ";
		$query = mysql_query($sql);
	}
	else {
		$sql = "INSERT INTO matsushima_sheet (st_s_id, st_key, st_val) VALUES ('{$id}' , '{$st_key}' , '{$st_val}')";
		$query = mysql_query($sql);
	}

//echo $sql;	

}
else {
	exit();	
}

?>

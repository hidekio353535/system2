<?php
session_start();

require_once("../php/db_connect.php");
require_once("../php/common.php");

if( isset($_POST['flag']) ) {
	$flag = $_POST['flag'];
}
else {
	$data = array("status" => "NG", "msg" => "パラメータエラー");
	dg_return_json($data);
}

if($flag === "SAVE") {
	//トランザクション
	if( !empty($_POST['update_info']) ) {
		$update_info = $_POST['update_info'];
	}
	else {
		//変更箇所なし

		//変更箇所が無い
		$data = array("status" => "OK", "msg" => $msg);
		dg_return_json($data);
	}
	
	//トランザクションスタート
	$sql = "set autocommit = 0";
	$query = mysql_query($sql);
	$sql = "begin";
	$query = mysql_query($sql);
	$sql_text = "sql:";
	for($i=0; $i < count($update_info) ; $i++) {
		
		if(!$update_info[$i]['val'] && ($update_info[$i]['field_name'] == "np_kazu" || $update_info[$i]['field_name'] == "np_tanka" || $update_info[$i]['field_name'] == "np_kin")) {
			$sql = "UPDATE {$update_info[$i]['table']}
					SET
						{$update_info[$i]['field_name']} = NULL
					WHERE
						{$update_info[$i]['id_field']} = {$update_info[$i]['id']}
					";
		}
		else {
			$sql = "UPDATE {$update_info[$i]['table']}
					SET
						{$update_info[$i]['field_name']} = '{$update_info[$i]['val']}'
					WHERE
						{$update_info[$i]['id_field']} = {$update_info[$i]['id']}
					";
		}
		$query = mysql_query($sql);
		dg_rollback($query, "保存エラー");
		$sql_text .= $sql;
	}
	
	//トランザクションコミット
	$sql = "commit";
	$query = mysql_query($sql);
	$sql = "set autocommit = 1";
	$query = mysql_query($sql);
		
	$data = array("status" => "OK", "msg" => $sql_text);
	dg_return_json($data);
	exit();
	
}

function dg_return_json($data) {
	header('Content-type: text/html');
	echo json_encode($data);
	exit();
}
function dg_rollback($q, $sql_text="") {
	if(!$q) {
		$sql = "rollback";
		mysql_query($sql);
		$sql = "set autocommit = 1";
		$query = mysql_query($sqle);

		$data = array("status" => "NG", "msg" => $sql_text);
		dg_return_json($data);
		exit();
	}
}
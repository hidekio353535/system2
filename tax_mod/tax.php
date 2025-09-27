<?php

//システムの統一端数処理ルール
$GLOBALS['hasu'] = 1;  //0:ceil 1:round 2:floor

/****************************************************************************
*
*	役割:Control
*	関数名:calc_wtax
*	説明:
*
*****************************************************************************/
function calc_wtax($_val, $_date) {
	
	//日付が不定の場合は今日
	if($_date == "" || $_date == "0000-00-00" || $_date == null) {
		$_date = date("Y-m-d");
	}
	
	$sql = "SELECT * FROM tbl_tax WHERE tx_st_date <= '{$_date}' ORDER BY tx_id DESC LIMIT 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if($num) {
		$row = mysql_fetch_object($query);
		return sround2( $_val * (($row->tx_tax / 100) + 1) );
	}
	else {
		//何にもヒットしない
		return $_val;
	}
}

/****************************************************************************
*
*	役割:Control
*	関数名:get_tax
*	説明:
*
*****************************************************************************/
function get_tax($_date) {
	
	//日付が不定の場合は今日
	if($_date == "" || $_date == "0000-00-00" || $_date == null) {
		$_date = date("Y-m-d");
	}
	
	$sql = "SELECT * FROM tbl_tax WHERE tx_st_date <= '{$_date}' ORDER BY tx_id DESC LIMIT 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if($num) {
		$row = mysql_fetch_object($query);
		return $row->tx_tax;
	}
	else {
		//何にもヒットしない
		return 0;
	}
}

/**********************************************************************
 *
 *  統一端数処理
 *
 **********************************************************************/
function sround2($_val) {

	switch($GLOBALS['hasu']) {
		case 0:
			return ceil($_val);
			break;		
		case 1:
		default:
			return round($_val);
			break;		
		
		case 2:
			return floor($_val);
			break;		
	}
}

?>
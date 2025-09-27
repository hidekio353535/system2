<?php
session_start();

require_once("../php/db_connect.php");
require_once("../php/common.php");

/**********************************************************************
 *
 * 	パラメータ受け取り
 *
 **********************************************************************/
if(isset($_REQUEST['parm'])){
	$parm = $_REQUEST['parm'];
	$flag = $parm[0];

} else {
	return false;
}


if($flag == "EDIT_NIPPOU") {
	
	$hat_id = $parm[1];
	$hat_flag = $parm[2];
	
	$width = 960;//640
	$td_width = $width / 12;
	$cellpadding = 3;
	$form_flag = true;
	$msize = 10;
	
	require_once("../nippou/nippou.php");
	require_once("../nippou/check_data.php");
	require_once("../nippou/table.php");
	

	echo $html;
	
}

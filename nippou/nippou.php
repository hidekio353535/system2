<?php
/* 初期化 */
require_once("../php/db_connect.php");

$hat_slip = "matsushima_slip_hat";
if($hat_flag) {
	$hat_slip = "matsushima_slip_jv";
}

//パラメータチェック
if(!$hat_id) {
	echo "<p style='font-size:21px;color:red'>対象の発注がありません。</p>";
	exit();
}

/* DB 処理 */

$sql = "SELECT * FROM {$hat_slip} 
		LEFT OUTER JOIN matsushima_genba ON g_id = s_genba_id 
		LEFT OUTER JOIN matsushima_seko ON seko_id = s_seko_id
		LEFT OUTER JOIN matsushima_kouji_syu_hat ON s_seko_kubun_id = sy_id
		LEFT OUTER JOIN matsushima_moto ON moto_id = g_moto_id
		WHERE s_id = '{$hat_id}' ";
$query = mysql_query($sql);
$num = mysql_num_rows($query);

if(!$num) {
	echo "<p style='font-size:21px;color:red'>対象の発注がありません。</p>";
	exit();
}

while ($row = mysql_fetch_object($query)) {
	
	//ID  JVの場合  jv を追記
	$id = $row->g_id ."-".$row->s_id;

	//職方
	$seko = $row->seko;
	
	//jvの場合は複数のseko
	if($hat_slip == "matsushima_slip_jv") {
		
		$id .= "-JV";
		
		$seko = "";
		$sql_seko = "SELECT * FROM matsushima_jv_rel
					INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_jv_rel.jv_seko_id
					WHERE jv_slip_id = '{$hat_id}'";
		$query_seko = @mysql_query($sql_seko);
		$num_seko = @mysql_num_rows($query_seko);
		if($num_seko) {
			while ($row_seko = mysql_fetch_object($query_seko)) {
				$seko .= $row_seko->seko . " ";
			}
			if($seko) {
				$seko .= " JV";
			}
		}
	}
	
	//日付
	$s_st_date = $row->s_st_date;
	if($s_st_date && $s_st_date != "0000-00-00") {
		$year = date("Y", strtotime($s_st_date))."年";
		$month = date("n", strtotime($s_st_date));
		$day = date("j", strtotime($s_st_date));
		$date = date("Y年n月j日", strtotime($s_st_date));
	}
	else {
		$year = "";
		$month = "";
		$day = "";
		$date = "";
	}
	
	//施工区分
	$sy_name = $row->sy_name;
	
	//元請名
	$moto = $row->moto;
	
	//現場名
	$g_genba = $row->g_genba;
	
	//現場住所
	$g_genba_address = $row->g_genba_address;
	
	//主担当
	$sql_st = "SELECT * FROM matsushima_tantou WHERE t_id = '{$row->g_tantou_id}' ";
	$query_st = mysql_query($sql_st);
	$syu_tantou = "";
	while ($row_st = mysql_fetch_object($query_st)) {
		$syu_tantou = $row_st->t_tantou;
	}
	
	//副担当
	$sql_st = "SELECT * FROM matsushima_tantou WHERE t_id = '{$row->g_tantou_sub_id}' ";
	$query_st = mysql_query($sql_st);
	$sub_tantou = "";
	while ($row_st = mysql_fetch_object($query_st)) {
		$sub_tantou = $row_st->t_tantou;
	}
	//m2
	$g_m2 = $row->g_m2;
	
	if(is_numeric($g_m2)) {
		$m2_num = $g_m2;
	}
	
	//備考
	$s_biko = $row->s_biko;
	//工事内容2
	$g_nai2_id = $row->g_nai2_id;
	//施工区分ID
	$s_seko_kubun_id = $row->s_seko_kubun_id;
	
}


?>
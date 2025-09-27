<?php
session_start();

require_once("../php/db_connect.php");
require_once("../php/common.php");

/*
$hat_id
$hat_flag
$hat_slip
*/	
	
$sql = "SELECT * FROM matsushima_nippou_meisan WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}'";
$query = mysql_query($sql);
$num = mysql_num_rows($query);

if(!$num) {
	//データが無いのでデフォルトセット
	$sql = "INSERT INTO matsushima_nippou_meisan
			(np_rel_id,np_flag,np_name,np_unit,np_tanka,np_biko,corder)
			SELECT '{$hat_id}' as np_rel_id,'{$hat_flag}' as np_flag,nm_name,nm_unit,nm_tanka,nm_biko,corder FROM matsushima_nippou_master ORDER BY corder
	";
	$query = mysql_query($sql);
	
	for($i=0;$i<5;$i++) {
		$od = 1000 + $i;
		$sql = "INSERT INTO matsushima_nippou_meisan
				(np_rel_id,np_flag,corder)
				SELECT '{$hat_id}' as np_rel_id,'{$hat_flag}' as np_flag,'{$od}' as corder
		";
		$query = mysql_query($sql);
	}
	
	//デフォルト値セット
	if($m2_num) {
		if($g_nai2_id == 1 || $g_nai2_id == 2) {
			// 1F or 2F
			if($s_seko_kubun_id == 1 || $s_seko_kubun_id == 2) {
				//架払 or 架
				$sql = "UPDATE matsushima_nippou_meisan SET np_kazu = '{$m2_num}', np_kin = TRUNCATE({$m2_num} * np_tanka,0) WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' AND np_name='外部足場(延床)架2Fまで'";
				$query = mysql_query($sql);
			}
			if($s_seko_kubun_id == 1 || $s_seko_kubun_id == 3) {
				//架払 or 払
				$sql = "UPDATE matsushima_nippou_meisan SET np_kazu = '{$m2_num}', np_kin = TRUNCATE({$m2_num} * np_tanka,0) WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' AND np_name='外部足場(延床)払2Fまで'";
				$query = mysql_query($sql);
			}
		}
		else if($g_nai2_id == 3) {
			//3F
			if($s_seko_kubun_id == 1 || $s_seko_kubun_id == 2) {
				//架払 or 架
				$sql = "UPDATE matsushima_nippou_meisan SET np_kazu = '{$m2_num}', np_kin = TRUNCATE({$m2_num} * np_tanka,0) WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' AND np_name='外部足場(延床)架3F'";
				$query = mysql_query($sql);
			}
			if($s_seko_kubun_id == 1 || $s_seko_kubun_id == 3) {
				//架払 or 払
				$sql = "UPDATE matsushima_nippou_meisan SET np_kazu = '{$m2_num}', np_kin = TRUNCATE({$m2_num} * np_tanka,0) WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' AND np_name='外部足場(延床)払3F'";
				$query = mysql_query($sql);
			}
		}
		else if($g_nai2_id == 16) {
			//3F
			if($s_seko_kubun_id == 1 || $s_seko_kubun_id == 2) {
				//架払 or 架
				$sql = "UPDATE matsushima_nippou_meisan SET np_kazu = '{$m2_num}', np_kin = TRUNCATE({$m2_num} * np_tanka,0) WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' AND np_name='外部足場(延床)架2.5F'";
				$query = mysql_query($sql);
			}
			if($s_seko_kubun_id == 1 || $s_seko_kubun_id == 3) {
				//架払 or 払
				$sql = "UPDATE matsushima_nippou_meisan SET np_kazu = '{$m2_num}', np_kin = TRUNCATE({$m2_num} * np_tanka,0) WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' AND np_name='外部足場(延床)払2.5F'";
				$query = mysql_query($sql);
			}
		}
	}
	//塞ぎ
	if($s_seko_kubun_id == 4) {
		$sql = "UPDATE matsushima_nippou_meisan SET np_kazu = 1, np_kin = TRUNCATE(1 * np_tanka,0) WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' AND np_name='開口塞ぎ(シート・圧縮・親綱施工)'";
		$query = mysql_query($sql);
	}
}

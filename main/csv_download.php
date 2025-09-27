<?php
session_start();
require_once("../php/db_connect.php");

$enter = "\r\n";
$delimiter = ",";
$kakomi = '"';
$path_file = "genba.csv";

if(!isset($_SESSION['csv_sql'])) {
	echo "検索条件を指定してCSVをダウンロードして下さい。";
	exit();
}

//初期化
$csv = "";
//sql
$sql = $_SESSION['csv_sql'];
//join 追加
$sql = preg_replace('/FROM matsushima_genba WHERE 1/', 'FROM matsushima_genba LEFT OUTER JOIN matsushima_tantou ON t_id = g_tantou_id LEFT OUTER JOIN matsushima_est_syu ON sy_id = g_status LEFT OUTER JOIN matsushima_moto ON moto_id = g_moto_id LEFT OUTER JOIN matsushima_nai_1 ON nai1_id = g_nai1_id LEFT OUTER JOIN matsushima_nai_2 ON nai2_id = g_nai2_id LEFT OUTER JOIN matsushima_nai_3 ON nai3_id = g_nai3_id WHERE 1', $sql);
//echo "SQL:" . $sql . "<br />";	

//タイトル
$csv .= q("現場ID");
$csv .= q("ステータス");
$csv .= q("現場");
$csv .= q("住所");
$csv .= q("担当");
$csv .= q("元請");
$csv .= q("支店");
$csv .= q("元請担当者");
$csv .= q("工事1");
$csv .= q("工事2");
$csv .= q("工事3");
$csv .= q("m2数");
$csv .= q("見積額");
$csv .= q("請求額");
$csv .= q("発注額");
$csv .= q("発注可能額");
$csv .= q("発注％");
$csv .= q("備考", false);
$csv .= $enter;

$query = mysql_query($sql);
while ($row = mysql_fetch_object($query)) {

	$csv .= q($row->g_id);
	$csv .= q($row->sy_name);
	$csv .= q($row->g_genba);
	$csv .= q($row->g_genba_address);
	$csv .= q($row->t_tantou);
	$csv .= q($row->moto);
	$csv .= q($row->branch);
	$csv .= q($row->g_moto_tantou);
	$csv .= q($row->nai1);
	$csv .= q($row->nai2);
	$csv .= q($row->nai3);
	$csv .= q($row->g_m2);
	$csv .= q($row->est);
	$csv .= q($row->inv);
	$csv .= q($row->hat);
	
	$kanou = floor($row->inv * 0.4) - $row->hat;
	$csv .= q($kanou);

	if($row->inv) {
		$hat_per = floor($row->hat / $row->inv * 100);
	}
	else {
		$hat_per = "-";
	}
	$csv .= q($hat_per);
	
	$csv .= q($row->g_biko, false);
	
	$csv .= $enter;
}

//echo $csv;

//encording
$csv = mb_convert_encoding($csv, "SJIS", "UTF-8");
//書き込み
file_put_contents($path_file, $csv);

/* ダウンロード用のHTTPヘッダ送信 */
header("Content-Disposition: inline; filename=\"".basename($path_file)."\"");
header("Content-Type: application/octet-stream");

/* ファイルを読んで出力 */
if (!readfile($path_file)) {
	die("Cannot read the file(".$path_file.")");
}
else {
	unlink($path_file);
}

function q($str, $del = true) {
	global $delimiter;
	global $kakomi;
	$str = preg_replace('/\"|\'|\,|\n|\r|0000\-00\-00/', '', $str);
	$str = $kakomi . $str . $kakomi;
	if($del) {
		$str .= $delimiter;
	}
	return $str;
}

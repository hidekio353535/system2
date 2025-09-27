<?php
session_start();
require_once("../php/db_connect.php");
$enter = "\r\n";
//$enter = "<br />";
$delimiter = ",";
$kakomi = '"';
$path_file = "inv_list.csv";

if(!isset($_SESSION['csv_sql'])) {
	echo "検索条件を指定してCSVをダウンロードして下さい。";
	exit();
}

//初期化
$csv = "";

//sql
$sql = $_SESSION['csv_sql'];

//join 追加
//$sql = preg_replace('/FROM matsushima_genba WHERE 1/', 'FROM matsushima_genba LEFT OUTER JOIN matsushima_tantou ON t_id = g_tantou_id LEFT OUTER JOIN matsushima_est_syu ON sy_id = g_status LEFT OUTER JOIN matsushima_moto ON moto_id = g_moto_id LEFT OUTER JOIN matsushima_nai_1 ON nai1_id = g_nai1_id LEFT OUTER JOIN matsushima_nai_2 ON nai2_id = g_nai2_id LEFT OUTER JOIN matsushima_nai_3 ON nai3_id = g_nai3_id WHERE 1', $sql);

//echo "SQL:" . $sql . "<br />";	
//exit();

//タイトル
$csv .= q("現場ID");
$csv .= q("受注ID");
$csv .= q("区分");
$csv .= q("現場名");
$csv .= q("現場住所");
$csv .= q("元請");
$csv .= q("支店");
$csv .= q("担当");
$csv .= q("受注日"); //非表示 display:none
$csv .= q("開始日");
$csv .= q("終了日");
$csv .= q("請求締日");
$csv .= q("元請受注額");
$csv .= q("請求額");
$csv .= q("営業3%");
$csv .= q("備考");
$csv .= q("請求", false);

$csv .= $enter;

$query = mysql_query($sql);
while ($row = mysql_fetch_object($query)) {
	$csv .= q($row->g_id);
	$csv .= q($row->s_id);
	$csv .= q($row->sy_name_nik);
	$csv .= q($row->g_genba);
	$csv .= q($row->g_genba_address);
	$csv .= q($row->moto);
	$csv .= q($row->branch);
	$csv .= q($row->t_tantou);
	$csv .= q($row->s_date); //非表示 display:none
	$csv .= q($row->s_st_date);
	$csv .= q($row->s_end_date);
	$csv .= q($row->s_shime_date);
	$csv .= q($row->s_moto_invoice);
	$csv .= q($row->s_invoice);
	$s1 = floor($row->s_invoice*0.03);
	$csv .= q($s1);
	$csv .= q($row->s_biko);
	if($row->s_inv_id) {
		$s2 = "請求締済";
	}
	else {
		$s2 = "未請求";
	}
	$csv .= q($s2, false);
	
	$csv .= $enter;
}

//echo $csv;
//exit();

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

<?php
session_start();
require_once("../php/db_connect.php");

$enter = "\r\n";
//$enter = "<br />";
$delimiter = ",";
$kakomi = '"';
$path_file = "hat.csv";

if(!isset($_SESSION['csv_sql'])) {
	echo "検索条件を指定してCSVをダウンロードして下さい。";
	exit();
}

//初期化
$csv = "";

//sql
$sql = $_SESSION['csv_sql'];

//join 追加
$sql = preg_replace('/FROM matsushima_hat WHERE 1/', 'FROM matsushima_hat LEFT OUTER JOIN matsushima_seko ON seko_id = h_seko_id WHERE 1', $sql);

//echo "SQL:" . $sql . "<br />";	
//exit();

//タイトル
$csv .= q("ID");
$csv .= q("発注先名");
$csv .= q("発注年");
$csv .= q("発注月");
$csv .= q("発注額");
$csv .= q("調整項目名");
$csv .= q("調整金額");
$csv .= q("発注総額");
$csv .= q("発注日");
$csv .= q("発注書送付日");
$csv .= q("振込予定日");
$csv .= q("振込日", false);

$csv .= $enter;

$query = mysql_query($sql);
while ($row = mysql_fetch_object($query)) {
	$csv .= q($row->h_id);
	$csv .= q($row->seko);
	$csv .= q($row->h_year);
	$csv .= q($row->h_month);
	$csv .= q($row->hattyu); //発注額
	$csv .= q($row->h_chosei_name);
	$csv .= q($row->h_chosei);
	$csv .= q($row->hattyuall); //発注総額
	$csv .= q($row->h_hat_date);
	$csv .= q($row->h_send_date);
	$csv .= q($row->h_receipt_yotei_date);
	$csv .= q($row->h_receipt_date, false);
	
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

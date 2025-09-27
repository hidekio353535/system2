<?php
session_start();
require_once("../php/db_connect.php");

$enter = "\r\n";
//$enter = "<br />";
$delimiter = ",";
$kakomi = '"';
$path_file = "inv.csv";

if(!isset($_SESSION['csv_sql'])) {
	echo "検索条件を指定してCSVをダウンロードして下さい。";
	exit();
}

//初期化
$csv = "";

//sql
$sql = $_SESSION['csv_sql'];

//join 追加
$sql = preg_replace('/FROM matsushima_inv WHERE 1/', 'FROM matsushima_inv LEFT OUTER JOIN matsushima_moto ON moto_id = i_moto_id WHERE 1', $sql);

//echo "SQL:" . $sql . "<br />";	
//exit();

//タイトル
$csv .= q("ID");
$csv .= q("元請");
$csv .= q("支店");
$csv .= q("請求年");
$csv .= q("請求月");
$csv .= q("請求額");
$csv .= q("調整項目名");
$csv .= q("調整金額");
$csv .= q("請求総額");
$csv .= q("請求日");
$csv .= q("請求書送付日");
$csv .= q("入金予定日");
$csv .= q("入金日");
$csv .= q("入金額");
$csv .= q("手数料等");
$csv .= q("差額");
$csv .= q("メモ", false);

$csv .= $enter;

$query = mysql_query($sql);
while ($row = mysql_fetch_object($query)) {
	
	$sagaku = $row->invoiceall - ($row->i_commission + $row->i_receipt_kingaku);

	$csv .= q($row->i_id);
	$csv .= q($row->moto);
	$csv .= q($row->branch);
	$csv .= q($row->i_year);
	$csv .= q($row->i_month);
	$csv .= q($row->invoice); //請求額
	$csv .= q($row->i_chosei_name);
	$csv .= q($row->i_chosei);
	$csv .= q($row->invoiceall); //請求総額
	$csv .= q($row->i_inv_date);
	$csv .= q($row->i_send_date);
	$csv .= q($row->i_receipt_yotei_date);
	$csv .= q($row->i_receipt_date);
	$csv .= q($row->i_receipt_kingaku);
	$csv .= q($row->i_commission);
	$csv .= q($sagaku);
	$csv .= q($row->i_biko, false);
	
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

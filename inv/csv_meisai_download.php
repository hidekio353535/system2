<?php
session_start();
require_once("../php/db_connect.php");

$enter = "\r\n";
//$enter = "<br />";
$delimiter = ",";
$kakomi = '"';
$path_file = "inv_meisai.csv";

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
$csv .= q("現場ID");
$csv .= q("受注ID");
$csv .= q("工事日");
$csv .= q("締め日");
$csv .= q("元請け");
$csv .= q("支店");
$csv .= q("現場名");
$csv .= q("現場住所");
$csv .= q("区分");
$csv .= q("請求額");
$csv .= q("備考");

$csv .= $enter;

$query = mysql_query($sql);
while ($row = mysql_fetch_object($query)) {

	$csv .= q("請求ID:".$row->i_id . " ".$row->moto." ".$row->branch." 請求年月:".$row->i_year."年".$row->i_month."月"." 請求額:". $row->invoiceall);
	$csv .= ",,,,,,,";
	$csv .= $enter;
	
	$sql_m = "SELECT * FROM matsushima_slip
			INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
			INNER JOIN matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id
			LEFT OUTER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = s_seko_kubun_id
			WHERE s_inv_id = {$row->i_id} ORDER BY s_st_date, s_id";
	
	
	$query_m = mysql_query($sql_m);
	while ($row_m = mysql_fetch_object($query_m)) {

		$csv .= q($row_m->g_id);
		$csv .= q($row_m->s_id);
		$csv .= q($row_m->s_st_date);
		$csv .= q($row_m->s_shime_date);
		$csv .= q($row_m->moto);
		$csv .= q($row_m->branch);
		$csv .= q($row_m->g_genba); //請求額
		$csv .= q($row_m->g_genba_address);
		$csv .= q($row_m->sy_name_nik);
        $csv .= q($row_m->s_invoice);
		$csv .= q($row_m->s_biko, false);
		$csv .= $enter;
	}
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

<?php
require_once("../php/db_connect.php");

$table = "matsushima_moto";
$ID_FIELD = "moto_id";

$datestr = date("Y_m_d");
$fileName = "moto_list_{$datestr}.csv";

//タイトル
$str = '"ID","元請名","カナ","支店名","宛名","担当名","サブ担当名","郵便番号","住所","電話番号","FAX","担当A","担当A TEL","担当A email","担当B","担当B TEL","担当B email","担当C","担当C TEL","担当C email","備考","仕様"' . "\n";
$clm = '"moto_id","moto","kana","branch","atena","t_tantou","sub","m_postal","m_address","m_tel","m_fax","m_tantou_a","m_tantou_tel_a","m_moto_email_a","m_tantou_b","m_tantou_tel_b","m_moto_email_b","m_tantou_c","m_tantou_tel_c","m_moto_email_c","m_biko","m_shiyo"';

//定数  $rowの実カラム数
$clm_cnt = 20;

$JOIN = "LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_moto.m_tantou_id";

$JYOKEN = "";

//変数
$sql = "SELECT 
moto_id, moto, kana,branch, atena, t_tantou, 
(SELECT t.t_tantou FROM matsushima_tantou as t WHERE t.t_id = m_tantou_sub_id) as sub,
m_postal, m_address, m_tel, m_fax, m_tantou_a, m_tantou_tel_a, m_moto_email_a, m_tantou_b, m_tantou_tel_b, m_moto_email_b, m_tantou_c, m_tantou_tel_c, m_moto_email_c, m_biko, m_shiyo
FROM {$table} {$JOIN} WHERE 1 {$JYOKEN} ORDER BY moto_id";

$fp = fopen($fileName,"w");
flock($fp, LOCK_EX);

//SJISへ変換
$str = mb_convert_encoding($str, "sjis-win", "UTF-8");
fwrite($fp,$str);

$query = mysql_query($sql);
$num = mysql_num_rows($query);

while ($row = mysql_fetch_array($query)) {

	$str = "";

	for($i=0;$i < $clm_cnt;$i++) {

		$tmp = preg_replace('/&quot;/','""',$row[$i]);
		//$tmp = preg_replace('/\n/','<br />',$tmp);
		
		$str .= '"'.$tmp . '",';
	}
	$str = preg_replace('/\,$/', '', $str);
	$str .= "\n";
	
	//SJISへ変換
	$str = mb_convert_encoding($str, "sjis-win", "UTF-8");
	fwrite($fp,$str);
}

fclose($fp);

$fileSize = filesize($fileName);
$mime = 'text/plain';
//$mime = 'application/octet-stream'; // MIMEタイプが不明な場合

header('Content-Type: "' . $mime . '"');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
# Microsoft Internet Explorerと他のブラウザで処理を分けます。
if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');
} else {
  header('Pragma: no-cache');
}
header('Content-Length: ' . $fileSize);

readfile($fileName);

unlink($fileName);

?>

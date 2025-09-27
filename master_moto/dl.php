<?php
require_once("../php/db_connect.php");

//変数
$clm_cnt = 22;
$sql = "SELECT * FROM matsushima_moto";
$fileName = "moto.csv";
//タイトル
$str = '"ID","元請会社名","元請短縮会社名","請求書宛名","担当ID","締めパターン","締めグループ","支払月","支払日","支店名","カナ","消費税計算区分","郵便番号","住所","ＴＥＬ","ＦＡＸ","A現場担当者","A担当者携帯","B現場担当者","B担当者携帯","C現場担当者","C担当者携帯"' . "\r\n";


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
		$tmp = preg_replace('/\n/','',$tmp);
		
		$str .= '"'.$tmp . '"';

		if($i != $clm_cnt-1)
			$str .= ",";
	}

	$str .= "\r\n";
	
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

?>

<?php

if(isset($_REQUEST['d']) && isset($_REQUEST['f'])) {
	$d = $_REQUEST['d']; // ダウンロード対象のファイル名
	$f = urldecode($_REQUEST['f']); // ダウンロード対象のファイル名
	$fileName = $d . "/" . $f;
	
	$f = mb_convert_encoding($f, 'sjis-win', 'utf8');
}
else {
	exit();
}

$fileSize = filesize($fileName);
//$mime = 'text/plain';
$mime = 'application/octet-stream'; // MIMEタイプが不明な場合

header('Content-Type: "' . $mime . '"');
//header('Content-Disposition: attachment; filename="' . $f . '"');
header("Content-Disposition: attachment; filename=\"".basename($f)."\";" );
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
/* ?>*/

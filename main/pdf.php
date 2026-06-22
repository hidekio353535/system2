<?php

if(isset($_REQUEST['d']) && isset($_REQUEST['f'])) {
	$d = $_REQUEST['d']; // ダウンロード対象のディレクトリ
	$f = urldecode($_REQUEST['f']); // ダウンロード対象のファイル名(UTF-8)
	$fileName = $d . "/" . $f;       // 実ファイルパス(UTF-8)
}
else {
	exit();
}

if(!is_file($fileName)) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$fileSize = filesize($fileName);
//$mime = 'text/plain';
$mime = 'application/octet-stream'; // MIMEタイプが不明な場合

// 保存されている本来のファイル名(UTF-8)
$base = basename($f);
// 古いブラウザ用のASCIIフォールバック名(非ASCII・ダブルクォートを除去)
$ascii = preg_replace('/[^\x20-\x7E]/u', '', $base);
$ascii = str_replace('"', '', $ascii);
if($ascii === '') {
	$ascii = 'download';
}

header('Content-Type: ' . $mime);
// RFC 5987/6266: filename* で UTF-8 のファイル名を渡し、現代ブラウザで文字化けを防ぐ
header("Content-Disposition: attachment; filename=\"{$ascii}\"; filename*=UTF-8''" . rawurlencode($base));
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

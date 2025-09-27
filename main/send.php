<?php
/*
$mailTo      = 'ogawa@digitaling.jp';                   // 宛て先アドレス
$mailFrom    = 'ogawa@digitaling.jp';                   // 差出人のメールアドレス
$mailSubject = '添付ファイル付きメール';               // メールのタイトル
$mailMessage = '添付ファイル付きメールのテストです。'; // メール本文
$fileName    = '見積書.pdf';                         // 添付するファイル
$returnMail  = 'ogawa@digitaling.jp';                   // Return-Pathに指定するメールアドレス
*/
# メールで日本語使用するための設定をします。
mb_language("Ja") ;
mb_internal_encoding("UTF-8");

$header  = "From: $mailFrom\r\n";
$header .= "Cc: matsushima.granz@gmail.com\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"__PHPRECIPE__\"\r\n";
$header .= "\r\n";

$body  = "--__PHPRECIPE__\r\n";
$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\r\n";
$body .= "\r\n";
$body .= $mailMessage . "\r\n";
$body .= "--__PHPRECIPE__\r\n";

# 添付ファイルへの処理をします。
$handle = fopen($fileName, 'r');
$attachFile = fread($handle, filesize($fileName));
fclose($handle);
$attachEncode = base64_encode($attachFile);

$body .= "Content-Type: image/jpeg; name=\"$fileName\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
$body .= "\r\n";
$body .= chunk_split($attachEncode) . "\r\n";
$body .= "--__PHPRECIPE__--\r\n";

# メールの送信と結果の判定をします。セーフモードがOnの場合は第5引数が使えません。
if (ini_get('safe_mode')) {
  $result = mb_send_mail($mailTo, $mailSubject, $body, $header);
} else {
  $result = mb_send_mail($mailTo, $mailSubject, $body, $header,
                         '-f' . $returnMail);
}

unlink($fileName);

if ($result) {
  echo '<p style="color:green;font-size:24px;">見積メールを送信しました。</p>';
} else {
  echo '<p>送信に失敗しました。</p>';
  exit();
}
?>

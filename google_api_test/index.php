<?php
require_once( "../google-api-php-client/src/Google_Client.php");
require_once ("../google-api-php-client/src/contrib/Google_DriveService.php");

$client = new Google_Client();// Get your credentials from the APIs Console
$client->setClientId(‘25940758819-q6iu1ms7acgnvh3gg7h37v5e4nk3h80a.apps.googleusercontent.com’);
$client->setClientSecret(‘2vn4BnRiOrvEjdTBfmJnnMoi’);
$client->setRedirectUri("http://digitaling.sakura.ne.jp/granz/system2/oauth2callback");
$client->setScopes(array("https://www.googleapis.com/auth/drive"));
$service = new Google_DriveService($client);
# codeパラメータがGETに無ければ、取得するためGoogleへリダイレクトする
if (! isset($_GET['code'])) {
	$authUrl = $client->createAuthUrl();
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: $authUrl');
	return;
}
$authCode = $_GET['code'];
try {
	// Exchange authorization code for access token
	$accessToken = $client->authenticate($authCode);
	$client->setAccessToken($accessToken);
} catch (Exception $e) {
	print '[Error] Authenticate : ' . $e->getMessage();
	exit;
}

header('Content-Type: text/plain');
//Insert a file
$mimeType = ‘text/plain’;
$file = new Google_DriveFile();
$file->setTitle("My document");
$file->setDescription("A test document");
$file->setMimeType($mimeType);
$data = “あいうえお”;
$createdFile = “”;
try {
$createdFile = $service->files->insert($file, array(
‘data’ => $data,
‘mimeType’ => $mimeType,
));
} catch (Exception $e) {
print '[Error] Save File : ' . $e->getMessage();
exit;
}

print_r($createdFile);
?>



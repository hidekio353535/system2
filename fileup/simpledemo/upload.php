<?php
// Define a destination
$targetFolder = '../../main/uploads/' . $_REQUEST['gid']; // Relative to the root

if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	//$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	$targetPath = $targetFolder;

	//ターゲットフォルダがあるか　なければ作る
	if(!is_dir($targetPath))
		mkdir($targetPath);

	$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
	
	
	// Validate the file type
	$fileTypes = array('jpg','jpeg','gif','png','JPG','JPEG','GIF','PNG','pdf','PDF'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {
		move_uploaded_file($tempFile,$targetFile);
		
		//JPEGのみ変換
		if(preg_match('/jpg$|jpeg$|JPG$|JPEG$/',$targetFile))
			img_size_change($targetFile);
		
		echo '1';
	} else {
		echo 'Invalid file type.';
	}
}

//JPEG縮小処理
function img_size_change($targetFile) {
	// 出力する画像サイズの指定
	$width = 300;
	//$height = 50;
	
	// コピー元画像の指定
	$targetImage = $targetFile;
	// ファイル名から、画像インスタンスを生成
	$image = imagecreatefromjpeg($targetImage);
	// コピー元画像のファイルサイズを取得
	list($image_w, $image_h) = getimagesize($targetImage);
	
	//ターゲットサイズより小さい場合
	if($width > $image_w)
		return;
	
	//比率を維持して高さを設定
	$height = $width * $image_h / $image_w;
	
	// サイズを指定して、背景用画像を生成
	$canvas = imagecreatetruecolor($width, $height);
	
	// 背景画像に、画像をコピーする
	imagecopyresampled($canvas,  // 背景画像
					   $image,   // コピー元画像
					   0,        // 背景画像の x 座標
					   0,        // 背景画像の y 座標
					   0,        // コピー元の x 座標
					   0,        // コピー元の y 座標
					   $width,   // 背景画像の幅
					   $height,  // 背景画像の高さ
					   $image_w, // コピー元画像ファイルの幅
					   $image_h  // コピー元画像ファイルの高さ
					  );
	
	// 画像を出力する
	imagejpeg($canvas,           // 背景画像
			  $targetImage,    // 出力するファイル名（省略すると画面に表示する）
			  100                // 画像精度（この例だと100%で作成）
			 );
	
	// メモリを開放する
	imagedestroy($canvas);
}





?>
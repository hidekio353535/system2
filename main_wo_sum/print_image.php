<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>書類印刷</title>
</head>

<body>

<?php
if(isset($_REQUEST['id'])) {
	$id= $_REQUEST['id'];
	$col= $_REQUEST['col'];
	if($col == "" || !$col)
		$col = 2;
}
else {
	echo "現場IDの指定が不正です。";
	exit();	
}
require_once("../php/db_connect.php");

$sql = "SELECT * FROM `matsushima_genba` WHERE g_id = '{$id}'";
$query = mysql_query($sql);
$num = mysql_num_rows($query);
if($num) {
	$row = mysql_fetch_object($query);
	$genba = $row->g_genba;
	$genba_address = $row->g_genba_address;
}

echo "<h3>現場名：{$genba}</h2>";
echo "<h3>現場住所：{$genba_address}</h2>";

$dirName = "./uploads/".$id;
if(is_dir($dirName)) {
	$fileArray = scandir($dirName);
	
	echo "<table class='file-up-table'><tr>";

	$cnt = 0;
	$w = 800 / $col;
	
	for($i=0;$i < count($fileArray);$i++) {
		if($fileArray[$i] != "." && $fileArray[$i] != "..") {
			echo "<td id='pic{$i}' style='padding:5px;text-align:center'>";
			if(preg_match('/jpg$/',$fileArray[$i]) || preg_match('/JPG$/',$fileArray[$i]) || preg_match('/jpeg$/',$fileArray[$i]) || preg_match('/JPEG$/',$fileArray[$i]) || preg_match('/gif$/',$fileArray[$i]) || preg_match('/GIF$/',$fileArray[$i]) || preg_match('/png$/',$fileArray[$i]) || preg_match('/PNG$/',$fileArray[$i]))
				echo "<img src='{$dirName}/{$fileArray[$i]}' width='{$w}' />";
			
			echo "</td>";

			$cnt++;

			if($cnt % $col == 0 && $cnt != 0) {
				echo "</tr><tr>";
			}
		}
	}

	echo "</tr></table>";
	
	if($cnt)
		echo "<input type='hidden' id='shorui_ari' />";
}
?>

</body>
</html>
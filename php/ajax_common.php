<?php
require_once("db_connect.php");
require_once("common.php");

$parm = $_REQUEST['parm'];

$flag = $parm[0];

/**********************************************************************
 *
 * 	オプショナルデータ表示処理
 *
 **********************************************************************/
if($flag == "OPTIONAL") {


	//引数
	$table = $parm[1]; //関連テーブル
	$elm = $parm[2]; //格納先エレメント
	$idx = $parm[3]; //classのインデックス番号
	$rtbl = $parm[4]; //履歴用テーブル 
	$rclm = $parm[5]; //履歴用カラム

	//履歴から除外するリスト配列
	$notrireki = array();

	//オプショナル表示
	if($table != "DUMMY_opt") {
		$sql = "SELECT * FROM {$table} WHERE 1 ORDER BY corder";
		//debug
		/*echo "<script>write_debug(\"{$sql}\");</script>";*/
	
		$query = mysql_query($sql);
		echo "<table>";
		while ($row = mysql_fetch_array($query)) {
			echo "<tr>";
			echo "<td><a href='#' onclick='set_optional(\"{$row[1]}\",\"{$elm}\",\"{$idx}\");return false;'>".$row[1]."</a></td>";
			echo "</tr>";
			$notrireki[] = $row[1];
		}
		echo "</table>";
	}

	//履歴表示 利用頻度の多い順 100件のみ表示
	if($rtbl) {
		//仕切り線
		echo "-----過去の入力履歴-----";
		
		//既に表示しているものを除外する条件設定
		$JYOGAI = "";
		for($i = 0;$i < count($notrireki);$i++) {
 			$JYOGAI .= " AND {$rclm} != '$notrireki[$i]' "; 
		}
		
		$sql = "SELECT {$rclm}, COUNT({$rclm}) AS cnt FROM {$rtbl} WHERE 1 {$JYOGAI} GROUP BY {$rclm} ORDER BY cnt DESC LIMIT 50";
		//debug
		/*echo "<script>write_debug(\"{$sql}\");</script>";*/
	
		$query = mysql_query($sql);
		echo "<table>";
		while ($row = mysql_fetch_array($query)) {
			echo "<tr>";
			echo "<td><a href='#' onclick='set_optional(\"{$row[0]}\",\"{$elm}\",\"{$idx}\");return false;'>".$row[0]."</a></td>";
			echo "</tr>";
		}
		echo "</table>";
	}
}

/**********************************************************************
 *
 * 	検索条件 status
 *
 **********************************************************************/
else if($flag == "SEARCH_SELECT") {

	$table = $parm[1]; //テーブル
	$elm = $parm[2]; //テーブル

	$sql = "SELECT * FROM {$table} WHERE 1";
	$query = mysql_query($sql);
	echo "<select id='{$elm}'>";
	echo "<option value='0'>全て</option>";
	while ($row = mysql_fetch_array($query)) {
		echo "<option value='{$row[0]}'>{$row[1]}</option>";
	}
	echo "</select>";
}

else if($flag == "TEST") {
	
	make_optional("test", "test", 20,"","");
}

?>
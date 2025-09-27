<?php
/**********************************************************************
 *
 * 
 *
 **********************************************************************/

//曜日
$week = array("日","月","火","水","木","金","土");

/**********************************************************************
 *
 * 
 *
 **********************************************************************/
//和暦表示関数
function conv_wareki($str) {
	
	//空文字なら空文字を返す
	if($str == '' || $str == null || $str == '0000-00-00')
		return '';
	
	$d = getdate(strtotime($str));
	$year = $d['year'];
	$month = $d['mon'];
	$day = $d['mday'];

	$sql = "SELECT * FROM base_db_wareki ORDER BY w_id DESC";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	while ($row = mysql_fetch_object($query)) {

		$date = (int)sprintf("%04d%02d%02d", $year, $month, $day);
		if($date >= $row->w_gannen) {
			$name = $row->w_alpha;
			$year -= $row->w_sabun;
			break;
		}
	}
	return $name.(string)$year.".".$month.".".$day;
}

/**********************************************************************
 *
 * 
 *
 **********************************************************************/
function conv_wareki_k($str) {
	
	//空文字なら空文字を返す
	if($str == '' || $str == null || $str == '0000-00-00')
		return '';
	
	$d = getdate(strtotime($str));
	$year = $d['year'];
	$month = $d['mon'];
	$day = $d['mday'];

	$sql = "SELECT * FROM base_db_wareki ORDER BY w_id DESC";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_object($query)) {

		$date = (int)sprintf("%04d%02d%02d", $year, $month, $day);
		if($date >= $row->w_gannen) {
			$name = $row->w_kanji;
			$year -= $row->w_sabun;
			break;
		}
	}
 
 	//1年なら元年にする
	if($year == 1)
		$year = "元";

	return $name.(string)$year."年".$month."月".$day."日";
}

/**********************************************************************
 *
 *  和暦の年のみを返す
 *
 **********************************************************************/
function conv_wareki_year($str) {
	
	//空文字なら空文字を返す
	if($str == '' || $str == null || $str == '0000-00-00')
		return '';
	
	$d = getdate(strtotime($str));
	$year = $d['year'];
	$month = $d['mon'];
	$day = $d['mday'];

	$sql = "SELECT * FROM base_db_wareki ORDER BY w_id DESC";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	while ($row = mysql_fetch_object($query)) {

		$date = (int)sprintf("%04d%02d%02d", $year, $month, $day);
		if($date >= $row->w_gannen) {
			$name = $row->w_alpha;
			$year -= $row->w_sabun;
			break;
		}
	}
	return $year;
}

?>

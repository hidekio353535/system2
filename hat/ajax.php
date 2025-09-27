<?php
session_start();
require_once("../php/db_connect.php");
require_once("../php/common.php");

//----------------------個別領域 ここから----------------------------
$VIEW_TABLE = "matsushima_hat";
$VIEW_ID_FIELD = "h_id";

$MAIN_TABLE = "matsushima_hat";
$MAIN_ID_FIELD = "h_id";

//----------------------個別領域 ここまで----------------------------

/**********************************************************************
 *
 * 	パラメータ受け取り
 *
 **********************************************************************/
if(isset($_REQUEST['parm'])){
	$parm = $_REQUEST['parm'];
	$flag = $parm[0];

} else {
	return false;
}

/**********************************************************************
 *
 * 	リスト表示処理
 *
 **********************************************************************/
if($flag == "SHOW_LIST") {
	//引数
	if($parm[9] != "" && $parm[9] != 0)
		$MAXLIMIT = $parm[9];
	
	$page = $parm[1]; //ページ
	//ソート順が空の場合は先頭行をデフォルトのソート順にする
	if($parm[2])
		$ORDER = "ORDER BY ".$parm[2]; //ソート順
	else	
		$ORDER = "ORDER BY {$VIEW_ID_FIELD} DESC";
	
	$JYOKEN = "";
	$search_id = $parm[3];
	if($search_id != "")
		$JYOKEN .= " AND {$VIEW_ID_FIELD} = {$search_id} ";

//----------------------個別領域 ここから----------------------------
	$search_kw1 = $parm[4];
	if($search_kw1 != "")
		$JYOKEN .= " AND (h_seko_id like '%{$search_kw1}%' || h_year like '%{$search_kw1}%' || EXISTS (SELECT * FROM matsushima_seko WHERE seko_id = h_seko_id AND seko like '%{$search_kw1}%')) ";
	$search_kw2 = $parm[5];
	if($search_kw2 != "")
		$JYOKEN .= " AND (h_seko_id like '%{$search_kw2}%' || h_year like '%{$search_kw2}%' || EXISTS (SELECT * FROM matsushima_seko WHERE seko_id = h_seko_id AND seko like '%{$search_kw2}%')) ";

	$shime_year = $parm[6];
	if($shime_year != "")
		$JYOKEN .= " AND (h_year like '{$shime_year}') ";
	
	$shime_month = $parm[7];
	if($shime_month != "")
		$JYOKEN .= " AND (h_month like '{$shime_month}') ";

	$search_sel_seko = $parm[8];
	if($search_sel_seko != "" && $search_sel_seko != 0)
		$JYOKEN .= " AND (h_seko_id = '{$search_sel_seko}') ";
	
//----------------------個別領域 ここまで----------------------------
	//20170210 セッションリセット
	unset($_SESSION['csv_sql']);
		
	//ページ管理用 最終ページ計算
	try {
		$sql = "SELECT * FROM {$VIEW_TABLE} WHERE 1 {$JYOKEN} {$ORDER}";
		@$query = mysql_query($sql);
		if(!$query) {
			throw new dbException("SQLが不完全");
		}
		@$num = mysql_num_rows($query);
		if(!$num) {
			throw new returnZeroRowException("該当するデータがありません");
		}
		$lastpage = ceil($num / $MAXLIMIT);
	
		// for show page
		$pagelimit = ($page - 1) * $MAXLIMIT;
	
		//表示用SQL
		$sql = "SELECT *,
				(SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_hat_id = {$VIEW_TABLE}.h_id) as hattyu,
				((SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_hat_id = {$VIEW_TABLE}.h_id) + h_chosei) as hattyuall
				
				FROM {$VIEW_TABLE} WHERE 1 {$JYOKEN} {$ORDER} LIMIT {$pagelimit}, {$MAXLIMIT}";
		@$query = mysql_query($sql);
		if(!$query) {
			throw new dbException("SQLが不完全");
		}
		//20170210 セッションセット
		if($JYOKEN) {
			$_SESSION['csv_sql'] = "SELECT *,
				(SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_hat_id = {$VIEW_TABLE}.h_id) as hattyu,
				((SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_hat_id = {$VIEW_TABLE}.h_id) + h_chosei) as hattyuall
				
				FROM {$VIEW_TABLE} WHERE 1 {$JYOKEN} {$ORDER}";
		}
	}
	//返り値が0
	catch(returnZeroRowException $e) {
		//status msg
		/*echo "<script>show_status(\"{$e->getMessage()}\");</script>";*/
		//debug
		/*echo "<script>write_debug(\"{$e->getMessage()}\"{$sql}\");</script>";*/
		//lastpageを1にセット
		$lastpage = 1;
	}
	//MySQLエラー
	catch(dbException $e) {
		//error msg
		/*echo "<script>show_error(\"{$e->getMessage()}\");</script>";*/
		//debug
		/*echo "<script>write_debug(\"{$e->getMessage()}{$sql}\");</script>";*/
	}

	echo "<table class='list-table'>";
	echo "<tr>";
//----------------------個別領域 ここから----------------------------
	showTh("", "アクション");
	showTh("h_id", "ID");
	showTh("h_seko_id", "発注先名");
	showTh("h_year", "発注年");
	showTh("h_month", "発注月");
	showTh("", "発注額");
	showTh("h_chosei_name", "調整項目名");
	showTh("h_chosei", "調整金額");
	showTh("", "発注総額");
	showTh("h_hat_date", "発注日");
	showTh("h_send_date", "発注書送付日");
	showTh("h_receipt_yotei_date", "振込予定日");
	showTh("h_receipt_date", "振込日");
//----------------------個別領域 ここまで----------------------------
	echo "</tr>";

	//表示するレコードがある場合
	if($num) {
		while ($row = @mysql_fetch_object($query)) {
		
			echo "<tr>";
//----------------------個別領域 ここから----------------------------
			echo "<td class='nw' style='vertical-align:middle'>";
				echo '<input type="checkbox" class="chk" value="'.$row->$VIEW_ID_FIELD.'">
				 <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
				 <img src="../img/b_hat.png" title="印刷" class="opa smlb ui-icon-print">
				 <img src="../img/b_drop.png" title="締めを解除" class="opa smlb ui-icon-trash">
				';
			echo "</td>";

			echo "<td class='nw tar'>";
				echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->$VIEW_ID_FIELD."' />";
				show_clm($row->$VIEW_ID_FIELD, $VIEW_ID_FIELD);
			echo "</td>";

			$clm = "h_seko_id";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_select_clm($row->$clm, "matsushima_seko", 2);
				echo "</span>";
			echo "</td>";

			$clm = "h_year";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 4);
				echo "</span>";
			echo "</td>";

			$clm = "h_month";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 4);
				echo "</span>";
			echo "</td>";

			$clm = "hattyu";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm(number_format($row->$clm), $clm, $F_NUM, 4);
				echo "</span>";
			echo "</td>";

			$clm = "h_chosei_name";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "h_chosei";
			echo "<td class='nw tar'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "8", 0, "text", "tar");
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_NUM, 8);
				echo "</span>";
			echo "</td>";

			$clm = "hattyuall";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm(number_format($row->$clm), $clm, $F_NUM, 4);
				echo "</span>";
			echo "</td>";

			$clm = "h_hat_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "h_send_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "h_receipt_yotei_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "h_receipt_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";
/*
function show_clm($_val, $_clm, $_type = 0, $_width = 20) {
	//グローバル宣言
	global $F_NORMAL;
	global $F_DATE;
	global $F_DATE_SH;
	global $F_NUM;
	global $F_WIDTH;
	global $F_NUM_NO_0;
*/
//----------------------個別領域 ここまで----------------------------
			echo "</tr>";
		
		}
	}
	//表示するレコードが0の場合
	else {
		echo "<tr>";
		
			if($shime_year != "")
				$shime_year = $shime_year . "年";
			if($shime_month != "")
				$shime_month = $shime_month . "月";
			if($shime_year != "" ||	$shime_month != "")
				$msg = "{$shime_year} {$shime_month}の発注書に条件で";
			else
				$msg = "";
			
		
			echo "<td colspan='13' style='text-align:center;color:red'>{$msg}該当するデータがありません</td>";
		echo "</tr>";
	}
	echo "</table>";
	
	//ページ管理
	if($num > 1) {

		echo "<div class='page-area'>";
		//総ページ数
		echo "Page {$page} / {$lastpage} ";
		
		echo " (";
	
		//最初のページ以外
		if($page != 1) {
			$prepage = $page - 1;
			echo "<a href='javascript:set_page(1)'>&lt;&lt;</a>&nbsp;&nbsp;<a href='javascript:set_page({$prepage})'>&lt;</a>&nbsp;&nbsp;";
		}
		
		//ページ数が多い場合は省略
		if($lastpage > $MAXSHOWPERPAGE) {
			if($page <= ($MAXSHOWPERPAGE / 2)) {
				$formax = $MAXSHOWPERPAGE;
				$forst = 1;
			}
			else if($page > ($MAXSHOWPERPAGE / 2) && $lastpage > $page + ($MAXSHOWPERPAGE / 2)) {
				$formax = $page + ($MAXSHOWPERPAGE / 2);
				$forst = $page - ($MAXSHOWPERPAGE / 2);
			}
			else if($lastpage <= $page + ($MAXSHOWPERPAGE / 2)) {
				$formax = $lastpage;
				$forst = $lastpage - $MAXSHOWPERPAGE;
			}
		} else {
			$formax = $lastpage;
			$forst = 1;
		}
		
		for($i=$forst;$i <= $formax;$i++) {
			if($i == $page)//現在のページなら
				echo "{$i}&nbsp;";
			else
				echo "<a href='javascript:set_page({$i})' class='pagebox'>{$i}</a>&nbsp;";
	
		}
		if($formax == $MAXSHOWPERPAGE) //$MAXSHOWPERPAGEページ以上は省略
			echo "...";
		
		if($page != $lastpage) {
			$nextpage = $page + 1;
			echo "&nbsp;&nbsp;<a href='javascript:set_page({$nextpage})'>&gt;</a>&nbsp;&nbsp;<a href='javascript:set_page({$lastpage})'>&gt;&gt;</a>";
		}

		echo ") 条件に合致した件数 {$num} 件";
		echo "</div>";
	}

	//保存ボタン
	echo '<p class="tar">';
	echo '<button class="button main_update_btn mr5">保存する</button>';
	echo '</p>';

}

/**********************************************************************
 *
 * 	MAIN編集画面表示処理
 *
 **********************************************************************/
else if($flag == "EDIT_MAIN") {

	//ID取得
	$id = $parm[1];

	if($id) {
		//情報取得
		$sql = "SELECT * FROM matsushima_slip_hat
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
				INNER JOIN matsushima_seko ON matsushima_slip_hat.s_seko_id = matsushima_seko.seko_id
				INNER JOIN matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id
				WHERE s_hat_id = {$id} ORDER BY s_st_date, s_id";

		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	
	} else {
		//ダミー
		$sql = "SELECT 1";
		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	}

	//締めていた場合編集不可
	echo "<h2>発注書一覧</h2>";
	
	echo "<table class='list-table'>";
	echo "<tr>";
//----------------------個別領域 ここから----------------------------
	showTh("", "アクション");
	showTh("s_genba_id", "現場ID");
	showTh("s_id", "発注ID");
	showTh("s_st_date", "工事日");
	showTh("seko", "発注先");
	showTh("moto", "元請");
	showTh("moto", "支店");
	showTh("g_genba", "現場名");
	showTh("g_genba_address", "現場住所");
	showTh("s_seko_kubun_id", "区分");
	showTh("s_hattyu", "発注額");

//----------------------個別領域 ここまで----------------------------
	echo "</tr>";

	//表示するレコードがある場合
	if($num) {
		while ($row = @mysql_fetch_object($query)) {
		
			echo "<tr>";
//----------------------個別領域 ここから----------------------------
			echo "<td class='nw' style='vertical-align:middle'>";
				echo '<input type="checkbox" class="chk" value="'.$row->s_id.'">
				 <img src="../img/b_hat.png" title="発注書印刷" class="opa smlb ui-icon-print">
				 <img src="../img/b_drop.png" title="締めを解除" class="opa smlb ui-icon-trash">
				';
			echo "</td>";

			echo "<td class='nw tac'>";
				//show_clm($row->s_genba_id, "s_genba_id");
				echo "<a href='../main/?{$row->s_genba_id}' target='_blank'>{$row->s_genba_id}</a>";
			echo "</td>";
			
			echo "<td class='nw tac'>";
				echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->s_id."' />";
				show_clm($row->s_id, "s_id");
			echo "</td>";

			$clm = "s_st_date";
			echo "<td class='nw tal'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 30);
				echo "</span>";

			$clm = "seko";
			echo "<td class='nw tal'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";

			$clm = "moto_nik";
			echo "<td class='nw tal'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 60);
				echo "</span>";
			
			$clm = "branch";
			echo "<td class='nw tal'>";
					echo "<span class='list_mode'>";
						show_clm($row->$clm, $clm, $F_WIDTH, 60);
					echo "</span>";
	
			$clm = "g_genba";
			echo "<td class='nw tal'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";

			$clm = "g_genba_address";
			echo "<td class='nw tal'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";

			$clm = "s_seko_kubun_id";
			echo "<td class='nw tac'>";
				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, "matsushima_kouji_syu_hat", 1);
				echo "</span>";
			echo "</td>";

			$clm = "s_hattyu";
			echo "<td class='nw tar'>";
				echo "<span class='list_mode'>";
					show_clm(number_format($row->$clm), $clm, $F_WIDTH, 30);
				echo "</span>";
			echo "</td>";

		}
	}
	echo "</table>";

	//保存ボタン
	echo '<p class="tar">';
	echo '<button class="button back_btn mr5">一覧に戻る</button>';
	echo '</p>';
	
}
else if($flag == "SET_JYOKEN") {

	//発注先
	$sql = "SELECT * FROM matsushima_seko WHERE is_seko_show = 1 ORDER BY orderc";
	$query = @mysql_query($sql);
	$num = @mysql_num_rows($query);
	if(!$query) {
		echo "fail";
		exit();
	}
	if($num) {
		echo "<span style='font-weight:bold'>発注先</span>&nbsp;&nbsp;";
		echo "<select id='search_sel_seko'>";
		echo "<option value='0'>発注先を選択して下さい</option>";
		while ($row = @mysql_fetch_array($query)) {
			echo "<option value='{$row[0]}'>{$row[1]}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
	}
}

?>

<?php
require_once("../php/db_connect.php");
require_once("../php/common.php");

//----------------------個別領域 ここから----------------------------
$VIEW_TABLE = "matsushima_slip";
$VIEW_ID_FIELD = "s_id";

$MAIN_TABLE = "matsushima_slip";
$MAIN_ID_FIELD = "s_id";

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
	$page = $parm[1]; //ページ
	//ソート順が空の場合は先頭行をデフォルトのソート順にする
	if($parm[2])
		$ORDER = "ORDER BY ".$parm[2]; //ソート順
	else	
		$ORDER = "ORDER BY s_shime_date DESC";
	
	$JYOKEN = "";
	$search_id = $parm[3];
	if($search_id != "")
		$JYOKEN .= " AND {$VIEW_ID_FIELD} = {$search_id} ";

//----------------------個別領域 ここから----------------------------
	$search_kw1 = $parm[4];
	if($search_kw1 != "")
		$JYOKEN .= " AND (moto like '%{$search_kw1}%' || moto_nik like '%{$search_kw1}%') ";
	$search_kw2 = $parm[5];
	if($search_kw2 != "")
		$JYOKEN .= " AND (moto like '%{$search_kw2}%' || moto_nik like '%{$search_kw2}%') ";

//----------------------個別領域 ここまで----------------------------

	$JOIN = "	INNER JOIN matsushima_genba ON matsushima_genba.g_id = {$VIEW_TABLE}.s_genba_id
				INNER JOIN matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id ";
		
	//ページ管理用 最終ページ計算
	try {
		$sql = "SELECT * FROM {$VIEW_TABLE} {$JOIN} WHERE 1 {$JYOKEN} {$ORDER}";
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
		$sql = "SELECT * FROM {$VIEW_TABLE} {$JOIN} WHERE 1 {$JYOKEN} {$ORDER} LIMIT {$pagelimit}, {$MAXLIMIT}";
		@$query = mysql_query($sql);
		if(!$query) {
			throw new dbException("SQLが不完全");
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
	showTh("", "印刷");
	showTh("s_id", "ID");
	showTh("s_date", "工事日");
	showTh("moto", "元請");
	showTh("g_genba", "現場名");
	showTh("g_genba_address", "現場住所");
	showTh("s_seko_kubun_id", "区分");
	showTh("s_invoice", "請求額");
	showTh("", "請求締");
	showTh("", "入金状況");
//----------------------個別領域 ここまで----------------------------
	echo "</tr>";

	//表示するレコードがある場合
	if($num) {
		while ($row = @mysql_fetch_object($query)) {
		
			echo "<tr>";
//----------------------個別領域 ここから----------------------------
			echo "<td class='nw tac' style='vertical-align:middle'>";
				echo '<input type="hidden" class="chk" value="'.$row->s_id.'">
				 <img src="../img/b_inv.png" title="請求書印刷" class="opa smlb ui-icon-print">
				';
			echo "</td>";

			echo "<td class='nw tac'>";
				echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->s_id."' />";
				show_clm($row->s_id, "s_id");
			echo "</td>";

			$clm = "s_shime_date";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 30);
				echo "</span>";

			$clm = "moto";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";

			$clm = "g_genba";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";

			$clm = "g_genba_address";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";

			$clm = "s_seko_kubun_id";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_select_clm($row->$clm, "matsushima_kouji_syu", 2);
				echo "</span>";
			echo "</td>";

			$clm = "s_invoice";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm(number_format($row->$clm), $clm, $F_NUM, 30);
				echo "</span>";
			echo "</td>";

			echo "<td class='nw'>";
				if($row->s_inv_id) {
					echo "<span style='color:green'>請求締済</span>";
					$is_hat = true;
				}
				else {
					echo "<span style='color:red'>未請求</span>";
				}
			echo "</td>";

			echo "<td class='nw'>";

				//入金確認
				$sql_n = "SELECT * FROM matsushima_inv WHERE i_id = '{$row->s_inv_id}' AND (i_receipt_date != '0000-00-00' AND i_receipt_date is not null AND i_receipt_date != '')";
				$query_n = mysql_query($sql_n);
				$num_n = mysql_num_rows($query_n);

				if($num_n) {
					echo "<span style='color:green'>入金済</span>";
					$is_hat = true;
				}
				else {
					echo "<span style='color:red'>未入金</span>";
				}
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
			echo "<td>該当するデータがありません</td>";
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
		$MAIN_JYOKEN = "AND {$MAIN_ID_FIELD} = '{$id}'";
		$sql = "SELECT * FROM  {$MAIN_TABLE} WHERE 1 {$MAIN_JYOKEN} LIMIT 1";

		//debug
		/*echo "<script>write_debug(\"{$sql}\");</script>";*/

		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	
	} else {
		//ダミー
		$sql = "SELECT 1";
		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	}

	//締めていた場合編集不可
	echo "<h2>マスタ管理</h2>";

	//保存ボタン
	echo '<p class="tar">';
	echo '<button class="button update_btn mr5">保存する</button>';
	echo '<button class="button back_btn mr5">一覧に戻る</button>';
	echo '</p>';
	
	echo "<table class='edit-table'>";
	while ($row = mysql_fetch_object($query)) {
//----------------------個別領域 ここから----------------------------

		echo "<tr>";
		echo "<th>ID</th>";
		echo "<td class='nw'>";
		//フィールドIDセット
		echo "<span class='{$MAIN_ID_FIELD}_span'>" . $row->$MAIN_ID_FIELD . "</span>";
		make_textbox($row->$MAIN_ID_FIELD, $MAIN_ID_FIELD, "10", 0,"hidden");
		echo "</td>";
		echo "</tr>";


		$clm = "moto";
		echo "<tr>";
		echo "<th>元請会社名</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "40", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "moto_nik";
		echo "<tr>";
		echo "<th>元請短縮会社名</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "branch";
		echo "<tr>";
		echo "<th>支店名</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "12", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "atena";
		echo "<tr>";
		echo "<th>請求書宛名</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "8", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "kana";
		echo "<tr>";
		echo "<th>カナ</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "4", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_pat";
		echo "<tr>";
		echo "<th>締めパターン</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "10", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_shime_group";
		echo "<tr>";
		echo "<th>締めグループ</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "10", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_shiharai_m";
		echo "<tr>";
		echo "<th>支払月</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "10", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_shiharai_day";
		echo "<tr>";
		echo "<th>支払日</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "10", 0);
		echo "</td>";
		echo "</tr>";

//		make_select($row->builder_id, builder_id, te_builder, 0);
//		make_textbox($row->genba, "genba", "60", 0);
//		make_optional($row->sp_cust, sp_cust,"40", 0, "DUMMY_opt", "matsushima_meisho_opt", "name");
//----------------------個別領域 ここまで----------------------------

	}
	echo "</table>";

	//保存ボタン
	echo '<p class="tar">';
	echo '<button class="button update_btn mr5">保存する</button>';
	echo '<button class="button back_btn mr5">一覧に戻る</button>';
	echo '</p>';
	
}

?>

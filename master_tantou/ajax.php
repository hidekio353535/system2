<?php
require_once("../php/db_connect.php");
require_once("../php/common.php");

//----------------------個別領域 ここから----------------------------
$VIEW_TABLE = "matsushima_tantou";
$VIEW_ID_FIELD = "t_id";

$MAIN_TABLE = "matsushima_tantou";
$MAIN_ID_FIELD = "t_id";

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

	//表示件数
	if($parm[6] != "" && $parm[6] != 0)
		$MAXLIMIT = $parm[6];

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
		$search_kw1_tel = preg_replace('/\-/','',$search_kw1);

		$JYOKEN .= " AND (
			t_tantou like '%{$search_kw1}%' || 
			t_tantou_nik like '%{$search_kw1}%' ||
			replace(t_tel,'-','') like '%{$search_kw1_tel}%' ||
			t_biko like '%{$search_kw1}%'
			) ";
	$search_kw2 = $parm[5];
	if($search_kw2 != "")
		$search_kw2_tel = preg_replace('/\-/','',$search_kw2);
		$JYOKEN .= " AND (
			t_tantou like '%{$search_kw2}%' || 
			t_tantou_nik like '%{$search_kw2}%' ||
			replace(t_tel,'-','') like '%{$search_kw2_tel}%' ||
			t_biko like '%{$search_kw2}%'
			) ";

//----------------------個別領域 ここまで----------------------------
		
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
		$sql = "SELECT * FROM {$VIEW_TABLE} WHERE 1 {$JYOKEN} {$ORDER} LIMIT {$pagelimit}, {$MAXLIMIT}";
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
	showTh("", "アクション");
	showTh("t_id", "ID");
	showTh("t_tantou", "担当名");
	showTh("t_tantou_nik", "担当短縮名");
	showTh("t_tel", "携帯番号");
	showTh("t_biko", "備考");
	showTh("t_gencho", "現調");
	showTh("is_show_tantou", "表示");
	showTh("t_order", "表示順");
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
				 <img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">
				';
			echo "</td>";

			echo "<td class='nw tar'>";
				echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->$VIEW_ID_FIELD."' />";
				show_clm($row->$VIEW_ID_FIELD, $VIEW_ID_FIELD);
			echo "</td>";

			$clm = "t_tantou";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "20", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "t_tantou_nik";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "20", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "t_tel";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "t_biko";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "20", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "t_gencho";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_normal_checkbox($row->$clm, $clm, "");
				echo "</span>";

				echo "<span class='list_mode'>";
					if($row->$clm)
						echo "表示する";
					else
						echo "表示しない";
				echo "</span>";
			echo "</td>";

			$clm = "is_show_tantou";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_normal_checkbox($row->$clm, $clm, "");
				echo "</span>";

				echo "<span class='list_mode'>";
					if($row->$clm)
						echo "表示する";
					else
						echo "表示しない";
				echo "</span>";
			echo "</td>";

			$clm = "t_order";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "5", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 5);
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


		$clm = "t_tantou";
		echo "<tr>";
		echo "<th>担当名</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "60", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "t_tantou_nik";
		echo "<tr>";
		echo "<th>担当短縮名</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "30", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "t_tel";
		echo "<tr>";
		echo "<th>携帯番号</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "t_biko";
		echo "<tr>";
		echo "<th>備考</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "40", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "t_gencho";
		echo "<tr>";
		echo "<th>現調</th>";
		echo "<td class='nw'>";
		make_normal_checkbox($row->$clm, $clm, "");
		echo "</td>";
		echo "</tr>";

		$clm = "is_show_tantou";
		echo "<tr>";
		echo "<th>表示</th>";
		echo "<td class='nw'>";
		make_normal_checkbox($row->$clm, $clm, "");
		echo "</td>";
		echo "</tr>";

		$clm = "t_order";
		echo "<tr>";
		echo "<th>表示順</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "30", 0);
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

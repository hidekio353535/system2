<?php
require_once("../php/db_connect.php");
require_once("../php/common.php");

//----------------------個別領域 ここから----------------------------
$VIEW_TABLE = "matsushima_genba";
$VIEW_ID_FIELD = "g_id";

$MAIN_TABLE = "matsushima_genba";
$MAIN_ID_FIELD = "g_id";

$SLIP_TABLE[0] = "matsushima_slip_est";
$SLIP_ID_FIELD[0] = "s_id";
$SLIP_REL_FIELD[0] = "s_genba_id";

$SLIP_TABLE[1] = "matsushima_slip";
$SLIP_ID_FIELD[1] = "s_id";
$SLIP_REL_FIELD[1] = "s_genba_id";

$SLIP_TABLE[2] = "matsushima_slip_hat";
$SLIP_ID_FIELD[2] = "s_id";
$SLIP_REL_FIELD[2] = "s_genba_id";

$SLIP_TABLE[3] = "matsushima_slip_jv";
$SLIP_ID_FIELD[3] = "s_id";
$SLIP_REL_FIELD[3] = "s_genba_id";

$MEISAI_TABLE[0] = "matsushima_meisai_est";
$MEISAI_ID_FIELD[0] = "m_id";
$MEISAI_REL_FIELD[0] = "m_s_id";

$MEISAI_TABLE[1] = "matsushima_meisai";
$MEISAI_ID_FIELD[1] = "m_id";
$MEISAI_REL_FIELD[1] = "m_s_id";

$MEISAI_TABLE[2] = "matsushima_meisai_hat";
$MEISAI_ID_FIELD[2] = "m_id";
$MEISAI_REL_FIELD[2] = "m_s_id";

$MEISAI_TABLE[3] = "matsushima_meisai_jv";
$MEISAI_ID_FIELD[3] = "m_id";
$MEISAI_REL_FIELD[3] = "m_s_id";

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
	if($parm[12] != "" && $parm[12] != 0)
		$MAXLIMIT = $parm[12];
		
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
		$JYOKEN .= " AND (
							g_genba like '%{$search_kw1}%' 			|| 
							g_genba_address like '%{$search_kw1}%'	||
							g_biko like '%{$search_kw1}%'			||
							g_moto_tantou like '%{$search_kw1}%'
					) ";
	$search_kw2 = $parm[5];
	if($search_kw2 != "")
		$JYOKEN .= " AND (
							g_genba like '%{$search_kw2}%' 			|| 
							g_genba_address like '%{$search_kw2}%'	||
							g_biko like '%{$search_kw2}%'			||
							g_moto_tantou like '%{$search_kw2}%'
					) ";

	$search_st_date = $parm[6];
	$search_end_date = $parm[7];
	$date_sel = $parm[8];
	
	if($search_st_date != '' && $search_end_date != '')
		$JYOKEN .= " AND {$date_sel} >= '{$search_st_date}' AND {$date_sel} <= '{$search_end_date}' ";	
	else if($search_st_date != '' && $search_end_date == '')
		$JYOKEN .= " AND {$date_sel} >= '{$search_st_date}' ";
	else if($search_st_date == '' && $search_end_date != '')
		$JYOKEN .= " AND {$date_sel} <= '{$search_end_date}' ";	
		
	$search_sel_moto = $parm[9];	
	if($search_sel_moto != 0)
		$JYOKEN .= " AND g_moto_id = '{$search_sel_moto}' ";	
	$search_sel_tantou = $parm[10];	
	if($search_sel_tantou != 0)
		$JYOKEN .= " AND g_tantou_id = '{$search_sel_tantou}' ";	
	$search_sel_status = $parm[11];	
	if($search_sel_status != 0)
		$JYOKEN .= " AND g_status = '{$search_sel_status}' ";	

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
		$sql = "SELECT *,
				
				(SELECT s_moto_invoice FROM matsushima_slip_est WHERE s_moto_invoice != 0 AND s_genba_id = {$VIEW_TABLE}.g_id AND s_seko_kubun_id < 3 ORDER BY s_id DESC LIMIT 1) as est,
				(SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_genba_id = {$VIEW_TABLE}.g_id) as inv,
				
				CASE
					WHEN g_moto_id !=31 AND (SELECT s_hattyu FROM matsushima_hat_sousa WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) != 0
						THEN (SELECT s_hattyu FROM matsushima_hat_sousa WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1)
					WHEN g_moto_id =31 AND (SELECT shi1 FROM matsushima_hat_sousa_azuma WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) != 0
						THEN truncate((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_genba_id = {$VIEW_TABLE}.g_id) * (SELECT shi1 FROM matsushima_hat_sousa_azuma WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) / 100, 0)
					ELSE '未発注'	
				END as hat,
				
				CASE
					WHEN g_moto_id !=31 
							AND	
							(SELECT s_hattyu FROM matsushima_hat_sousa WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) != 0
							AND
							(SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_genba_id = {$VIEW_TABLE}.g_id) is not null
						THEN (SELECT s_hattyu FROM matsushima_hat_sousa WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) - (SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_genba_id = {$VIEW_TABLE}.g_id)
					WHEN g_moto_id !=31
							AND
							(SELECT s_hattyu FROM matsushima_hat_sousa WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) != 0
							AND
							(SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_genba_id = {$VIEW_TABLE}.g_id) is null
						THEN (SELECT s_hattyu FROM matsushima_hat_sousa WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1)
					WHEN g_moto_id =31 
							AND	
							(SELECT shi1 FROM matsushima_hat_sousa_azuma WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) != 0
							AND
							(SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_genba_id = {$VIEW_TABLE}.g_id) is not null
						THEN truncate((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_genba_id = {$VIEW_TABLE}.g_id) * (SELECT shi1 FROM matsushima_hat_sousa_azuma WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) / 100, 0) - (SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_genba_id = {$VIEW_TABLE}.g_id)
					WHEN g_moto_id =31
							AND
							(SELECT shi1 FROM matsushima_hat_sousa_azuma WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) != 0
							AND
							(SELECT SUM(s_hattyu) FROM matsushima_slip_hat WHERE s_genba_id = {$VIEW_TABLE}.g_id) is null
						THEN truncate((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_genba_id = {$VIEW_TABLE}.g_id) * (SELECT shi1 FROM matsushima_hat_sousa_azuma WHERE z_g_id = {$VIEW_TABLE}.g_id LIMIT 1) / 100, 0)
					ELSE '未発注'
				END as hatzan
				
				FROM {$VIEW_TABLE} WHERE 1 {$JYOKEN} {$ORDER} LIMIT {$pagelimit}, {$MAXLIMIT}";
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

	echo "<table class='list-table'>";
	echo "<tr>";
//----------------------個別領域 ここから----------------------------
	showTh("", "アクション");
	showTh("g_id", "ID");
	showTh("g_status", "ステータス");
	showTh("g_genba", "現場");
	showTh("g_tantou_id", "担当");
	showTh("g_moto_id", "元請");
	showTh("g_moto_tantou", "元請担当者");
	showTh("g_nai_1", "工事1");
	showTh("g_nai_2", "工事2");
	showTh("g_nai_3", "工事3");
	showTh("g_m2", "㎡数");
	showTh("", "見積額");
	showTh("", "受注額");
	showTh("", "発注額");
	showTh("", "発注残額");
	showTh("g_biko", "備考");
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

			$clm = "g_status";
			$sel_table = "matsushima_est_syu";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, $sel_table, 1);
				echo "</span>";
			echo "</td>";

			$clm = "g_genba";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "g_tantou_id";
			$sel_table = "matsushima_tantou";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, $sel_table, 1);
				echo "</span>";
			echo "</td>";

			echo "<td class='nw tal'>";
			$clm = "g_moto_id";
			$sel_table = "matsushima_moto";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, $sel_table, 0, "kana", 2);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, $sel_table, 2);
				echo "</span>";
			echo "</td>";

			$clm = "g_moto_tantou";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 12);
				echo "</span>";
			echo "</td>";

			$clm = "g_nai1_id";
			$sel_table = "matsushima_nai_1";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, $sel_table, 1);
				echo "</span>";
			echo "</td>";

			$clm = "g_nai2_id";
			$sel_table = "matsushima_nai_2";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, $sel_table, 1);
				echo "</span>";
			echo "</td>";

			$clm = "g_nai3_id";
			$sel_table = "matsushima_nai_3";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, $sel_table, 1);
				echo "</span>";
			echo "</td>";

			$clm = "g_m2";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm);
				echo "</span>";
			echo "</td>";

			echo "<td class='nw tar'>";
				echo number_format($row->est);
			echo "</td>";

			echo "<td class='nw tar'>";
				echo number_format($row->inv);
			echo "</td>";

			echo "<td class='nw tar'>";
				if($row->hat == "未発注")
					echo "未発注";
				else
					echo number_format($row->hat);
			echo "</td>";

			echo "<td class='nw tar'>";
				if($row->hatzan == "未発注")
					echo "未発注";
				else
					echo number_format($row->hatzan);
			echo "</td>";

			echo "<td class='nw tal'>";
				show_clm($row->g_biko, "$row->g_biko", $F_WIDTH, 20);
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
			echo "<td colspan='16' style='text-align:center;color:red'>該当するデータがありません</td>";
		echo "</tr>";
	}
	echo "</table>";
	

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

	//現場ID取得
	$id = $parm[1];

	if($id) {
		//現場情報取得
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
	echo "<h2 style='float:left'>現場管理</h2>";

	//保存ボタン
	echo '<p class="tar">';
	echo '<button class="button update_btn mr5">保存する</button>';
	echo '<button class="button back_btn mr5">一覧に戻る</button>';
	echo '</p>';
	echo "<div class='clearfloat'></div>";
	
	echo "<table class='edit-table'>";
	while ($row = mysql_fetch_object($query)) {
//----------------------個別領域 ここから----------------------------

		echo "<tr>";
		echo "<th>現場ID</th>";
		echo "<td class='nw'>";
		//フィールドIDセット
		echo "<span class='{$MAIN_ID_FIELD}_span'>" . $row->$MAIN_ID_FIELD . "</span>";
		make_textbox($row->$MAIN_ID_FIELD, $MAIN_ID_FIELD, "10", 0,"hidden");
		echo "</td>";
		echo "</tr>";


		$clm = "g_status";
		$sel_table = "matsushima_est_syu";
		echo "<tr>";
		echo "<th>ステータス</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, $sel_table, 0);
		echo "</td>";
		echo "</tr>";

		$clm = "g_genba";
		echo "<tr>";
		echo "<th>現場名 <span style='color:red'>*</span></th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "60", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "g_genba_address";
		echo "<tr>";
		echo "<th>現場住所</th>";
		echo "<td class='nw'>";
		make_optional($row->$clm, $clm,"40", 0, "DUMMY_opt", "matsushima_genba", "g_genba_address");
		echo "</td>";
		echo "</tr>";

		$clm = "g_tantou_id";
		$sel_table = "matsushima_tantou";
		echo "<tr>";
		echo "<th>担当</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, $sel_table, 0);
		echo "</td>";
		echo "</tr>";

		$clm = "g_moto_id";
		$sel_table = "matsushima_moto";
		echo "<tr>";
		echo "<th>元請 <span style='color:red'>*</span></th>";
		echo "<td class='nw'>";
		make_select_genba($row->$clm, $clm, $sel_table, 0, "kana", 1);
		echo "</td>";
		echo "</tr>";

		$clm = "g_moto_tantou";
		echo "<tr>";
		echo "<th>元請担当者</th>";
		echo "<td class='nw'>";
		make_optional($row->$clm, $clm,"12", 0, "DUMMY_opt", "matsushima_genba", "g_moto_tantou");
		echo "</td>";
		echo "</tr>";

		$clm = "g_moto_tantou_tel";
		echo "<tr>";
		echo "<th>元請担当者連絡先</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "15", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "g_nai1_id";
		$sel_table = "matsushima_nai_1";
		echo "<tr>";
		echo "<th>工事内容1</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, $sel_table, 0);
		echo "</td>";
		echo "</tr>";

		$clm = "g_nai2_id";
		$sel_table = "matsushima_nai_2";
		echo "<tr>";
		echo "<th>工事内容2</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, $sel_table, 0);
		echo "</td>";
		echo "</tr>";

		$clm = "g_nai3_id";
		$sel_table = "matsushima_nai_3";
		echo "<tr>";
		echo "<th>工事内容3</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, $sel_table, 0);
		echo "</td>";
		echo "</tr>";

		$clm = "g_m2";
		echo "<tr>";
		echo "<th>㎡数</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
		echo " ㎡</td>";
		echo "</tr>";

		$clm = "g_biko";
		echo "<tr>";
		echo "<th>備考</th>";
		echo "<td class='nw'>";
		make_textarea($row->$clm, $clm, 20, 0, "", "", "5", "40");
		echo "<br /><span style='font-size:10px;color:red'>※この項目は管理用の備考です。内容は伝票に出力されません。</span>";
		echo "</td>";
		echo "</tr>";

//		make_select($row->builder_id, builder_id, te_builder, 0);
//		make_textbox($row->genba, "genba", "60", 0);
//		make_optional($row->sp_cust, sp_cust,"40", 0, "DUMMY_opt", "matsushima_meisho_opt", "name");
//----------------------個別領域 ここまで----------------------------

	}
	echo "</table>";

//----------------------個別領域 ここから----------------------------
	//slip area
	if($id)
		echo "<button style='margin-top:15px;' onclick='show_fileup()'>書類アップロード</button>";
	echo "<div class='file-upload-area' style=''></div>";
	echo "<div class='slip-area' style='margin-top:15px;'></div>";
	echo "<div class='slip-area' style='margin-top:15px;'></div>";
	echo "<div class='slip-area' style='margin-top:15px;'></div>";
	echo "<div class='slip-area' style='margin-top:15px;'></div>";

//----------------------個別領域 ここまで----------------------------

	//保存ボタン
	echo '<p class="tar">';
	echo '<button class="button update_btn mr5">保存する</button>';
	echo '<button class="button back_btn mr5">一覧に戻る</button>';
	echo '</p>';
	echo '<p class="tar mt5" style="font-size:12px;color:red">各項目名の右横にある「<span style="color:red">*</span>」マークは必須入力項目です。</p>';
	
}

/**********************************************************************
 *
 * 	SLIP編集画面表示処理
 *
 **********************************************************************/
else if($flag == "EDIT_SLIP") {

	//ID取得
	$id = $parm[1];
	$slip_no = $parm[2];
	
	//請求済みかチェックするフラグ
	$is_inv = false;

	//発注済みかチェックするフラグ
	$is_hat = false;
	
	//東リースかのフラグ
	$is_azuma = false;
	
	$sql = "SELECT * FROM matsushima_genba WHERE g_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if($num) {
		$row = mysql_fetch_object($query);
		if($row->g_moto_id == 31)
			$is_azuma = true;
	}
	
	if($id) {
		//現場情報取得
		$SLIP_JYOKEN = "AND {$SLIP_REL_FIELD[$slip_no]} = '{$id}'";
		$sql = "SELECT * FROM  {$SLIP_TABLE[$slip_no]} 
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = {$SLIP_TABLE[$slip_no]}.s_genba_id
				WHERE 1 {$SLIP_JYOKEN} ORDER BY {$SLIP_ID_FIELD[$slip_no]}";

		//debug
		/*echo "<script>write_debug(\"{$sql}\");</script>";*/

		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
		if(!$num) {
		//ダミー
			$sql = "SELECT 1";
			$query = @mysql_query($sql);
			$num = @mysql_num_rows($query);
		}
	} else {
		//ダミー
		$sql = "SELECT 1";
		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	}
	
	switch($slip_no) {
//----------------------個別領域 ここから----------------------------
		case 0:
			echo "<h2>見積管理</h2>";
		
			echo "<table class='slip-table'>";
				echo "<tr>";
					echo "<th colspan='2'>アクション</th>";
					echo "<th>ID</th>";
					echo "<th>区分 <span style='color:red'>*</span></th>";
					echo "<th>見積日</th>";
					echo "<th>見積金額</th>";
					echo "<th>備考</th>";
					echo "<th>操作</th>";
				echo "</tr>";
			break;
		case 1:
		
			echo "<h2 style='float:left;margin-right:30px;'>受注(請求)管理</h2><p>請求締めパターン : <span id='m_pat_area'></span></p><div class='clearfloat'></div>";
		
			echo "<table class='slip-table'>";
				echo "<tr>";
					echo "<th colspan='2'>アクション</th>";
					echo "<th>ID</th>";
					echo "<th>区分 <span style='color:red'>*</span></th>";
					echo "<th>受注日</th>";
					echo "<th>開始日 <span style='color:red'>*</span></th>";
					echo "<th>終了日</th>";
					echo "<th>請求締日</th>";
					echo "<th>元請受注額</th>";
					echo "<th>請求額</th>";
					echo "<th>備考</th>";
					echo "<th>請求</th>";
					echo "<th>操作</th>";
				echo "</tr>";
			break;
		case 2:
			echo "<h2>発注管理";
			
			if(!$is_azuma) { //東リース以外
				echo " <input type='button' value='発注一括編集' class='hattyu_mod_btn' style='vertical-align:top;'>";
				//通常発注情報
				$sql_z = "SELECT * FROM `matsushima_hat_sousa` WHERE z_g_id = '{$id}'";
				$query_z = mysql_query($sql_z);
				$row_z = mysql_fetch_object($query_z);
				$tmp_hat = $row_z->s_hattyu;
				$tmp_per = $row_z->shi1;
	
				$sql_z = "SELECT SUM(s_hattyu) as s_hattyu FROM `matsushima_slip_hat` WHERE s_genba_id = '{$id}'";
				$query_z = mysql_query($sql_z);
				$row_z = mysql_fetch_object($query_z);
				$tmp_s_hat = $row_z->s_hattyu;
				
				echo "<span style='font-size:14px;font-weight:normal;'>&nbsp;発注額: ￥".number_format($tmp_hat). " (".$tmp_per."%) 発注済: ￥".number_format($tmp_s_hat)." 発注残: ￥".number_format($tmp_hat - $tmp_s_hat) . "</span></h2>";
			}
			if($is_azuma) { //東リース
				echo " <input type='button' value='東リ発注一括編集' class='hattyu_mod_azuma_btn' style='vertical-align:top;'>";
				//通常発注情報
				$sql_z = "SELECT * FROM `matsushima_hat_sousa_azuma` WHERE z_g_id = '{$id}'";
				$query_z = mysql_query($sql_z);
				$row_z = mysql_fetch_object($query_z);
				$tmp_per = $row_z->shi1;

				//受注額
				$sql_z = "SELECT SUM(s_invoice) as s_invoice FROM `matsushima_slip` WHERE s_genba_id = '{$id}'";
				$query_z = mysql_query($sql_z);
				$row_z = mysql_fetch_object($query_z);
				$tmp_hat = floor($row_z->s_invoice * $tmp_per / 100);
	
				//発注額
				$sql_z = "SELECT SUM(s_hattyu) as s_hattyu FROM `matsushima_slip_hat` WHERE s_genba_id = '{$id}'";
				$query_z = mysql_query($sql_z);
				$row_z = mysql_fetch_object($query_z);
				$tmp_s_hat = $row_z->s_hattyu;
				
				echo "<span style='font-size:14px;font-weight:normal;'>&nbsp;発注額: ￥".number_format($tmp_hat). " (".$tmp_per."%) 発注済: ￥".number_format($tmp_s_hat)." 発注残: ￥".number_format($tmp_hat - $tmp_s_hat) . "</span></h2>";
			}
		
			echo "<table class='slip-table'>";
				echo "<tr>";
					echo "<th colspan='2'>アクション</th>";
					echo "<th>ID</th>";
					echo "<th>区分 <span style='color:red'>*</span></th>";
					echo "<th>発注日</th>";
					echo "<th>発注先</th>";
					echo "<th>開始日</th>";
					echo "<th>終了日</th>";
					echo "<th>発注締日</th>";
					echo "<th>発注額</th>";
					echo "<th>備考</th>";
					echo "<th>JV</th>";
					echo "<th>発注</th>";
					echo "<th>ス</th>";
					echo "<th>操作</th>";
				echo "</tr>";
			break;
		case 3:
			echo "<h2>JV発注書管理";
			if(!$is_azuma)
				echo "&nbsp;<input type='button' value='発注一括編集' class='hattyu_mod_jv_btn' style='vertical-align:top'></h2>";
			if($is_azuma)
				echo "&nbsp;<input type='button' value='東リ発注一括編集' class='hattyu_mod_jv_azuma_btn' style='vertical-align:top'></h2>";
		
			echo "<table class='slip-table'>";
				echo "<tr>";
					echo "<th colspan='2'>アクション</th>";
					echo "<th>ID</th>";
					echo "<th>区分 <span style='color:red'>*</span></th>";
					echo "<th>発注先</th>";
					echo "<th>開始日</th>";
					echo "<th>終了日</th>";
					echo "<th>発注額</th>";
					echo "<th>備考</th>";
					echo "<th>ス</th>";
					echo "<th>操作</th>";
				echo "</tr>";
			break;
	}
//----------------------個別領域 ここまで----------------------------
	while ($row = mysql_fetch_object($query)) {
		
		//発注限界値のセット
		//if($slip_no == 1)
		//	echo "<input type='hidden' id='g_hat_per' value='{$row->g_hat_per}' />";
		
		echo "<tbody id='slip_tbody'>";
		echo "<tr>";
//----------------------個別領域 ここから----------------------------
		switch($slip_no) {
			case 0:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
						 <img src="../img/b_mit.png" title="見積印刷" class="opa smlb ui-icon-print">
						';
					}

				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_est_syu";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</td>";

				$clm = "s_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_moto_invoice";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "7", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				echo "<td class='nw'>";
					if($row->$SLIP_ID_FIELD[$slip_no]) {
						//見積コピーボタン
						echo "<input type='button' value='見積コピー' class='copy_btn'>";
						//受注以外で受注ボタンを表示 2:受注
						//if($row->s_seko_kubun_id != 2)
						echo "<input type='button' value='受注処理' class='jyutyu_btn'>";
					}
				echo "</td>";
				break;
			case 1:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
						 <img src="../img/b_inv.png" title="請求書表示" class="opa smlb ui-icon-print">
						';
					}
					if(!$row->s_is_print) {
						echo '
						 <img src="../img/b_print.png" title="請求書印刷" class="opa smlb ui-icon-print2">
						';
					}
					else {
						echo '
						 <img src="../img/b_sumi.png" title="解除" class="opa smlb ui-icon-print2">
						';
					}
				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_kouji_syu";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</td>";
		
				$clm = "s_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_st_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_end_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_shime_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_moto_invoice";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_invoice";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				echo "<td class='nw'>";
					if($row->s_inv_id) {
						echo "<span style='color:green'>請求締済</span>";
						$is_inv = true;
					}
					else {
						echo "<span style='color:red'>未請求</span>";
					}
				echo "</td>";

				echo "<td class='nw'>";
					if($row->s_id) {
						if(!$is_azuma)
							echo "<input type='button' value='発注操作' class='hattyu_btn'>";
						if($is_azuma)
							echo "<input type='button' value='東リ発注操作' class='hattyu_azuma_btn'>";
					}
				echo "</td>";

				break;
			case 2:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_hat.png" title="発注書表示" class="opa smlb ui-icon-print">
						';
						 // <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
					}
					if(!$row->s_is_print) {
						echo '
						 <img src="../img/b_print.png" title="発注書印刷" class="opa smlb ui-icon-print2">
						';
					}
					else {
						echo '
						 <img src="../img/b_sumi.png" title="解除" class="opa smlb ui-icon-print2">
						';
					}
				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_kouji_syu_hat";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0, "sy_order");
				echo "</td>";
		
				$clm = "s_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_seko_id";
				$sel_table = "matsushima_seko";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</td>";

				$clm = "s_st_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_end_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_shime_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_hattyu";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				$clm = "s_is_jv";
				echo "<td class='nw'>";
					if($row->$clm)
						echo "<input type='checkbox' checked='checked' value='1' class='{$clm}' disabled='disabled' />";
					else
						echo "<input type='checkbox' value='1' class='{$clm}' disabled='disabled' />";
				echo "</td>";

				echo "<td class='nw'>";
					if($row->s_hat_id) {
						echo "<span style='color:green'>発注締済</span>";
						$is_hat = true;
					}
					else {
						echo "<span style='color:red'>未発注</span>";
					}
				echo "</td>";
				
				echo "<td class='nw'>";
					if($row->s_st_date == "0000-00-00" || $row->s_st_date == "" || $row->s_st_date == null)
						$jump_date = "";
					else
						$jump_date = $row->s_st_date;
					
					if($row->s_id)
						echo "<a href='../../sche/main/index.php?sid={$row->s_id}&date={$jump_date}' target='_blank'><img src='../img/b_sche.png' title='スケジュール表示' class='opa'></a>";
				echo "</td>";
				
				echo "<td class='nw'>";
					if($row->s_id) {
						if(!$row->s_is_jv)
							echo "<input type='button' value='JV発注に変更' class='hattyu_normal_to_jv_btn'>";
					}
				echo "</td>";

				break;
			case 3:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
						 <img src="../img/b_hat.png" title="発注書表示" class="opa smlb ui-icon-print">
						';
					}
					if(!$row->s_is_print) {
						echo '
						 <img src="../img/b_print.png" title="発注書印刷" class="opa smlb ui-icon-print2">
						';
					}
					else {
						echo '
						 <img src="../img/b_sumi.png" title="解除" class="opa smlb ui-icon-print2">
						';
					}
				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_kouji_syu_hat";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0, "sy_order");
				echo "</td>";
		

				//JV施工業者取得
				echo "<td class='nw'>";
					$sql_seko = "SELECT * FROM matsushima_jv_rel
								INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_jv_rel.jv_seko_id
								WHERE jv_slip_id = '{$row->$SLIP_ID_FIELD[$slip_no]}'";
					$query_seko = @mysql_query($sql_seko);
					$num_seko = @mysql_num_rows($query_seko);
					if($num_seko) {
						while ($row_seko = mysql_fetch_object($query_seko)) {
							echo $row_seko->seko_nik . "&nbsp;";
						}
					}
				echo "</td>";

				$clm = "s_st_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_end_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_hattyu";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				echo "<td class='nw'>";
					if($row->s_st_date == "0000-00-00" || $row->s_st_date == "" || $row->s_st_date == null)
						$jump_date = "";
					else
						$jump_date = $row->s_st_date;
					
					if($row->s_id)
						echo "<a href='../../sche/main/index.php?sid={$row->s_id}&date={$jump_date}' target='_blank'><img src='../img/b_sche.png' title='スケジュール表示' class='opa'></a>";
				echo "</td>";

				echo "<td class='nw'>";
					if($row->s_id) {
						echo "<input type='button' value='発注操作' class='hattyu_jv_btn'>";
						echo "<input type='button' value='通常発注に変更' class='hattyu_jv_to_normal_btn'>";
					}
				echo "</td>";

				break;
		}
//----------------------個別領域 ここまで----------------------------
		echo "</tr>";
	}
	echo "<tr style='display:none'>";
//----------------------個別領域 ここから----------------------------
		switch($slip_no) {
			case 0:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
						 <img src="../img/b_mit.png" title="見積印刷" class="opa smlb ui-icon-print">
						';
					}

				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_est_syu";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</td>";

				$clm = "s_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_moto_invoice";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "7", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";
				break;
			case 1:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
						 <img src="../img/b_inv.png" title="請求書印刷" class="opa smlb ui-icon-print">
						';
					}
				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_kouji_syu";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</td>";
		
				$clm = "s_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_st_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_end_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_shime_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_moto_invoice";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_invoice";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";

				break;
			case 2:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_hat.png" title="発注書印刷" class="opa smlb ui-icon-print">
						';
					}
						// <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">

				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_kouji_syu_hat";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0, "sy_order");
				echo "</td>";
		
				$clm = "s_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_seko_id";
				$sel_table = "matsushima_seko";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0);
				echo "</td>";

				$clm = "s_st_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_end_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";


				$clm = "s_shime_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_hattyu";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				$clm = "s_is_jv";
				echo "<td class='nw'>";
					if($row->$clm)
						echo "<input type='checkbox' checked='checked' value='1' class='{$clm}' disabled='disabled' />";
					else
						echo "<input type='checkbox' value='1' class='{$clm}' disabled='disabled' />";
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";

				break;
			case 3:
				echo "<td class='nw' style='vertical-align:middle'>";
					if($row->$SLIP_ID_FIELD[$slip_no] != "") {
						echo '
						 <img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil">
						 <img src="../img/b_hat.png" title="発注書印刷" class="opa smlb ui-icon-print">
						';
					}
				echo "</td>";
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<img src="../img/b_drop.png" title="削除" class="opa smlb ui-icon-trash">';
				echo "</td>";
		
				echo "<td class='nw tac'>";
					//フィールドIDセット
					echo "<span class='{$SLIP_ID_FIELD[$slip_no]}_span'>" . $row->$SLIP_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$SLIP_ID_FIELD[$slip_no], $SLIP_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $SLIP_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";

				$clm = "s_seko_kubun_id";
				$sel_table = "matsushima_kouji_syu_hat";
				echo "<td class='nw'>";
					make_select($row->$clm, $clm, $sel_table, 0, "sy_order");
				echo "</td>";


				echo "<td class='nw'>";
				echo "</td>";

				$clm = "s_st_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_end_date";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tac");
				echo "</td>";

				$clm = "s_hattyu";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tar");
				echo "</td>";

				$clm = "s_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";

				echo "<td class='nw'>";
				echo "</td>";

				break;
		}
//----------------------個別領域 ここまで----------------------------
	echo "</tr>";

	echo "</tbody>";
	echo "</table>";
	
	if($slip_no == 0) {
		echo '<p class="tal mt5" style="color:red;font-size:10px;">※見積明細を入力する場合は一度保存してから「鉛筆」アイコンをクリックして下さい。</p>';
	}
	if($is_inv)
		echo '<p class="tal mt20" style="color:red;font-size:20px;border:1px solid red;padding:5px;text-align:center">※この現場は既に月次請求を締めた受注があります。値を修正すると請求内容に影響しますのでご注意下さい。</p>';

	if($is_hat)
		echo '<p class="tal mt20" style="color:red;font-size:20px;border:1px solid red;padding:5px;text-align:center">※この現場は既に月次発注を締めた現場があります。値を修正すると発注内容に影響しますのでご注意下さい。</p>';
	
	echo '<p class="tar mt10">';
	echo '<button id="insert_slip_btn" class="button">追加</button>';
	echo '</p>';
	
}

/**********************************************************************
 *
 * 	SLIP編集画面表示処理
 *
 **********************************************************************/
else if($flag == "EDIT_MEISAI") {

	//ID取得
	$id = $parm[1];
	$slip_no = $parm[2];
	
	//s_idの隠しフィールド
	make_textbox($id, "slip_s_id", "10", 0,"hidden");

	if($id) {
		//現場情報取得
		$MEISAI_JYOKEN = "AND {$MEISAI_REL_FIELD[$slip_no]} = '{$id}'";
		$sql = "SELECT * FROM {$MEISAI_TABLE[$slip_no]} WHERE 1 {$MEISAI_JYOKEN} ORDER BY sorder, {$MEISAI_ID_FIELD[$slip_no]}";

		//debug
		/*echo "<script>write_debug(\"{$sql}\");</script>";*/

		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
		if(!$num) {
			//ダミー
			$sql = "SELECT 1";
			$query = @mysql_query($sql);
			$num = @mysql_num_rows($query);
		}
	} else {

		//ダミー
		$sql = "SELECT 1";
		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	}
	
//----------------------個別領域 ここから----------------------------

	//jvの複数選択機能
	if($slip_no == 3) {

		$sql_seko = "SELECT * FROM matsushima_seko WHERE is_seko_show = 1 ORDER BY orderc";
	
		//debug
		/*echo "<script>write_debug(\"{$sql}\");</script>";*/
	
		$query_seko = @mysql_query($sql_seko);
		$num_seko = @mysql_num_rows($query_seko);
		if(!$num_seko) {
			//ダミー
			$sql_seko = "SELECT 1";
			$query_seko = @mysql_query($sql_seko);
			$num_seko = @mysql_num_rows($query_seko);
		}

		echo "<h2>JV施工者選択</h2>";
		echo "<div class='jv-seko-area mb20'>";

		while ($row_seko = mysql_fetch_object($query_seko)) {

			//JVチェックボックスコントロール
			$sql_j = "SELECT * FROM matsushima_jv_rel WHERE jv_slip_id = '{$id}' AND jv_seko_id = '{$row_seko->seko_id}'";
			$query_j = mysql_query($sql_j);
			$num_j = mysql_num_rows($query_j);
			
			if($num_j)
				$jv_check = "checked='checked'";
			else
				$jv_check = "";
			
			echo "<input type='checkbox' value='{$row_seko->seko_id}' class='jv_seko' {$jv_check}>";
			echo $row_seko->seko . "&nbsp;";
		}
		echo "</div>";
	}
	

	switch($slip_no) {
		case 0:
			echo "<h2 style='float:left;margin-right:50px'>見積明細編集</h2>";
			echo "<input type='button' value='ひな形1(ステップ無し)' onClick='hinagata(1)' />";
			echo "<input type='button' value='ひな形2(ステップ有り)' onClick='hinagata(2)' class='ml5'/>";
			echo "<div class='clearfloat'></div>";
			
			echo "<table class='meisai-table'>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>アクション</th>";
						echo "<th>並順</th>";
						echo "<th>ID</th>";
						echo "<th>名称 <span style='color:red'>*</span></th>";
						echo "<th>数量</th>";
						echo "<th>単位</th>";
						echo "<th>単価</th>";
						echo "<th>金額</th>";
						echo "<th>備考</th>";
					echo "</tr>";
				echo "</thead>";
			break;
		case 1:
			echo "<h2>受注明細編集</h2>";
			echo "<table class='meisai-table'>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>アクション</th>";
						echo "<th>並順</th>";
						echo "<th>ID</th>";
						echo "<th>名称 <span style='color:red'>*</span></th>";
						echo "<th>数量</th>";
						echo "<th>単位</th>";
						echo "<th>単価</th>";
						echo "<th>金額</th>";
						echo "<th>備考</th>";
					echo "</tr>";
				echo "</thead>";
			break;
		case 2:
			echo "<h2>発注明細編集</h2>";
			echo "<table class='meisai-table'>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>アクション</th>";
						echo "<th>並順</th>";
						echo "<th>ID</th>";
						echo "<th>名称 <span style='color:red'>*</span></th>";
						echo "<th>数量</th>";
						echo "<th>単位</th>";
						echo "<th>単価</th>";
						echo "<th>金額</th>";
						echo "<th>備考</th>";
					echo "</tr>";
				echo "</thead>";
			break;
		case 33:
			echo "<h2>JV発注明細編集</h2>";
			echo "<table class='meisai-table'>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>アクション</th>";
						echo "<th>並順</th>";
						echo "<th>ID</th>";
						echo "<th>名称 <span style='color:red'>*</span></th>";
						echo "<th>数量</th>";
						echo "<th>単位</th>";
						echo "<th>単価</th>";
						echo "<th>金額</th>";
						echo "<th>備考</th>";
					echo "</tr>";
				echo "</thead>";
			break;
	}
//----------------------個別領域 ここまで----------------------------

	echo "<tbody id='meisai_tbody'>";

	while ($row = mysql_fetch_object($query)) {
		echo "<tr>";
//----------------------個別領域 ここから----------------------------
		switch($slip_no) {
			case 0:
			case 1:
			case 2:
			case 33:
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<input type="checkbox" class="chk" value="'.$row->$MEISAI_ID_FIELD[$slip_no].'">
					 <img src="../img/ins.gif" title="挿入" class="opa smlb insert_line_middle_btn">
					 <img src="../img/up.gif" title="上へ移動" class="opa smlb up_line_btn">
					 <img src="../img/down.gif" title="下へ移動" class="opa smlb down_line_btn">
					 <img src="../img/b_drop.png" title="削除" class="opa smlb del_line_btn">
					';
				echo "</td>";

				$clm = "sorder";
				echo "<td class='tac'>";
					make_textbox($row->$clm, $clm, "2", 0, "text", "tac");
				echo "</td>";

				echo "<td class='nw'>";
					//フィールドIDセット
					echo "<span class='{$MEISAI_ID_FIELD[$slip_no]}_span'>" . $row->$MEISAI_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$MEISAI_ID_FIELD[$slip_no], $MEISAI_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $MEISAI_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";
		
				$clm = "m_meisho";
				echo "<td class='nw'>";
					make_optional($row->$clm, $clm,"20", 0, "matsushima_meisho_opt", "matsushima_meisai_est", "m_meisho", "tal");
				echo "</td>";

				$clm = "m_kazu";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tac");
				echo "</td>";

				$clm = "m_unit";
				echo "<td class='nw'>";
					make_optional($row->$clm, $clm,"3", 0, "matsushima_unit_opt", "matsushima_meisai_est", "m_unit", "tac");
				echo "</td>";

				$clm = "m_tanka";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "6", 0, "text", "tar");
				echo "</td>";

				$clm = "m_kingaku";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "7", 0, "text", "tar");
				echo "</td>";

				$clm = "m_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</td>";
				break;
		}
//----------------------個別領域 ここまで----------------------------
		echo "</tr>";

	}

//隠しフィールド
	echo "<tr style='display:none'>";
//----------------------個別領域 ここから----------------------------
		switch($slip_no) {
			case 0:
			case 1:
			case 2:
			case 33:
				echo "<td class='nw' style='vertical-align:middle'>";
					echo '<input type="checkbox" class="chk" value="'.$row->$MEISAI_ID_FIELD[$slip_no].'">
					 <img src="../img/ins.gif" title="挿入" class="opa smlb insert_line_middle_btn">
					 <img src="../img/up.gif" title="上へ移動" class="opa smlb up_line_btn">
					 <img src="../img/down.gif" title="下へ移動" class="opa smlb down_line_btn">
					 <img src="../img/b_drop.png" title="削除" class="opa smlb del_line_btn">
					';
				echo "</td>";

				$clm = "sorder";
				echo "<td class='tac'>";
					make_textbox($row->$clm, $clm, "2", 0, "text", "tac");
				echo "</td>";

				echo "<td class='nw'>";
					//フィールドIDセット
					echo "<span class='{$MEISAI_ID_FIELD[$slip_no]}_span'>" . $row->$MEISAI_ID_FIELD[$slip_no] . "</span>";
					make_textbox($row->$MEISAI_ID_FIELD[$slip_no], $MEISAI_ID_FIELD[$slip_no], "10", 0,"hidden");
					make_textbox($id, $MEISAI_REL_FIELD[$slip_no], "10", 0,"hidden");
				echo "</td>";
		
				$clm = "m_meisho";
				echo "<td class='nw'>";
					make_optional($row->$clm, $clm,"20", 0, "matsushima_meisho_opt", "matsushima_meisai_est", "m_meisho", "tal");
				echo "</td>";

				$clm = "m_kazu";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "5", 0, "text", "tac");
				echo "</td>";

				$clm = "m_unit";
				echo "<td class='nw'>";
					make_optional($row->$clm, $clm,"3", 0, "matsushima_unit_opt", "matsushima_meisai_est", "m_unit", "tac");
				echo "</td>";

				$clm = "m_tanka";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "6", 0, "text", "tar");
				echo "</td>";

				$clm = "m_kingaku";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "7", 0, "text", "tar");
				echo "</td>";

				$clm = "m_biko";
				echo "<td class='nw'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</td>";
				break;
		}
//----------------------個別領域 ここまで----------------------------
	echo "</tr>";

	echo "</tbody>";
	echo "</table>";
	
	//消費税ハンドリング
	$tax_check = "";
	$tax_per = $TAX;
	$SLIP_JYOKEN = "AND {$SLIP_ID_FIELD[$slip_no]} = '{$id}'";
	$sql = "SELECT * FROM  {$SLIP_TABLE[$slip_no]} WHERE 1 {$SLIP_JYOKEN} LIMIT 1";
	$query = @mysql_query($sql);
	$row = @mysql_fetch_object($query);
	if($row->s_tax) {
		$tax_check = "checked='checked'";
	}

	//計算領域
	echo "<div class='calc-area' style='margin-top:20px;'>";
		if($slip_no != 3) {
			echo "<p class='size16'>小&nbsp;&nbsp;計 <input type='text' size='10' id='meisai_shokei' class='tar size16'></p>";
			echo "<p class='size16'><input type='checkbox' id='tax_flag' {$tax_check} value='{$tax_per}'> 消費税 <input type='text' size='10' id='meisai_tax' class='tar size16'></p>";
			echo "<p class='size16'>合&nbsp;&nbsp;計 <input type='text' size='10' id='meisai_total' class='tar size16'></p>";
		
			//保存ボタン
			echo '<p class="tar mb10 mt10">';
			echo '追加する行 <input type="text" class="mr5 tac" id="add_line_cnt" size="2" value="1" />';
			echo '<input type="button" value=" + " onClick="add_line_val(1)" />';
			echo '<input type="button" value=" - " onClick="add_line_val(-1)" />';
			echo '<button class="button insert_meisai_btn mr5 ml5">追加</button>';
			echo '</p>';
		
			echo '<p class="tar mb10">';
			echo '<button class="button allcheck mr5">全てチェックする</button>';
			echo '<button class="button alluncheck mr5">全てチェックを外す</button>';
			echo '<button class="button meisai_del_btn mr5">チェックした行を一括削除</button>';
			echo '</p>';
		}
		echo '<p class="tar mb10">';
		echo '<button class="button update_meisai_btn mr5">保存する</button>';
		echo '<button class="button mr5" onClick="print_denpyo('.$slip_no.','. $id.')">印刷する</button>';
		echo '<button class="button closeDiag mr5">閉じる</button>';
		echo '</p>';
	echo "</div>";
}

/**********************************************************************
 *
 * 	受注処理
 *
 **********************************************************************/
else if($flag == "M_JYUTYU") {
//----------------------個別領域 ここから----------------------------

	$id = $parm[1];
	$moto_id = $parm[2];

	$today = date("Y-m-d");
	
	//分割ルール 0.5
	$div_rule = 0.5;

	//パターンを解析して受注を一つまたは複数作成
	$sql = "SELECT * FROM matsushima_moto WHERE moto_id = '{$moto_id}'";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);
	$m_pat = $row->m_pat;
	
	switch($m_pat) {
		case 1: //一括架時
		case 2: //一括払時

			//slipコピー
			$sql = "INSERT INTO matsushima_slip 
					(s_genba_id, s_seko_kubun_id, s_date, s_moto_invoice, s_invoice,s_tax)
					SELECT
					s_genba_id, '1' as s_seko_kubun_id, '{$today}' as s_date, s_moto_invoice, s_moto_invoice as s_invoice,s_tax
					FROM matsushima_slip_est WHERE s_id = '{$id}'";
			$query = @mysql_query($sql);
			$last_id = mysql_insert_id();
		
			//meisaiコピー
			$sql = "INSERT INTO matsushima_meisai 
					(m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder)
					SELECT
					'{$last_id}' as m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder
					FROM matsushima_meisai_est WHERE m_s_id = '{$id}'";
			$query = @mysql_query($sql);
		
			//受注にステータスを変更
			$sql = "UPDATE matsushima_slip_est SET s_seko_kubun_id = '2' WHERE s_id = '{$id}'"; 
			$query = @mysql_query($sql);
		
			//現場のステータスを受注に変更
			$sql = "UPDATE matsushima_genba 
					INNER JOIN matsushima_slip ON matsushima_genba.g_id = matsushima_slip.s_genba_id
					SET g_status = '2'
					WHERE s_id = '{$last_id}'"; 
			$query = @mysql_query($sql);

			break;

		case 3: //分割
		
		
			//分割金額計算
			
			$sql = "SELECT * FROM matsushima_slip_est WHERE s_id = '{$id}'";
			$query = mysql_query($sql);
			$row = mysql_fetch_object($query);
			$kingaku = $row->s_moto_invoice;
			$kake = floor($kingaku * $div_rule);
			$harai = $kingaku - $kake;

			//slipコピー 架
			$sql = "INSERT INTO matsushima_slip 
					(s_genba_id, s_seko_kubun_id, s_date, s_moto_invoice, s_invoice,s_tax)
					SELECT
					s_genba_id, '2' as s_seko_kubun_id, '{$today}' as s_date, s_moto_invoice, '{$kake}' as s_invoice,s_tax
					FROM matsushima_slip_est WHERE s_id = '{$id}'";
			$query = @mysql_query($sql);
			$last_id = mysql_insert_id();
		
			//meisaiコピー架
			$sql = "INSERT INTO matsushima_meisai 
					(m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder)
					SELECT
					'{$last_id}' as m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder
					FROM matsushima_meisai_est WHERE m_s_id = '{$id}'";
			$query = @mysql_query($sql);
		


			//slipコピー 払い
			$sql = "INSERT INTO matsushima_slip 
					(s_genba_id, s_seko_kubun_id, s_date, s_moto_invoice, s_invoice,s_tax)
					SELECT
					s_genba_id, '3' as s_seko_kubun_id, '{$today}' as s_date, s_moto_invoice, '{$harai}' as s_invoice,s_tax
					FROM matsushima_slip_est WHERE s_id = '{$id}'";
			$query = @mysql_query($sql);
			$last_id = mysql_insert_id();
		
			//meisaiコピー払い
			$sql = "INSERT INTO matsushima_meisai 
					(m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder)
					SELECT
					'{$last_id}' as m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder
					FROM matsushima_meisai_est WHERE m_s_id = '{$id}'";
			$query = @mysql_query($sql);
			break;
	}

	//受注にステータスを変更
	$sql = "UPDATE matsushima_slip_est SET s_seko_kubun_id = '2' WHERE s_id = '{$id}'"; 
	$query = @mysql_query($sql);

	//現場のステータスを受注に変更
	$sql = "UPDATE matsushima_genba 
			INNER JOIN matsushima_slip ON matsushima_genba.g_id = matsushima_slip.s_genba_id
			SET g_status = '2'
			WHERE s_id = '{$last_id}'"; 
	$query = @mysql_query($sql);


//----------------------個別領域 ここまで----------------------------
}

/**********************************************************************
 *
 * 	見積コピー処理
 *
 **********************************************************************/
else if($flag == "M_COPY") {

	$id = $parm[1];

//----------------------個別領域 ここから----------------------------
	//slipコピー
	$sql = "INSERT INTO matsushima_slip_est 
			(s_genba_id,s_inv_id,s_seko_kubun_id,s_date,s_st_date,s_end_date,s_moto_invoice,s_invoice,s_biko,s_tax)
			SELECT
			s_genba_id,s_inv_id,s_seko_kubun_id,s_date,s_st_date,s_end_date,s_moto_invoice,s_invoice,s_biko,s_tax
			FROM matsushima_slip_est WHERE s_id = '{$id}'";
	$query = @mysql_query($sql);
	$last_id = mysql_insert_id();

	//meisaiコピー
	$sql = "INSERT INTO matsushima_meisai_est 
			(m_s_id,m_meisho,m_shiyo,m_kazu,m_unit,m_tanka,m_kingaku,m_biko,sorder,m_group_id)
			SELECT
			'{$last_id}' as m_s_id,m_meisho,m_shiyo,m_kazu,m_unit,m_tanka,m_kingaku,m_biko,sorder,m_group_id
			FROM matsushima_meisai_est WHERE m_s_id = '{$id}'";
	$query = @mysql_query($sql);

//----------------------個別領域 ここまで----------------------------
}

/**********************************************************************
 *
 * 	通常受注に変換
 *
 **********************************************************************/
else if($flag == "CHANGE_TO_NORMAL") {

	$id = $parm[1];
	$k = $parm[2];

	//JVから発注操作されて残っている発注を削除する
	$sql = "DELETE FROM matsushima_slip_hat WHERE s_jv_rel_id = '{$id}' AND s_seko_kubun_id = '{$k}'"; 
	$query = mysql_query($sql);

	$today = date('Y-m-d');
	//slipコピー
	$sql = "INSERT INTO matsushima_slip_hat 
			(s_genba_id,s_seko_kubun_id,s_date,s_st_date,s_end_date,s_hattyu,s_biko,s_tax)
			SELECT
			s_genba_id,s_seko_kubun_id,'{$today}' as s_date,s_st_date,s_end_date,s_hattyu,s_biko,s_tax
			FROM matsushima_slip_jv WHERE s_id = '{$id}'";
	$query = @mysql_query($sql);
	$last_id = mysql_insert_id();

	//meisaiコピー
	$sql = "INSERT INTO matsushima_meisai_hat 
			(m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder)
			SELECT
			'{$last_id}' as m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder
			FROM matsushima_meisai_jv WHERE m_s_id = '{$id}'";
	$query = @mysql_query($sql);

	//削除
	$sql = "DELETE FROM matsushima_slip_jv WHERE s_id = '{$id}'"; 
	$query = @mysql_query($sql);
	$sql = "DELETE FROM matsushima_meisai_jv WHERE m_s_id = '{$id}'"; 
	$query = @mysql_query($sql);
}

/**********************************************************************
 *
 * 	JV受注に変換
 *
 **********************************************************************/
else if($flag == "CHANGE_TO_JV") {

	$id = $parm[1];

	//slipコピー
	$sql = "INSERT INTO matsushima_slip_jv 
			(s_genba_id,s_seko_kubun_id,s_st_date,s_end_date,s_hattyu,s_biko,s_tax)
			SELECT
			s_genba_id,s_seko_kubun_id,s_st_date,s_end_date,s_hattyu,s_biko,s_tax
			FROM matsushima_slip_hat WHERE s_id = '{$id}'";
	$query = @mysql_query($sql);
	$last_id = mysql_insert_id();

	//meisaiコピー
	$sql = "INSERT INTO matsushima_meisai_jv 
			(m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder)
			SELECT
			'{$last_id}' as m_s_id, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko, sorder
			FROM matsushima_meisai_hat WHERE m_s_id = '{$id}'";
	$query = @mysql_query($sql);

	//削除
	$sql = "DELETE FROM matsushima_slip_hat WHERE s_id = '{$id}'"; 
	$query = @mysql_query($sql);
	$sql = "DELETE FROM matsushima_meisai_hat WHERE m_s_id = '{$id}'"; 
	$query = @mysql_query($sql);

}

/**********************************************************************
 *
 * 	請求コピー処理
 *
 **********************************************************************/
else if($flag == "I_COPY") {
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
}
/**********************************************************************
 *
 * 	発注処理
 *
 **********************************************************************/
else if($flag == "M_HATTYU") {
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
}

else if($flag == "M_PAT") {
//----------------------個別領域 ここから----------------------------

	$moto_id = $parm[1];

	//パターンを解析して受注を一つまたは複数作成
	$sql = "SELECT * FROM matsushima_moto 
			LEFT OUTER JOIN matsushima_m_pat ON matsushima_m_pat.p_id = matsushima_moto.m_pat
			LEFT OUTER JOIN matsushima_shime ON matsushima_shime.id = matsushima_moto.m_shime_group
			WHERE moto_id = '{$moto_id}'";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);
	$moto = $row->moto;
	$p_name = $row->p_name;
	$m_shime_group = $row->name;
	
	if(preg_match('/末|随|日/',$m_shime_group))
		echo $moto . " : " . $p_name . " : " . $m_shime_group . "締め";
	else if(preg_match('/スポット/',$m_shime_group))
		echo $moto . " : " . $p_name . " : " . $m_shime_group;
	else
		echo $moto . " : " . $p_name . " : " . $m_shime_group . "日締め";
}

else if($flag == "SET_JYOKEN") {

	//元請け
	$sql = "SELECT * FROM matsushima_moto WHERE 1 ORDER BY kana";
	$query = @mysql_query($sql);
	$num = @mysql_num_rows($query);
	if(!$query) {
		echo "fail";
		exit();
	}
	if($num) {
		echo "<span style='font-weight:bold'>元請</span>&nbsp;&nbsp;";
		echo "<select id='search_sel_moto'>";
		echo "<option value='0'>元請を選択して下さい</option>";
		while ($row = @mysql_fetch_array($query)) {
			if($row[9])
				$branch = "({$row[9]})";
			else
				$branch = "";

			if($row[10])
				$kana = "[{$row[10]}]";
			else
				$kana = "";
			
			echo "<option value='{$row[0]}'>{$kana}{$row[2]} {$branch}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
	}

	//担当
	$sql = "SELECT * FROM matsushima_tantou WHERE is_show_tantou = 1 ORDER BY t_order, t_id";
	$query = @mysql_query($sql);
	$num = @mysql_num_rows($query);
	if(!$query) {
		echo "fail";
		exit();
	}
	if($num) {
		echo "<span style='font-weight:bold'>担当</span>&nbsp;&nbsp;";
		echo "<select id='search_sel_tantou'>";
		echo "<option value='0'>担当を選択して下さい</option>";
		while ($row = @mysql_fetch_array($query)) {
			echo "<option value='{$row[0]}'>{$row[1]}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
	}

	//商談状況
	$sql = "SELECT * FROM matsushima_est_syu WHERE 1 ORDER BY sy_id";
	$query = @mysql_query($sql);
	$num = @mysql_num_rows($query);
	if(!$query) {
		echo "fail";
		exit();
	}
	if($num) {
		echo "<span style='font-weight:bold'>商談状況</span>&nbsp;&nbsp;";
		echo "<select id='search_sel_status'>";
		echo "<option value='0'>商談状況を選択して下さい</option>";
		while ($row = @mysql_fetch_array($query)) {
			echo "<option value='{$row[0]}'>{$row[1]}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
	}


}

else if($flag == "EDIT_HATTYU") {
	$id = $parm[1];
	$g_nai1_id = $parm[2];

	echo "<h2>発注金額計算</h2>";
	//隠しフィールド
	echo "<input type='hidden' value='{$id}' id='hat_sousa_s_id' >";

	//受注額を抽出
	$sql = "SELECT * FROM matsushima_slip WHERE s_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);
	
	$s_moto_invoice = $row->s_moto_invoice;
	$g_id = $row->s_genba_id;

	//職方比率を抽出
	$sql = "SELECT * FROM matsushima_genba WHERE g_id = '{$g_id}' LIMIT 1";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);

	//$g_hat_per = $row->g_hat_per;
	
	//受注額を抽出
	$sql = "SELECT SUM(s_hattyu) as hat_ttl FROM matsushima_slip_hat WHERE s_genba_id = '{$g_id}'";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);
	if($row->hat_ttl > 0) {
		$btn_flag = false;
		echo "<p style='color:red'>既に発注書が登録されている為、このままでは発注操作は行えません。発注内容を変更する場合は「発注管理」の「発注一括編集」から行って下さい。<br />新規に発注操作を行う場合は、既存の発注額を0に設定するか削除してから再度実施して下さい。</p><br />";
	}
	else {
		$btn_flag = true;	

		echo "<table>";
			echo "<tr>";
				echo "<td>受注額</td>";
				echo "<td><input type='text' value='{$s_moto_invoice}' id='s_moto_invoice' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>職方比率</td>";
				echo "<td><input type='text' value='' id='shi1' size='3' class='tar'> %</td>";
				echo "<td>営業インセンティブ 3%</td>";
				echo "<td><input type='text' value='' id='sales3p' size='10' class='tar'> 円</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>発注額</td>";
				echo "<td><input type='text' value='' id='s_hattyu' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>建比率</td>";
				echo "<td><input type='text' value='60' id='tate_hi' size='3' class='tar'> %</td>";
				echo "<td>払比率</td>";
				echo "<td><input type='text' value='40' id='harai_hi' size='3' class='tar'> %</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>建</td>";
				echo "<td><input type='text' value='' id='tate' size='7' class='tar'> 円</td>";
				echo "<td>払</td>";
				echo "<td><input type='text' value='' id='harai' size='7' class='tar'> 円</td>";
			echo "</tr>";
	
			echo "<tr>";
			echo "<td colspan='4'>&nbsp;</td>";
			echo "</tr>";
	
			echo "<tr>";
				echo "<td></td>";
				echo "<td class='tac'>㎡数(ヶ所)</td>";
				echo "<td class='tac'>単価</td>";
				echo "<td class='tac'>小計</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>外部足場</td>";
				echo "<td><input type='text' value='' id='ashi_m2' size='7' class='tar'> m2</td>";
				echo "<td><input type='text' value='600' id='ashi_tanka' size='7' class='tar'> 円</td>";
				echo "<td><input type='text' value='' id='ashi_total' size='7' class='tar'> 円</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>シート</td>";
				echo "<td><input type='text' value='' id='sheet_m2' size='7' class='tar'> m2</td>";
				echo "<td><input type='text' value='100' id='sheet_tanka' size='7' class='tar'> 円</td>";
				echo "<td><input type='text' value='' id='sheet_total' size='7' class='tar'> 円</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>下屋(盛)</td>";
				echo "<td><input type='text' value='' id='geya_m2' size='7' class='tar'> m2</td>";
				echo "<td><input type='text' value='7000' id='geya_tanka' size='7' class='tar'> 円</td>";
				echo "<td><input type='text' value='' id='geya_total' size='7' class='tar'> 円</td>";
			echo "</tr>";
	
			echo "<tr>";
			echo "<td colspan='4'>&nbsp;</td>";
			echo "</tr>";
	
			echo "<tr>";
				echo "<td>架</td>";
				echo "<td><input type='text' value='' id='kake' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>塞ぎ</td>";
				echo "<td><input type='text' value='' id='fusagi' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>盛替(下屋)</td>";
				echo "<td><input type='text' value='' id='mori' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>払</td>";
				echo "<td><input type='text' value='' id='barashi' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
	
			echo "<tr>";
			echo "<td colspan='4'>&nbsp;</td>";
			echo "</tr>";
	
		echo "</table>";
		
		echo "<input type='button' class='button mr25' value='再計算' onclick='hat_calc()' >";
	}
	
	if($btn_flag) {
		echo " <input type='checkbox' id='zerohat'> 0円で発注する";
		
		echo "<br />";

		echo "<input type='button' class='button mr5' value='新築として発注' id='hat_btn1' >";
		echo "<input type='button' class='button mr5' value='新築(下屋あり)として発注' id='hat_btn2' >";
		echo "<input type='button' class='button mr5' value='リフォームとして発注' id='hat_btn3' >";
	
		echo "<br />";
	
		echo "<input type='button' class='button mr5' value='新築として発注(JV)' id='hat_btn4' >";
		echo "<input type='button' class='button mr5' value='新築(下屋あり)として発注(JV)' id='hat_btn5' >";
		echo "<input type='button' class='button mr25' value='リフォームとして発注(JV)' id='hat_btn6' >";
	
		echo "<br />";
	}
	
	echo '<button class="button closeDiag mr5">閉じる</button>';
	
}

else if($flag == "EDIT_MOD_HATTYU") {
	$id = $parm[1];

	echo "<h2>発注金額計算</h2>";
	//隠しフィールド
	echo "<input type='hidden' value='{$id}' id='hat_sousa_s_id' >";

	//職方比率を抽出
	$sql = "SELECT * FROM matsushima_genba WHERE g_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);

	//$g_hat_per = $row->g_hat_per;

	//保存した操作情報を取得
	$sql = "SELECT * FROM matsushima_hat_sousa WHERE z_g_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	$row = mysql_fetch_object($query);

	$tate_hi = $row->tate_hi;
	$harai_hi = $row->harai_hi;
	$ashi_tanka = $row->ashi_tanka;
	$sheet_tanka = $row->sheet_tanka;
	$geya_tanka = $row->geya_tanka;

	//保存データが無い場合
	if(!$num) {
		$tate_hi = 40;
		$harai_hi = 60;
		$ashi_tanka = 600;
		$sheet_tanka = 100;
		$geya_tanka = 7000;
	}

	echo "<table>";
		echo "<tr>";
			echo "<td>受注額</td>";
			echo "<td><input type='text' value='{$row->s_moto_invoice}' id='s_moto_invoice' size='7' class='tar'> 円 <button id='get_new_jyutyu'>最新受注額セット</button></td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>職方比率</td>";
			echo "<td><input type='text' value='{$row->shi1}' id='shi1' size='3' class='tar'> %</td>";
			echo "<td>営業インセンティブ 3%</td>";
			echo "<td><input type='text' value='{$row->sales3p}' id='sales3p' size='10' class='tar'> 円</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>発注額</td>";
			echo "<td><input type='text' value='{$row->s_hattyu}' id='s_hattyu' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>建比率</td>";
			echo "<td><input type='text' value='{$tate_hi}' id='tate_hi' size='3' class='tar'> %</td>";
			echo "<td>払比率</td>";
			echo "<td><input type='text' value='{$harai_hi}' id='harai_hi' size='3' class='tar'> %</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>建</td>";
			echo "<td><input type='text' value='{$row->tate}' id='tate' size='7' class='tar'> 円</td>";
			echo "<td>払</td>";
			echo "<td><input type='text' value='{$row->harai}' id='harai' size='7' class='tar'> 円</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan='4'>&nbsp;</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td></td>";
			echo "<td class='tac'>㎡数(ヶ所)</td>";
			echo "<td class='tac'>単価</td>";
			echo "<td class='tac'>小計</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>外部足場</td>";
			echo "<td><input type='text' value='{$row->ashi_m2}' id='ashi_m2' size='7' class='tar'> m2</td>";
			echo "<td><input type='text' value='{$ashi_tanka}' id='ashi_tanka' size='7' class='tar'> 円</td>";
			echo "<td><input type='text' value='{$row->ashi_total}' id='ashi_total' size='7' class='tar'> 円</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>シート</td>";
			echo "<td><input type='text' value='{$row->sheet_m2}' id='sheet_m2' size='7' class='tar'> m2</td>";
			echo "<td><input type='text' value='{$sheet_tanka}' id='sheet_tanka' size='7' class='tar'> 円</td>";
			echo "<td><input type='text' value='{$row->sheet_total}' id='sheet_total' size='7' class='tar'> 円</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>下屋(盛)</td>";
			echo "<td><input type='text' value='{$row->geya_m2}' id='geya_m2' size='7' class='tar'> m2</td>";
			echo "<td><input type='text' value='{$geya_tanka}' id='geya_tanka' size='7' class='tar'> 円</td>";
			echo "<td><input type='text' value='{$row->geya_total}' id='geya_total' size='7' class='tar'> 円</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan='4'>&nbsp;</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>架</td>";
			echo "<td><input type='text' value='{$row->kake}' id='kake' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>塞ぎ</td>";
			echo "<td><input type='text' value='{$row->fusagi}' id='fusagi' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>盛替(下屋)</td>";
			echo "<td><input type='text' value='{$row->mori}' id='mori' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>払</td>";
			echo "<td><input type='text' value='{$row->barashi}' id='barashi' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan='4'>&nbsp;</td>";
		echo "</tr>";

	echo "</table>";
	
	if($row->z_type == 1 || $row->z_type == 4) {
		$std1 = "checked='checked''";
		$std2 = "disabled='disabled'";
		$std3 = "disabled='disabled'";
	}
	else if($row->z_type == 2 || $row->z_type == 5) {
		$std1 = "disabled='disabled'";
		$std2 = "checked='checked''";
		$std3 = "disabled='disabled'";
	}
	else if($row->z_type == 3 || $row->z_type == 6) {
		$std1 = "disabled='disabled'";
		$std2 = "disabled='disabled'";
		$std3 = "checked='checked''";
	}

	echo "<br />";

	echo "<input type='radio' name='ztype' value='4' {$std1} class='mr5'>新築として発注";
	echo "<input type='radio' name='ztype' value='5' {$std2} class='mr5'>新築(下屋あり)として発注";
	echo "<input type='radio' name='ztype' value='6' {$std3} class='mr5'>リフォームとして発注";

	echo "<br />";
	echo "<input type='button' class='button mr25' value='再計算' onclick='hat_calc();set_sousa_mod()' >";
	echo "<br />";

	//echo "<input type='button' class='button mr5' value='新築として発注(JV)' id='hat_btn4' >";
	//echo "<input type='button' class='button mr5' value='新築(下屋あり)として発注(JV)' id='hat_btn5' >";
	//echo "<input type='button' class='button mr25' value='リフォームとして発注(JV)' id='hat_btn6' >";

	echo "<br />";

	$sql = "SELECT * FROM `matsushima_slip_hat` 
			LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id	
			LEFT OUTER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_slip_hat.s_seko_id	
			WHERE s_genba_id = '{$id}' AND s_is_jv = 0";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	if($num) {
	
		echo "<table class='hats_table'>";
	
			echo "<tr>";
			echo "<td class='tac'>ID</td>";
			echo "<td class='tac'>区分</td>";
			echo "<td class='tac'>発注日</td>";
			echo "<td class='tac'>職方</td>";
			echo "<td class='tac'>開始日</td>";
			echo "<td class='tac'>終了日</td>";
			echo "<td class='tac'>発注締日</td>";
			echo "<td class='tac'>現在発注額</td>";
			echo "<td class='tac'>変更後発注額</td>";
			echo "</tr>";
		
		while ($row = mysql_fetch_object($query)) {
			echo "<tr>";
			echo "<td class='tac'>{$row->s_id}<input type='hidden' value='{$row->s_id}' class='s_id_after'></td>";
			echo "<td class='tac'>{$row->sy_name_nik}<input type='hidden' value='{$row->sy_name_nik}' class='sy_name_nik_after'></td>";
			echo "<td class='tac'>".show_date($row->s_date)."</td>";
			echo "<td class='tac'>{$row->seko_nik}</td>";
			echo "<td class='tac'>".show_date($row->s_st_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_end_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_shime_date)."</td>";
			echo "<td class='tar'>{$row->s_hattyu}</td>";
			echo "<td class='tar'><input type='text' value='' class='s_hattyu_after tar' size='10'></td>";
			echo "</tr>";
		}	
		echo "</table>";
		echo "<span style='color:red'>※JVの発注はこの画面に表示されません。JVの個別発注金額は発注管理画面から入力して下さい。</span>";
	}
	
	echo "<br />";

	echo '<button class="button closeDiag mr5">閉じる</button>';
	echo '<button class="button mr5" id="update_sousa_btn">更新</button>';
}

else if($flag == "EDIT_MOD_HATTYU_JV") {
	$id = $parm[1];

	echo "<h2>発注金額計算</h2>";
	//隠しフィールド
	echo "<input type='hidden' value='{$id}' id='hat_sousa_s_id' >";

	//職方比率を抽出
	$sql = "SELECT * FROM matsushima_genba WHERE g_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);

	//$g_hat_per = $row->g_hat_per;

	//保存した操作情報を取得
	$sql = "SELECT * FROM matsushima_hat_sousa WHERE z_g_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	$row = mysql_fetch_object($query);

	$tate_hi = $row->tate_hi;
	$harai_hi = $row->harai_hi;
	$ashi_tanka = $row->ashi_tanka;
	$sheet_tanka = $row->sheet_tanka;
	$geya_tanka = $row->geya_tanka;

	//保存データが無い場合
	if(!$num) {
		$tate_hi = 40;
		$harai_hi = 60;
		$ashi_tanka = 600;
		$sheet_tanka = 100;
		$geya_tanka = 7000;
	}

	echo "<table>";
		echo "<tr>";
			echo "<td>受注額</td>";
			echo "<td><input type='text' value='{$row->s_moto_invoice}' id='s_moto_invoice' size='7' class='tar'> 円 <button id='get_new_jyutyu'>最新受注額セット</button></td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>職方比率</td>";
			echo "<td><input type='text' value='{$row->shi1}' id='shi1' size='3' class='tar'> %</td>";
			echo "<td>営業インセンティブ 3%</td>";
			echo "<td><input type='text' value='{$row->sales3p}' id='sales3p' size='10' class='tar'> 円</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>発注額</td>";
			echo "<td><input type='text' value='{$row->s_hattyu}' id='s_hattyu' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>建比率</td>";
			echo "<td><input type='text' value='{$tate_hi}' id='tate_hi' size='3' class='tar'> %</td>";
			echo "<td>払比率</td>";
			echo "<td><input type='text' value='{$harai_hi}' id='harai_hi' size='3' class='tar'> %</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>建</td>";
			echo "<td><input type='text' value='{$row->tate}' id='tate' size='7' class='tar'> 円</td>";
			echo "<td>払</td>";
			echo "<td><input type='text' value='{$row->harai}' id='harai' size='7' class='tar'> 円</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan='4'>&nbsp;</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td></td>";
			echo "<td class='tac'>㎡数(ヶ所)</td>";
			echo "<td class='tac'>単価</td>";
			echo "<td class='tac'>小計</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>外部足場</td>";
			echo "<td><input type='text' value='{$row->ashi_m2}' id='ashi_m2' size='7' class='tar'> m2</td>";
			echo "<td><input type='text' value='{$ashi_tanka}' id='ashi_tanka' size='7' class='tar'> 円</td>";
			echo "<td><input type='text' value='{$row->ashi_total}' id='ashi_total' size='7' class='tar'> 円</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>シート</td>";
			echo "<td><input type='text' value='{$row->sheet_m2}' id='sheet_m2' size='7' class='tar'> m2</td>";
			echo "<td><input type='text' value='{$sheet_tanka}' id='sheet_tanka' size='7' class='tar'> 円</td>";
			echo "<td><input type='text' value='{$row->sheet_total}' id='sheet_total' size='7' class='tar'> 円</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>下屋(盛)</td>";
			echo "<td><input type='text' value='{$row->geya_m2}' id='geya_m2' size='7' class='tar'> m2</td>";
			echo "<td><input type='text' value='{$geya_tanka}' id='geya_tanka' size='7' class='tar'> 円</td>";
			echo "<td><input type='text' value='{$row->geya_total}' id='geya_total' size='7' class='tar'> 円</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan='4'>&nbsp;</td>";
		echo "</tr>";

		echo "<tr>";
			echo "<td>架</td>";
			echo "<td><input type='text' value='{$row->kake}' id='kake' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>塞ぎ</td>";
			echo "<td><input type='text' value='{$row->fusagi}' id='fusagi' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>盛替(下屋)</td>";
			echo "<td><input type='text' value='{$row->mori}' id='mori' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>払</td>";
			echo "<td><input type='text' value='{$row->barashi}' id='barashi' size='7' class='tar'> 円</td>";
			echo "<td></td>";
			echo "<td></td>";

		echo "</tr>";

		echo "<tr>";
		echo "<td colspan='4'>&nbsp;</td>";
		echo "</tr>";

	echo "</table>";

	if($row->z_type == 1 || $row->z_type == 4) {
		$std1 = "checked='checked''";
		$std2 = "disabled='disabled'";
		$std3 = "disabled='disabled'";
	}
	else if($row->z_type == 2 || $row->z_type == 5) {
		$std1 = "disabled='disabled'";
		$std2 = "checked='checked''";
		$std3 = "disabled='disabled'";
	}
	else if($row->z_type == 3 || $row->z_type == 6) {
		$std1 = "disabled='disabled'";
		$std2 = "disabled='disabled'";
		$std3 = "checked='checked''";
	}


	echo "<input type='radio' name='ztype' value='1' {$std1} class='mr5'>新築として発注";
	echo "<input type='radio' name='ztype' value='2' {$std2} class='mr5'>新築(下屋あり)として発注";
	echo "<input type='radio' name='ztype' value='3' {$std3} class='mr5'>リフォームとして発注";

	echo "<br />";
	echo "<input type='button' class='button mr25' value='再計算' onclick='hat_calc();set_sousa_mod()' >";
	echo "<br />";

	//echo "<input type='button' class='button mr5' value='新築として発注(JV)' id='hat_btn4' >";
	//echo "<input type='button' class='button mr5' value='新築(下屋あり)として発注(JV)' id='hat_btn5' >";
	//echo "<input type='button' class='button mr25' value='リフォームとして発注(JV)' id='hat_btn6' >";

	echo "<br />";

	$sql = "SELECT * FROM `matsushima_slip_jv` 
			LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_jv.s_seko_kubun_id	
			LEFT OUTER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_slip_jv.s_seko_id	
			WHERE s_genba_id = '{$id}'";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	if($num) {
	
		echo "<table class='hats_table'>";
	
			echo "<tr>";
			echo "<td class='tac'>ID</td>";
			echo "<td class='tac'>区分</td>";
			echo "<td class='tac'>開始日</td>";
			echo "<td class='tac'>終了日</td>";
			echo "<td class='tac'>現在発注額</td>";
			echo "<td class='tac'>変更後発注額</td>";
			echo "</tr>";
		
		while ($row = mysql_fetch_object($query)) {
			echo "<tr>";
			echo "<td class='tac'>{$row->s_id}<input type='hidden' value='{$row->s_id}' class='s_id_after'></td>";
			echo "<td class='tac'>{$row->sy_name_nik}<input type='hidden' value='{$row->sy_name_nik}' class='sy_name_nik_after'></td>";
			echo "<td class='tac'>".show_date($row->s_end_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_shime_date)."</td>";
			echo "<td class='tar'>{$row->s_hattyu}</td>";
			echo "<td class='tar'><input type='text' value='' class='s_hattyu_after tar' size='10'></td>";
			echo "</tr>";
		}	
		echo "</table>";
	}
	
	echo "<br />";

	echo '<button class="button closeDiag mr5">閉じる</button>';
	echo '<button class="button mr5" id="update_sousa_jv_btn">更新</button>';
}

else if($flag == "EDIT_MOD_HATTYU_AZUMA") {
	$id = $parm[1];

	echo "<h2>発注金額計算</h2>";
	//隠しフィールド
	echo "<input type='hidden' value='{$id}' id='hat_sousa_s_id' >";

	//保存した操作情報を取得
	$sql = "SELECT * FROM matsushima_hat_sousa_azuma WHERE z_g_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	$row = mysql_fetch_object($query);

	echo "<table>";
		echo "<tr>";
		echo "<tr>";
			echo "<td>職方比率</td>";
			echo "<td><input type='text' value='{$row->shi1}' id='shi1' size='3' class='tar'> %</td>";
			echo "<td><input type='hidden' value='{$row->sales3p}' id='sales3p' size='10' class='tar'> 円</td>";
		echo "</tr>";

	echo "</table>";

	echo "<br />";
	echo "<input type='button' class='button mr25' value='再計算' onclick='hat_calc_mod_azuma()' >";
	echo "<br />";

	$sql = "SELECT * FROM `matsushima_slip_hat` 
			LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id	
			LEFT OUTER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_slip_hat.s_seko_id	
			WHERE s_genba_id = '{$id}' AND s_is_jv = 0";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	if($num) {
	
		echo "<table class='hats_table' style='margin-top:20px;'>";
	
			echo "<tr>";
			echo "<td class='tac'>ID</td>";
			echo "<td class='tac'>区分</td>";
			echo "<td class='tac'>発注日</td>";
			echo "<td class='tac'>職方</td>";
			echo "<td class='tac'>開始日</td>";
			echo "<td class='tac'>終了日</td>";
			echo "<td class='tac'>発注締日</td>";
			echo "<td class='tac'>受注額</td>";
			echo "<td class='tac'>現在発注額</td>";
			echo "<td class='tac'>変更後発注額</td>";
			echo "</tr>";
		
		while ($row = mysql_fetch_object($query)) {
			echo "<tr>";
			echo "<td class='tac'>{$row->s_id}<input type='hidden' value='{$row->s_id}' class='s_id_after'></td>";
			echo "<td class='tac'>{$row->sy_name_nik}<input type='hidden' value='{$row->sy_name_nik}' class='sy_name_nik_after'></td>";
			echo "<td class='tac'>".show_date($row->s_date)."</td>";
			echo "<td class='tac'>{$row->seko_nik}</td>";
			echo "<td class='tac'>".show_date($row->s_st_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_end_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_shime_date)."</td>";

			//受注額取得
			$sql_s = "SELECT * FROM `matsushima_slip` WHERE s_genba_id = '{$id}' AND s_seko_kubun_id = '{$row->s_seko_kubun_id}' ORDER BY s_id DESC LIMIT 1";
			$query_s = mysql_query($sql_s);
			$row_s = mysql_fetch_object($query_s);
			echo "<td class='tar'>{$row_s->s_invoice}<input type='hidden' value='{$row_s->s_invoice}' class='s_invoice_azuma tar' size='10'></td>";

			echo "<td class='tar'>{$row->s_hattyu}<input type='hidden' value='{$row->s_hattyu}' class='s_hattyu_before tar' size='10'></td>";
			echo "<td class='tar'><input type='text' value='' class='s_hattyu_after tar' size='10'></td>";
			echo "</tr>";
		}	
		echo "</table>";
		echo "<span style='color:red'>※JVの発注はこの画面に表示されません。JVの個別発注金額は発注管理画面から入力して下さい。</span>";
	}
	
	echo "<br />";
	echo '<button class="button mr5" id="update_sousa_azuma_btn">更新</button>';
	echo '<button class="button closeDiag mr5">閉じる</button>';
}

else if($flag == "EDIT_MOD_HATTYU_JV_AZUMA") {
	$id = $parm[1];

	echo "<h2>発注金額計算</h2>";
	//隠しフィールド
	echo "<input type='hidden' value='{$id}' id='hat_sousa_s_id' >";

	//保存した操作情報を取得
	$sql = "SELECT * FROM matsushima_hat_sousa_azuma WHERE z_g_id = '{$id}' LIMIT 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	$row = mysql_fetch_object($query);

	echo "<table>";
		echo "<tr>";
		echo "<tr>";
			echo "<td>職方比率</td>";
			echo "<td><input type='text' value='{$row->shi1}' id='shi1' size='3' class='tar'> %</td>";
			echo "<td><input type='hidden' value='{$row->sales3p}' id='sales3p' size='10' class='tar'> 円</td>";
		echo "</tr>";

	echo "</table>";

	echo "<br />";
	echo "<input type='button' class='button mr25' value='再計算' onclick='hat_calc_mod_azuma()' >";
	echo "<br />";

	$sql = "SELECT * FROM `matsushima_slip_jv` 
			LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_jv.s_seko_kubun_id	
			WHERE s_genba_id = '{$id}'";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	if($num) {
	
		echo "<table class='hats_table' style='margin-top:20px;'>";
	
			echo "<tr>";
			echo "<td class='tac'>ID</td>";
			echo "<td class='tac'>区分</td>";
			echo "<td class='tac'>発注日</td>";
			echo "<td class='tac'>開始日</td>";
			echo "<td class='tac'>終了日</td>";
			echo "<td class='tac'>発注締日</td>";
			echo "<td class='tac'>受注額</td>";
			echo "<td class='tac'>現在発注額</td>";
			echo "<td class='tac'>変更後発注額</td>";
			echo "</tr>";
		
		while ($row = mysql_fetch_object($query)) {
			echo "<tr>";
			echo "<td class='tac'>{$row->s_id}<input type='hidden' value='{$row->s_id}' class='s_id_after'></td>";
			echo "<td class='tac'>{$row->sy_name_nik}<input type='hidden' value='{$row->sy_name_nik}' class='sy_name_nik_after'></td>";
			echo "<td class='tac'>".show_date($row->s_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_st_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_end_date)."</td>";
			echo "<td class='tac'>".show_date($row->s_shime_date)."</td>";

			//受注額取得
			$sql_s = "SELECT * FROM `matsushima_slip` WHERE s_genba_id = '{$id}' AND s_seko_kubun_id = '{$row->s_seko_kubun_id}' ORDER BY s_id DESC LIMIT 1";
			$query_s = mysql_query($sql_s);
			$row_s = mysql_fetch_object($query_s);
			echo "<td class='tar'>{$row_s->s_invoice}<input type='hidden' value='{$row_s->s_invoice}' class='s_invoice_azuma tar' size='10'></td>";

			echo "<td class='tar'>{$row->s_hattyu}<input type='hidden' value='{$row->s_hattyu}' class='s_hattyu_before tar' size='10'></td>";
			echo "<td class='tar'><input type='text' value='' class='s_hattyu_after tar' size='10'></td>";
			echo "</tr>";
		}	
		echo "</table>";
	}
	
	echo "<br />";
	echo '<button class="button mr5" id="update_sousa_azuma_jv_btn">更新</button>';
	echo '<button class="button closeDiag mr5">閉じる</button>';
}

else if($flag == "EDIT_AZUMA_HATTYU") {
	$gid = $parm[1];
	$sid = $parm[2];
	$k = $parm[3];

	echo "<h2>東リース発注金額計算</h2>";

	//受注額を抽出
	$sql = "SELECT SUM(s_hattyu) as hat_ttl FROM matsushima_slip_hat WHERE s_genba_id = '{$gid}' AND s_seko_kubun_id = '{$k}'";
	$query = mysql_query($sql);
	$row = mysql_fetch_object($query);
	if($row->hat_ttl > 0) {
		$btn_flag = false;
		echo "<p style='color:red'>既に同じ区分の発注書が登録されている為、このままでは発注操作は行えません。発注内容を変更する場合は「発注管理」の「発注一括編集」から行って下さい。<br />新規に発注操作を行う場合は、既存の発注額を0に設定するか削除してから再度実施して下さい。</p><br />";
	}
	else {
		//受注額を抽出
		$sql = "SELECT * FROM matsushima_slip 
				LEFT OUTER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = matsushima_slip.s_seko_kubun_id
				WHERE s_id = '{$sid}' LIMIT 1";
		$query = mysql_query($sql);
		$row = mysql_fetch_object($query);
		
		$s_moto_invoice = $row->s_invoice;
	
		echo "<table>";
			echo "<tr>";
				echo "<td>受注額</td>";
				echo "<td><input type='text' value='{$s_moto_invoice}' id='s_moto_invoice' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>職方比率</td>";
				echo "<td><input type='text' value='90' id='shi1' size='3' class='tar'> %</td>";
				echo "<td>営業インセンティブ 3%</td>";
				echo "<td><input type='text' value='' id='sales3p' size='10' class='tar'> 円</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>発注額</td>";
				echo "<td><input type='text' value='' id='s_hattyu' size='7' class='tar'> 円</td>";
				echo "<td></td>";
				echo "<td></td>";
			echo "</tr>";
	
			echo "<tr>";
			echo "<td colspan='4'>&nbsp;</td>";
			echo "</tr>";
	
		echo "</table>";
		
		echo "<input type='button' class='button mr5' value='再計算' onclick='hat_azuma_calc()' >";
		
		echo "<input type='button' class='button mr5' value='発注' id='hat_azuma_btn' > ";
		echo "<input type='button' class='button mr5' value='JVとして発注' id='hat_azuma_jv_btn' > <input type='checkbox' id='zerohat'> 0円で発注する";
	
		echo "<input type='hidden' value='{$row->s_seko_kubun_id}' id='seko_kubun_id' >";
		echo "<input type='hidden' value='{$row->sy_name_nik}' id='seko_kubun' >";
	
		echo "<br />";
	}
	echo '<button class="button closeDiag mr5">閉じる</button>';
	
}

else if($flag == "HAT_EXEC") {
	$g_id = $parm[1];
	$syu = $parm[2];
	$tate = $parm[3];
	$kake = $parm[4];
	$fusagi = $parm[5];
	$mori = $parm[6];
	$harai = $parm[7];
	$sid = $parm[8];
	$shi1 = $parm[9];

	$s_moto_invoice= $parm[10]; 
	$sales3p= $parm[11]; 
	$s_hattyu= $parm[12]; 
	$tate_hi= $parm[13]; 
	$harai_hi= $parm[14]; 
	$ashi_m2= $parm[15]; 
	$ashi_tanka= $parm[16]; 
	$ashi_total= $parm[17]; 
	$sheet_m2= $parm[18]; 
	$sheet_tanka= $parm[19]; 
	$sheet_total= $parm[20]; 
	$geya_m2= $parm[21]; 
	$geya_tanka= $parm[22]; 
	$geya_total= $parm[23]; 
	$barashi= $parm[24];

	$sql = "DELETE FROM matsushima_hat_sousa WHERE z_g_id = '{$g_id}'";
	$query = mysql_query($sql);

	//$sql = "UPDATE matsushima_genba SET g_hat_per = '{$shi1}' WHERE g_id = '{$g_id}'";
	//$query = mysql_query($sql);

	switch($syu) {
		case 1:
	
			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '2', date(now()), '{$kake}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','架','1','','{$kake}','{$kake}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '4', date(now()), '{$fusagi}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','塞ぎ','1','','{$fusagi}','{$fusagi}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '3', date(now()), '{$harai}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','払し','1','','{$harai}','{$harai}')";
			$query = mysql_query($sql);

			break;

		case 2:
	
			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '2', date(now()), '{$kake}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','架','1','','{$kake}','{$kake}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '4', date(now()), '{$fusagi}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','塞ぎ','1','','{$fusagi}','{$fusagi}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '5', date(now()), '{$mori}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','盛替(下屋)','1','','{$mori}','{$mori}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '3', date(now()), '{$harai}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','払し','1','','{$harai}','{$harai}')";
			$query = mysql_query($sql);

			break;

		case 3:
	
			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '2', date(now()), '{$tate}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','架','1','','{$tate}','{$tate}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '3', date(now()), '{$harai}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','払し','1','','{$harai}','{$harai}')";
			$query = mysql_query($sql);

			break;

		case 4:
	
			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '2', date(now()), '{$kake}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','架','1','','{$kake}','{$kake}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '4', date(now()), '{$fusagi}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','塞ぎ','1','','{$fusagi}','{$fusagi}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '3', date(now()), '{$harai}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','払し','1','','{$harai}','{$harai}')";
			$query = mysql_query($sql);

			break;

		case 5:
	
			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '2', date(now()), '{$kake}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','架','1','','{$kake}','{$kake}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '4', date(now()), '{$fusagi}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','塞ぎ','1','','{$fusagi}','{$fusagi}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '5', date(now()), '{$mori}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','盛替(下屋)','1','','{$mori}','{$mori}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '3', date(now()), '{$harai}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','払し','1','','{$harai}','{$harai}')";
			$query = mysql_query($sql);

			break;

		case 6:
	
			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '2', date(now()), '{$tate}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','架','1','','{$tate}','{$tate}')";
			$query = mysql_query($sql);

			$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '3', date(now()), '{$harai}', '0')";
			$query = mysql_query($sql);
			$last_id = mysql_insert_id();
			$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','払し','1','','{$harai}','{$harai}')";
			$query = mysql_query($sql);

			break;
	}
	
	//計算式を保存
	$sql = "INSERT INTO matsushima_hat_sousa (z_inv_id, z_g_id,z_type,shi1,tate,kake,fusagi,mori,harai,s_moto_invoice, sales3p, s_hattyu, tate_hi, harai_hi, ashi_m2, ashi_tanka, ashi_total, sheet_m2, sheet_tanka, sheet_total, geya_m2, geya_tanka, geya_total, barashi) VALUES ('{$sid}','{$g_id}','{$syu}','{$shi1}','{$tate}','{$kake}','{$fusagi}','{$mori}','{$harai}','{$s_moto_invoice}','{$sales3p}','{$s_hattyu}','{$tate_hi}','{$harai_hi}','{$ashi_m2}','{$ashi_tanka}','{$ashi_total}','{$sheet_m2}','{$sheet_tanka}','{$sheet_total}','{$geya_m2}','{$geya_tanka}','{$geya_total}','{$barashi}')";
	$query = mysql_query($sql);
	
}

else if($flag == "HAT_MOD_EXEC") {
	$g_id = $parm[1];
	$syu = $parm[2];
	$tate = $parm[3];
	$kake = $parm[4];
	$fusagi = $parm[5];
	$mori = $parm[6];
	$harai = $parm[7];
	$sid = $parm[8];
	$shi1 = $parm[9];

	$s_moto_invoice= $parm[10]; 
	$sales3p= $parm[11]; 
	$s_hattyu= $parm[12]; 
	$tate_hi= $parm[13]; 
	$harai_hi= $parm[14]; 
	$ashi_m2= $parm[15]; 
	$ashi_tanka= $parm[16]; 
	$ashi_total= $parm[17]; 
	$sheet_m2= $parm[18]; 
	$sheet_tanka= $parm[19]; 
	$sheet_total= $parm[20]; 
	$geya_m2= $parm[21]; 
	$geya_tanka= $parm[22]; 
	$geya_total= $parm[23]; 
	$barashi= $parm[24];


	$sql = "DELETE FROM matsushima_hat_sousa WHERE z_g_id = '{$g_id}'";
	$query = mysql_query($sql);
	//計算式を保存
	$sql = "INSERT INTO matsushima_hat_sousa (z_inv_id, z_g_id,z_type,shi1,tate,kake,fusagi,mori,harai,s_moto_invoice, sales3p, s_hattyu, tate_hi, harai_hi, ashi_m2, ashi_tanka, ashi_total, sheet_m2, sheet_tanka, sheet_total, geya_m2, geya_tanka, geya_total, barashi) VALUES ('{$sid}','{$g_id}','{$syu}','{$shi1}','{$tate}','{$kake}','{$fusagi}','{$mori}','{$harai}','{$s_moto_invoice}','{$sales3p}','{$s_hattyu}','{$tate_hi}','{$harai_hi}','{$ashi_m2}','{$ashi_tanka}','{$ashi_total}','{$sheet_m2}','{$sheet_tanka}','{$sheet_total}','{$geya_m2}','{$geya_tanka}','{$geya_total}','{$barashi}')";
	$query = mysql_query($sql);

}

else if($flag == "HAT_AZUMA_EXEC") {
	$g_id = $parm[1];
	$s_hattyu = $parm[2];
	$seko_kubun_id = $parm[3];
	$seko_kubun = $parm[4];

	$shi1 = $parm[5];
	$s_moto_invoice= $parm[6]; 
	$sales3p= $parm[7]; 

	//計算式を削除
	$sql = "DELETE FROM matsushima_hat_sousa_azuma WHERE z_g_id = '{$g_id}'";
	$query = mysql_query($sql);

	//計算式を保存
	$sql = "INSERT INTO matsushima_hat_sousa_azuma (z_inv_id, z_g_id,z_type,shi1,s_moto_invoice, sales3p, s_hattyu) VALUES ('{$sid}','{$g_id}','{$syu}','{$shi1}','{$s_moto_invoice}','{$sales3p}','{$s_hattyu}')";
	$query = mysql_query($sql);
	
	$sql = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '{$seko_kubun_id}', date(now()), '{$s_hattyu}', '0')";
	$query = mysql_query($sql);
	$last_id = mysql_insert_id();
	$sql = "INSERT INTO matsushima_meisai_hat (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','{$seko_kubun}','1','','{$s_hattyu}','{$s_hattyu}')";
	$query = mysql_query($sql);
}

else if($flag == "HAT_AZUMA_EXEC_JV") {
	$g_id = $parm[1];
	$s_hattyu = $parm[2];
	$seko_kubun_id = $parm[3];
	$seko_kubun = $parm[4];

	$shi1 = $parm[5];
	$s_moto_invoice= $parm[6]; 
	$sales3p= $parm[7]; 

	//計算式を削除
	$sql = "DELETE FROM matsushima_hat_sousa_azuma WHERE z_g_id = '{$g_id}'";
	$query = mysql_query($sql);

	//計算式を保存
	$sql = "INSERT INTO matsushima_hat_sousa_azuma (z_inv_id, z_g_id,z_type,shi1,s_moto_invoice, sales3p, s_hattyu) VALUES ('{$sid}','{$g_id}','{$syu}','{$shi1}','{$s_moto_invoice}','{$sales3p}','{$s_hattyu}')";
	$query = mysql_query($sql);
	
	$sql = "INSERT INTO matsushima_slip_jv (s_genba_id, s_seko_kubun_id, s_date, s_hattyu, s_seko_id) VALUES ('{$g_id}', '{$seko_kubun_id}', date(now()), '{$s_hattyu}', '0')";
	$query = mysql_query($sql);
	$last_id = mysql_insert_id();
	$sql = "INSERT INTO matsushima_meisai_jv (m_s_id,m_meisho,m_kazu,m_unit,m_tanka,m_kingaku) VALUES ('{$last_id}','{$seko_kubun}','1','','{$s_hattyu}','{$s_hattyu}')";
	$query = mysql_query($sql);
}

else if($flag == "HAT_AZUMA_MOD_EXEC") {
	$g_id = $parm[1];
	$s_hattyu = $parm[2];
	$seko_kubun_id = $parm[3];
	$seko_kubun = $parm[4];

	$shi1 = $parm[5];
	$s_moto_invoice= $parm[6]; 
	$sales3p= $parm[7]; 

	//計算式を削除
	$sql = "DELETE FROM matsushima_hat_sousa_azuma WHERE z_g_id = '{$g_id}'";
	$query = mysql_query($sql);

	//計算式を保存
	$sql = "INSERT INTO matsushima_hat_sousa_azuma (z_inv_id, z_g_id,z_type,shi1,s_moto_invoice, sales3p, s_hattyu) VALUES ('{$sid}','{$g_id}','{$syu}','{$shi1}','{$s_moto_invoice}','{$sales3p}','{$s_hattyu}')";
	$query = mysql_query($sql);
}

//JVの発注先保存処理
else if($flag == "M_JV_SEKO") {
	
	$s_id = $parm[1];
	$jv_cbno = $parm[2];

	$sql_u = "DELETE FROM matsushima_jv_rel WHERE jv_slip_id = '{$s_id}'";
	$query_u = mysql_query($sql_u);
	
	$sql = "SELECT * FROM matsushima_seko WHERE seko_id in ({$jv_cbno})";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_object($query)) {
		$sql_u = "INSERT INTO matsushima_jv_rel (jv_slip_id, jv_seko_id) VALUES ('{$s_id}', '{$row->seko_id}')";
		$query_u = mysql_query($sql_u);
	}
}

else if($flag == "ALREADY_PRINT") {

	$slipno = $parm[1];
	$id = $parm[2];


	if($slipno == 0)
		return;
	else if($slipno == 1) {
		$sql = "SELECT * FROM matsushima_slip WHERE s_id = '{$id}'";
		$query = mysql_query($sql);
		$row = mysql_fetch_object($query);
		
		if($row->s_is_print)
			$sql = "UPDATE matsushima_slip SET s_is_print = 0 WHERE s_id = '{$id}'";
		else
			$sql = "UPDATE matsushima_slip SET s_is_print = 1 WHERE s_id = '{$id}'";

		$query = mysql_query($sql);
	}
	else if($slipno == 2) {
		
		$sql = "SELECT * FROM matsushima_slip_hat WHERE s_id = '{$id}'";
		$query = mysql_query($sql);
		$row = mysql_fetch_object($query);
		
		if($row->s_is_print)
			$sql = "UPDATE matsushima_slip_hat SET s_is_print = 0 WHERE s_id = '{$id}'";
		else
			$sql = "UPDATE matsushima_slip_hat SET s_is_print = 1 WHERE s_id = '{$id}'";

		$query = mysql_query($sql);
	}

	else if($slipno == 3) {
		
		$sql = "SELECT * FROM matsushima_slip_jv WHERE s_id = '{$id}'";
		$query = mysql_query($sql);
		$row = mysql_fetch_object($query);
		
		if($row->s_is_print)
			$sql = "UPDATE matsushima_slip_jv SET s_is_print = 0 WHERE s_id = '{$id}'";
		else
			$sql = "UPDATE matsushima_slip_jv SET s_is_print = 1 WHERE s_id = '{$id}'";

		$query = mysql_query($sql);
	}
	else
		return;

}

else if($flag == "M_HAT_FROM_JV") {

	$s_id = $parm[1];
	
	$today = date("Y-m-d");
	
	$sql = "SELECT * FROM matsushima_slip_jv 
			INNER JOIN matsushima_jv_rel ON matsushima_jv_rel.jv_slip_id = matsushima_slip_jv.s_id
	
			WHERE s_id = '{$s_id}'";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_object($query)) {
		
		$sql_s = "INSERT INTO matsushima_slip_hat (s_genba_id, s_seko_kubun_id, s_seko_id, s_date, s_st_date, s_end_date, s_tax, s_is_jv, s_jv_rel_id)
					SELECT
					s_genba_id, s_seko_kubun_id, {$row->jv_seko_id} as s_seko_id, '{$today}' as s_date, s_st_date, s_end_date, s_tax, '1' as s_is_jv, s_id as s_jv_rel_id
					FROM matsushima_slip_jv
					WHERE
					s_id = '{$s_id}'
		";
		$query_s = mysql_query($sql_s);
		
	}	


}

else if($flag == "DELETE_FILE") {
	$file = $parm[1];
	
	if(file_exists($file)) {
		unlink($file);
	}
}

else if($flag == "SHOW_FILES") {
	$id = $parm[1];
	echo '<h2>書類管理 <span id="tenkai"></span></h2>';
	
	$dirName = "./uploads/".$id;
	if(is_dir($dirName)) {
		$fileArray = scandir($dirName);
		
		echo "<table class='file-up-table'><tr>";

		$cnt = 0;
		
		for($i=0;$i < count($fileArray);$i++) {
			if($fileArray[$i] != "." && $fileArray[$i] != "..") {
				echo "<td id='pic{$i}' style='width:130px;padding:5px;text-align:center'>";
				if(preg_match('/jpg$/',$fileArray[$i]) || preg_match('/JPG$/',$fileArray[$i]) || preg_match('/jpeg$/',$fileArray[$i]) || preg_match('/JPEG$/',$fileArray[$i]) || preg_match('/gif$/',$fileArray[$i]) || preg_match('/GIF$/',$fileArray[$i]) || preg_match('/png$/',$fileArray[$i]) || preg_match('/PNG$/',$fileArray[$i]))
					echo "<a href='{$dirName}/{$fileArray[$i]}' target='_blank'><img src='{$dirName}/{$fileArray[$i]}' width='100' /><br />" . $fileArray[$i] . "</a>";
				else
					echo "<a href='{$dirName}/{$fileArray[$i]}' target='_blank'>" . $fileArray[$i] . "</a>";
				
				//削除	
				echo " <img src='uploadify-cancel.png' style='cursor:pointer;vertical-align:middle' onClick='delFileUpload(\"pic{$i}\",\"{$dirName}/{$fileArray[$i]}\")' />";
				echo "</td>";

				$cnt++;

				if($cnt % 5 == 0 && $cnt != 0) {
					echo "</tr><tr>";
				}
			}
		}
	
		echo "</tr></table>";
		
		if($cnt) {
			echo "<input type='hidden' id='shorui_ari' />";
		
			//印刷関連
			echo "<div style='padding-top:20px;font-size:12px;'>画像印刷：<a href='print_image.php?id={$id}&col=1' target='_blank'>1列</a>&nbsp;<a href='print_image.php?id={$id}&col=2' target='_blank'>2列</a>&nbsp;<a href='print_image.php?id={$id}&col=3' target='_blank'>3列</a>&nbsp;<a href='print_image.php?id={$id}&col=4' target='_blank'>4列</a></div>";
		
		
		
		}
	}
}

else if($flag = "GET_NEW_JYUTYU") {
	$id = $parm[1];
	
	if($id) {
		$sql = "SELECT s_moto_invoice FROM matsushima_slip WHERE s_genba_id = '{$id}' AND s_moto_invoice > 0 ORDER BY s_id DESC LIMIT 1";
		$query = mysql_query($sql);
		$num = mysql_num_rows($query);
		if($num) {
			$row = mysql_fetch_object($query);
			
			if($row->s_moto_invoice > 0)
				echo $row->s_moto_invoice;
			else
				echo 0;
		}
		else {
			echo 0;
		}
	}
	else {
		echo 0;
	}
}

/**********************************************************************
 *
 * 	セレクトボックス生成
 *
 **********************************************************************/
function make_select_genba($_val, $_cls, $_table, $_tabindex = "", $_corder = "", $_num = 1) {

	if($_corder != "")
		$sql = "SELECT * FROM {$_table} ORDER BY {$_corder}";
	else
		$sql = "SELECT * FROM {$_table}";

	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	//debug
	/*echo "<script>write_debug(\"{$sql}\");</script>";*/

	echo "<span class='{$_cls}_span'>";
	echo "<select class='{$_cls}'  tabindex='{$_tabindex}'>";

	echo "<option value='0' >-----</option>";

	while ($row = mysql_fetch_array($query)) {
//----------------------個別領域 ここから----------------------------
			if($row[9])
				$branch = "({$row[9]})";
			else
				$branch = "";

			if($row[10])
				$kana = "[{$row[10]}]";
			else
				$kana = "";

		if($row[0] == $_val)
			echo "<option value='{$row[0]}' selected='selected'>{$kana}{$row[2]} {$branch}</option>";
		else
			echo "<option value='{$row[0]}'>{$kana}{$row[2]} {$branch}</option>";
			
			
	}
	echo "</select>";
	echo "</span>";
	echo "<span class='{$_cls}_err error-field'></span>";
}



//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------


?>

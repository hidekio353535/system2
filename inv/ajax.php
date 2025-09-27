<?php
session_start();
require_once("../php/db_connect.php");
require_once("../php/common.php");

//----------------------個別領域 ここから----------------------------
$VIEW_TABLE = "matsushima_inv";
$VIEW_ID_FIELD = "i_id";

$MAIN_TABLE = "matsushima_inv";
$MAIN_ID_FIELD = "i_id";

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
	if($parm[10] != "" && $parm[10] != 0)
		$MAXLIMIT = $parm[10];

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

	$search_moto_id = $parm[11];
	if($search_moto_id != "")
		$JYOKEN .= " AND i_moto_id = {$search_moto_id} ";

	$search_g_id = $parm[12];
	if($search_g_id != "")
		$JYOKEN .= " AND exists (SELECT * FROM matsushima_slip WHERE s_inv_id = i_id AND s_genba_id = {$search_g_id} )";
			
//----------------------個別領域 ここから----------------------------
	$search_kw1 = $parm[4];
	if($search_kw1 != "")
		$JYOKEN .= " AND (i_moto_id like '%{$search_kw1}%' || i_year like '%{$search_kw1}%' || EXISTS (SELECT * FROM matsushima_moto WHERE moto_id = i_moto_id AND moto like '%{$search_kw1}%')) ";

	$search_kw2 = $parm[5];
	if($search_kw2 != "")
		$JYOKEN .= " AND (i_moto_id like '%{$search_kw2}%' || i_year like '%{$search_kw2}%' || EXISTS (SELECT * FROM matsushima_moto WHERE moto_id = i_moto_id AND moto like '%{$search_kw2}%')) ";

	$shime_year = $parm[6];
	if($shime_year != "")
		$JYOKEN .= " AND (i_year like '{$shime_year}') ";
	
	$shime_month = $parm[7];
	if($shime_month != "")
		$JYOKEN .= " AND (i_month like '{$shime_month}') ";

	$search_sel_moto = $parm[8];
	if($search_sel_moto != "" && $search_sel_moto != 0)
		$JYOKEN .= " AND (i_moto_id = '{$search_sel_moto}') ";

	$search_sel_branch = $parm[9];	
	if($search_sel_branch)
		$JYOKEN .= " AND EXISTS (SELECT * FROM matsushima_moto WHERE moto_id = i_moto_id AND moto like '{$search_sel_branch}') ";	
	
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
		if($MAXLIMIT >= 1000 && $num > 1000) {
			//throw new returnZeroRowException("該当するデータがありません");

			echo "該当するデータが1000件を超えています。絞り込んで検索を実行して下さい。";
			exit();

		}
		$lastpage = ceil($num / $MAXLIMIT);
	
		// for show page
		$pagelimit = ($page - 1) * $MAXLIMIT;
	
		//表示用SQL
		$sql = "SELECT *,
				(SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = {$VIEW_TABLE}.i_id) as invoice,
				((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = {$VIEW_TABLE}.i_id) + i_chosei) as invoiceall
				
				FROM {$VIEW_TABLE} WHERE 1 {$JYOKEN} {$ORDER} LIMIT {$pagelimit}, {$MAXLIMIT}";
//echo $sql;
		@$query = mysql_query($sql);
		if(!$query) {
			throw new dbException("SQLが不完全");
		}
		//20170210 セッションセット
		if($JYOKEN) {
			$_SESSION['csv_sql'] = "SELECT *,
				(SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = {$VIEW_TABLE}.i_id) as invoice,
				((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = {$VIEW_TABLE}.i_id) + i_chosei) as invoiceall
				
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

	$sql_sum = "SELECT *,
			SUM(i_receipt_kingaku) as i_receipt_kingaku,
			SUM(i_commission) as i_commission,
			SUM(
			(SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = {$VIEW_TABLE}.i_id)
			)
			as invoice,
			SUM(
			((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = {$VIEW_TABLE}.i_id) + i_chosei)
			)
			as invoiceall

			FROM {$VIEW_TABLE} WHERE 1 {$JYOKEN}";
	$query_sum = mysql_query($sql_sum);
	$row_sum = @mysql_fetch_object($query_sum);
	
	//集計
	$ttl = array(num=>$num,inv=>$row_sum->invoiceall,rec=>$row_sum->i_receipt_kingaku,com=>$row_sum->i_commission,sagaku=>$row_sum->invoiceall - ($row_sum->i_receipt_kingaku + $row_sum->i_commission));
	
	echo "<input type='hidden' id='ttl-num' value='{$ttl['num']}' />";
	echo "<input type='hidden' id='ttl-inv' value='{$ttl['inv']}' />";
	echo "<input type='hidden' id='ttl-rec' value='{$ttl['rec']}' />";
	echo "<input type='hidden' id='ttl-com' value='{$ttl['com']}' />";
	echo "<input type='hidden' id='ttl-sagaku' value='{$ttl['sagaku']}' />";
	
	echo "<table class='list-table'>";
	echo "<tr>";
//----------------------個別領域 ここから----------------------------
	showTh("", "アクション");
	showTh("i_id", "請求ID");
	showTh("i_moto_id", "元請ID");
	showTh("i_moto_id", "請求先名");
	showTh("i_year", "請求年");
	showTh("i_month", "請求月");
	showTh("", "請求額");
	showTh("i_chosei_name", "調整項目名");
	showTh("i_chosei", "調整金額");
	showTh("", "請求総額");
	showTh("i_inv_date", "請求日");
	showTh("i_send_date", "請求書送付日");
	showTh("i_receipt_yotei_date", "入金予定日");
	showTh("i_receipt_date", "入金日");
	showTh("i_receipt_kingaku", "入金額");
	showTh("i_commission","手数等");
	showTh("","差額");
	showTh("i_biko","メモ");
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
				 <img src="../img/b_inv.png" title="一覧請求書印刷" class="opa smlb ui-icon-print">
				 <img src="../img/b_invmei.png" title="明細請求書印刷" class="opa smlb ui-icon-print-m">
				 <img src="../img/b_drop.png" title="締めを解除" class="opa smlb ui-icon-trash">
				';
			echo "</td>";

			echo "<td class='nw tar'>";
				echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->$VIEW_ID_FIELD."' />";
				show_clm($row->$VIEW_ID_FIELD, $VIEW_ID_FIELD);
			echo "</td>";

			echo "<td class='nw tar'>";
				show_clm($row->i_moto_id, "i_moto_id");
			echo "</td>";

			$clm = "i_moto_id";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_select_clm($row->$clm, "matsushima_moto", 2);
				echo "</span>";
			echo "</td>";

			$clm = "i_year";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 4);
				echo "</span>";
			echo "</td>";

			$clm = "i_month";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 4);
				echo "</span>";
			echo "</td>";

			$clm = "invoice";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm(number_format($row->$clm), $clm, $F_NUM, 4);
				echo "</span>";
			echo "</td>";

			$clm = "i_chosei_name";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "i_chosei";
			echo "<td class='nw tar'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "8", 0, "text", "tar");
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_NUM, 8);
				echo "</span>";
			echo "</td>";

			$clm = "invoiceall";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm(number_format($row->$clm), $clm, $F_NUM, 4);
				echo "</span>";
			echo "</td>";

			$clm = "i_inv_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "i_send_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "i_receipt_yotei_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "i_receipt_date";
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 10);
				echo "</span>";
			echo "</td>";
						
			$clm = "i_receipt_kingaku";			
			echo "<td class='nw tar'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tar");
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_NUM, 10);
				echo "</span>";
			echo "</td>";

			$clm = "i_commission";			
			echo "<td class='nw tar'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0, "text", "tar");
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_NUM, 10);
				echo "</span>";
			echo "</td>";

			//差額 = 請求総額 - (入金額 + 手数料等)
			if($row->i_receipt_kingaku) {
				$sagaku = $row->invoiceall - ($row->i_receipt_kingaku + $row->i_commission);
				$sagaku_fmt = number_format($sagaku);
			}
			else {
				$sagaku = 0;
				$sagaku_fmt = "-";
			}
			
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					echo $sagaku_fmt;
				echo "</span>";
			echo "</td>";
			
			$clm = "i_biko";			
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "20", 0, "text", "tal");
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
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
				$msg = "{$shime_year} {$shime_month}の請求書に条件で";
			else
				$msg = "";

			echo "<td colspan='17' style='text-align:center;color:red'>{$msg}該当するデータがありません</td>";
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
		$sql = "SELECT * FROM matsushima_slip
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
				INNER JOIN matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id
				WHERE s_inv_id = {$id} ORDER BY s_st_date, s_id";

		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	
	} else {
		//ダミー
		$sql = "SELECT 1";
		$query = @mysql_query($sql);
		$num = @mysql_num_rows($query);
	}

	//締めていた場合編集不可
	echo "<h2>請求書一覧</h2>";
	
	echo "<table class='list-table'>";
	echo "<tr>";
//----------------------個別領域 ここから----------------------------
	showTh("", "アクション");
	showTh("s_genba_id", "現場ID");
	showTh("s_id", "受注ID");
	showTh("s_st_date", "工事日");
	showTh("s_shime_date", "締日");
	showTh("moto", "元請");
	showTh("branch", "支店");
	showTh("g_genba", "現場名");
	showTh("g_genba_address", "現場住所");
	showTh("s_seko_kubun_id", "区分");
	showTh("s_invoice", "請求額");
	showTh("s_biko", "備考");

//----------------------個別領域 ここまで----------------------------
	echo "</tr>";

	//表示するレコードがある場合
	if($num) {
		while ($row = @mysql_fetch_object($query)) {
		
			echo "<tr>";
//----------------------個別領域 ここから----------------------------
			echo "<td class='nw' style='vertical-align:middle'>";
				echo '<input type="checkbox" class="chk" value="'.$row->s_id.'">
				 <img src="../img/b_inv.png" title="請求書印刷" class="opa smlb ui-icon-print">
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

			$clm = "s_shime_date";
			echo "<td class='nw tal'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 30);
				echo "</span>";

			$clm = "moto_nik";
			echo "<td class='nw tal'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 60);
				echo "</span>";

				$clm = "branch";
				echo "<td class='nw tal'>";
					echo "<span class='list_mode'>";
						show_clm($row->$clm, $clm, $F_WIDTH, 40);
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
					show_select_clm($row->$clm, "matsushima_kouji_syu", 2);
				echo "</span>";
			echo "</td>";

			$clm = "s_invoice";
			echo "<td class='nw tar'>";
				echo "<span class='list_mode'>";
					show_clm(number_format($row->$clm), $clm, $F_WIDTH, 30);
				echo "</span>";
			echo "</td>";

			$clm = "s_biko";
			echo "<td class='nw tar'>";
				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
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

	//元請け
	$sql = "SELECT *,
	CASE
		WHEN kana = '' OR kana is null THEN 'ンンンン'
		WHEN CHAR_LENGTH(kana) = 1 THEN concat(kana,'ンンン')
		ELSE kana
	END as kana2,
	LEFT(kana,1) as kana3
	 FROM matsushima_moto WHERE 1 ORDER BY kana2, moto, moto_id";
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
		while ($row = @mysql_fetch_object($query)) {
			if($row->branch)
				$branch = "({$row->branch})";
			else
				$branch = "";

			if($row->kana3)
				$kana = "[{$row->kana3}]";
			else
				$kana = "";
			
			echo "<option value='{$row->moto_id}'>{$kana}{$row->moto_nik} {$branch}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
	}
	//支店 20161206
	$sql = "SELECT *,
	CASE
	WHEN kana = '' OR kana is null THEN 'ンンンン'
	WHEN CHAR_LENGTH(kana) = 1 THEN concat(kana,'ンンン')
	ELSE kana
END as kana2,
LEFT(kana,1) as kana3
 FROM matsushima_moto WHERE branch != '' AND branch is not null 
 GROUP BY moto 
 ORDER BY kana2, moto, moto_id";
	$query = @mysql_query($sql);
	$num = @mysql_num_rows($query);
	if(!$query) {
		echo "fail";
		exit();
	}
	if($num) {
		echo "<span style='font-weight:bold'>支店グループ</span>&nbsp;&nbsp;";
		echo "<select id='search_sel_branch'>";
		echo "<option value='0'>選択して下さい</option>";
		while ($row = @mysql_fetch_object($query)) {
			if($row->kana3)
				$kana = "[{$row->kana3}]";
			else
				$kana = "";
			
			echo "<option value='{$row->moto}'>{$kana}{$row->moto}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
	}
	
}

?>

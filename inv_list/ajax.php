<?php
session_start();

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

	if($parm[14] != "" && $parm[14] != 0)
		$MAXLIMIT = $parm[14];

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
							sy_name like '%{$search_kw1}%'			||
							EXISTS (SELECT * FROM matsushima_moto WHERE moto_id = g_moto_id AND moto like '%{$search_kw1}%')
					) ";
	$search_kw2 = $parm[5];
	if($search_kw2 != "")
		$JYOKEN .= " AND (
							g_genba like '%{$search_kw2}%' 			|| 
							g_genba_address like '%{$search_kw2}%'	||
							g_biko like '%{$search_kw2}%'			||
							sy_name like '%{$search_kw2}%'			||
							EXISTS (SELECT * FROM matsushima_moto WHERE moto_id = g_moto_id AND moto like '%{$search_kw2}%')
					) ";


	$search_sel_moto = $parm[8];
	if($search_sel_moto != "" && $search_sel_moto != 0)
		$JYOKEN .= " AND (g_moto_id = '{$search_sel_moto}') ";

	$search_st_date = $parm[9];
	$search_end_date = $parm[10];
	$date_sel = $parm[11];
	
	if($search_st_date != '' && $search_end_date != '')
		$JYOKEN .= " AND {$date_sel} >= '{$search_st_date}' AND {$date_sel} <= '{$search_end_date}' ";	
	else if($search_st_date != '' && $search_end_date == '')
		$JYOKEN .= " AND {$date_sel} >= '{$search_st_date}' ";
	else if($search_st_date == '' && $search_end_date != '')
		$JYOKEN .= " AND {$date_sel} <= '{$search_end_date}' ";	

	$search_sel_tantou = $parm[12];
	if($search_sel_tantou != "" && $search_sel_tantou != 0)
		$JYOKEN .= " AND (g_tantou_id = '{$search_sel_tantou}') ";

	$search_sel_kubun = $parm[13];
	if($search_sel_tantou != "" && $search_sel_kubun != 0)
		$JYOKEN .= " AND (s_seko_kubun_id = '{$search_sel_kubun}') ";

	$search_sel_branch = $parm[15];	
	if($search_sel_branch)
		$JYOKEN .= " AND EXISTS (SELECT * FROM matsushima_moto WHERE moto_id = g_moto_id AND moto like '{$search_sel_branch}') ";	

	$search_g_id = $parm[16];
	if($search_g_id != "")
		$JYOKEN .= " AND s_genba_id = {$search_g_id} ";
			
//----------------------個別領域 ここまで----------------------------
	//20170210 セッションリセット
	unset($_SESSION['csv_sql']);
		
	//ページ管理用 最終ページ計算
	try {
		$sql = "SELECT * FROM {$VIEW_TABLE} 
		LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = {$VIEW_TABLE}.s_genba_id
		LEFT OUTER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = {$VIEW_TABLE}.s_seko_kubun_id
		LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
		
		WHERE 1 {$JYOKEN} {$ORDER}";
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

		if($JYOKEN != "") {
			//合計計算
			//表示用SQL
			$sql = "SELECT *
					
					FROM {$VIEW_TABLE} 
					LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = {$VIEW_TABLE}.s_genba_id
					LEFT OUTER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = {$VIEW_TABLE}.s_seko_kubun_id
					LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
					
					WHERE 1 {$JYOKEN} {$ORDER}";
			@$query = mysql_query($sql);
			if(!$query) {
				throw new dbException("SQLが不完全");
			}
			@$num = mysql_num_rows($query);
			if(!$num) {
				throw new returnZeroRowException("該当するデータがありません");
			}
	
			$ttl_est = 0;
			$ttl_inv = 0;
			$ttl_hat = 0;
			$ttl_hatzan = 0;
	
			while ($row = @mysql_fetch_object($query)) {
				$ttl_inv += $row->s_invoice;
				$ttl_inv3p += floor($row->s_invoice * 0.03);
				//$ttl_inv += $row->inv;
				//$ttl_hat += $row->hat;
				//$ttl_hatzan += $row->hatzan;
			}
			//echo "<input type='hidden' value='￥".number_format($ttl_est)."' id='ttl_est' />";
			echo "<input type='hidden' value='￥".number_format($ttl_inv)."' id='ttl_inv' />";
			echo "<input type='hidden' value='￥".number_format($ttl_inv3p)."' id='ttl_inv3p' />";
			//echo "<input type='hidden' value='￥".number_format($ttl_hat)."' id='ttl_hat' />";
			//echo "<input type='hidden' value='￥".number_format($ttl_hatzan)."' id='ttl_hatzan' />";
			echo "<input type='hidden' value='{$num}' id='allrecord' />";
		}

	
		//表示用SQL
		$sql = "SELECT *
				
				FROM {$VIEW_TABLE} 
				LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = {$VIEW_TABLE}.s_genba_id
				LEFT OUTER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = {$VIEW_TABLE}.s_seko_kubun_id
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
				
				WHERE 1 {$JYOKEN} {$ORDER} LIMIT {$pagelimit}, {$MAXLIMIT}";
		@$query = mysql_query($sql);
		if(!$query) {
			throw new dbException("SQLが不完全");
		}
		//20170210 セッションセット
		if($JYOKEN) {
			$_SESSION['csv_sql'] = 
				"SELECT *
				
				FROM {$VIEW_TABLE} 
				LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = {$VIEW_TABLE}.s_genba_id
				LEFT OUTER JOIN matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id
				LEFT OUTER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = {$VIEW_TABLE}.s_seko_kubun_id
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
				
				WHERE 1 {$JYOKEN} {$ORDER}";
				
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
	showTh("g_id", "現場ID");
	showTh("s_id", "受注ID");
	showTh("sy_name_nik", "区分");
	showTh("g_genba", "現場名");
	showTh("g_genba_address", "現場住所");
	showTh("g_moto_id", "元請");
	showTh("g_tantou_id", "担当");
	showTh("s_date", "受注日", false); //非表示 display:none
	showTh("s_st_date", "開始日");
	showTh("s_end_date", "終了日");
	showTh("s_shime_date", "請求締日");
	showTh("s_moto_invoice", "元請受注額");
	showTh("s_invoice", "請求額");
	showTh("", "営業3%");
	showTh("s_biko", "備考");
	showTh("", "請求");
	
//----------------------個別領域 ここまで----------------------------
	echo "</tr>";

	//表示するレコードがある場合
	if($num) {
		while ($row = @mysql_fetch_object($query)) {
		
			echo "<tr>";
//----------------------個別領域 ここから----------------------------
			echo "<td class='nw' style='vertical-align:middle'>";
				echo '<input type="checkbox" class="chk" value="'.$row->$VIEW_ID_FIELD.'">
				 <a href="../main/?'.$row->g_id.'" target="_blank"><img src="../img/b_edit.png" title="編集" class="opa smlb ui-icon-pencil"></a>
				 <img src="../img/b_inv.png" title="請求書表示" class="opa smlb ui-icon-print">
				';
				
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

			echo "<td class='nw tac'>";
				echo "<input type='hidden' class='g_id' value='".$row->g_id."' />";
				show_clm($row->g_id, "g_id");
			echo "</td>";

			echo "<td class='nw tac'>";
				echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->$VIEW_ID_FIELD."' />";
				show_clm($row->$VIEW_ID_FIELD, $VIEW_ID_FIELD);
			echo "</td>";

			$clm = "sy_name_nik";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_NORMAL, 6);
				echo "</span>";
			echo "</td>";

			$clm = "g_genba";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "g_genba_address";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "g_moto_id";
			$sel_table = "matsushima_moto";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_select_clm($row->$clm, $sel_table, 2);
					
				echo "</span>";
			echo "</td>";

			$clm = "g_tantou_id";
			$sel_table = "matsushima_tantou";
			echo "<td class='nw tal'>";
				echo "<span class=''>";
					show_select_clm($row->$clm, $sel_table, 1);
					
				echo "</span>";
			echo "</td>";

			$clm = "s_date";
			echo "<td class='nw tac' style='display:none'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "s_st_date";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "s_end_date";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "s_shime_date";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_DATE_SH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "s_moto_invoice";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_NUM, 20);
				echo "</span>";
			echo "</td>";

			$clm = "s_invoice";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_NUM, 20);
				echo "</span>";
			echo "</td>";

			$clm = "s_invoice";
			echo "<td class='nw tar'>";
				echo "<span class=''>";
					show_clm(floor($row->$clm*0.03), $clm, $F_NUM, 20);
				echo "</span>";
			echo "</td>";


			$clm = "s_biko";
			echo "<td class='nw tac'>";
				echo "<span class=''>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
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
			echo "<td colspan='17' style='text-align:center;color:red'>該当するデータがありません</td>";
		echo "</tr>";
	}
	echo "</table>";
	//echo "<input type='hidden' value='￥".number_format($ttl_est)."' id='ttl_est' />";
	echo "<input type='hidden' value='￥".number_format($ttl_inv)."' id='ttl_inv' />";
	echo "<input type='hidden' value='￥".number_format($ttl_inv3p)."' id='ttl_inv3p' />";
	//echo "<input type='hidden' value='￥".number_format($ttl_hat)."' id='ttl_hat' />";
	//echo "<input type='hidden' value='￥".number_format($ttl_hatzan)."' id='ttl_hatzan' />";
	echo "<input type='hidden' value='{$num}' id='allrecord' />";
	
}

else if($flag == "SET_JYOKEN") {

	//区分
	$sql = "SELECT * FROM matsushima_kouji_syu WHERE 1";
	$query = @mysql_query($sql);
	$num = @mysql_num_rows($query);
	if(!$query) {
		echo "fail";
		exit();
	}
	if($num) {
		echo "<span style='font-weight:bold'>区分</span>&nbsp;&nbsp;";
		echo "<select id='search_sel_kubun'>";
		echo "<option value='0'>区分を選択して下さい</option>";
		while ($row = @mysql_fetch_object($query)) {
			echo "<option value='{$row->sy_id}'>{$row->sy_name_nik}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
	}

	//請求先
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
		while ($row = @mysql_fetch_object($query)) {
			echo "<option value='{$row->t_id}'>{$row->t_tantou}</option>";
		}
		echo "</select>&nbsp;&nbsp;";
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
		
		$sql = "SELECT * FROM matsushima_slip WHERE s_id = '{$id}'";
		$query = mysql_query($sql);
		$row = mysql_fetch_object($query);
		
		if($row->s_is_print)
			$sql = "UPDATE matsushima_slip SET s_is_print = 0 WHERE s_id = '{$id}'";
		else
			$sql = "UPDATE matsushima_slip SET s_is_print = 1 WHERE s_id = '{$id}'";

		$query = mysql_query($sql);
	}
	else
		return;

}

else if($flag == "ALREADY_PRINT2") {

	$slipno = $parm[1];
	$id = $parm[2];

	if($slipno == 0)
		return;
	else if($slipno == 1) {
		$sql = "UPDATE matsushima_slip SET s_is_print = 1 WHERE s_id in ({$id})";
		$query = mysql_query($sql);
	}
	else if($slipno == 2) {
		
		$sql = "UPDATE matsushima_slip SET s_is_print = 1 WHERE s_id in ({$id})";
		$query = mysql_query($sql);
	}
	else
		return;

}

else if($flag == "ALREADY_PRINT3") {

	$slipno = $parm[1];
	$id = $parm[2];

	if($slipno == 0)
		return;
	else if($slipno == 1) {
		$sql = "UPDATE matsushima_slip SET s_is_print = 0 WHERE s_id in ({$id})";
		$query = mysql_query($sql);
	}
	else if($slipno == 2) {
	
		$sql = "UPDATE matsushima_slip SET s_is_print = 0 WHERE s_id in ({$id})";
		$query = mysql_query($sql);
	}
	else
		return;

}

?>

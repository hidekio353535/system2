<?php
require_once("../php/db_connect.php");
require_once("../php/common.php");

//----------------------個別領域 ここから----------------------------
$VIEW_TABLE = "matsushima_moto";
$VIEW_ID_FIELD = "moto_id";

$MAIN_TABLE = "matsushima_moto";
$MAIN_ID_FIELD = "moto_id";

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
	$search_kw1_tel = preg_replace('/\-/','',$search_kw1);
	if($search_kw1 != "")
		$JYOKEN .= " AND (
			moto like '%{$search_kw1}%' || 
			moto_nik like '%{$search_kw1}%' ||
			atena like '%{$search_kw1}%' ||
			m_tantou_id in (SELECT t_id FROM matsushima_tantou WHERE t_id = m_tantou_id AND t_tantou like '%{$search_kw1}%') ||
			branch like '%{$search_kw1}%' ||
			kana like '%{$search_kw1}%' ||
			replace(m_postal,'-','') like '%{$search_kw1_tel}%' ||
			m_address like '%{$search_kw1}%' ||
			replace(m_tel,'-','') like '%{$search_kw1_tel}%' ||
			replace(m_fax,'-','') like '%{$search_kw1_tel}%' ||
			m_tantou_a like '%{$search_kw1}%' ||
			replace(m_tantou_tel_a,'-','') like '%{$search_kw1_tel}%' ||
			m_moto_email_a like '%{$search_kw1}%' ||
			m_tantou_b like '%{$search_kw1}%' ||
			replace(m_tantou_tel_b,'-','') like '%{$search_kw1_tel}%' ||
			m_moto_email_b like '%{$search_kw1}%' ||
			m_tantou_c like '%{$search_kw1}%' ||
			replace(m_tantou_tel_c,'-','') like '%{$search_kw1_tel}%' ||
			m_moto_email_c like '%{$search_kw1}%' ||
			m_biko like '%{$search_kw1}%' ||
			m_tantou_sub_id in (SELECT t_id FROM matsushima_tantou WHERE t_id = m_tantou_sub_id AND t_tantou like '%{$search_kw1}%') ||
			m_shiyo like '%{$search_kw1}%'
		)
		";
	$search_kw2 = $parm[5];
	$search_kw2_tel = preg_replace('/\-/','',$search_kw2);
	if($search_kw2 != "")
		$JYOKEN .= " AND (
			moto like '%{$search_kw2}%' || 
			moto_nik like '%{$search_kw2}%' ||
			atena like '%{$search_kw2}%' ||
			m_tantou_id in (SELECT t_id FROM matsushima_tantou WHERE t_id = m_tantou_id AND t_tantou like '%{$search_kw2}%') ||
			branch like '%{$search_kw2}%' ||
			kana like '%{$search_kw2}%' ||
			replace(m_postal,'-','') like '%{$search_kw2_tel}%' ||
			m_address like '%{$search_kw2}%' ||
			replace(m_tel,'-','') like '%{$search_kw2_tel}%' ||
			replace(m_fax,'-','') like '%{$search_kw2_tel}%' ||
			m_tantou_a like '%{$search_kw2}%' ||
			replace(m_tantou_tel_a,'-','') like '%{$search_kw2_tel}%' ||
			m_moto_email_a like '%{$search_kw2}%' ||
			m_tantou_b like '%{$search_kw2}%' ||
			replace(m_tantou_tel_b,'-','') like '%{$search_kw2_tel}%' ||
			m_moto_email_b like '%{$search_kw2}%' ||
			m_tantou_c like '%{$search_kw2}%' ||
			replace(m_tantou_tel_c,'-','') like '%{$search_kw2_tel}%' ||
			m_moto_email_c like '%{$search_kw2}%' ||
			m_biko like '%{$search_kw2}%' ||
			m_tantou_sub_id in (SELECT t_id FROM matsushima_tantou WHERE t_id = m_tantou_sub_id AND t_tantou like '%{$search_kw2}%') ||
			m_shiyo like '%{$search_kw2}%'
		)
		";

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
	showTh("moto_id", "ID");
	showTh("moto", "元請会社名");
	showTh("moto_nik", "元請短縮会社名");
	showTh("branch", "支店名");
	showTh("atena", "請求書宛名");
	showTh("m_tantou_id", "主担当");
	showTh("m_pat", "締めパターン");
	showTh("m_shime_group", "締め<br />グループ");
	showTh("m_shiharai_m", "支払月");
	showTh("m_shiharai_day", "支払日");
	showTh("div_rule", "架分割<br />比率(%)");
	showTh("m_biko", "備考");
	showTh("m_address", "住所");
	showTh("m_invoice_no", "インボイス<br>登録番号");
	showTh("m_furikomi", "振込手数料<br>表示");
//	showTh("is_stax", "消費税計算を行う");
/*
showTh("m_postal", "〒");
showTh("m_address", "住所");
showTh("m_tel", "電話番号");
showTh("m_fax", "FAX");
showTh("m_tantou_a", "担当名A");
showTh("m_tantou_tel_a", "担当携帯A");
showTh("m_tantou_b", "担当名B");
showTh("m_tantou_tel_b", "担当携帯B");
showTh("m_tantou_c", "担当名C");
showTh("m_tantou_tel_c", "担当携帯C");
*/
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

			$clm = "moto";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "20", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "moto_nik";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "20", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "branch";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "atena";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "14", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 14);
				echo "</span>";
			echo "</td>";

			$clm = "m_tantou_id";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select_tantou($row->$clm, $clm, "matsushima_tantou", 0, "t_id", 1,"is_show_tantou=1");
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, "matsushima_tantou", 1);
				echo "</span>";

			echo "</td>";


			$clm = "m_pat";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, "matsushima_m_pat", 0, "p_id", 1);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, "matsushima_m_pat", 1);
				echo "</span>";
			echo "</td>";


			$clm = "m_shime_group";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, "matsushima_shime", 0, "corder", 2);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, "matsushima_shime", 2);
				echo "</span>";
			echo "</td>";

			$clm = "m_shiharai_m";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, "matsushima_shiharai_month", 0, "id", 2);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, "matsushima_shiharai_month", 2);
				echo "</span>";
			echo "</td>";

			$clm = "m_shiharai_day";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_select($row->$clm, $clm, "matsushima_shiharai_day", 0, "id", 2);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_select_clm($row->$clm, "matsushima_shiharai_day", 2);
				echo "</span>";
			echo "</td>";

			$clm = "div_rule";
			if($row->$clm == 0)
				$rval = "";
			else
				$rval = $row->$clm;
				
			echo "<td class='nw tac'>";
				echo "<span class='edit_mode'>";
					make_textbox($rval, $clm, "4", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($rval, $clm, $F_WIDTH, 4);
				echo "</span>";
			echo "</td>";

			$clm = "m_biko";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "20", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 20);
				echo "</span>";
			echo "</td>";

			$clm = "m_address";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "30", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";
			echo "</td>";

			$clm = "m_invoice_no";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "30", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 30);
				echo "</span>";
			echo "</td>";
            
			$clm = "m_furikomi";
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

/*
			$clm = "m_postal";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_address";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_tel";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_fax";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_tantou_a";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_tantou_tel_a";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_tantou_b";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_tantou_tel_b";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_tantou_c";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";

			$clm = "m_tantou_tel_c";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "10", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 10);
				echo "</span>";
			echo "</td>";
*/



/*
			$clm = "is_stax";
			echo "<td class='nw tal'>";
				echo "<span class='edit_mode'>";
					make_textbox($row->$clm, $clm, "2", 0);
				echo "</span>";

				echo "<span class='list_mode'>";
					show_clm($row->$clm, $clm, $F_WIDTH, 2);
				echo "</span>";
			echo "</td>";
*/

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
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "atena";
		echo "<tr>";
		echo "<th>請求書宛名</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tantou_id";
		$clmsub = "m_tantou_sub_id";

		echo "<tr>";
		echo "<th>主担当</th>";
		echo "<td class='nw'>";
		make_select_tantou($row->$clm, $clm, "matsushima_tantou", 0, "t_id", 1,"is_show_tantou=1");
		echo " 副担当 ";
		make_select_tantou($row->$clmsub, $clmsub, "matsushima_tantou", 0, "t_id", 1,"is_show_tantou=1");
		echo "</td>";
		echo "</tr>";

		$clm = "kana";
		echo "<tr>";
		echo "<th>カナ</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "8", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_pat";
		echo "<tr>";
		echo "<th>締めパターン</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, "matsushima_m_pat", 0, "p_id", 1);
		echo "</td>";
		echo "</tr>";

		$clm = "m_shime_group";
		echo "<tr>";
		echo "<th>締めグループ</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, "matsushima_shime", 0, "corder", 2);
		echo "</td>";
		echo "</tr>";

		$clm = "m_shiharai_m";
		echo "<tr>";
		echo "<th>支払月</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, "matsushima_shiharai_month", 0, "id", 2);
		echo "</td>";
		echo "</tr>";

		$clm = "m_shiharai_day";
		echo "<tr>";
		echo "<th>支払日</th>";
		echo "<td class='nw'>";
		make_select($row->$clm, $clm, "matsushima_shiharai_day", 0, "id", 2);
		echo "</td>";
		echo "</tr>";

		$clm = "div_rule";
		if($row->$clm == 0)
			$rval = "";
		else
			$rval = $row->$clm;

		echo "<tr>";
		echo "<th>架分割比率(%)</th>";
		echo "<td class='nw'>";
		make_textbox($rval, $clm, "4", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_invoice_no";
		echo "<tr>";
		echo "<th>インボイス登録番号</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "40", 0);
		echo "</td>";
		echo "</tr>";
        
		$clm = "m_furikomi";
		echo "<tr>";
		echo "<th>振込手数料表示</th>";
		echo "<td class='nw'>";
		make_normal_checkbox($row->$clm, $clm, "");
		echo "</td>";
		echo "</tr>";
        

		$clm = "m_postal";
		echo "<tr>";
		echo "<th>郵便番号</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "10", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_address";
		echo "<tr>";
		echo "<th>住所</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "40", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tel";
		echo "<tr>";
		echo "<th>電話番号</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_fax";
		echo "<tr>";
		echo "<th>FAX</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tantou_a";
		echo "<tr>";
		echo "<th>A現場担当者</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tantou_tel_a";
		echo "<tr>";
		echo "<th>A担当者携帯</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_moto_email_a";
		echo "<tr>";
		echo "<th>A担当者E-mail</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "40", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tantou_b";
		echo "<tr>";
		echo "<th>B現場担当者</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tantou_tel_b";
		echo "<tr>";
		echo "<th>B担当者携帯</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_moto_email_b";
		echo "<tr>";
		echo "<th>B担当者E-mail</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "40", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tantou_c";
		echo "<tr>";
		echo "<th>C現場担当者</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_tantou_tel_c";
		echo "<tr>";
		echo "<th>C担当者携帯</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "20", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_moto_email_c";
		echo "<tr>";
		echo "<th>C担当者E-mail</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "40", 0);
		echo "</td>";
		echo "</tr>";

		$clm = "m_biko";
		echo "<tr>";
		echo "<th>備考</th>";
		echo "<td class='nw'>";
		//make_textbox($row->$clm, $clm, "40", 0);
		make_textarea($row->$clm, $clm, 40, 0, "", "", "5", "40");
		echo "</td>";
		echo "</tr>";

		$clm = "m_shiyo";
		echo "<tr>";
		echo "<th>仕様</th>";
		echo "<td class='nw'>";
		//make_textbox($row->$clm, $clm, "40", 0);
		make_textarea($row->$clm, $clm, 40, 0, "", "", "5", "40");
		echo "</td>";
		echo "</tr>";


/*
		$clm = "is_stax";
		echo "<tr>";
		echo "<th>消費税計算を行う</th>";
		echo "<td class='nw'>";
		make_textbox($row->$clm, $clm, "2", 0);
		echo "</td>";
		echo "</tr>";
*/
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

function make_select_tantou($_val, $_cls, $_table, $_tabindex = "", $_corder = "", $_num = 1, $jo = "", $attr = "") {

	if($jo == "t_gencho") {
		
	}
	else if($jo != "") {
		$jo = " WHERE " . $jo . " ";
	}
	
	//sorderカラムの存在確認
	$sql_c = "DESCRIBE {$_table} sorder";
	$query_c = mysql_query($sql_c);
	$num_c = mysql_num_rows($query_c);
	if($num_c) {
		$_corder = "sorder";
	}
	
	if($_cls == "m_tantou_id" || $_cls == "m_tantou_sub_id") {
		$_corder = "t_order";
	}

	if($_corder != "")
		$sql = "SELECT * FROM {$_table} {$jo} ORDER BY {$_corder}";
	else
		$sql = "SELECT * FROM {$_table} {$jo}";

	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	//debug
	/*echo "<script>write_debug(\"{$sql}\");</script>";*/

	echo "<span class='{$_cls}_span'>";
	echo "<select class='{$_cls}'  tabindex='{$_tabindex}' {$attr}>";

	echo "<option value='0' >-----</option>";

	while ($row = mysql_fetch_array($query)) {
//----------------------個別領域 ここから----------------------------
		if($_cls == "g_moto_id") {
			if($row[9] == "")
				$kana = "";
			else
				$kana = "[".$row[9]."]";
		} else {
			$kana = "";
		}
//----------------------個別領域 ここまで----------------------------
		if(	$_cls == "s_seko_id"
			&&
			$_table == "matsushima_seko"
		) {
			if($row[0] == $_val)
				echo "<option value='{$row[0]}' selected='selected'>{$kana}{$row[$_num]}</option>";
			else if($row[14] == 1)
				echo "<option value='{$row[0]}' >{$kana}{$row[$_num]}</option>";
		}
		else if($jo == "t_gencho") {
			if($row[0] == $_val) {
				echo "<option value='{$row[0]}' selected='selected'>{$kana}{$row[$_num]}</option>";
			}
			else if($row[5] == 1 || $row[6] == 1) {
				echo "<option value='{$row[0]}' >{$kana}{$row[$_num]}</option>";
			}
		}
		else if($_cls == "g_tantou_id" || $_cls == "g_tantou_sub_id") {
			if($row[0] == $_val) {
				echo "<option value='{$row[0]}' selected='selected'>{$kana}{$row[$_num]}</option>";
			}
			else if($row[6] == 1) {
				echo "<option value='{$row[0]}' >{$kana}{$row[$_num]}</option>";
			}
		}
		else {
			if($row[0] == $_val)
				echo "<option value='{$row[0]}' selected='selected'>{$kana}{$row[$_num]}</option>";
			else
				echo "<option value='{$row[0]}' >{$kana}{$row[$_num]}</option>";
		}
	}
	echo "</select>";
	echo "</span>";
	echo "<span class='{$_cls}_err error-field'></span>";
}

?>

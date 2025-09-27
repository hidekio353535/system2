<?php
/**********************************************************************
 *
 * 
 *
 **********************************************************************/
 //消費税
 $TAX = 0.1;
 
//一度にselect するlimit数
$MAXLIMIT = 15;

//ページ番号を振る最大値
$MAXSHOWPERPAGE = 20;

//定数
$F_NORMAL 	= 0;
$F_DATE 	= 1;
$F_DATE_SH 	= 2;
$F_NUM 		= 3;
$F_WIDTH	= 4;
$F_TEXTAREA	= 5;
$F_NUM_NO_0 = 6;

/**********************************************************************
 *
 * 	チェックボックス生成
 *
 **********************************************************************/
function make_checkbox($_val, $_fcls = "", $_checked = "") {
	echo "<input type='checkbox' class='chk {$_fcls}' value='{$_val}' />";
}

function make_normal_checkbox($_val, $_cls, $_size, $_tabindex = "") {
	if($_val)
		$chked = "checked='checked'";
	else
		$chked = "";
	
	echo "<input type='checkbox' class='{$_cls} {$_fcls}' value='{$_val}' {$chked} tabindex='{$_tabindex}'/>";
}
/**********************************************************************
 *
 * 	テキストボックス生成
 *
 **********************************************************************/
function make_textbox($_val, $_cls, $_size, $_tabindex = "", $_text = "text", $_fcls = "", $attr = "") {
	//日付表示コントロール
	if($_val == "0000-00-00")
		$_val = "";
	
	//日付フィールドなら本日ボタンを追加
	if(preg_match('/_date/',$_cls)) {
		
		$attr_title = "今日";
		
		if($attr == "")
			$today_button = "<img src='../img/today.png' alt='{$attr_title}' title='{$attr_title}' class='{$_cls}_today_btn opa icon' style='vertical-align:middle'>";
		else
			$today_button = "<img src='../img/today.png' alt='{$attr_title}' title='{$attr_title}' class='{$_cls}_today_btn opa icon' style='vertical-align:middle;display:none'>";


		echo "<input type='{$_text}' class='{$_cls} {$_fcls}' value='{$_val}' tabindex='{$_tabindex}' size='{$_size}' {$attr} />{$today_button}";
		
		echo "<span class='{$_cls}_err error-field'></span>";
	
		echo "<script>
			$('.{$_cls}_today_btn').unbind('click');
			$('.{$_cls}_today_btn').click(function(event) {
				var idx = $('.{$_cls}_today_btn').index(this);
				set_today('{$_cls}',idx);
				$('.{$_cls}').eq(idx).trigger('change');
			});
	    </script>";

	}
	else {
		
		if(
			(
			$_cls == "s_moto_invoice" 
			||
			$_cls == "s_invoice"
			||
			$_cls == "s_hattyu"
			||
			$_cls == "m_tanka"
			||
			$_cls == "m_kingaku"
			||
			$_cls == "meisai_shokei"
			||
			$_cls == "meisai_tax"
			||
			$_cls == "meisai_total"
			)
			&& $_val && is_numeric($_val)
		) {
			$_val = number_format($_val);
		}
		
		echo "<input type='{$_text}' class='{$_cls} {$_fcls}' value='{$_val}' tabindex='{$_tabindex}' size='{$_size}' {$attr} />";
		echo "<span class='{$_cls}_err error-field'></span>";
	}

}

/**********************************************************************
 *
 * 	テキストボックス生成
 *
 **********************************************************************/
function make_textbox_f($_val, $_cls, $_size, $_tabindex = "", $_text = "text", $_fcls = "", $attr = "") {
	//日付表示コントロール
	if($_val == 0)
		$_val = "";
	
	echo "<input type='{$_text}' class='{$_cls} {$_fcls}' value='{$_val}' tabindex='{$_tabindex}' size='{$_size}' {$attr} />";
	echo "<span class='{$_cls}_err error-field'></span>";

}

/**********************************************************************
 *
 * 	テキストエリア生成
 *
 **********************************************************************/
function make_textarea($_val, $_cls, $_size, $_tabindex = "", $_text = "text", $_fcls = "", $_rows = "5", $_cols = "40") {
	echo "<textarea class='{$_cls} {$_fcls}' rows='{$_rows}' cols='{$_cols}' tabindex='{$_tabindex}'>{$_val}</textarea>";
}

/**********************************************************************
 *
 * 	オプショナル生成
 *
 **********************************************************************/
function make_optional($_val, $_cls, $_size, $_tabindex = "", $_table, $_rtbl = "", $_rclm = "", $_fcls = "") {
	echo "<input type='text' class='{$_cls} {$_fcls}' value='{$_val}' tabindex='{$_tabindex}' size='{$_size}' /><img src='../img/optional.gif' alt='選択' title='選択' class='{$_cls}_btn opa icon' style='vertical-align:middle' />";
	echo "<span class='{$_cls}_err error-field'></span>";

	echo "<script>
		$('.{$_cls}_btn').unbind('click');
		$('.{$_cls}_btn').click(function(event) {
			event.stopPropagation();
			var idx = $('.{$_cls}_btn').index(this);
			show_optional('{$_table}','{$_cls}',idx, '{$_rtbl}', '{$_rclm}');
		});
    </script>";
}
/**********************************************************************
 *
 * 	セレクトボックス生成
 *
 **********************************************************************/
function make_select($_val, $_cls, $_table, $_tabindex = "", $_corder = "", $_num = 1, $jo = "", $attr = "") {

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
	
	if($_cls == "g_tantou_id" || $_cls == "g_tantou_sub_id" || $_cls == "s_tantou_id") {
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

/**********************************************************************
 *
 * 	セレクトボックス生成
 *
 **********************************************************************/
function make_select_f($_val, $_cls, $_table, $_tabindex = "", $_corder = "", $_num = 1, $jo = "", $attr = "") {

	if($jo != "")
		$jo = " WHERE " . $jo . " ";

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
		
		if($row[0] == $_val)
			echo "<option value='{$row[0]}' selected='selected'>{$row[1]}月{$row[2]}</option>";
		else
			echo "<option value='{$row[0]}' >{$row[1]}月{$row[2]}</option>";
	}
	echo "</select>";
	echo "</span>";
	echo "<span class='{$_cls}_err error-field'></span>";
}

/**********************************************************************
 *
 * 	リスト表示フォーマットコントロール
 *
 **********************************************************************/
function show_clm($_val, $_clm, $_type = 0, $_width = 20) {

	//グローバル宣言
	global $F_NORMAL;
	global $F_DATE;
	global $F_DATE_SH;
	global $F_NUM;
	global $F_WIDTH;
	global $F_NUM_NO_0;

	if($_type == $F_NORMAL) { //通常
		echo "<span title='{$_val}'>" . $_val . "</span>";
	}
	else if($_type == $F_WIDTH) { //表示横幅短縮
		echo "<span title='{$_val}'>". mb_strimwidth($_val, 0, $_width, '...', 'UTF-8') ."</span>";
	}
	else if($_type == $F_DATE) { //日付
		if($_val == "0000-00-00" || $_val == "" || $_val == null)
			echo "";
		else
			echo $_val;
	}
	else if($_type == $F_DATE_SH) { //日付短縮 / 表示
		if($_val == "0000-00-00" || $_val == "" || $_val == null)
			echo "";
		else
			echo date("m/d",strtotime($_val));
	}
	else if($_type == $F_NUM || $_type == $F_NUM_NO_0) { //カンマフォーマット
		//数値判定 数値かつ小数点を含まない
		if(preg_match('/^[0-9]+$/', $_val) && !preg_match('/\./', $_val)) {
			//NO_0の場合0は表示しない
			if($_type == $F_NUM_NO_0 && $_val == 0) {
				echo "";
			} else {
				echo number_format($_val);
			}
		} else {
			echo $_val;
		}
	}
}

/**********************************************************************
 *
 * 	セレクトボックス生成
 *
 **********************************************************************/
function show_select_clm($_val, $_table, $_num = 1) {

	$sql = "SELECT * FROM {$_table}";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	while ($row = mysql_fetch_array($query)) {
		
		if( $_table == "matsushima_moto") {
			$b = $row[9];
			if(!$b) {
				$b = "";
			}
			else {
				//$b = " " . $b;
				$b = " " . mb_strimwidth($b, 0, 6, '', 'UTF-8');
			}
		}
		else {
			$b = "";
		}
		

		if($row[0] == $_val)
			echo $row[$_num] . $b;
		else
			echo "";
	}
}

/**********************************************************************
 *
 * 
 *
 **********************************************************************/
function del_pref($tmp_addr) {
	
	$tmp_addr = preg_replace('/北海道|青森県|岩手県|宮城県|秋田県|山形県|福島県|茨城県|栃木県|群馬県|埼玉県|千葉県|東京都|神奈川県|新潟県|富山県|石川県|福井県|山梨県|長野県|岐阜県|静岡県|愛知県|三重県|滋賀県|京都府|大阪府|兵庫県|奈良県|和歌山県|鳥取県|島根県|岡山県|広島県|山口県|徳島県|香川県|愛媛県|高知県|福岡県|佐賀県|長崎県|熊本県|大分県|宮崎県|鹿児島県|沖縄県/', '', $tmp_addr);

	return $tmp_addr;
}



/**********************************************************************
 *
 * 
 *
 **********************************************************************/
//エンコード
function h($string) {
	return htmlspecialchars($string, ENT_QUOTES);
}
//デコード
function hd($string) {
	return html_entity_decode($string,ENT_QUOTES);
}

function showTh($_clm, $_th, $vis = true) {
	
	if($vis) {
		$visstyle = "";
	}
	else {
		$visstyle = "style='display:none'";
	}
	
	if($_clm != "")
		echo "<th class='nw' {$visstyle}><a href='#' onclick='sort_order(\"{$_clm}\");return false'>{$_th}</a></th>";
	else
		echo "<th class='nw' {$visstyle}>{$_th}</th>";
}

/**********************************************************************
 *
 * 	例外クラス
 *
 **********************************************************************/
class returnZeroRowException extends Exception { }
class dbException extends Exception { }



function show_date($d) {
	if($d == "0000-00-00" || $d == "" || $d == null)
		return "";
	else
		return date("Y-m-d", strtotime($d));	
}

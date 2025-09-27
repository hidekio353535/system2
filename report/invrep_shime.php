<?php
//set_time_limit(180);
require_once('../tcpdf/config/lang/eng.php');
require_once('../tcpdf/tcpdf.php');
require_once("../php/db_connect.php");
require_once("../php/company_info.php");

// create new PDF document
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($COMPANY_NAME_ENG);
$pdf->SetTitle($COMPANY_NAME_ENG);
$pdf->SetSubject($COMPANY_NAME_ENG);
$pdf->SetKeywords($COMPANY_NAME_ENG);

// remove default header/footer 
$pdf->setPrintHeader(false); 
$pdf->setPrintFooter(false); 
 
// set default monospaced font 
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED); 
 
//set margins 
$pdf->SetMargins(8, 8, 8); 
 
//set auto page breaks 
$pdf->SetAutoPageBreak(false, 20); 
 
//set image scale factor 
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
 
//set some language-dependent strings 
$pdf->setLanguageArray($l); 

$pdf->SetFont('kozgopromedium', '', 14);

// -----------------------------------------------------------------------------

//idの受け取り
if(isset($_REQUEST['year'])) {
	$shime_year = $_REQUEST['year'];
}
if(isset($_REQUEST['month'])) {
	$shime_month = $_REQUEST['month'];
}

$title = "{$COMPANY_NAME} 請求・入金状況レポート(締め日ベース) {$shime_year}年{$shime_month}月度";

//表示用SQL
$sql_m = "SELECT * FROM matsushima_moto 
			LEFT OUTER JOIN matsushima_shime ON matsushima_shime.id = matsushima_moto.m_shime_group
			WHERE 1
			ORDER BY m_shime_group
			";
@$query_m = mysql_query($sql_m);
@$num_m = mysql_num_rows($query_m);

if(!$query_m) {
	echo "fail";
	exit();
}
//対象SLIPカウンター
$cnt = 0;
	
$m0 = array();
$m1 = array();
$m2 = array();
$m3 = array();
$m4 = array();
$m5 = array();
$m6 = array();
$m7 = array();
$m8 = array();
$m9 = array();

$total_hat = 0;
$total_inv = 0;
$total_prof = 0;
$total_den = 0;


//表示するレコードがある場合
if($num_m) {
	while ($row_m = @mysql_fetch_object($query_m)) {

		$m_shime_group = $row_m->sday;
		
		//元請け取得(前元請け対象)
		//$moto = $row_m->moto;
		$moto = $row_m->moto_nik;
		$moto_id = $row_m->moto_id;
		$zuiji_flag = false;

		//締めグループで検索条件をハンドリング $m_shime_group
		//月末なら
		if($m_shime_group == 31) {
			//月末日を取得
			$ts = mktime(0, 0, 0, $shime_month, 1, $shime_year);
			$m_shime_group = intval(date('t', $ts));
		}
		//随時ハンドリング 空白
		else if($m_shime_group == 40 || $m_shime_group == "" || $m_shime_group == null) {
			//月末日を取得
			$ts = mktime(0, 0, 0, $shime_month, 1, $shime_year);
			$m_shime_group = intval(date('t', $ts));
		
			$zuiji_flag = true;
		}
		
		//前月計算
		if($shime_month == 1) {
			$tmp_year = $shime_year - 1;
			$tmp_month = 12;
		}
		else {
			$tmp_year = $shime_year;
			$tmp_month = $shime_month - 1;
		}
		//先月の月末
		if($m_shime_group > 27) {
			$ts = mktime(0, 0, 0, $tmp_month, 1, $tmp_year);
			$tmp_m_shime_group = intval(date('t', $ts));
		}
		else {
			$tmp_m_shime_group = $m_shime_group;
		}
		
		//最終的に数字かチェック
		if(is_numeric($m_shime_group)) {
			$JYOKEN = " AND (s_shime_date <= '{$shime_year}-{$shime_month}-{$m_shime_group}' AND s_shime_date != '0000-00-00' AND s_shime_date is not null)";
			$JYOKEN .= " AND (s_shime_date > '{$tmp_year}-{$tmp_month}-{$tmp_m_shime_group}' AND s_shime_date != '0000-00-00' AND s_shime_date is not null)";
		}
		else {
			//範囲外の条件
			$JYOKEN = " AND (s_shime_date <= '1970-01-01' AND s_shime_date != '0000-00-00' AND s_shime_date is not null)";
		}
		
		//条件に元請けセット
		$JYOKEN .= " AND (g_moto_id = '{$moto_id}')";

		//既に締めてあるSLIPを除外
		//$JYOKEN .= " AND (s_inv_id = 0 OR s_inv_id = ''  OR s_inv_id is null)";

		//SQL
		$sql = "SELECT * FROM matsushima_slip 
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
				INNER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = matsushima_slip.s_seko_kubun_id
				
				WHERE 1 {$JYOKEN} ORDER BY s_shime_date";
		@$query = mysql_query($sql);
		@$num = mysql_num_rows($query);

		if($num) {
			while ($row = @mysql_fetch_object($query)) {

			$g_id = $row->g_id;
			$s_id = $row->s_id;
		
			$inv = 0;
		
			//請求状況
			$s_inv_id = $row->s_inv_id;
			
			//入金状況
			$sql_w = "SELECT * FROM matsushima_inv WHERE i_id = '{$s_inv_id}'";
			$query_w = mysql_query($sql_w);
			$num_w = mysql_num_rows($query_w);
			$row_w = mysql_fetch_object($query_w);
			
			if($num_w) {
				$with_date = $row_w->i_receipt_date;
				if($with_date == "0000-00-00" || $with_date == null)
					$with_date = '<p class="red">未入金</p>';
				else
					$with_date = date('m月d日',strtotime($row_w->i_receipt_date));
			
				$i_receipt_yotei_date = $row_w->i_receipt_yotei_date;
				if($i_receipt_yotei_date == "0000-00-00" || $i_receipt_yotei_date == null)
					$i_receipt_yotei_date = '<p class="red">未記載</p>';
				else
					$i_receipt_yotei_date = date('m月d日',strtotime($i_receipt_yotei_date));
			}
			else {
					$with_date = '<p class="red">未入金</p>';
					$i_receipt_yotei_date = '<p class="red">未記載</p>';
			}
			
			if($s_inv_id)
				$al_inv = '<p class="green">請求締め済</p>';
			else
				$al_inv = '<p class="red">未請求</p>';

			//日付ハンドリング
			$s_date = $row->s_date;
			if($s_date == '0000-00-00' || $s_date == null)
				$s_date = '<p class="red">未記入</p>';
			else
				$s_date = date('m月d日',strtotime($row->s_date));
		
			$s_shime_date = $row->s_shime_date;
			if($s_shime_date == '0000-00-00' || $s_shime_date == null)
				$s_shime_date = '<p class="red">未記入</p>';
			else
				$s_shime_date = date('m月d日',strtotime($row->s_shime_date));
		
			//トータル計算
		
			$inv = $row->s_invoice;
			$total_inv += $inv;
		
			$m0[$cnt] = $row->s_id;
			$m1[$cnt] = $moto;
			$m2[$cnt] = $i_receipt_yotei_date;
			$m3[$cnt] = $s_shime_date;
			$m4[$cnt] = mb_strimwidth("[".$row->sy_name_nik."]" . $row->g_genba, 0, 30, '', 'UTF-8');
			$m5[$cnt] = number_format($inv);
			$m6[$cnt] = $al_inv;
			$m7[$cnt] = $with_date;
			$m8[$cnt] = "";
		
			$cnt++;
			}
		}
	}
}

$total_den = $cnt;

//トータルフォーマット
if($total_inv)
	$total_inv = "￥" . number_format($total_inv);
else
	$total_inv = "";

if($total_hat)
	$total_hat = "￥" . number_format($total_hat);
else
	$total_hat = "";

if($total_prof)
	$total_prof = "￥" . number_format($total_prof);
else
	$total_prof = "";

$ONEPAGE = 25;
$FIRST_PAGE = 25;
$MID_PAGE = 25;
$END_PAGE = 25;

//ページ計算
if($total_den  <= $ONEPAGE)
	$page_total = 1;
else if($total_den  <= $FIRST_PAGE + $END_PAGE)
	$page_total = 2;
else
	$page_total = ceil(($total_den  - $FIRST_PAGE - $END_PAGE) / $MID_PAGE) + 2;

$cnt = 0;

// -----------------------------------------------------------------------------

$css = <<<EOD
<style type="text/css">

table {
	margin:0;
	padding:0;
	font-size:10pt;
}

td {
	vertical-align:middle;
}

.waku {
	border: 1px solid #000;
}
.wakub {
	border-bottom: 1px solid #000;
}
.wakut {
	border-top: 1px solid #000;
}
.wakur {
	border-right: 1px solid #000;
}
.wakul {
	border-left: 1px solid #000;
}

.waku1 {
	border-top: 1px solid #000;
	border-left: 1px solid #000;
	border-right: 1px solid #000;
	border-bottom: 1px solid #000;
}
.waku2 {
	border-top: 1px solid #000;
	border-left: 1px solid #000;
	border-right: 1px solid #000;
	border-bottom: 1px solid #000;
}
.waku3 {
	border-top: 1px solid #000;
	border-left: 1px solid #000;
	border-right: 1px solid #000;
	border-bottom: 1px solid #000;
}
.waku4 {
	border-top: 1px solid #000;
	border-left: 1px solid #000;
	border-right: 1px solid #000;
	border-bottom: 1px solid #000;
}

.wakub1 {
	border-top: 2px solid #000;
	border-left: 2px solid #000;
	border-right: 1px solid #000;
	border-bottom: 2px solid #000;
}
.wakub2 {
	border-top: 2px solid #000;
	border-left: 1px solid #000;
	border-right: 1px solid #000;
	border-bottom: 2px solid #000;
}
.wakub3 {
	border-top: 2px solid #000;
	border-left: 1px solid #000;
	border-right: 2px solid #000;
	border-bottom: 2px solid #000;
}

.bgkuro {
	color: #FFF;
	background-color: #333;
}
.size00 {
	font-size:1pt;
}
.size0 {
	font-size:10pt;
}
.size1 {
	font-size:12pt;
}
.size2 {
	font-size:14pt;
}
.size3 {
	font-size:16pt;
}
.size4 {
	font-size:18pt;
}
.size5 {
	font-size:24pt;
}
.tac {
	text-align:center;
}
.tar {
	text-align:right;
}

.red {
	color:#F00;
}

.green {
	color:#090;
}

.space {
}

</style>
EOD;

//請求書
$tbl = $css;
$tbl .= <<<EOD
<table width="640" border="0" cellpadding="3">
	<tr>
		<td colspan="6" class="tac size3">{$title}</td>
	</tr>
</table>
<table width="640" border="0" cellpadding="3">
	<tr>
		<td width="70" class="size00"></td>
		<td width="180" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="270" class="size00"></td>
		<td width="130" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="100" class="size00"></td>
		<td width="100" class="size00"></td>
	</tr>
	<tr>
		<td class="waku tac">受注ID</td>
		<td class="waku tac">元請名</td>
		<td class="waku tac">締対象日</td>
		<td class="waku tac">現場名</td>
		<td class="waku tac">受注額</td>
		<td class="waku tac">入金予定日</td>
		<td class="waku tac">請求状況</td>
		<td class="waku tac">入金状況</td>
	</tr>
EOD;

for($page = 1;$page <= $page_total;$page++) {

	//各ページの行数計算
	// 1ページしかない
	if($page_total == 1)
		$max = $ONEPAGE;
	// 複数ページあるうちの1ページ目
	else if($page_total > 1 && $page == 1)
		$max = $FIRST_PAGE;	
	// 中間のページ	
	else if($page > 1 && $page < $page_total)
		$max = $MID_PAGE;
	//最後のページ
	else
		$max = $END_PAGE;
	

if($page > 1) {

//変数初期化cssセット
$tbl = $css;
$tbl .= <<<EOD
<table width="640" border="0" cellpadding="3">
	<tr>
		<td width="70" class="size00"></td>
		<td width="180" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="270" class="size00"></td>
		<td width="130" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="100" class="size00"></td>
		<td width="100" class="size00"></td>
	</tr>
EOD;

}

for($i = 0;$i < $max;$i++) {
$tbl .= <<<EOD
	<tr>
		<td class="waku tac">{$m0[$cnt]}</td>
		<td class="waku">{$m1[$cnt]}</td>
		<td class="waku tac">{$m3[$cnt]}</td>
		<td class="waku">{$m4[$cnt]}</td>
		<td class="waku tar">{$m5[$cnt]}</td>
		<td class="waku tac">{$m2[$cnt]}</td>
		<td class="waku tar">{$m6[$cnt]}</td>
		<td class="waku tar">{$m7[$cnt]}</td>
	</tr>
EOD;
$cnt++;

} // $i Loop

//ページ番号を振る
if($page_total != 1 && $page != $page_total) {
$tbl .= <<<EOD
	<tr>
		<td colspan="7" class="tar">Page: {$page} / {$page_total}</td>
	</tr>
EOD;
}


//最終ページ以外
if($page != $page_total) {
	$tbl .= "</table>";
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');
}
} // $page Loop

//最終ページの最終行

$tbl .= <<<EOD
	<tr>
		<td colspan="1" class="waku tac"></td>
		<td colspan="1" class="waku tac">請求伝票数：{$total_den }</td>
		<td colspan="1" class="waku tac"></td>
		<td colspan="1" class="waku tac">合計</td>
		<td colspan="1" class="waku tar">{$total_inv}</td>
		<td colspan="3" class="waku tac"></td>
	</tr>
</table>
EOD;

//ページ番号を振る
if($page_total != 1) {
$tbl .= <<<EOD
<table>
	<tr>
		<td colspan="6" class="tar">Page: {$page_total} / {$page_total}</td>
	</tr>
</table>
EOD;
}
$pdf->AddPage();
$pdf->writeHTML($tbl, true, false, false, false, '');

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('invrep_shime.pdf', 'I');

//============================================================+
// END OF FILE                                                
//============================================================+


/*
		<td width="180" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="240" class="size00"></td>
		<td width="130" class="size00"></td>
		<td width="100" class="size00"></td>
		<td width="100" class="size00"></td>
		<td width="100" class="size00"></td>
*/
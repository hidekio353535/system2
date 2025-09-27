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
	$year = $_REQUEST['year'];
}
if(isset($_REQUEST['month'])) {
	$month = $_REQUEST['month'];
}

if($month == 1) {
	$tmp_year = $year - 1;
	$hs_st_date = $tmp_year . "-12-01";
	$hs_end_date = $year . "-02-01";
}
else if($month == 12) {
	$tmp_year = $year + 1;
	$hs_st_date = $year . "-11-01";
	$hs_end_date = $tmp_year . "-01-01";
}
else {
	$tmp_st_month = $month - 1;
	$tmp_end_month = $month + 1;
	$hs_st_date = $year . "-".$tmp_st_month."-01";
	$hs_end_date = $year . "-".$tmp_end_month."-01";
}

$title = "{$COMPANY_NAME} 工事売上表 {$year}年{$month}月度 (速報版)";

$sql = "
SELECT * 
FROM matsushima_genba AS g
LEFT OUTER JOIN matsushima_moto AS m ON g.g_moto_id = m.moto_id
LEFT OUTER JOIN matsushima_slip AS s ON s.s_genba_id = g.g_id
LEFT OUTER JOIN matsushima_shime AS sm ON sm.id = m.m_shime_group

WHERE
s_shime_date >= '{$hs_st_date}'
AND
s_shime_date < '$hs_end_date'

GROUP BY g_id
";	

$query = mysql_query($sql);
$num = mysql_num_rows($query);

if(!$num) {
	$tbl = "<p>対象期間のデータがありません。</p>";
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');
	$pdf->Output('example_048.pdf', 'I');
	exit();	
}

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

while ($row = mysql_fetch_object($query)) {

	$g_id = $row->g_id;

	//締日で除外
	$shime_date = $row->s_shime_date;
	$shime_date_ts = strtotime($shime_date);
	$sday = $row->sday;
	
	switch($sday) {
		case 10:
		case 15:
		case 20:
		case 25:
			if($month == 1) {
				$tmp_year = $year - 1;
				$hikaku_st_date_ts = strtotime("{$tmp_year}-12-{$sday}");
				$hikaku_end_date_ts = strtotime("{$year}-1-{$sday}");
			}
			else {
				$tmp_month = $month - 1;
				$hikaku_st_date_ts = strtotime("{$year}-{$tmp_month}-{$sday}");
				$hikaku_end_date_ts = strtotime("{$year}-{$month}-{$sday}");
			}
			break;
		default:
				//月末日を取得
				$ts = mktime(0, 0, 0, $month, 1, $year);
				$lastday = intval(date('t', $ts));
		
				$hikaku_st_date_ts = strtotime("{$year}-{$month}-01");
				$hikaku_end_date_ts = strtotime("{$year}-{$month}-{$lastday}");
	}

	if(!($shime_date_ts > $hikaku_st_date_ts && $shime_date_ts <= $hikaku_end_date_ts))
		continue;


	$hat = 0;
	$inv = 0;
	$prof = 0;

	$tmp_seko = array();
	$seko = "";

	//発注情報
	$sql2 = "
	SELECT * FROM matsushima_slip_hat AS sh
	INNER JOIN matsushima_seko AS sk ON sk.seko_id = sh.s_seko_id
	WHERE
	s_genba_id = '{$g_id}'
	AND
	s_shime_date >= '{$year}-{$month}-01'
	AND
	s_shime_date <= LAST_DAY('{$year}-{$month}-01')
	";

	$query2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_object($query2)) {
		//発注先(複数の場合あり)
		$tmp_seko[] = $row2->seko_nik;
		$hat += $row2->s_hattyu;
	}

	//発注先抽出（重複なしで）
	$tmp_seko = array_unique($tmp_seko);
	foreach($tmp_seko AS $ts) {	
		$seko .= $ts . ",";
	}
	//最後のカンマを削除
	$seko = preg_replace('/,$/','',$seko);

	//受注情報
	$sql2 = "
	SELECT
	SUM(s_invoice) as s_invoice, 
	MIN(s_st_date) as s_st_date, 
	MAX(s_end_date) as s_end_date 
	FROM matsushima_slip
	WHERE
	s_genba_id = '{$g_id}'
	AND
	s_shime_date >= '{$year}-{$month}-01'
	AND
	s_shime_date <= LAST_DAY('{$year}-{$month}-01')
	";

	$query2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_object($query2)) {
		$inv += $row2->s_invoice;
		
		if($row2->s_st_date == "0000-00-00" || $row2->s_st_date == null)
			$m2[$cnt] = "未記入";
		else
			$m2[$cnt] = date("m月d日",strtotime($row2->s_st_date));
	
		if($row2->s_end_date == "0000-00-00" || $row2->s_end_date == null)
			$m3[$cnt] = "未記入";
		else
			$m3[$cnt] = date("m月d日",strtotime($row2->s_end_date));
	}
	
	//トータル計算
	$total_hat += $hat;
	$total_inv += $inv;
	$total_prof += ($inv - $hat);

	$m1[$cnt] = $row->moto_nik;

	$m4[$cnt] = mb_strimwidth($row->g_genba, 0, 30, '', 'UTF-8');
	$m5[$cnt] = mb_strimwidth($seko, 0, 20, '', 'UTF-8');
	$m6[$cnt] = number_format($inv);
	$m7[$cnt] = number_format($hat);
	$m8[$cnt] = number_format($inv - $hat);
	$m9[$cnt] = $g_id;

	$cnt++;
}

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
$cnt = 0;

//ページ計算
if($num <= $ONEPAGE)
	$page_total = 1;
else if($num <= $FIRST_PAGE + $END_PAGE)
	$page_total = 2;
else
	$page_total = ceil(($num - $FIRST_PAGE - $END_PAGE) / $MID_PAGE) + 2;

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
		<td width="170" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="60" class="size00"></td>
		<td width="220" class="size00"></td>
		<td width="130" class="size00"></td>
		<td width="90" class="size00"></td>
		<td width="90" class="size00"></td>
		<td width="90" class="size00"></td>
	</tr>
	<tr>
		<td class="waku tac">ビルダー名</td>
		<td class="waku tac">開始日</td>
		<td class="waku tac">終了日</td>
		<td class="waku tac">現場ID</td>
		<td class="waku tac">現場名</td>
		<td class="waku tac">発注先</td>
		<td class="waku tac">受注額</td>
		<td class="waku tac">発注額</td>
		<td class="waku tac">利益</td>
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
		<td width="170" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="60" class="size00"></td>
		<td width="220" class="size00"></td>
		<td width="130" class="size00"></td>
		<td width="90" class="size00"></td>
		<td width="90" class="size00"></td>
		<td width="90" class="size00"></td>
	</tr>
EOD;

}

for($i = 0;$i < $max;$i++) {
$tbl .= <<<EOD
	<tr>
		<td class="waku">{$m1[$cnt]}</td>
		<td class="waku tac">{$m2[$cnt]}</td>
		<td class="waku tac">{$m3[$cnt]}</td>
		<td class="waku tac">{$m9[$cnt]}</td>
		<td class="waku">{$m4[$cnt]}</td>
		<td class="waku">{$m5[$cnt]}</td>
		<td class="waku tar">{$m6[$cnt]}</td>
		<td class="waku tar">{$m7[$cnt]}</td>
		<td class="waku tar">{$m8[$cnt]}</td>
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
		<td colspan="3" class="waku tac"></td>
		<td colspan="2" class="waku tac">現場数：{$num}</td>
		<td colspan="1" class="waku tac">合計</td>
		<td colspan="1" class="waku tar">{$total_inv}</td>
		<td colspan="1" class="waku tar">{$total_hat}</td>
		<td colspan="1" class="waku tar">{$total_prof}</td>
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
$pdf->Output('example_048.pdf', 'I');

//============================================================+
// END OF FILE                                                
//============================================================+

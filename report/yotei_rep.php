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

if($year == "" || $month == "") {
	$tbl = "<p>対象期間のデータがありません。</p>";
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');
	$pdf->Output('example_048.pdf', 'I');
	exit();	
}
else {
	$first_date = $year . "-" . $month . "-01";
	$ts = mktime(0, 0, 0, $month, 1, $year);
	$lastday = intval(date('t', $ts));
	$last_date = $year . "-" . $month . "-" . $lastday;
}

$title = "{$COMPANY_NAME} 入金予定レポート {$year}年{$month}月入金予定分";

$sql = "
SELECT *
FROM matsushima_inv as inv
LEFT OUTER JOIN matsushima_moto as m ON m.moto_id = inv.i_moto_id

WHERE
i_receipt_yotei_date >= '{$first_date}'  AND i_receipt_yotei_date <= '{$last_date}' 
AND
i_receipt_yotei_date != '0000-00-00'
AND
i_receipt_yotei_date is not null

ORDER BY i_receipt_yotei_date, moto_id, i_id
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
$m10 = array();
$m11 = array();
$m12 = array();
$m13 = array();
$m14 = array();

$total_hat = 0;
$total_inv = 0;
$total_prof = 0;

$ttl_rec = 0;
$ttl_com = 0;
$ttl_sagaku = 0;

while ($row = mysql_fetch_object($query)) {

	$g_id = $row->g_id;
	$s_id = $row->s_id;
	$i_id = $row->i_id;
	
	$i_receipt_yotei_date = $row->i_receipt_yotei_date;
	$inv_kubun = $row->inv_kubun;

	$inv = 0;

	$tmp_seko = array();
	$seko = "";

	//請求情報
	$sql2 = "
	SELECT 
	
	SUM(s_invoice) as s_invoice,
	MIN(s_st_date) as s_st_date,
	MAX(s_end_date) as s_end_date
	
	FROM matsushima_slip
	INNER JOIN matsushima_inv ON matsushima_inv.i_id = matsushima_slip.s_inv_id
	
	WHERE 
	s_inv_id = '{$row->i_id}'
	
	";

	$query2 = mysql_query($sql2);
	$row2 = mysql_fetch_object($query2);
	
	
	//入金確認
	$with_date = $row->i_receipt_date;
	if($with_date == "0000-00-00" || $with_date == null)
		$with_date = '<p class="red">未入金</p>';
	else
		$with_date = date('m月d日',strtotime($with_date));

	//請求額＋調整額
	$inv = $row2->s_invoice + $row->i_chosei;
	//トータル計算
	$total_inv += $inv;

	$m1[$cnt] = $row->moto_nik;

	$m4[$cnt] = $row->i_year . "年" . $row->i_month . "月請求";
	$m5[$cnt] = $with_date;
	$m6[$cnt] = number_format($inv);

	$m7[$cnt] = $row->i_receipt_yotei_date;
		if($m7[$cnt] == "0000-00-00" || $m7[$cnt] == null)
			$m7[$cnt] = '<p class="red">未記入</span>';
		else
			$m7[$cnt] = date("m月d日",strtotime($m7[$cnt]));
			
	if($row->i_receipt_kingaku)
		$m8[$cnt] = number_format($row->i_receipt_kingaku);
	else
		$m8[$cnt] = '<p class="red">未入金</p>';
		
	$m9[$cnt] = $i_id;
	$m11[$cnt] = $row->i_biko;

	$m12[$cnt] = $row->i_inv_date;
		if($m12[$cnt] == "0000-00-00" || $m12[$cnt] == null)
			$m12[$cnt] = '<p class="red">未記入</span>';
		else
			$m12[$cnt] = date("m月d日",strtotime($m12[$cnt]));

	$sagaku = $inv - ($row->i_receipt_kingaku + $row->i_commission);
	$sagaku_fmt = number_format($sagaku);
	
	if($row->i_commission) {
		$i_commission = number_format($row->i_commission);
	}
	else {
		$i_commission = 0;
	}
	
	$m13[$cnt] = $i_commission;
	$m14[$cnt] = $sagaku_fmt;

	//合計計算
	$ttl_rec += $row->i_receipt_kingaku;
	$ttl_com += $row->i_commission;
	$ttl_sagaku += $sagaku;

	$cnt++;
}

//トータルフォーマット
if($total_inv)
	$total_inv = "￥" . number_format($total_inv);
else
	$total_inv = "";

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
		<td width="80" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="50" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="140" class="size00"></td>
		<td width="100" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="190" class="size00"></td>
	</tr>
	<tr>
		<td class="waku tac">入金予定日</td>
		<td class="waku tac">入金状況</td>
		<td class="waku tac">請求ID</td>
		<td class="waku tac">請求日</td>
		<td class="waku tac">元請名</td>
		<td class="waku tac">請求名</td>
		<td class="waku tac">請求額</td>
		<td class="waku tac">入金額</td>
		<td class="waku tac">手数料等</td>
		<td class="waku tac">差額</td>
		<td class="waku tac">備考</td>
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
		<td width="80" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="50" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="140" class="size00"></td>
		<td width="100" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="190" class="size00"></td>
	</tr>
	<tr>
		<td class="waku tac">入金予定日</td>
		<td class="waku tac">入金状況</td>
		<td class="waku tac">請求ID</td>
		<td class="waku tac">請求日</td>
		<td class="waku tac">元請名</td>
		<td class="waku tac">請求名</td>
		<td class="waku tac">請求額</td>
		<td class="waku tac">入金額</td>
		<td class="waku tac">手数料等</td>
		<td class="waku tac">差額</td>
		<td class="waku tac">備考</td>
	</tr>
EOD;

}

for($i = 0;$i < $max;$i++) {
$tbl .= <<<EOD
	<tr>
		<td class="waku tac">{$m7[$cnt]}</td>
		<td class="waku tac">{$m5[$cnt]}</td>
		<td class="waku tac">{$m9[$cnt]}</td>
		<td class="waku tar">{$m12[$cnt]}</td>
		<td class="waku">{$m1[$cnt]}</td>
		<td class="waku">{$m4[$cnt]}</td>
		<td class="waku tar">{$m6[$cnt]}</td>
		<td class="waku tac">{$m8[$cnt]}</td>
		<td class="waku tac">{$m13[$cnt]}</td>
		<td class="waku tac">{$m14[$cnt]}</td>
		<td class="waku tac">{$m11[$cnt]}</td>
	</tr>
EOD;
$cnt++;

} // $i Loop

//ページ番号を振る
if($page_total != 1 && $page != $page_total) {
$tbl .= <<<EOD
	<tr>
		<td colspan="12" class="tar">Page: {$page} / {$page_total}</td>
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
$ttl_rec = number_format($ttl_rec);
$ttl_com = number_format($ttl_com);
$ttl_sagaku = number_format($ttl_sagaku);

$tbl .= <<<EOD
	<tr>
		<td colspan="3" class="waku tac">入金予定請求伝票数：{$num}</td>
		<td colspan="1" class="waku tac"></td>
		<td colspan="1" class="waku tac">合計</td>
		<td colspan="2" class="waku tac">{$total_inv}</td>
		<td colspan="1" class="waku tac">{$ttl_rec}</td>
		<td colspan="1" class="waku tac">{$ttl_com}</td>
		<td colspan="1" class="waku tac">{$ttl_sagaku}</td>
		<td colspan="1" class="waku tac"></td>
	</tr>
</table>
EOD;

//ページ番号を振る
if($page_total != 1) {
$tbl .= <<<EOD
<table>
	<tr>
		<td colspan="14" class="tar">Page: {$page_total} / {$page_total}</td>
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

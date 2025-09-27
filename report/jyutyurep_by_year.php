<?php
//set_time_limit(180);
require_once('../tcpdf/config/lang/eng.php');
require_once('../tcpdf/tcpdf.php');
require_once("../php/db_connect.php");
require_once("../php/company_info.php");
require_once("../tax_mod/tax.php");

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
$pdf->SetMargins(15, 15, 15); 
 
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

$title = "{$COMPANY_NAME} 年間売上レポート {$year}年";

//初期化
$ar = array();
//合計初期化
$ar[13] = array();
$ar[13]['title'] = '合計';
$ar[13]['i_kingaku'] = 0;
$ar[13]['i_receipt_kingaku'] = 0;
$ar[13]['i_commission'] = 0;
$ar[13]['sagaku'] = 0;
$ar[13]['tax'] = 0;

//月ループ
for($m=1;$m <= 12;$m++) {

	$ar[$m] = array();
	
	$sql = "
	SELECT
	SUM((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = inv.i_id)) as i_kingaku,
	SUM(i_receipt_kingaku) as i_receipt_kingaku,
	SUM(i_commission) as i_commission,
	SUM(
		((SELECT SUM(s_invoice) FROM matsushima_slip WHERE s_inv_id = inv.i_id) + i_chosei) - (i_receipt_kingaku + i_commission)
	) as sagaku
	
	FROM matsushima_inv as inv

	WHERE
	i_year = '{$year}'
	AND
	i_month = '{$m}'
	";	

	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	$cnt = 0;
	
	while ($row = mysql_fetch_object($query)) {
		$ar[$m]['title'] = $m . "月";
			
		$ar[$m]['i_kingaku'] = $row->i_kingaku;
		$ar[$m]['i_receipt_kingaku'] = $row->i_receipt_kingaku;
		$ar[$m]['i_commission'] = $row->i_commission;
		$ar[$m]['sagaku'] = $row->sagaku;
		
		//内消費税
		$itax = get_tax($year."-".$m."-01");
		$ar[$m]['tax'] = $row->i_kingaku - ceil($row->i_kingaku / (1 +$itax/100));
		
		//合計
		$ar[13]['i_kingaku'] += $ar[$m]['i_kingaku'];
		$ar[13]['i_receipt_kingaku'] += $ar[$m]['i_receipt_kingaku'];
		$ar[13]['i_commission'] += $ar[$m]['i_commission'];
		$ar[13]['sagaku'] += $ar[$m]['sagaku'];
		$ar[13]['tax'] += $ar[$m]['tax'];
	}
}

// -----------------------------------------------------------------------------

$css = <<<EOD
<style type="text/css">

table {
	margin:0;
	padding:0;
	font-size:14pt;
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

//変数初期化cssセット
$tbl = $css;
$tbl .= "<h2>".$title."</h2>";
$tbl .= <<<EOD
<table width="640" border="0" cellpadding="3">
	<tr>
		<td width="150" class="waku tac">請求月</td>
		<td width="150" class="waku tac">請求額計</td>
		<td width="150" class="waku tac">内税額計</td>
		<td width="150" class="waku tac">入金額計</td>
		<td width="150" class="waku tac">手数料等計</td>
		<td width="150" class="waku tac">差額計</td>
	</tr>
EOD;
					
for($m = 1;$m <= 13;$m++) {
	
	$ar[$m]['i_kingaku'] = number_format($ar[$m]['i_kingaku']);
	$ar[$m]['i_receipt_kingaku'] = number_format($ar[$m]['i_receipt_kingaku']);
	$ar[$m]['i_commission'] = number_format($ar[$m]['i_commission']);
	$ar[$m]['sagaku'] = number_format($ar[$m]['sagaku']);
	$ar[$m]['tax'] = number_format($ar[$m]['tax']);
	
$tbl .= <<<EOD
	<tr>
		<td class="waku tac">{$ar[$m]['title']}</td>
		<td class="waku tar">{$ar[$m]['i_kingaku']}</td>
		<td class="waku tar">{$ar[$m]['tax']}</td>
		<td class="waku tar">{$ar[$m]['i_receipt_kingaku']}</td>
		<td class="waku tar">{$ar[$m]['i_commission']}</td>
		<td class="waku tar">{$ar[$m]['sagaku']}</td>
		
	</tr>
EOD;
}
$tbl .= "</table>";

$pdf->AddPage();
$pdf->writeHTML($tbl, true, false, false, false, '');

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_048.pdf', 'I');

//============================================================+
// END OF FILE                                                
//============================================================+

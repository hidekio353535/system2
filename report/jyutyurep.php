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

$title = "{$COMPANY_NAME} 明細請求レポート {$year}年{$month}月度";

$sql = "
SELECT *
FROM matsushima_inv as inv
INNER JOIN matsushima_slip as slip ON slip.s_inv_id = inv.i_id
INNER JOIN matsushima_genba as g ON slip.s_genba_id = g.g_id
LEFT OUTER JOIN matsushima_moto as m ON m.moto_id = g.g_moto_id
LEFT OUTER JOIN matsushima_kouji_syu as syu ON syu.sy_id = slip.s_seko_kubun_id

WHERE
i_year = '{$year}'
AND
i_month = '$month'
ORDER BY i_id, moto_id, s_id
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

$total_hat = 0;
$total_inv = 0;
$total_prof = 0;

$tmp_chosei = "";
$tmp_i_chosei_name = "";
$tmp_i_chosei = "";

while ($row = mysql_fetch_object($query)) {

	$g_id = $row->g_id;
	$s_id = $row->s_id;
	$i_id = $row->i_id;
	
	$i_receipt_yotei_date = $row->i_receipt_yotei_date;
	$inv_kubun = $row->inv_kubun;

	$hat = 0;
	$inv = 0;
	$prof = 0;

	$tmp_seko = array();
	$seko = "";

	//調整項目があった場合
	if($cnt == 0) {
		$tmp_chosei = $row->i_id;
	}
	else if($tmp_chosei != $row->i_id && $tmp_i_chosei != 0) {
		$m4[$cnt] = $tmp_i_chosei_name;
		$m6[$cnt] = $tmp_i_chosei;
		$m9[$cnt] = $tmp_chosei;

		$total_inv += $tmp_i_chosei;

		$tmp_chosei = $row->i_id;
		$cnt++;
	}

	$tmp_chosei = $row->i_id;
	$tmp_i_chosei_name = $row->i_chosei_name;
	$tmp_i_chosei = $row->i_chosei;

	//日付
	$st_date = $year . "-" . $month . "-01";
	$ts = mktime(0, 0, 0, $month, 1, $year);
	$lastday = intval(date('t', $ts));
	$end_date = $year . "-" . $month . "-" . $lastday;


	//請求情報
	$sql2 = "
	SELECT 
	
	SUM(s_invoice) as s_invoice,
	MIN(s_st_date) as s_st_date,
	MAX(s_end_date) as s_end_date
	
	FROM matsushima_slip
	INNER JOIN matsushima_inv ON matsushima_inv.i_id = matsushima_slip.s_inv_id
	
	WHERE 
	s_id = '{$s_id}'
	AND
	i_year = '{$year}'
	AND
	i_month = '{$month}'
	
	";

	$query2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_object($query2)) {
		$inv += $row2->s_invoice;		
		if($row2->s_st_date == "0000-00-00" || $row2->s_st_date == null)
			$m2[$cnt] = '<p class="red">未記入</span>';
		else
			$m2[$cnt] = date("m月d日",strtotime($row2->s_st_date));
	
		if($row2->s_end_date == "0000-00-00" || $row2->s_end_date == null)
			$m3[$cnt] = '<p class="red">未記入</span>';
		else
			$m3[$cnt] = date("m月d日",strtotime($row2->s_end_date));
	}

	//入金確認
	$with_date = $row->i_receipt_date;
	if($with_date == "0000-00-00" || $with_date == null)
		$with_date = '<p class="red">未入金</p>';
	else
		$with_date = date('m月d日',strtotime($with_date));

	
	//トータル計算
	$total_inv += $inv;

	$m1[$cnt] = $row->moto_nik;

	$m4[$cnt] = mb_strimwidth($row->g_genba, 0, 24, '', 'UTF-8');
	$m5[$cnt] = $with_date;
	$m6[$cnt] = number_format($inv);

	$m7[$cnt] = $row->i_receipt_yotei_date;
		if($m7[$cnt] == "0000-00-00" || $m7[$cnt] == null)
			$m7[$cnt] = '<p class="red">未記入</span>';
		else
			$m7[$cnt] = date("m月d日",strtotime($m7[$cnt]));
			
	$m8[$cnt] = $row->inv_kubun;
	$m9[$cnt] = $i_id;

	$m10[$cnt] = $row->s_genba_id;
	$m11[$cnt] = "[".$row->sy_name_nik."]";

	$m12[$cnt] = $row->s_shime_date;
		if($m12[$cnt] == "0000-00-00" || $m12[$cnt] == null)
			$m12[$cnt] = '<p class="red">未記入</span>';
		else
			$m12[$cnt] = date("m月d日",strtotime($m12[$cnt]));
			

	$cnt++;
}

//最終行の調整項目
if($tmp_i_chosei != 0) {
	$m4[$cnt] = $tmp_i_chosei_name;
	$m6[$cnt] = $tmp_i_chosei;
	$m9[$cnt] = $tmp_chosei;

	$total_inv += $tmp_i_chosei;

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
		<td width="50" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="50" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="140" class="size00"></td>
		<td width="180" class="size00"></td>
		<td width="60" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="70" class="size00"></td>
	</tr>
	<tr>
		<td class="waku tac">請求ID</td>
		<td class="waku tac">開始日</td>
		<td class="waku tac">終了日</td>
		<td class="waku tac">現場ID</td>
		<td class="waku tac">請求締日</td>
		<td class="waku tac">元請名</td>
		<td class="waku tac">現場名</td>
		<td class="waku tac">区分</td>
		<td class="waku tac">請求額</td>
		<td class="waku tac">入金予定日</td>
		<td class="waku tac">入金状況</td>
		<td class="waku tac">請求区分</td>
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
		<td width="50" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="70" class="size00"></td>
		<td width="50" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="140" class="size00"></td>
		<td width="180" class="size00"></td>
		<td width="60" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="80" class="size00"></td>
		<td width="70" class="size00"></td>
	</tr>
	<tr>
		<td class="waku tac">請求ID</td>
		<td class="waku tac">開始日</td>
		<td class="waku tac">終了日</td>
		<td class="waku tac">現場ID</td>
		<td class="waku tac">請求締日</td>
		<td class="waku tac">元請名名</td>
		<td class="waku tac">現場名</td>
		<td class="waku tac">区分</td>
		<td class="waku tac">請求額</td>
		<td class="waku tac">入金予定日</td>
		<td class="waku tac">入金状況</td>
		<td class="waku tac">請求区分</td>
	</tr>
EOD;

}

for($i = 0;$i < $max;$i++) {
$tbl .= <<<EOD
	<tr>
		<td class="waku tac">{$m9[$cnt]}</td>
		<td class="waku tac">{$m2[$cnt]}</td>
		<td class="waku tac">{$m3[$cnt]}</td>
		<td class="waku tac">{$m10[$cnt]}</td>
		<td class="waku tar">{$m12[$cnt]}</td>
		<td class="waku">{$m1[$cnt]}</td>
		<td class="waku">{$m4[$cnt]}</td>
		<td class="waku tac">{$m11[$cnt]}</td>
		<td class="waku tar">{$m6[$cnt]}</td>
		<td class="waku tac">{$m7[$cnt]}</td>
		<td class="waku tac">{$m5[$cnt]}</td>
		<td class="waku tac">{$m8[$cnt]}</td>
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

$tbl .= <<<EOD
	<tr>
		<td colspan="3" class="waku tac">請求明細伝票数：{$num}</td>
		<td colspan="3" class="waku tac"></td>
		<td colspan="1" class="waku tac">合計</td>
		<td colspan="5" class="waku tac">{$total_inv}</td>
	</tr>
</table>
EOD;

//ページ番号を振る
if($page_total != 1) {
$tbl .= <<<EOD
<table>
	<tr>
		<td colspan="12" class="tar">Page: {$page_total} / {$page_total}</td>
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

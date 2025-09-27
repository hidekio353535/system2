<?php
set_time_limit(180);
$pdf_flag = true;
require_once('../tcpdf/config/lang/eng.php');
require_once('../tcpdf/tcpdf.php');
require_once("../php/db_connect.php");
require_once("../php/company_info.php");

// create new PDF document
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
$pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT); 
 
//set auto page breaks 
$pdf->SetAutoPageBreak(false, 20); 
 
//set image scale factor 
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
 
//set some language-dependent strings 
$pdf->setLanguageArray($l); 

// set font
$pdf->SetFont('kozgopromedium', '', 14);

// -----------------------------------------------------------------------------
//idの受け取り
if(isset($_REQUEST['cbno'])) {
	$cbno = $_REQUEST['cbno'];
}
else {
	//パラメーター無しの場合はエラーメッセージで終了
	$tbl = "<p>パラメータが指定されていません</p>";
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');
	$pdf->Output('example_048.pdf', 'I');
	exit();	
}

$my_invoice_no = "";
if($COMPANY_INVOICE_NO) {
	$my_invoice_no = "登録番号 ".$COMPANY_INVOICE_NO;
}

//DBからフォーム情報取得

$sql = "SELECT * FROM matsushima_slip_hat left outer join matsushima_seko on matsushima_slip_hat.s_seko_id = matsushima_seko.seko_id left outer join matsushima_genba on matsushima_slip_hat.s_genba_id = matsushima_genba.g_id left outer join matsushima_tantou on matsushima_genba.g_tantou_id = matsushima_tantou.t_id left outer join matsushima_moto on matsushima_genba.g_moto_id = matsushima_moto.moto_id 
left outer join matsushima_kouji_syu_hat on matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
LEFT OUTER JOIN matsushima_nai_1 ON matsushima_genba.g_nai1_id = matsushima_nai_1.nai1_id

WHERE s_id in ({$cbno})";
$query = mysql_query($sql);
$num = mysql_num_rows($query);
while ($row = mysql_fetch_object($query)) {

	$nai1 = $row->nai1;

	$g_id = $row->g_id;
	$s_id = $row->s_id;
	$s_jv_rel_id = $row->s_jv_rel_id;
	
	if($row->s_st_date == null || $row->s_st_date == "0000-00-00")
		$s_st_date = "";
	else
		$s_st_date = date('Y年m月d日', strtotime($row->s_st_date));

	$s_shime_date = $row->s_shime_date;
	if($s_shime_date == '0000-00-00')
		$s_shime_date = '';
	else
		$s_shime_date = date('Y年m月d日',strtotime($row->s_shime_date));

	$fsize = "size4";
	$seko = $row->seko;
	$len = mb_strlen($seko,"UTF-8");
	if($len > 10) {
		$fsize = "size2";
	}
	if($len > 14) {
		$fsize = "size2";
	}

	$moto = $row->moto;
	
	if($row->s_is_jv) {
		$jv_member = "";
		$sql_seko = "SELECT * FROM matsushima_jv_rel
					INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_jv_rel.jv_seko_id
					WHERE jv_slip_id = '{$s_jv_rel_id}'";
		$query_seko = mysql_query($sql_seko);
		while ($row_seko = mysql_fetch_object($query_seko)) {
			$jv_member .= $row_seko->seko_nik;
			$jv_member .= ",";
		}
		$jv_member = preg_replace('/,$/','',$jv_member);

		$genba = mb_strimwidth($row->g_genba."(JV:".$jv_member .")", 0, 60, "", "UTF-8");
	}
	else {
		$genba = mb_strimwidth($row->g_genba, 0, 60, "", "UTF-8");
	}
	
	$g_genba_address = $row->g_genba_address;
	
	$kouji = $row->sy_name;
	
	$hattyu = $row->s_hattyu;

	$staxper = 10;

	$wotax = ceil($hattyu / (1 + $staxper/100));
	$stax = $hattyu - $wotax;

	if($hattyu) {
		$hattyu = "税込&nbsp;￥" . number_format($hattyu);
		//$hattyu_tax = "税抜&nbsp;￥" . number_format($wotax)."&nbsp;&nbsp;消費税({$staxper}%)&nbsp;￥" . number_format($stax);
		$hattyu_tax = '<table class="size8pt"><tr><td width="350">税抜　￥' . number_format($wotax).'</td><td width="150">消費税('.$staxper.'%) ￥' . number_format($stax) . '</td></tr></table>';
	} else {
		$hattyu = "";
		$hattyu_tax = "";
	}
	$biko = $row->s_biko;
	$tantou = $row->t_tantou;
	
	//工事日
	
	if(($row->s_st_date != "" && $row->s_st_date != null && $row->s_st_date != "0000-00-00") && ($row->s_end_date != "" && $row->s_end_date != null && $row->s_end_date != "0000-00-00") && ($row->s_st_date != $row->s_end_date))
		$kouji_kikan = date('m月d日',strtotime($row->s_st_date)) . " ～ " . date('m月d日',strtotime($row->s_end_date));
	else if(($row->s_st_date != "" && $row->s_st_date != null && $row->s_st_date != "0000-00-00") && (($row->s_end_date == "" || $row->s_end_date == null || $row->s_end_date == "0000-00-00") || ($row->s_st_date == $row->s_end_date)))
		$kouji_kikan = date('m月d日',strtotime($row->s_st_date));
	else
		$kouji_kikan = "";
	

$ONEPAGE = 28;
$FIRST_PAGE = 31;
$MID_PAGE = 42;
$END_PAGE = 39;
$cnt = 0;

$page_total = 1;

// -----------------------------------------------------------------------------

$css = <<<EOD
<style type="text/css">

table {
	margin:0;
	padding:0;
	font-size:12pt;
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
.wakutd {
	border-top: 1px dashed #000;
}
.wakur {
	border-right: 1px solid #000;
}
.wakul {
	border-left: 1px solid #000;
}

.wakub2 {
	border-bottom: 2px solid #000;
}
.wakut2 {
	border-top: 2px solid #000;
}
.wakur2 {
	border-right: 2px solid #000;
}
.wakul2 {
	border-left: 2px solid #000;
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

.bgkuro {
	color: #FFF;
	background-color: #333;
}
.size00 {
	font-size:1pt;
}
.size8pt {
	font-size:8pt;
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
	font-size:22pt;
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

$tbl = $css;
$tbl .= <<<EOD

<table width="640" border="0" cellpadding="3">
	<tr>
		<td width="110" class="size0"></td>
		<td width="200" class="size0"></td>
		<td width="50" class="size0"></td>
		<td width="90" class="size0"></td>
		<td width="100" class="size0"></td>
		<td width="70" class="size0 tar"></td>
	</tr>
	<tr>
		<td colspan="6" class="tar">No.{$g_id}-{$s_id}<br />{$s_st_date}</td>
	</tr>
	<tr>
		<td colspan="6" class="tac size5">発　注　書</td>
	</tr>
	<tr>
		<td colspan="6"></td>
	</tr>
	<tr>
		<td colspan="2" class="tac {$fsize} wakub">{$seko}&nbsp;御中</td>
		<td colspan="4"></td>
	</tr>
	<tr>
		<td colspan="3"><br /><br /></td>
		<td colspan="3" >
						<table width="280" border="0" cellpadding="2">
							<tr>
								<td class="size14">&nbsp;</td>
							</tr>
							<tr>
								<td class="size4" width="180">{$COMPANY_NAME}</td>
								<td rowspan="7" width="101"><img src="../img/granz.jpg" width="101px" height="100px"></td>
							</tr>
							<tr>
								<td>代表取締役&nbsp;{$COMPANY_CEO}</td>
							</tr>
							<tr>
								<td class="size0">{$my_invoice_no}</td>
							</tr>
							<tr>
								<td>〒{$COMPANY_POSTAL}</td>
							</tr>
							<tr>
								<td>{$COMPANY_ADDRESS}</td>
							</tr>
							<tr>
								<td>TEL&nbsp;{$COMPANY_TEL}</td>
							</tr>
							<tr>
								<td>FAX&nbsp;{$COMPANY_FAX}</td>
							</tr>
							<tr>
								<td colspan="2">担当者&nbsp;{$tantou}&nbsp;{$t_tel}</td>
							</tr>
						</table>
		
		</td>
	</tr>
</table>
EOD;

//最終ページの最終行
$cdate = date("00y0mdHi");

$tbl .= <<<EOD
<table width="640" border="0" cellpadding="5">
	<tr>
		<td width="100" class="size00"></td>
		<td width="160" class="size00"></td>
		<td width="60" class="size00"></td>
		<td width="20" class="size00"></td>
		<td width="300" class="size00"></td>
	</tr>
	<tr>
		<td colspan="5">下記の内容で発注致します。ご手配の程、宜しくお願い申し上げます。</td>
	</tr>
	<tr>
		<td class="waku tac">現場名</td>
		<td class="waku" colspan="4">{$genba}</td>
	</tr>
	<tr>
		<td class="waku tac">現場住所</td>
		<td class="waku" colspan="4">{$g_genba_address}</td>
	</tr>
	<tr>
		<td class="waku tac">工事内容</td>
		<td class="waku" colspan="4">{$nai1}</td>
	</tr>
	<tr>
		<td class="waku tac">工事名</td>
		<td class="waku" colspan="4">{$kouji}</td>
	</tr>
	<tr>
		<td class="waku tac">工事日</td>
		<td class="waku" colspan="4">{$kouji_kikan}</td>
	</tr>
	<tr>
		<td class="waku tac">注文者</td>
		<td class="waku" colspan="4">{$moto}</td>
	</tr>
	<tr>
		<td class="waku tac" rowspan="2">金額</td>
		<td class="wakur wakul wakut tac" colspan="4">{$hattyu}</td>
	</tr>
	<tr>
		<td class="wakur wakul wakub tar size8pt" colspan="4">{$hattyu_tax}</td>
	</tr>
	<tr>
		<td class="waku tac">備考<br /><br /></td>
		<td class="waku" colspan="4">{$biko}</td>
	</tr>
</table>

<table width="640" border="0" cellpadding="5">
	<tr>
		<td width="340" class="size0"></td>
		<td width="100" class="size0"></td>
		<td width="160" class="size0"></td>
		<td width="40" class="size0"></td>
	</tr>
	<tr>
		<td colspan="1" class=""></td>
		<td colspan="1" class="wakub tac">担当者：</td>
		<td colspan="1" class="wakub tac">{$tantou}</td>
		<td></td>
	</tr>
	<tr>
		<td colspan="4" class="size0"></td>
	</tr>
	<tr>
		<td colspan="1" class="wakutd">◎ご記入の上、FAXにて送信下さい。</td>
		<td colspan="3" class="wakutd">FAX：{$COMPANY_FAX}</td>
		<td></td>
	</tr>
</table>

<table width="640" border="0" cellpadding="5">
	<tr>
		<td width="60" class="size0"></td>
		<td width="60" class="size0"></td>
		<td width="400" class="size0"></td>
		<td width="60" class="size0"></td>
		<td width="60" class="size0"></td>
	</tr>
	<tr>
		<td colspan="5" class="size3 tac">発 注 請 書</td>
	</tr>
	<tr>
		<td colspan="5" class="wakut wakul wakur">ご注文確かに承りました</td>
	</tr>
	<tr>
		<td class="wakul"></td>
		<td colspan="4" class="wakur">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="4" class="wakul tar">{$s_st_date}</td>
		<td class="wakur"></td>
	</tr>
	<tr>
		<td class="wakul"></td>
		<td colspan="1" class="wakub">請負者</td>
		<td colspan="1" class="wakub tac">{$seko}</td>
		<td colspan="1" class="wakub tac">印</td>
		<td class="wakur"></td>
	</tr>
	<tr>
		<td colspan="5" class="wakul wakub wakur size00"></td>
	</tr>
    <tr>  
		<td colspan="5" class="">{$cdate}</td>
    </tr>  

</table>

EOD;

$pdf->AddPage();
$pdf->writeHTML($tbl, true, false, false, false, '');


} //END of SQL Loop

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_048.pdf', 'I');

//============================================================+
// END OF FILE                                                
//============================================================+

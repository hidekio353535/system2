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
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT); 
$pdf->SetMargins(15, 10, 15); 
 
//set auto page breaks 
$pdf->SetAutoPageBreak(FALSE, 15); 
 
//set image scale factor 
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
 
//set some language-dependent strings 
$pdf->setLanguageArray($l); 

$pdf->setFontSubsetting(false);

// set font
$pdf->SetFont('kozgopromedium', '', 14);

$pdf->SetCellPadding(0); 
$tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0))); 
$pdf->setHtmlVSpace($tagvs); 
$pdf->setCellHeightRatio(1.25); 

//PDF表示かローカル保存かのパラメータ
if(isset($_REQUEST['mail'])) {
	$save_str = "F";
} else {
	$save_str = "I";
}

//idの受け取り
if(isset($_REQUEST['sid'])) {
	$sid = $_REQUEST['sid'];
} else {
	//パラメーター無しの場合はエラーメッセージで終了
	$tbl = "<p>パラメータが指定されていません</p>";
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');
	$pdf->Output('example_048.pdf', 'I');
	exit();	
}

// -----------------------------------------------------------------------------
//データ取得
$sql = "SELECT * FROM matsushima_slip_jv
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_jv.s_genba_id
				INNER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
				LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_jv.s_seko_kubun_id
				WHERE s_id in ({$sid})
				";
$query = @mysql_query($sql);
if(!$query) {
	$tbl = "<p>SQLが不正です</p>";
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');
	$pdf->Output('example_048.pdf', 'I');
	exit();	
}
else {
	$num = @mysql_num_rows($query);
}
if($num) {
while ($row = mysql_fetch_object($query)) {

	$g_id = $row->g_id;
	
	$moto = $row->moto;
	$atena = $row->atena;
	$g_genba = $row->g_genba;
	$g_genba_address = $row->g_genba_address;
	
	if($row->s_st_date == "0000-00-00" || $row->s_st_date == "" || $row->s_st_date == "null")
		$s_st_date = "";	
	else
		$s_st_date = date('Y年m月d日',strtotime($row->s_st_date));

	if($row->s_end_date == "0000-00-00" || $row->s_end_date == "" || $row->s_end_date == "null")
		$s_end_date = "";	
	else
		$s_end_date = date('Y年m月d日',strtotime($row->s_end_date));
	
	$kouji_date = $s_st_date;
	if($s_end_date) {
		$kouji_date .= " 〜 ".$s_end_date;
	}

	$s_id = $row->s_id;
	$s_tax = $row->s_tax;
	$s_biko = $row->s_biko;
	$tantou = $row->t_tantou;
	$t_tel = $row->t_tel;
	$sy_name = $row->sy_name;

	//JV施工業者
	$jv_seko = array();
	$jv_atena = "";
	$sql_seko = "SELECT * FROM matsushima_jv_rel
				INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_jv_rel.jv_seko_id
				WHERE jv_slip_id = '{$s_id}'";
	$query_seko = mysql_query($sql_seko);
	if(!$query_seko) {
		$tbl = "<p>SQLが不正です</p>";
		$pdf->AddPage();
		$pdf->writeHTML($tbl, true, false, false, false, '');
		$pdf->Output('example_048.pdf', 'I');
		exit();	
	}
	$num_seko = mysql_num_rows($query_seko);
	
	if($num_seko) {
		while ($row_seko = mysql_fetch_object($query_seko)) {
			$jv_seko[] = $row_seko->seko_nik;
			$jv_atena .= $row_seko->seko_nik . "・";
		}
		$jv_atena = preg_replace('/・$/','',$jv_atena);
	}

	//DBからフォーム情報取得
	$m1 = array();
	$m2 = array();
	$m3 = array();
	$m4 = array();
	$m5 = array();
	$m6 = array();
	
	$cnt = 0;
	
	$shokei = 0;
	$total = 0;
	
	$m1[$cnt] = $row->sy_name_nik;
	$m2[$cnt] = "1";
	$m3[$cnt] = "";
	$m4[$cnt] = number_format($row->s_hattyu);
	$m5[$cnt] = number_format($row->s_hattyu);
	$m6[$cnt] = "";

	$shokei = $row->s_hattyu;
		
	//値引きとマイナスの処理
	if(	preg_match('/値引/',$row_s->m_meisho)) {
		$m2[$cnt] = "";
		$m3[$cnt] = "";
		$m4[$cnt] = "";
	}
	if($row->s_hattyu < 0)
		$m5[$cnt] = preg_replace('/\-/','▲',$m5[$cnt]);
	
/*
	if($s_tax) {
		$total = floor($shokei * (1+$s_tax));
		$stax = $total - $shokei;
		
		$total = "￥".number_format($total);
		$shokei = number_format($shokei);
		$stax = number_format($stax);
	}
	else {
		$total = $shokei;
		$stax = "税込";
		$total = "￥".number_format($total);
		$shokei = number_format($shokei);
	}
*/	

	$total = $shokei;
	$stax = "税込";
	$total = "￥".number_format($total);
	$shokei = number_format($shokei);

	$ONEPAGE 	= 5;
	$FIRST_PAGE = 15;
	$MID_PAGE 	= 20;
	$END_PAGE 	= 20;
	
	//ページ計算
	if($cnt <= $ONEPAGE)
		$page_total = 1;
	else if($cnt <= $FIRST_PAGE + $END_PAGE)
		$page_total = 2;
	else
		$page_total = ceil(($cnt - $FIRST_PAGE - $END_PAGE) / $MID_PAGE) + 2;
	
	$cnt = 0;
	
	//css
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
		.waku-t {
			border-top: 1px solid #000;
		}
		.waku-l {
			border-left: 1px solid #000;
		}
		.waku-r {
			border-right: 1px solid #000;
		}
		.waku-b {
			border-bottom: 1px solid #000;
		}
	
		.waku2 {
			border: 2px solid #000;
		}
		.waku2-t {
			border-top: 2px solid #000;
		}
		.waku2-l {
			border-left: 2px solid #000;
		}
		.waku2-r {
			border-right: 2px solid #000;
		}
		.waku2-b {
			border-bottom: 2px solid #000;
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
		
		.bgkuro {
			color: #FFF;
			background-color: #333;
		}
		
		.bggray {
			background-color:#DDD
		}

		.size00 {
			font-size:1pt;
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
		
		.size0 {
			font-size:1pt;
		}
		.size8 {
			font-size:8pt;
		}
		.size9 {
			font-size:9pt;
		}
		.size10 {
			font-size:10pt;
		}
		.size11 {
			font-size:11pt;
		}
		.size12 {
			font-size:12pt;
		}
		.size13 {
			font-size:13pt;
		}
		.size14 {
			font-size:14pt;
		}
		.size15 {
			font-size:15pt;
		}
		.size16 {
			font-size:16pt;
		}
		.size17 {
			font-size:17pt;
		}
		.size18 {
			font-size:18pt;
		}
		.size20 {
			font-size:20pt;
		}
		.size22 {
			font-size:22pt;
		}
		.size24 {
			font-size:24pt;
		}
		.tac {
			text-align:center;
		}
		.tal {
			text-align:left;
		}
		.tar {
			text-align:right;
		}
		
		.uline {
			border-bottom:solid 1px #000;
		}
		
		.space {
		}
	</style>
EOD;
	
	// -----------------------------------------------------------------------------
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
	
		//一ページのみの表示
		if($page == 1) {
			$tbl = $css;
			$tbl .= <<<EOD
			<table width="640" border="0" cellpadding="1">
				<tr>
					<td class="tar">No.{$g_id}-{$s_id}</td>
				</tr>
				<tr>
					<td class="tar">{$s_st_date}</td>
				</tr>
				<tr>
					<td class="tac size22">発注書・作業日報&nbsp;</td>
				</tr>
				<tr>
					<td class="tac size22">&nbsp;</td>
				</tr>
			</table>
			
			<table width="640" border="0" cellpadding="3">
				<tr>
					<td class="" width="370">
	
						<table width="300" border="0" cellpadding="3">
							<tr>
								<td width="60" class="waku-b tal">施工者</td>
								<td width="240" class="waku-b tac">{$jv_atena}</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td class="waku-b tal">得意先名</td>
								<td class="waku-b tac">{$moto}</td>
							</tr>
							<tr>
								<td class="waku-b tal">現場名</td>
								<td class="waku-b tac">{$g_genba}</td>
							</tr>
							<tr>
								<td class="waku-b tal">現場住所</td>
								<td class="waku-b tac">{$g_genba_address}</td>
							</tr>
							<tr>
								<td class="waku-b tal">工事区分</td>
								<td class="waku-b tac">{$sy_name}</td>
							</tr>
							<tr>
								<td class="waku-b tal">工事日</td>
								<td class="waku-b tac">{$kouji_date}</td>
							</tr>
						</table>
					
					</td>
	
					<td class="" width="40">
					</td>
	
					<td class="" width="230">
	
						<table width="230" border="0" cellpadding="2">
							<tr>
								<td class="size14">&nbsp;</td>
							</tr>
							<tr>
								<td class="size13" width="129">{$COMPANY_NAME}</td>
								<td rowspan="6" width="101"><img src="../img/granz.jpg" width="101px" height="100px"></td>
							</tr>
							<tr>
								<td class="size9">代表取締役&nbsp;{$COMPANY_CEO}</td>
							</tr>
							<tr>
								<td class="size9">〒{$COMPANY_POSTAL}</td>
							</tr>
							<tr>
								<td class="size9">{$COMPANY_ADDRESS}</td>
							</tr>
							<tr>
								<td class="size9">TEL&nbsp;{$COMPANY_TEL}</td>
							</tr>
							<tr>
								<td class="size9">FAX&nbsp;{$COMPANY_FAX}</td>
							</tr>
							<tr>
								<td colspan="2" class="size9">担当者&nbsp;{$tantou}&nbsp;{$t_tel}</td>
							</tr>
						</table>
						
					</td>
				</tr>
				<tr>
					<td colspan="1"></td>
					<td colspan="1">&nbsp;</td>
					<td colspan="1" class="tar">
					
						<table width="200" border="0" cellpadding="2">
							<tr>
								<td>&nbsp;<br />&nbsp;</td>
								<td>&nbsp;<br />&nbsp;</td>
								<td class="waku">&nbsp;<br />&nbsp;</td>
								<td class="waku">&nbsp;<br />&nbsp;</td>
							</tr>
						</table>
					
					</td>
				</tr>
			</table>
			&nbsp;<br />

EOD;
	
		} else {
			$tbl = $css;
		}
	
		//タイトルを表示するか
		$tbl .= <<<EOD
		<table width="640" border="0" cellpadding="2">
			<tr>
				<td colspan="3" width="450" class="waku bgkuro tac">発注詳細</td>
				<td colspan="1" width="190" class="waku bgkuro tac">出来高金額(税込)</td>
			</tr>
EOD;
		
		for($i = 0;$i < 1;$i++) {
			$tbl .= <<<EOD
				<tr>
					<td colspan="3" class="waku tal">{$m1[$cnt]}</td>
					<td colspan="1" class="waku tar">{$m5[$cnt]}</td>
				</tr>
EOD;
			$cnt++;
		}
	
		//最終ページ以外
		if($page != $page_total) {
			//ページ番号を振る
			if($page_total != 1 && $page != $page_total) {
				$tbl .= <<<EOD
					<tr>
						<td colspan="4" class="tar">Page: {$page} / {$page_total}</td>
					</tr>
		</table>
EOD;
			}
	
			$pdf->AddPage();
			$pdf->writeHTML($tbl, true, false, false, false, '');
		}
	} // $page Loop
	
EOD;
	
	//最後のページに付加する
		$tbl .= <<<EOD
	
			<tr>
				<td colspan="3" class="waku tar">合計金額(税込)&nbsp;</td>
				<td colspan="1" class="waku tar">{$total}</td>
			</tr>
			<tr>
				<td colspan="4" class="waku" height="70">
					備考<br />
					&nbsp;<br />
					<span style="font-size:10pt">{$s_biko}</span>
				
				</td>
			</tr>

			<tr>
				<td width = "200" colspan="1" class="waku tac bggray"></td>
				<td width = "440"  colspan="3" class="waku tar bggray">個人別出来高記入欄</td>
			</tr>
			<tr>
				<td width = "200" colspan="1" class="waku-l tac bggray"></td>
				<td width = "250" colspan="1" class="waku tac bggray">職方名</td>
				<td width = "190" colspan="1" class="waku tac bggray">金額(税込)</td>
			</tr>
EOD;

		if($num_seko < 4)
			$num_seko = 4;

		for($c=0;$c<$num_seko;$c++) {
		$tbl .= <<<EOD
			<tr>
				<td width = "200" colspan="1" class="waku-l tac bggray"></td>
				<td width = "250" colspan="1" class="waku tac bggray">{$jv_seko[$c]}</td>
				<td width = "190" colspan="1" class="waku tac bggray"></td>
			</tr>
EOD;
		}

		$tbl .= <<<EOD
			<tr>
				<td width = "200" colspan="1" class="waku-l waku-b tac bggray"></td>
				<td width = "250"  colspan="2" class="waku tar bggray">合計金額(税込)</td>
				<td width = "190"  colspan="1" class="waku tar bggray">{$total}</td>
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
		<td colspan="1" class="wakub tac">{$jv_atena}</td>
		<td colspan="1" class="wakub tac">印</td>
		<td class="wakur"></td>
	</tr>
	<tr>
		<td colspan="5" class="wakul wakub wakur size00"></td>
	</tr>

			<tr>
				<td colspan="5" class="tar">Page: {$page_total} / {$page_total}</td>
			</tr>

</table>

EOD;
	
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');

}
}
//Close and output PDF document
$pdf->Output('invoice.pdf', $save_str);


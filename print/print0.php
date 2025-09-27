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
$pdf->SetMargins(15, 15, 15); 
 
//set auto page breaks 
$pdf->SetAutoPageBreak(FALSE, 15); 
 
//set image scale factor 
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
 
//set some language-dependent strings 
$pdf->setLanguageArray($l); 

$pdf->setFontSubsetting(false);

// set font
$pdf->SetFont('kozminproregular', '', 14);

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

$my_invoice_no = "";
if($COMPANY_INVOICE_NO) {
	$my_invoice_no = "登録番号 ".$COMPANY_INVOICE_NO;
}

// -----------------------------------------------------------------------------
//データ取得
$sql = "SELECT * FROM matsushima_slip_est
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_est.s_genba_id
				INNER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
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
	$atena = $row->g_moto_tantou;
	$g_genba = $row->g_genba;
	$g_genba_address = $row->g_genba_address;
	
	$s_date = $row->s_date;
	if($s_date == '0000-00-00')
		$s_date = '';
	else
		$s_date = date('Y年m月d日',strtotime($row->s_date));
	
	$s_id = $row->s_id;
	$s_tax = $row->s_tax;
	$s_biko = $row->s_biko;
	$tantou = $row->t_tantou;
	$t_tel = $row->t_tel;
    
    if($s_biko) {
        $s_biko .= "<br />発行日より3ヶ月有効";
    }
    else {
        $s_biko .= "発行日より3ヶ月有効";
    }
	
	//消費税修正
	//税率表示
	if($s_tax) {
		$show_tax = "(". ($s_tax*100) ."％)";
	}
	else {
		$show_tax = "";
	}

	//DBからフォーム情報取得
	$m1 = array();
	$m2 = array();
	$m3 = array();
	$m4 = array();
	$m5 = array();
	$m6 = array();
	
	$sql_s = "SELECT * FROM matsushima_meisai_est WHERE m_s_id = {$row->s_id} ORDER BY sorder";
	$query_s = mysql_query($sql_s);
	if(!$query_s) {
		$tbl = "<p>SQLが不正です</p>";
		$pdf->AddPage();
		$pdf->writeHTML($tbl, true, false, false, false, '');
		$pdf->Output('example_048.pdf', 'I');
		exit();	
	}
	$num_s = mysql_num_rows($query_s);
	$cnt = 0;
	
	$shokei = 0;
	$total = 0;
	
	$kaigyo1 = array();
	$kaigyo2 = array();
	
	if($num_s) {
		while ($row_s = mysql_fetch_object($query_s)) {
		
			$m1[$cnt] = $row_s->m_meisho;
			$m2[$cnt] = $row_s->m_kazu;
			$m3[$cnt] = $row_s->m_unit;
			if($row_s->m_unit == "式" && false)
				$m4[$cnt] = "";
			else
				$m4[$cnt] = number_format($row_s->m_tanka);

			$m5[$cnt] = number_format($row_s->m_kingaku);
			$m6[$cnt] = $row_s->m_biko;

			if($row_s->m_kingaku != "" && is_numeric($row_s->m_kingaku))
				$shokei += $row_s->m_kingaku;
				
			//値引きとマイナスの処理
			if(	preg_match('/値引|端数|調整$/',$row_s->m_meisho)) {
				$m2[$cnt] = "";
				$m3[$cnt] = "";
				$m4[$cnt] = "";
			}
			if($row_s->m_kingaku < 0)
				$m5[$cnt] = preg_replace('/\-/','▲',$m5[$cnt]);
			
			
			//改行制御
			$kaigyo1[$cnt] = array();
			$kaigyo1[$cnt]['line_num'] = $cnt;
			$line = ceil(mb_strwidth($m1[$cnt]) / 44);
			if(!$line || $line <=0) {
				$line = 1;
			}
			$kaigyo1[$cnt]['line_cnt'] = $line;

			$kaigyo2[$cnt] = array();
			$kaigyo2[$cnt]['line_num'] = $cnt;
			$line = ceil(mb_strwidth($m6[$cnt]) / 50);
			if(!$line || $line <=0) {
				$line = 1;
			}
			$kaigyo2[$cnt]['line_cnt'] = $line;
			
				
			$cnt++;
		}
	}
	
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

	
	$ONEPAGE 	= 20; //20
	$FIRST_PAGE = 25; //25
	$MID_PAGE 	= 38; //38
	$END_PAGE 	= 30; //30

	//改行制御
	$line_count = 0;
	$add_line_count = 0;
	for($k=0;$k < count($kaigyo1);$k++) {
		
		if( $kaigyo1[$k]['line_cnt'] >= $kaigyo2[$k]['line_cnt']) {
			$line_count += $kaigyo1[$k]['line_cnt'];
			$add_line_count += $kaigyo1[$k]['line_cnt'] - 1;
		}
		else {
			$line_count += $kaigyo2[$k]['line_cnt'];
			$add_line_count += $kaigyo2[$k]['line_cnt'] - 1;
		}
		
		if(count($kaigyo1) <= $ONEPAGE && $line_count >= $ONEPAGE) {
			$start_k = $k;
			break;
		}
		else if($line_count >= $FIRST_PAGE) {
			$start_k = $k;
			break;
		}
		else {
			$start_k = $k;
		}
	}
	if( $add_line_count >= 2) {
		$ONEPAGE -= $add_line_count;
		$FIRST_PAGE -= $add_line_count;
	}

	$add_line_count = 0;
	for($k=$start_k;$k < count($kaigyo1);$k++) {
		
		if( $kaigyo1[$k]['line_cnt'] >= $kaigyo2[$k]['line_cnt']) {
			$add_line_count += $kaigyo1[$k]['line_cnt'] - 1;
		}
		else {
			$add_line_count += $kaigyo2[$k]['line_cnt'] - 1;
		}
		
	}
	if( $add_line_count >= 2) {
		$END_PAGE -= $add_line_count;
		$MID_PAGE -= $add_line_count;
	}

	
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
			font-size:9pt;
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
		
		.bgkuro {
			color: #FFF;
			background-color: #333;
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
					<td class="tar">{$s_date}</td>
				</tr>
				<tr>
					<td class="tac size22">御&nbsp;見&nbsp;積&nbsp;書&nbsp;&nbsp;&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td class="tac">&nbsp;</td>
				</tr>
			</table>
			
			<table width="640" border="0" cellpadding="3">
				<tr>
					<td class="" width="370">
	
						<table width="370" border="0" cellpadding="3">
							<tr>
								<td width="250" class="waku-b size16 tac">{$moto}</td>
								<td width="50" class="waku-b size16 tac">御中</td>
							</tr>
							<tr>
								<td class="waku-b size16 tac">{$atena}</td>
								<td class="waku-b size16 tac">様</td>
							</tr>
						</table>
						<table width="300" border="0" cellpadding="3">
							<tr>
								<td width="70">&nbsp;</td>
								<td width="230">&nbsp;</td>
							</tr>
							<tr>
								<td>現場名</td>
								<td>{$g_genba}</td>
							</tr>
							<tr>
								<td>現場住所</td>
								<td>{$g_genba_address}</td>
							</tr>
							<tr>
								<td colspan="2" class="waku-b">
								&nbsp;<br />
								下記の通り御見積り致します。<br />
								&nbsp;<br />
									<table style="font-size:14pt;">
										<tr>
										<td width="160">御見積金額(税込)：</td>
										<td width="140" class="tac">{$total}</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					
					</td>
	
					<td class="" width="10">
					</td>
	
					<td class="" width="260">
	
						<table width="260" border="0" cellpadding="2">
							<tr>
								<td class="size14">&nbsp;</td>
							</tr>
							<tr>
								<td class="size13" width="159">{$COMPANY_NAME}</td>
								<td rowspan="6" width="101"><img src="../img/granz.jpg" width="101px" height="100px"></td>
							</tr>
							<tr>
								<td>{$my_invoice_no}</td>
							</tr>
							<tr>
								<td>代表取締役&nbsp;{$COMPANY_CEO}</td>
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
						&nbsp;<br />
						<table width="220" border="0" cellpadding="2">
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
		<table width="640" border="0" cellpadding="4">
			<tr>
				<td width="180" class="waku bgkuro tac">名称</td>
				<td width="60" class="waku bgkuro tac">数量</td>
				<td width="40" class="waku bgkuro tac">単位</td>
				<td width="70" class="waku bgkuro tac">単価</td>
				<td width="90" class="waku bgkuro tac">金額</td>
				<td width="200" class="waku bgkuro tac">備考</td>
			</tr>
EOD;
		
		
		for($i = 0;$i < $max;$i++) {

			$tbl .= <<<EOD
				<tr>
					<td class="waku tal">{$m1[$cnt]}</td>
					<td class="waku tar">{$m2[$cnt]}</td>
					<td class="waku tac">{$m3[$cnt]}</td>
					<td class="waku tar">{$m4[$cnt]}</td>
					<td class="waku tar">{$m5[$cnt]}</td>
					<td class="waku tal">{$m6[$cnt]}</td>
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
						<td colspan="6" class="tar">Page: {$page} / {$page_total}</td>
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
        $cdate = date("00y0mdHi");
		$tbl .= <<<EOD
	
			<tr>
				<td colspan="1" class="waku" rowspan="3">&nbsp;</td>
				<td colspan="3" class="waku tac">小計</td>
				<td colspan="2" class="waku tar">{$shokei}</td>
			</tr>
			<tr>
				<td colspan="3" class="waku tac">消費税{$show_tax}</td>
				<td colspan="2" class="waku tar">{$stax}</td>
			</tr>
			<tr>
				<td colspan="3" class="waku2 tac">御見積金額</td>
				<td colspan="2" class="waku2 tar">{$total}</td>
			</tr>
			<tr>
				<td colspan="6" class="waku" height="100">
					※返却時、欠損、欠品・曲がり・修理等は別途請求させて頂きます。<br />
					※積雪、暴風雨時等、倒壊防止の為、ネットシートの取り外しをお願い致します。<br />
					&nbsp;<br />
					<span style="font-size:12pt">{$s_biko}</span>
				</td>
			</tr>
	
			<tr>
                <td>{$cdate}</td>
				<td colspan="5" class="tar">Page: {$page_total} / {$page_total}</td>
			</tr>
		</table>
EOD;
	
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');

}
}
//Close and output PDF document

if(isset($dl_flag)) {
    $pdf->Output('見積書.pdf', $save_str);
}
else {
    $pdf_data = $pdf->Output('estimate.pdf', "S");
    session_start();
    $_SESSION["pdf"] = $pdf_data; //セッションに退避
    $_SESSION["moto"] = $moto;
    $_SESSION["address"] = $g_genba_address;
    $_SESSION["genba"] = $g_genba;
    $_SESSION["g_id"] = $g_id;
}
header("Location:pdf.php"); //ここでXXX.pdfみたいにファイル名を記述


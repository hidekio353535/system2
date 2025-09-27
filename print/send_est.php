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
			if(	preg_match('/値引|端数|調整/',$row_s->m_meisho)) {
				$m2[$cnt] = "";
				$m3[$cnt] = "";
				$m4[$cnt] = "";
			}
			if($row_s->m_kingaku < 0)
				$m5[$cnt] = preg_replace('/\-/','▲',$m5[$cnt]);
				
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

	
	$ONEPAGE 	= 20;
	$FIRST_PAGE = 25;
	$MID_PAGE 	= 38;
	$END_PAGE 	= 30;
	
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
						
					</td>
				</tr>
				<tr>
					<td colspan="1">下記の通り御見積り致します。</td>
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
		<table width="640" border="0" cellpadding="4">
			<tr>
				<td width="250" class="waku bgkuro tac">名称</td>
				<td width="60" class="waku bgkuro tac">数量</td>
				<td width="70" class="waku bgkuro tac">単位</td>
				<td width="70" class="waku bgkuro tac">単価</td>
				<td width="90" class="waku bgkuro tac">金額</td>
				<td width="100" class="waku bgkuro tac">備考</td>
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
		$tbl .= <<<EOD
	
			<tr>
				<td colspan="1" class="waku" rowspan="3">&nbsp;</td>
				<td colspan="3" class="waku tac">小計</td>
				<td colspan="2" class="waku tar">{$shokei}</td>
			</tr>
			<tr>
				<td colspan="3" class="waku tac">消費税</td>
				<td colspan="2" class="waku tar">{$stax}</td>
			</tr>
			<tr>
				<td colspan="3" class="waku2 tac">御見積金額</td>
				<td colspan="2" class="waku2 tar">{$total}</td>
			</tr>
			<tr>
				<td colspan="6" class="waku" height="100">
					※返却時、欠損、欠品・曲がり・修理等は別途請求させて頂きます。<br />
					&nbsp;<br />
					<span style="font-size:12pt">{$s_biko}</span>
				
				</td>
			</tr>
	
			<tr>
				<td colspan="6" class="tar">Page: {$page_total} / {$page_total}</td>
			</tr>
		</table>
EOD;
	
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');

}
}
//Close and output PDF document
$pdf->Output('estimate.pdf', "F");

$mailTo      = 'ogawa@digitaling.jp';                   // 宛て先アドレス
$mailFrom    = 'ogawa@digitaling.jp';                   // 差出人のメールアドレス
$mailSubject = '添付ファイル付きメール';               // メールのタイトル
$mailMessage = '添付ファイル付きメールのテストです。'; // メール本文
$fileName    = 'estimate.pdf';                               // 添付するファイル
$returnMail  = 'ogawa@digitaling.jp';                   // Return-Pathに指定するメールアドレス

# メールで日本語使用するための設定をします。
mb_language("Ja") ;
mb_internal_encoding("UTF-8");

$header  = "From: $mailFrom\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"__PHPRECIPE__\"\r\n";
$header .= "\r\n";

$body  = "--__PHPRECIPE__\r\n";
$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\r\n";
$body .= "\r\n";
$body .= $mailMessage . "\r\n";
$body .= "--__PHPRECIPE__\r\n";

# 添付ファイルへの処理をします。
$handle = fopen($fileName, 'r');
$attachFile = fread($handle, filesize($fileName));
fclose($handle);
$attachEncode = base64_encode($attachFile);

$body .= "Content-Type: image/jpeg; name=\"$fileName\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
$body .= "\r\n";
$body .= chunk_split($attachEncode) . "\r\n";
$body .= "--__PHPRECIPE__--\r\n";

# メールの送信と結果の判定をします。セーフモードがOnの場合は第5引数が使えません。
if (ini_get('safe_mode')) {
  $result = mb_send_mail($mailTo, $mailSubject, $body, $header);
} else {
  $result = mb_send_mail($mailTo, $mailSubject, $body, $header,
                         '-f' . $returnMail);
}

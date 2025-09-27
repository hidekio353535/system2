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

// ---------------------------------------------------------

$pdf->setFontSubsetting(false);

// set font
$pdf->SetFont('kozgopromedium', '', 14);

// -----------------------------------------------------------------------------
/* tips */
$pdf->SetCellPadding(0); 
$tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0))); 
$pdf->setHtmlVSpace($tagvs); 
$pdf->setCellHeightRatio(1.25); 
//$pdf->setImageScale(0.47); 

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
$sql = "SELECT * FROM matsushima_hat
				INNER JOIN matsushima_slip_hat ON matsushima_slip_hat.s_hat_id = matsushima_hat.h_id
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
				INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_slip_hat.s_seko_id
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
				WHERE h_id in ({$sid})
				GROUP BY h_id
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
while ($row = mysql_fetch_object($query)) {
	
	$seko = $row->seko;
	$atena = $row->atena;
	$g_genba = $row->g_genba;
	$g_genba_address = $row->g_genba_address;
	$h_hat_id = $row->h_hat_id;
	$h_id = $row->h_id;
	$h_biko = $row->h_biko;

	//$tantou = $row->t_tantou;
	//$t_tel = $row->t_tel;
	
	$tantou = "松島芳幸";
	$t_tel = "090-9019-8976";
	
	if($row->postal)
		$postal = "〒".$row->postal;
	else
		$postal = "";
		
	$address = $row->address;
	if($row->tel)
		$tel = "TEL&nbsp;".$row->tel;
	else
		$tel = "";
	if($row->fax)	
		$fax = "FAX&nbsp;".$row->fax;
	else
		$fax = "";
		
	$account = $row->account;
	$meigi = $row->meigi;
	
	$is_bankshow = $row->is_bankshow;
	
	if($row->h_hat_date == "0000-00-00" || $row->h_hat_date == "" || $row->h_hat_date == null)
		$h_hat_date = "";
	else	
		$h_hat_date = date('Y年m月d日',strtotime($row->h_hat_date));

	if($row->h_receipt_yotei_date == "0000-00-00" || $row->h_receipt_yotei_date == "" || $row->h_receipt_yotei_date == null)
		$h_receipt_yotei_date = "";
	else	
		$h_receipt_yotei_date = date('Y年m月d日',strtotime($row->h_receipt_yotei_date));
	
	if($h_receipt_yotei_date == "")
		$shiharai = "";
	else
		$shiharai = "<br />支払方法：月末〆 " . $h_receipt_yotei_date . " 支払い";
	
	
	$h_chosei_name = $row->h_chosei_name;
	$h_chosei = $row->h_chosei;

	//DBからフォーム情報取得
	$m1 = array();
	$m2 = array();
	$m3 = array();
	$m4 = array();
	$m5 = array();
	$m6 = array();
	$m7 = array();
	
	$sql_s = "SELECT * FROM matsushima_slip_hat 
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
				LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
				WHERE s_hat_id = {$row->h_id} ORDER BY s_st_date, s_id";
	$query_s = mysql_query($sql_s);
	if(!$query_s) {
		$tbl = "<p>SQLが不正です</p>";
		$pdf->AddPage();
		$pdf->writeHTML($tbl, true, false, false, false, '');
		$pdf->Output('example_048.pdf', 'I');
		exit();	
	}
	$num_s = mysql_num_rows($query_s);


/********************  発注書  **************************************************************/

	$cnt = 0;
	$shokei = 0;
	$total = 0;
	
	if($num_s) {
		while ($row_s = mysql_fetch_object($query_s)) {
		
			if($row_s->s_jv_rel_id) {
				$jvid = "-" . $row_s->s_jv_rel_id;
				$jvmem = "";

				//JV施工業者取得
				$sql_seko = "SELECT * FROM matsushima_jv_rel
							INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_jv_rel.jv_seko_id
							WHERE jv_slip_id = '{$row_s->s_jv_rel_id}'";
				$query_seko = @mysql_query($sql_seko);
				$num_seko = @mysql_num_rows($query_seko);
				if($num_seko) {
					while ($row_seko = mysql_fetch_object($query_seko)) {
						$jvmem .= mb_substr($row_seko->seko_nik, 0, 1,"UTF-8") . " ";
					}
				}
			}
			else {
				$jvid = "";
				$jvmem = "";
			}
		
			$m1[$cnt] = $row_s->g_id . "-". $row_s->s_id . $jvid;
			
			if($row_s->s_st_date == "0000-00-00" || $row_s->s_st_date == "" || $row_s->s_st_date == null)
				$k_date = "";
			else	
				$k_date = date('m/d',strtotime($row_s->s_st_date));

			$m2[$cnt] = $k_date;

			$len = mb_strlen($row_s->g_genba);
			if($len > 50) {
				$m3[$cnt] = '<span class="size7">'.$row_s->g_genba."</span>";
			}
			else {
				$m3[$cnt] = $row_s->g_genba;
			}
			
			$m4[$cnt] = $row_s->g_genba_address;
			$m5[$cnt] = $row_s->sy_name_nik;
			$m6[$cnt] = "￥".number_format($row_s->s_hattyu);
			$m7[$cnt] = $jvmem;

			if($row_s->s_hattyu != "" && is_numeric($row_s->s_hattyu))
				$shokei += $row_s->s_hattyu;
				
			if($row_s->s_hattyu < 0)
				$m6[$cnt] = preg_replace('/\-/','▲',$m6[$cnt]);

			$cnt++;
				
		}
	}
	
	if($h_chosei_name != "" && is_numeric($h_chosei) && $h_chosei != 0) {
		$total = $shokei + $h_chosei;
		$total = "￥".number_format($total);
		$shokei = "￥".number_format($shokei);
		$chosei_flag = true;
	}
	else {
		$total = $shokei;
		$total = "￥".number_format($total);
		$shokei = "￥".number_format($shokei);
		$chosei_flag = false;
	}

	if($h_chosei < 0 && is_numeric($h_chosei))
		$h_chosei = preg_replace('/\-/','▲',(string)$h_chosei);
	
	$ONEPAGE 	= 20;
	$FIRST_PAGE = 20;
	$MID_PAGE 	= 30;
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
		.size5 {
			font-size:5pt;
		}
		.size6 {
			font-size:6pt;
		}
		.size7 {
			font-size:7pt;
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
					<td class="tar">No.{$h_id}</td>
				</tr>
				<tr>
					<td class="tar">{$h_hat_date}</td>
				</tr>
				<tr>
					<td class="tac size22">発&nbsp;注&nbsp;書&nbsp;&nbsp;&nbsp;&nbsp;</td>
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
								<td width="250" class="waku-b size16 tac">{$seko}</td>
								<td width="50" class="waku-b size16 tac">様</td>
							</tr>
							<tr>
								<td class="size16 tac"></td>
								<td class="size16 tac"></td>
							</tr>
						</table>
						<table width="370" border="0" cellpadding="3">
							<tr>
								<td width="100">&nbsp;</td>
								<td width="200">&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td class="size14 waku-b tal" colspan="1">発注金額</td>
								<td class="size14 waku-b tac" colspan="1">{$total}－(税込)</td>
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
					<td colspan="2">
						下記の通り発注致します。
						{$shiharai}					
					</td>
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
				<td width="105" class="waku bgkuro tac">No.</td>
				<td width="50" class="waku bgkuro tac">工事日</td>
				<td width="140" class="waku bgkuro tac">現場名</td>
				<td width="175" class="waku bgkuro tac">現場住所</td>
				<td width="40" class="waku bgkuro tac">区分</td>
				<td width="65" class="waku bgkuro tac">金額(税込)</td>
				<td width="65" class="waku bgkuro tac">備考</td>
			</tr>
EOD;
		
		for($i = 0;$i < $max;$i++) {
			$tbl .= <<<EOD
				<tr>
					<td class="waku tac">{$m1[$cnt]}</td>
					<td class="waku tac">{$m2[$cnt]}</td>
					<td class="waku tac">{$m3[$cnt]}</td>
					<td class="waku tac">{$m4[$cnt]}</td>
					<td class="waku tac">{$m5[$cnt]}</td>
					<td class="waku tar">{$m6[$cnt]}</td>
					<td class="waku tal">{$m7[$cnt]}</td>
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
						<td colspan="7" class="tar">Page: {$page} / {$page_total}</td>
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
		if($chosei_flag) {
		$tbl .= <<<EOD
			<tr>
				<td colspan="4" class="waku tac">小計</td>
				<td colspan="2" class="waku tar">{$shokei}</td>
				<td colspan="1" class="waku"></td>
			</tr>
			<tr>
				<td colspan="4" class="waku tac">{$h_chosei_name}</td>
				<td colspan="2" class="waku tar">{$h_chosei}</td>
				<td colspan="1" class="waku"></td>
			</tr>
			<tr>
				<td colspan="4" class="waku2 tac">発注金額</td>
				<td colspan="2" class="waku2 tar">{$total}</td>
				<td colspan="1" class="waku"></td>
			</tr>
EOD;
		}
		else {
		$tbl .= <<<EOD
			<tr>
				<td colspan="4" class="waku2 tac">発注金額</td>
				<td colspan="2" class="waku2 tar">{$total}</td>
				<td colspan="1" class="waku"></td>
			</tr>
EOD;
		}

		$tbl .= <<<EOD
			<tr>
				<td colspan="7" class="tar">Page: {$page_total} / {$page_total}</td>
			</tr>
		</table>
EOD;
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');


/********************  請求書  **************************************************************/
	
	$cnt = 0;
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
					<td class="tar">No.{$h_id}</td>
				</tr>
				<tr>
					<td class="tar">{$h_hat_date}</td>
				</tr>
				<tr>
					<td class="tac size22">御&nbsp;請&nbsp;求&nbsp;書&nbsp;&nbsp;&nbsp;&nbsp;</td>
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
								<td width="250" class="waku-b size16 tac">{$COMPANY_NAME}</td>
								<td width="50" class="waku-b size16 tac">御中</td>
							</tr>
							<tr>
								<td class="waku-b size16 tac">{$COMPANY_CEO}</td>
								<td class="waku-b size16 tac">様</td>
							</tr>
						</table>
						<table width="370" border="0" cellpadding="3">
							<tr>
								<td width="100">&nbsp;</td>
								<td width="200">&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td class="size14 waku-b tal" colspan="1">御請求金額</td>
								<td class="size14 waku-b tac" colspan="1">{$total}－(税込)</td>
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
								<td class="size16" width="130">{$seko}</td>
								<td rowspan="5" width="100"><img src="../img/in1.jpg"></td>
							</tr>
							<tr>
								<td>{$postal}</td>
							</tr>
							<tr>
								<td>{$address}</td>
							</tr>
							<tr>
								<td>{$tel}</td>
							</tr>
							<tr>
								<td>{$fax}</td>
							</tr>
							<tr>
								<td colspan="2"></td>
							</tr>
						</table>
						
					</td>
				</tr>
				<tr>
					<td colspan="1">下記の通り御請求致します。</td>
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
				<td width="105" class="waku bgkuro tac">No.</td>
				<td width="50" class="waku bgkuro tac">開始日</td>
				<td width="140" class="waku bgkuro tac">現場名</td>
				<td width="175" class="waku bgkuro tac">現場住所</td>
				<td width="40" class="waku bgkuro tac">区分</td>
				<td width="65" class="waku bgkuro tac">金額(税込)</td>
				<td width="65" class="waku bgkuro tac">備考</td>
			</tr>
EOD;
		
		for($i = 0;$i < $max;$i++) {
			$tbl .= <<<EOD
				<tr>
					<td class="waku tac">{$m1[$cnt]}</td>
					<td class="waku tac">{$m2[$cnt]}</td>
					<td class="waku tac">{$m3[$cnt]}</td>
					<td class="waku tac">{$m4[$cnt]}</td>
					<td class="waku tac">{$m5[$cnt]}</td>
					<td class="waku tar">{$m6[$cnt]}</td>
					<td class="waku tal">{$m7[$cnt]}</td>
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
						<td colspan="7" class="tar">Page: {$page} / {$page_total}</td>
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
		if($chosei_flag) {
		$tbl .= <<<EOD
			<tr>
				<td colspan="4" class="waku tac">小計</td>
				<td colspan="2" class="waku tar">{$shokei}</td>
				<td colspan="1" class="waku"></td>
			</tr>
			<tr>
				<td colspan="4" class="waku tac">{$h_chosei_name}</td>
				<td colspan="2" class="waku tar">{$h_chosei}</td>
				<td colspan="1" class="waku"></td>
			</tr>
			<tr>
				<td colspan="4" class="waku2 tac">御請求金額</td>
				<td colspan="2" class="waku2 tar">{$total}</td>
				<td colspan="1" class="waku"></td>
			</tr>
EOD;
		}
		else {
		$tbl .= <<<EOD
			<tr>
				<td colspan="4" class="waku2 tac">御請求金額</td>
				<td colspan="2" class="waku2 tar">{$total}</td>
				<td colspan="1" class="waku"></td>
			</tr>
EOD;
		}

		if($is_bankshow) {
		$tbl .= <<<EOD
			<tr>
				<td colspan="7" class="waku tal">【お振込口座】{$account} {$meigi}</td>
			</tr>
EOD;
		}

		$tbl .= <<<EOD
			<tr>
				<td colspan="7" class="tar">Page: {$page_total} / {$page_total}</td>
			</tr>
		</table>
EOD;
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');

}

//Close and output PDF document
$pdf->Output('invoice.pdf', $save_str);

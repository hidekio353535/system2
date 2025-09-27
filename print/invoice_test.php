<?php
set_time_limit(180);
$pdf_flag = true;

require_once('../tcpdf/config/lang/eng.php');
require_once('../tcpdf/tcpdf.php');
require_once("../php/db_connect.php");
require_once("../php/company_info.php");
require_once("../tax_mod/tax.php");

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
$pdf->SetMargins(10, 15, 10); 
 
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

$my_invoice_no = "";
if($COMPANY_INVOICE_NO) {
	$my_invoice_no = "登録番号 ".$COMPANY_INVOICE_NO;
}

// -----------------------------------------------------------------------------
//データ取得
$sql = "SELECT * FROM matsushima_inv
				INNER JOIN matsushima_slip ON matsushima_slip.s_inv_id = matsushima_inv.i_id
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
				INNER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_moto.m_tantou_id
				WHERE i_id in ({$sid})
				GROUP BY i_id
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
	
	$branch = $row->branch;
	$g_id = $row->g_id;
	$moto = $row->moto." ".$branch;
	$g_moto_id = $row->g_moto_id;
	$atena = $row->atena;
	$g_genba = $row->g_genba;
	$g_genba_address = $row->g_genba_address;
	$i_inv_id = $row->i_inv_id;
	$i_id = $row->i_id;
	$i_biko = $row->i_biko;

	$tantou = $row->t_tantou;
	$t_tel = $row->t_tel;
	
	if($row->i_inv_date == "0000-00-00" || $row->i_inv_date == "" || $row->i_inv_date == null)
		$i_inv_date = "";
	else
		$i_inv_date = date('Y年m月d日',strtotime($row->i_inv_date));
	
	$i_chosei_name = $row->i_chosei_name;
	$i_chosei = $row->i_chosei;
    
    if($row->m_furikomi) {
        $m_furikomi = "<br />恐れ入りますが振込手数料はお客様の負担でお願いいたします。";
    }
    else {
        $m_furikomi = "";
    }

	//DBからフォーム情報取得
	$m1 = array();
	$m2 = array();
	$m3 = array();
	$m4 = array();
	$m5 = array();
	$m6 = array();

	$m8 = array();
	$m9 = array();
	$m10 = array();
	
	$sql_s = "SELECT * FROM matsushima_slip 
				INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
				LEFT OUTER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = matsushima_slip.s_seko_kubun_id
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
				LEFT OUTER JOIN matsushima_nai_1 ON matsushima_genba.g_nai1_id = matsushima_nai_1.nai1_id
				WHERE s_inv_id = {$row->i_id} ORDER BY s_st_date, s_id";
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
	$total_stax = "";
	$total_wo_stax = 0;
	
	if($num_s) {
		while ($row_s = mysql_fetch_object($query_s)) {
		
			$m1[$cnt] = $row_s->g_id ."-".$row_s->s_id;
			
			if($row_s->s_st_date == "0000-00-00" || $row_s->s_st_date == "" || $row_s->s_st_date == null)
				$s_st_date = $row_s->s_st_date;
			else
				$s_st_date = date('m/d',strtotime($row_s->s_st_date));
			
			$m2[$cnt] = $s_st_date;
			
			$m3[$cnt] = $row_s->g_genba;
			$m4[$cnt] = $row_s->g_genba_address;
			$m5[$cnt] = $row_s->sy_name_nik;
			$m6[$cnt] = "￥".number_format($row_s->s_invoice);

			if($row_s->s_tax) {
				$m8[$cnt] = ceil($row_s->s_invoice / (1 + $row_s->s_tax));
				$m9[$cnt] = $row_s->s_invoice - $m8[$cnt];
			}
			else {
				$m8[$cnt] = ceil($row_s->s_invoice / (1 + get_tax($row->i_inv_date) / 100));
				$m9[$cnt] = $row_s->s_invoice - $m8[$cnt];
			}

			if($row_s->s_invoice != "" && is_numeric($row_s->s_invoice)) {
				$shokei += $row_s->s_invoice;
				$total_wo_stax += $m8[$cnt];
				$total_stax += $m9[$cnt];	
			}

			$m8[$cnt] = "￥".number_format($m8[$cnt]);
			$m9[$cnt] = "￥".number_format($m9[$cnt]);

			$m10[$cnt] = $row_s->nai1_nik;

			if($row_s->s_invoice < 0) {
				$m6[$cnt] = preg_replace('/\-/','▲',$m6[$cnt]);
				$m8[$cnt] = preg_replace('/\-/','▲',$m8[$cnt]);
				$m9[$cnt] = preg_replace('/\-/','▲',$m9[$cnt]);
			}


			$cnt++;
				
		}
	}
	
	if($i_chosei_name != "" && is_numeric($i_chosei) && $i_chosei != 0) {
		$total = $shokei + $i_chosei;

		$i_chosei_wo_stax = ceil($i_chosei / (1 + get_tax($row->i_inv_date) / 100));
		$total_wo_stax += $i_chosei_wo_stax;
		$i_chosei_stax = $i_chosei - $i_chosei_wo_stax;
		$total_stax += $i_chosei_stax;

		//$total_wo_stax = sround2($total / (1 + get_tax($row->i_inv_date) / 100));
		//$total_stax = $total - $total_wo_stax;
		$total = "￥".number_format($total);
		$shokei = "￥".number_format($shokei);
		$total_stax = "￥".number_format($total_stax);
		$total_wo_stax = "￥".number_format($total_wo_stax);
		$i_chosei = "￥".number_format($i_chosei);
		$i_chosei_wo_stax = "￥".number_format($i_chosei_wo_stax);
		$i_chosei_stax = "￥".number_format($i_chosei_stax);
		$chosei_flag = true;
	}
	else {
		$total = $shokei;
		//$total_wo_stax = sround2($total / (1 + get_tax($row->i_inv_date) / 100));
		//$total_stax = $total - $total_wo_stax;
		$total = "￥".number_format($total);
		$shokei = "￥".number_format($shokei);
		$total_stax = "￥".number_format($total_stax);
		$total_wo_stax = "￥".number_format($total_wo_stax);
		$chosei_flag = false;
	}

	if($chosei_flag) {
		$i_chosei = preg_replace('/\-/','▲',(string)$i_chosei);
		$i_chosei_wo_stax = preg_replace('/\-/','▲',(string)$i_chosei_wo_stax);
		$i_chosei_stax = preg_replace('/\-/','▲',(string)$i_chosei_stax);
	}


	
	$ONEPAGE 	= 20;
	$FIRST_PAGE = 25;
	$MID_PAGE 	= 38;
	$END_PAGE 	= 35;
	
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
					<td class="tar">No.{$g_moto_id}-{$i_id}</td>
				</tr>
				<tr>
					<td class="tar">{$i_inv_date}</td>
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
								<td width="250" class="waku-b size14 tac">{$moto}</td>
								<td width="50" class="waku-b size14 tac">御中</td>
							</tr>
							<tr>
								<td class="size14 tac">&nbsp;</td>
								<td class="size14 tac">&nbsp;</td>
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
							<tr>
								<td class="size8 waku-b tal" colspan="1">発注金額(10%対象)</td>
								<td class="size9 waku-b tac" colspan="1">{$total_wo_stax} (税別)</td>
							</tr>
							<tr>
								<td class="size8 waku-b tal" colspan="1">消費税(10%)</td>
								<td class="size9 waku-b tac" colspan="1">{$total_stax}</td>
							</tr>
						</table>
					
					</td>
	
					<td class="" width="20">
					</td>
	
					<td class="" width="250">
	
						<table width="250" border="0" cellpadding="2">
							<tr>
								<td class="size14">&nbsp;</td>
							</tr>
							<tr>
								<td class="size13" width="170">{$COMPANY_NAME}</td>
								<td rowspan="6" width="80"></td>
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
						
					</td>
				</tr>
				<tr>
					<td colspan="1">毎度有り難うございます。<br />下記の通り御請求申し上げます。</td>
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
		<table width="640" border="0" cellpadding="4" class="size5">
			<tr>
				<td width="70" class="waku tac">No.</td>
				<td width="25" class="waku tac">リ新</td>
				<td width="30" class="waku tac">工事日</td>
				<td width="140" class="waku tac">現場名</td>
				<td width="185" class="waku tac">現場住所</td>
				<td width="30" class="waku tac">区分</td>
				<td width="70" class="waku tac">金額(税抜)</td>
				<td width="60" class="waku tac">消費税</td>
				<td width="70" class="waku tac">金額(税込)</td>
			</tr>
EOD;
		
		for($i = 0;$i < $max;$i++) {
			$tbl .= <<<EOD
				<tr>
					<td class="waku tac">{$m1[$cnt]}</td>
					<td class="waku tac">{$m10[$cnt]}</td>
					<td class="waku tac">{$m2[$cnt]}</td>
					<td class="waku tac">{$m3[$cnt]}</td>
					<td class="waku tac">{$m4[$cnt]}</td>
					<td class="waku tac">{$m5[$cnt]}</td>
					<td class="waku tar">{$m8[$cnt]}</td>
					<td class="waku tar">{$m9[$cnt]}</td>
					<td class="waku tar">{$m6[$cnt]}</td>
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
						<td colspan="9" class="tar">Page: {$page} / {$page_total}</td>
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
				<td colspan="4" class="waku tar" rowspan="2"></td>
				<td colspan="2" class="waku tar">{$i_chosei_name}</td>
				<td colspan="1" class="waku tar">{$i_chosei_wo_stax}</td>
				<td colspan="1" class="waku tar">{$i_chosei_stax}</td>
				<td colspan="1" class="waku tar">{$i_chosei}</td>
			</tr>
			<tr>
				<td colspan="2" class="waku2 tar">御請求金額</td>
				<td colspan="1" class="waku2 tar">{$total_wo_stax}</td>
				<td colspan="1" class="waku2 tar">{$total_stax}</td>
				<td colspan="1" class="waku2 tar">{$total}</td>
			</tr>
EOD;
		}
		else {
		$tbl .= <<<EOD
			<tr>
				<td colspan="4" class="waku tar" rowspan="1"></td>
				<td colspan="2" class="waku2 tar">御請求金額</td>
				<td colspan="1" class="waku2 tar">{$total_wo_stax}</td>
				<td colspan="1" class="waku2 tar">{$total_stax}</td>
				<td colspan="1" class="waku2 tar">{$total}</td>
			</tr>
EOD;
		}
        $cdate = date("00y0mdHi");
		$tbl .= <<<EOD

			<tr>
				<td colspan="6">
				</td>
			</tr>
			<tr>
				<td colspan="6" class="tac">
					お手数ながらお振り込みは、下記銀行口座にお願い致します。
				</td>
			</tr>

			<tr>
				<td></td>
				<td colspan="3" class="waku tac">
					<span style="font-size:10pt">{$COMPANY_BANK}&nbsp;{$COMPANY_BRANCH}<br />{$COMPANY_ACCOUNT}<br />名義 {$COMPANY_MEIGI}</span>
				</td>
				<td colspan="5" style="font-size:8pt">個別に定めた場合を除き締め日より60日以内に指定口座へお振込み下さい{$m_furikomi}</td>
			</tr>
	
			<tr>
                <td colspan="2">{$cdate}</td>
				<td colspan="7" class="tar">Page: {$page_total} / {$page_total}</td>
			</tr>
		</table>
EOD;
	$pdf->AddPage();
	$pdf->writeHTML($tbl, true, false, false, false, '');

}

//Close and output PDF document
$pdf->Output('invoice.pdf', $save_str);

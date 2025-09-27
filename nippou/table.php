<?php

$bgcolor = "#666";
$cdate = date("00y0mdHi");

//単位ボタン

//明細部分
$meisai = '';
$sql = "SELECT * FROM matsushima_nippou_meisan WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}' ORDER BY corder";
$query = mysql_query($sql);
$ncnt = 0;
while ($row = mysql_fetch_object($query)) {
	$unit_btn = '';
	if($form_flag) {
		$unit_btn = '<button class="unit-opt" onclick="show_nip_optional(\'matsushima_nippou_unit\', \'np_unit\', '.$ncnt.', \'\', \'\')">選</button>';
	}
	$meisai .= '<tr>';
	$meisai .= '<td colspan="3" class="line text-left">'.show_row($row,"np_name").'</td>';
	$meisai .= '<td colspan="1" class="line text-right">'.show_row($row,"np_kazu","text-right").'</td>';
	$meisai .= '<td colspan="1" class="line text-center">'.show_row($row,"np_unit").$unit_btn.'</td>';
	$meisai .= '<td colspan="1" class="line text-right">'.show_row($row,"np_tanka","text-right",true).'</td>';
	$meisai .= '<td colspan="2" class="line text-right">'.show_row($row,"np_kin","text-right",true,true).'</td>';
	$meisai .= '<td colspan="4" class="line text-left">'.show_row($row,"np_biko").'</td>';
	$meisai .= '</tr>';
	$ncnt++;
}

//合計計算
$sql = "SELECT SUM(np_kin) as total FROM matsushima_nippou_meisan WHERE np_rel_id = '{$hat_id}' AND np_flag = '{$hat_flag}'";
$query = mysql_query($sql);
$num = mysql_num_rows($query);
$total = "";
if($num) {
	$row = mysql_fetch_object($query);
	if($row->total) {
		$total = "￥".number_format($row->total);
	}
}

$html = <<< EOM
<style type="text/css">
	.nippou-area td {
		font-size:{$msize}pt;
		border:none;
		padding:{$cellpadding}px;
	}
	.nippou-area th {
		font-size:{$msize}pt;
		background:{$bgcolor};
		color:#FFF;
		border:1px solid #000;
		padding:{$cellpadding}px;
	}
	.nippou-area .text-center {
		text-align:center;
	}
	.nippou-area .text-right {
		text-align:right;
	}
	.nippou-area .text-left {
		text-align:left;
	}
	.nippou-area .size0 {
		font-size:1pt;
	}
	.nippou-area .size8 {
		font-size:8pt;
	}
	.nippou-area .size9 {
		font-size:9pt;
	}
	.nippou-area .size10 {
		font-size:10pt;
	}
	.nippou-area .size11 {
		font-size:11pt;
	}
	.nippou-area .size12 {
		font-size:12pt;
	}
	.nippou-area .size13 {
		font-size:13pt;
	}
	.nippou-area .size14 {
		font-size:14pt;
	}
	.nippou-area .size15 {
		font-size:15pt;
	}
	.nippou-area .size16 {
		font-size:16pt;
	}
	.nippou-area .size17 {
		font-size:17pt;
	}
	.nippou-area .size18 {
		font-size:18pt;
	}
	.nippou-area .size20 {
		font-size:20pt;
	}
	.nippou-area .size22 {
		font-size:22pt;
	}
	.nippou-area .size24 {
		font-size:24pt;
	}
	.nippou-area .uline {
		border-bottom:1px solid #000;
	}
	.nippou-area .tline {
		border-top:1px solid #000;
	}
	.nippou-area .line {
		border:1px solid #000;
	}
	.nippou-area .inc {
		width:100%;
	}
	.form-ctr {
		width:90%;margin:0 2%;
	}
	.np_unit {
		width:30%;
	}
	.unit-opt {
		padding:0;
		margin:0;
	}
	.readonly-n {
		background:#EEE;
	}
</style>
<div class="nippou-area">
EOM;

$m2b = "";
if($form_flag) {
$html .= <<< EOM
<div class=""><button class="save-btn button" onclick="save()">保存</button>
 <a href="../nippou/print.php?hat_id={$hat_id}&hat_flag={$hat_flag}" target="_blank" class="np-btn">日報印刷</a>
 </div>
EOM;
	
$m2b = '<button onclick="m2_tori()">㎡数を取り込む</button><br /><span style="font-size:10px;color:red">※下記数量に連動します。</span>';
	
	
}

$html .= <<< EOM
<table width="{$width}" border="0" cellpadding="{$cellpadding}">
	<tr>
		<td class="text-center size20" colspan="12">現場日報</td>
	</tr>
</table>
<table width="{$width}" border="0" cellpadding="{$cellpadding}">
	<tr>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
	</tr>
	<tr>
		<td class="uline" colspan="1">職方名：</td>
		<td class="uline" colspan="5">{$seko}</td>
		<td class="" colspan="2"></td>
		<td class="" colspan="4" rowspan="6">　<img src="../img/daily2.jpg" class="inc"></td>
	</tr>
	<tr>
		<td class="uline" colspan="1">日付：</td>
		<td class="uline" colspan="3">{$date}</td>
		<td class="uline text-right" colspan="2">区分：</td>
		<td class="uline text-center" colspan="2" id="n_kubun">{$sy_name}</td>
	</tr>
	<tr>
		<td class="uline" colspan="1">元請名：</td>
		<td class="uline" colspan="7">{$moto}</td>
	</tr>
	<tr>
		<td class="uline" colspan="1">現場名：</td>
		<td class="uline" colspan="4">{$g_genba}</td>
		<td class="uline text-right" colspan="1">ID：</td>
		<td class="uline text-center" colspan="2">{$id}</td>
	</tr>
	<tr>
		<td class="uline" colspan="1">現場住所：</td>
		<td class="uline" colspan="6">{$g_genba_address}</td>
		<td class="uline"></td>
	</tr>
	<tr>
		<td class="uline" colspan="1">主担当：</td>
		<td class="uline" colspan="2">{$syu_tantou}</td>
		<td class="uline" colspan="1">副担当：</td>
		<td class="uline" colspan="2">{$sub_tantou}</td>
		<td class=""></td>
		<td class=""></td>
	</tr>
	<tr>
		<td class=" text-left" colspan="1">㎡数：</td>
		<td class=" text-right" colspan="3">{$g_m2}</td>
		<td class=" text-left" colspan="2">㎡ {$m2b}</td>
		<td></td>
		<td class="" colspan="5">※記入、または目を通したら↓にサイン、印をしてください</td>
	</tr>
	<tr>
		<td class="tline" colspan="6">　</td>
		<td class="" colspan="6" rowspan="2">
		
		<table>
			<tr>
				<td class="" width="{$td_width}"></td>
				<td class="" width="{$td_width}"></td>
				<td class="line text-center" width="{$td_width}" border="1" align="center">職方</td>
				<td class="line text-center" width="{$td_width}" border="1" align="center">営業</td>
				<td class="line text-center" width="{$td_width}" border="1" align="center">事務</td>
				<td class="" width="{$td_width}"></td>
			</tr>
			<tr>
				<td class=""></td>
				<td class=""></td>
				<td class="line text-center" border="1">　<br />　<br /></td>
				<td class="line text-center" border="1">　</td>
				<td class="line text-center" border="1">　</td>
				<td class=""></td>
			</tr>
		</table>
		
		
		</td>
	</tr>
	<tr>
		<td class="uline size16" colspan="3">発注金額(税込)：</td>
		<td class="uline size16 text-center" colspan="3"><span class="kin-total">{$total}</span></td>
		<td class=""></td>
		<td class=""></td>
	</tr>	
</table>
<table width="{$width}" border="0" cellpadding="{$cellpadding}" class="main-nip-table">
	<tr>
		<td class="size10" width="{$td_width}">　</td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
		<td class="size0" width="{$td_width}"></td>
	</tr>
	<tr>
		<th class="text-center" bgcolor="{$bgcolor}" colspan="3">名称</th>
		<th class="text-center" bgcolor="{$bgcolor}" colspan="1">数量</th>
		<th class="text-center" bgcolor="{$bgcolor}" colspan="1">単位</th>
		<th class="text-center" bgcolor="{$bgcolor}" colspan="1">単価</th>
		<th class="text-center" bgcolor="{$bgcolor}" colspan="2">金額</th>
		<th class="text-center" bgcolor="{$bgcolor}" colspan="4">備考</th>
	</tr>
	{$meisai}
	<tr>
		<td colspan="3"></td>
		<td class="line size14 text-center" colspan="3">合計金額</td>
		<td class="line size14 text-center" colspan="6"><span class='kin-total'>{$total}</span></td>
	</tr>
</table>
<table width="{$width}" border="0" cellpadding="{$cellpadding}">
	<tr>
		<td class="" colspan="10">備考:<br />{$s_biko}</td>
		<td class="text-right" colspan="2">{$cdate}</td>
	</tr>
</table>

EOM;

$html .= <<< EOM
</div>
EOM;

function show_row($row,$col, $s = "text-left", $c = false, $ro = false) {
	global $form_flag;
	
	$ro_attr = '';
	$ro_class = '';
	if($ro) {
		$ro_attr = ' readonly="readonly" ';
		$ro_class = 'readonly-n';
	}
	
	if($form_flag) {
		return '<input type="text" value="'.$row->$col.'" '.$ro_attr.' data-id="'.$row->np_id.'" data-field_name="'.$col.'" class="form-ctr '.$col.' '.$s.' '.$ro_class.'">';
	}
	else if($c){
		if(!$row->$col)
			return '';
		else
			return number_format($row->$col); 
	}
	else {
		if(!$row->$col)
			return '';
		else
			return $row->$col;
	}
}

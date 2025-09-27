<?php
require_once("../php/db_connect.php");
require_once("../php/common.php");

/**********************************************************************
 *
 * 	パラメータ受け取り
 *
 **********************************************************************/
if(isset($_REQUEST['parm'])){
	$parm = $_REQUEST['parm'];
	$flag = $parm[0];

} else {
	return false;
}

/**********************************************************************
 *
 * 	締め対象表示
 *
 **********************************************************************/
if($flag == "SHOW_LIST") {
	//引数
	$page = $parm[1]; //ページ

	$JYOKEN = "";
	
	$shime_date = $parm[3];
	$shime_year = $parm[4];
	$shime_month = $parm[5];
	
	$sel_hat_id = $parm[6];
	if($sel_hat_id) {
		$JYOKEN = " AND seko_id = '{$sel_hat_id}'";
	}

	//ページ管理用 最終ページ計算
	//表示用SQL
	$sql_m = "SELECT * FROM matsushima_seko WHERE 1 {$JYOKEN}";
	@$query_m = mysql_query($sql_m);
	@$num_m = mysql_num_rows($query_m);

	if(!$query_m) {
		echo "fail";
		exit();
	}

	echo "<table class='list-table'>";
	echo "<tr>";
	showTh("", "アクション");
	showTh("", "現場ID");
	showTh("", "発注ID");
	showTh("", "リ新");
	showTh("", "区分");
	showTh("", "現場名");
	showTh("", "現場住所");
	showTh("", "元請");
	showTh("", "発注先");
	showTh("", "担当");
	showTh("", "副担当");
	showTh("", "開始日(締日");
	showTh("", "終了日");
	showTh("", "発注額");
	showTh("", "備考");
	echo "</tr>";

	//対象SLIPカウンター
	$counter = 0;
	$ttl_kin = 0;

	//表示するレコードがある場合
	if($num_m) {
		while ($row_m = @mysql_fetch_object($query_m)) {

			//発注先け取得(前発注先け対象)
			$seko = $row_m->seko;
			$seko_id = $row_m->seko_id;
			//締めグループで検索条件をハンドリング $m_shime_group
			
			//月末日を取得
			$ts = mktime(0, 0, 0, $shime_month, 1, $shime_year);
			$m_shime_group = intval(date('t', $ts));
			
			//最終的に数字かチェック
			if(is_numeric($m_shime_group)) {
				$JYOKEN = " AND (s_st_date <= '{$shime_year}-{$shime_month}-{$m_shime_group}' AND s_st_date != '0000-00-00' AND s_st_date is not null)";
			}
			else {
				//範囲外の条件
				$JYOKEN = " AND (s_st_date <= '1970-01-01' AND s_st_date != '0000-00-00' AND s_st_date is not null)";
			}
			
			//条件に発注先けセット
			$JYOKEN .= " AND (s_seko_id = '{$seko_id}')";

			//既に締めてあるSLIPを除外
			$JYOKEN .= " AND (s_hat_id = 0 OR s_hat_id = '' OR s_hat_id is null)";

			//SQL
			$sql = "SELECT * FROM matsushima_slip_hat 
					INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
					LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
					LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
					INNER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
					LEFT OUTER JOIN matsushima_nai_1 ON matsushima_genba.g_nai1_id = matsushima_nai_1.nai1_id
					
					WHERE 1 {$JYOKEN} ORDER BY s_st_date";
			@$query = mysql_query($sql);
			@$num = mysql_num_rows($query);

			//重複した発注があるか
			$sql_d = "SELECT * FROM matsushima_hat WHERE h_seko_id = '{$seko_id}' AND h_year = '{$shime_year}' AND h_month = '{$shime_month}'";
			$query_d = mysql_query($sql_d);
			$num_d = mysql_num_rows($query_d);

			if($num_d && $num) {
				echo "<tr>";
					echo "<td class='nw tac' colspan='13' style='color:red'>{$seko}の{$shime_year}年{$shime_month}月分発注書が既にあります。<br />このまま締め処理を続けると当月発注書が複数枚になります。<br />一枚にまとめる場合は{$moto}の当月発注締めを解除してから再度締め処理を行って下さい。</td>";
				echo "<tr>";
			}


			if($num) {
				while ($row = @mysql_fetch_object($query)) {
					echo "<tr>";
					echo "<td class='nw' style='vertical-align:middle'>";
						echo '<input type="checkbox" class="chk" checked="checked" value="'.$row->s_id.'">
						';
					echo "</td>";
		
					echo "<td class='nw tar'>";
						//show_clm($row->g_id, "g_id");
						echo "<a href='../main/?{$row->g_id}' target='_blank'>{$row->g_id}</a>";
					echo "</td>";

					echo "<td class='nw tar'>";
						echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->s_id."' />";
						show_clm($row->s_id, "s_id");
					echo "</td>";

					$clm = "nai1_nik";
					echo "<td class='nw tac'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";


					$clm = "sy_name";
					echo "<td class='nw tac'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";

					$clm = "g_genba";
					echo "<td class='nw tal'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";

					$clm = "g_genba_address";
					echo "<td class='nw tal'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";

					$clm = "moto_nik";
					echo "<td class='nw tal'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";

					echo "<td class='nw tal'>";
						echo "<span class='list_mode'>";
						echo $seko;
						echo "</span>";
					echo "</td>";

					$clm = "t_tantou";
					echo "<td class='nw tal'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";

					$clm = "g_tantou_sub_id";
					echo "<td class='nw tac'>";
						echo "<span class='list_mode'>";
							//show_clm($row->$clm, $clm, $F_WIDTH, 20);
							echo get_tantou($row->$clm);
						echo "</span>";
					echo "</td>";

					$clm = "s_st_date";
					echo "<td class='nw tac'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_DATE_SH, 20);
						echo "</span>";
					echo "</td>";

					$clm = "s_end_date";
					echo "<td class='nw tac'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_DATE_SH, 20);
						echo "</span>";
					echo "</td>";

					$clm = "s_hattyu";
					echo "<td class='nw tar'>";
						echo "<span class='list_mode'>";
							show_clm(number_format($row->$clm), $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";

					$clm = "s_biko";
					echo "<td class='nw tac'>";
						echo "<span class='list_mode'>";
							show_clm($row->$clm, $clm, $F_WIDTH, 20);
						echo "</span>";
					echo "</td>";
		
		//----------------------個別領域 ここまで----------------------------
					echo "</tr>";
					if($row->s_hattyu) {
						$ttl_kin += $row->s_hattyu;
					}
					$counter++;
				}
			}
		}
	}

	//表示するレコードが0の場合
	if(!$counter) {
		echo "<tr>";
			echo "<td colspan='9' style='text-align:center;color:red'>該当するデータがありません</td>";
		echo "</tr>";
	}
	echo "</table>";
	
	if($counter) {
		//保存ボタン
		echo '<p class="tal mt20" style="font-size:20px;font-weight:bold">';
		echo '発注総額：￥'.number_format($ttl_kin)." 発注件数：".$counter."件";
		echo '</p>';



		echo '<p class="tal mt20">';
		echo '<button class="button shime_exec_btn mr5">チェックした発注の締め処理を確定する</button>';
		echo '</p>';
	}
}

/**********************************************************************
 *
 * 	締め処理
 *
 **********************************************************************/
else if($flag == "SHIME_EXEC") {

	//cbno取得
	$cbno = $parm[1];

	$shime_date = $parm[2];
	$shime_year = $parm[3];
	$shime_month = $parm[4];

	//ページ管理用 最終ページ計算
	//表示用SQL
	$sql_m = "SELECT * FROM matsushima_seko WHERE 1 {$JYOKEN}";
	@$query_m = mysql_query($sql_m);
	@$num_m = mysql_num_rows($query_m);

	if(!$query_m) {
		echo "fail";
		exit();
	}
	//対象SLIPカウンター
	$counter = 0;

	//表示するレコードがある場合
	if($num_m) {
		while ($row_m = @mysql_fetch_object($query_m)) {

			//発注先け取得(前発注先け対象)
			$seko_id = $row_m->seko_id;
			
			//SQL
			$sql = "SELECT * FROM matsushima_slip_hat
					INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
					WHERE s_seko_id = '{$seko_id}' AND s_id in ({$cbno})";
			@$query = mysql_query($sql);
			if(!$query_m) {
				echo "fail";
				exit();
			}
			@$num = mysql_num_rows($query);
			
			if($num) {

				//入金日計算(振り込み日)
				
				$shiharai_month = 1;
				$shiharai_day = 15;
				
				$withd_month = $shime_month + $shiharai_month;
				if($withd_month > 12) {
					$withd_month -= 12;
					$withd_year = $shime_year + 1;	
				}
				else {
					$withd_year = $shime_year;	
				}
				
				$withd_day = $shiharai_day;
				if($withd_day == 31) {
					$ts = mktime(0, 0, 0, $withd_month, 1, $withd_year);
					$withd_day = intval(date('t', $ts));
				}
				//土日除外
				$shiharai_date = '';
				for($i=0;$i<7;$i++) {
					$ts = mktime(0, 0, 0, $withd_month, $withd_day, $withd_year);
					$date = getdate($ts);
					$week = $date['wday'];
					if($week == 0 || $week == 6) {
						$withd_day--;
						continue;
					}
					else {
						$shiharai_date = $withd_year ."-". $withd_month ."-". $withd_day;
						break;
					}
				}

				//発注インサート処理
				$sql = "INSERT INTO matsushima_hat (h_seko_id, h_year, h_month, h_hat_date, h_receipt_yotei_date) VALUES ('{$seko_id}', '{$shime_year}', '{$shime_month}', '{$shime_date}', '{$shiharai_date}')";
				@$query = mysql_query($sql);
				if(!$query_m) {
					echo "fail";
					exit();
				}
				$last_id = mysql_insert_id();

				//発注IDセット
				$sql = "UPDATE matsushima_slip_hat 
						INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						SET s_hat_id = '{$last_id}' WHERE s_seko_id = '{$seko_id}' AND s_id in ({$cbno})";
				@$query = mysql_query($sql);
				if(!$query_m) {
					echo "fail";
					exit();
				}
			}
		}
	}
	
			
	echo '<p class="tal">';
	echo '<p>締め処理を確定しました。</p><p><a href="../hat/">発注書管理</a>から確認して下さい。</p>';
	echo '</p>';
	
}

function get_tantou($id) {
	$tantou = "";
	$sql = "SELECT * FROM matsushima_tantou WHERE t_id = '{$id}' ";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if($num) {
		$row = @mysql_fetch_object($query);
		$tantou = $row->t_tantou_nik;
	}

	return $tantou;
}
?>

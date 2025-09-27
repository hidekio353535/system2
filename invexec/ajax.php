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

	$m_shime_group = $parm[3];
	
	$sel_moto_id = $parm[7];

	$sel_moto_nobranch = $parm[8];
	
	//通常締め処理
	if($m_shime_group < 60) {
		//スポットの場合
		if($m_shime_group == 50)
			$JYOKEN .= "";
		else if($m_shime_group != "")
			$JYOKEN .= " AND (sday = '{$m_shime_group}')";
		
		if($sel_moto_id ) {
			$JYOKEN .= " AND (moto_id = '{$sel_moto_id}')";
		}
		else if($sel_moto_nobranch) {
			$JYOKEN .= " AND (moto like '{$sel_moto_nobranch}')";
		}
		$shime_date = $parm[4];
		$shime_year = $parm[5];
		$shime_month = $parm[6];
	
		//ページ管理用 最終ページ計算
		//表示用SQL
		$sql_m = "SELECT * FROM matsushima_moto 
					INNER JOIN matsushima_shime ON matsushima_shime.id = matsushima_moto.m_shime_group
					WHERE 1 {$JYOKEN}";
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
		showTh("", "受注ID");
		showTh("", "受注日");
		showTh("", "締め対象日");
		showTh("", "元請");
		showTh("", "現場名");
		showTh("", "現場住所");
		showTh("", "区分");
		showTh("", "請求額");

		showTh("", "GRANZ担当");
		showTh("", "副担当");
		showTh("", "元請担当");
		showTh("", "開始日");
		showTh("", "終了日");		
		showTh("", "備考");

		echo "</tr>";
	
		//対象SLIPカウンター
		$counter = 0;
		$ttl_kin = 0;
	
		//表示するレコードがある場合
		if($num_m) {
			while ($row_m = @mysql_fetch_object($query_m)) {
	
				//元請け取得(前元請け対象)
				//$moto = $row_m->moto;
				$moto = $row_m->moto_nik;
				$moto_id = $row_m->moto_id;
				$branch = "";
				if($row_m->branch){
					$branch = " ".$row_m->branch;
				}
				$zuiji_flag = false;
	
				//締めグループで検索条件をハンドリング $m_shime_group
				//月末なら
				if($m_shime_group == 31) {
					//月末日を取得
					$ts = mktime(0, 0, 0, $shime_month, 1, $shime_year);
					$m_shime_group = intval(date('t', $ts));
				}
				//随時ハンドリング
				else if($m_shime_group == 40) {
					//月末日を取得
					$ts = mktime(0, 0, 0, $shime_month, 1, $shime_year);
					$m_shime_group = intval(date('t', $ts));
				
					$zuiji_flag = true;
				}
				
				//最終的に数字かチェック
				if(is_numeric($m_shime_group)) {
					$JYOKEN = " AND (s_shime_date <= '{$shime_year}-{$shime_month}-{$m_shime_group}' AND s_shime_date != '0000-00-00' AND s_shime_date is not null)";
				}
				else {
					//範囲外の条件
					$JYOKEN = " AND (s_shime_date <= '1970-01-01' AND s_shime_date != '0000-00-00' AND s_shime_date is not null)";
				}
				
				//条件に元請けセット
				$JYOKEN .= " AND (g_moto_id = '{$moto_id}')";
	
				//既に締めてあるSLIPを除外
				$JYOKEN .= " AND (s_inv_id = 0 OR s_inv_id = ''  OR s_inv_id is null)";
	
				//SQL
				$sql = "SELECT * FROM matsushima_slip 
						INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
						INNER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = matsushima_slip.s_seko_kubun_id
						
						WHERE 1 {$JYOKEN} ORDER BY s_shime_date";
				@$query = mysql_query($sql);
				@$num = mysql_num_rows($query);
				
				
				//重複した請求があるか
				$sql_d = "SELECT * FROM matsushima_inv WHERE i_moto_id = '{$moto_id}' AND i_year = '{$shime_year}' AND i_month = '{$shime_month}'";
				$query_d = mysql_query($sql_d);
				$num_d = mysql_num_rows($query_d);
	
				if($num_d && $num) {
					echo "<tr>";
						echo "<td class='nw tac' colspan='9' style='color:red'>{$moto}の{$shime_year}年{$shime_month}月分請求書が既にあります。<br />このまま締め処理を続けると当月請求書が複数枚になります。<br />一枚にまとめる場合は{$moto}の当月請求締めを解除してから再度締め処理を行って下さい。</td>";
					echo "<tr>";
				}
	
				if($num) {
					while ($row = @mysql_fetch_object($query)) {
						echo "<tr>";
						echo "<td class='nw' style='vertical-align:middle'>";
							if($m_shime_group == 50) //スポットの場合はチェックしない
								echo '<input type="checkbox" class="chk" value="'.$row->s_id.'">';
							else
								echo '<input type="checkbox" class="chk" checked="checked" value="'.$row->s_id.'">';
						echo "</td>";
			
						echo "<td class='nw tar'>";
							echo "<a href='../main/?{$row->s_genba_id}' target='_blank'>{$row->s_genba_id}</a>";
						echo "</td>";

						echo "<td class='nw tar'>";
							echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->s_id."' />";
							show_clm($row->s_id, "s_id");
						echo "</td>";
	
						$clm = "s_date";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_DATE_SH, 20);
							echo "</span>";
						echo "</td>";
	
						$clm = "s_shime_date";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_DATE_SH, 20);
							echo "</span>";
						echo "</td>";
	
						echo "<td class='nw tal'>";
							echo "<span class='list_mode'>";
							echo $moto.$branch;
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
	
						$clm = "sy_name_nik";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_WIDTH, 20);
							echo "</span>";
						echo "</td>";
	
						$clm = "s_invoice";
						echo "<td class='nw tar'>";
							echo "<span class='list_mode'>";
								show_clm(number_format($row->$clm), $clm, $F_NUM, 20);
							echo "</span>";
						echo "</td>";

						$clm = "g_tantou_id";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								//show_clm($row->$clm, $clm, $F_WIDTH, 20);
								echo get_tantou($row->$clm);
							echo "</span>";
						echo "</td>";
						
						$clm = "g_tantou_sub_id";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								//show_clm($row->$clm, $clm, $F_WIDTH, 20);
								echo get_tantou($row->$clm);
							echo "</span>";
						echo "</td>";
						
						$clm = "g_moto_tantou";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_WIDTH, 20);
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

						$clm = "s_biko";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_WIDTH, 20);
							echo "</span>";
						echo "</td>";
						
						//----------------------個別領域 ここまで----------------------------
						echo "</tr>";

						if($row->s_invoice) {
							$ttl_kin += $row->s_invoice;
						}
						$counter++;
					}
				}
			}
		}
	
		//表示するレコードが0の場合
		if(!$counter) {
			echo "<tr>";
				echo "<td colspan='10' style='text-align:center;color:red'>該当するデータがありません</td>";
			echo "</tr>";
		}
		echo "</table>";
		
		if($counter) {
			//保存ボタン
			echo '<p class="tal mt20" style="font-size:20px;font-weight:bold">';
			echo '請求総額：￥'.number_format($ttl_kin)." 請求件数：".$counter."件";
			echo '</p>';
	
			echo '<p class="tal mt20">';
			echo '<button class="button shime_exec_btn mr5">チェックした受注の締め処理を確定する</button>';
			echo '</p>';
		}
	}
	//未請求チェック or 以降
	else {
		$shime_date = $parm[4];
		$shime_year = $parm[5];
		$shime_month = $parm[6];
	
		//ページ管理用 最終ページ計算
		//表示用SQL
		$sql_m = "SELECT * FROM matsushima_moto 
					INNER JOIN matsushima_shime ON matsushima_shime.id = matsushima_moto.m_shime_group
					WHERE 1 {$JYOKEN}";
		@$query_m = mysql_query($sql_m);
		@$num_m = mysql_num_rows($query_m);
	
		if(!$query_m) {
			echo "fail";
			exit();
		}
	
		echo "<table class='list-table'>";
		echo "<tr>";
		showTh("", "現場ID");
		showTh("", "受注ID");
		showTh("", "受注日");
		showTh("", "締め対象日");
		showTh("", "元請");
		showTh("", "現場名");
		showTh("", "現場住所");
		showTh("", "区分");
		showTh("", "請求額");

		showTh("", "GRANZ担当");
		showTh("", "副担当");
		showTh("", "元請担当");
		showTh("", "開始日");
		showTh("", "終了日");		
		showTh("", "備考");

		echo "</tr>";
	
		//対象SLIPカウンター
		$counter = 0;
	
		//表示するレコードがある場合
		if($num_m) {
			while ($row_m = @mysql_fetch_object($query_m)) {
	
				//元請け取得(前元請け対象)
				//$moto = $row_m->moto;
				$moto = $row_m->moto_nik;
				$moto_id = $row_m->moto_id;

				//月末日を取得
				$ts = mktime(0, 0, 0, $shime_month, 1, $shime_year);
				$lastday = intval(date('t', $ts));

				$JYOKEN = "";

				if($m_shime_group == 60) {
					//締め日が締日以前で未記入も含む
					$JYOKEN = " AND (s_shime_date <= '{$shime_year}-{$shime_month}-{$lastday}') AND (s_shime_date != '0000-00-00' AND s_shime_date is not null)";
				}
				else if($m_shime_group == 70) {
					//締め日が締日以前で未記入も含む
					$JYOKEN = " AND (s_shime_date > '{$shime_year}-{$shime_month}-{$lastday}')";
				}
				else if($m_shime_group == 80) {
					$JYOKEN = " AND (s_shime_date = '0000-00-00' OR s_shime_date is null)";
				}

				//既に締めてあるSLIPを除外
				$JYOKEN .= " AND (s_inv_id = 0 OR s_inv_id = ''  OR s_inv_id is null)";
				
				//条件に元請けセット
				$JYOKEN .= " AND (g_moto_id = '{$moto_id}')";
	
	
				//SQL
				$sql = "SELECT * FROM matsushima_slip 
						INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
						INNER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = matsushima_slip.s_seko_kubun_id
						
						WHERE 1 {$JYOKEN} ORDER BY s_shime_date";
				@$query = mysql_query($sql);
				@$num = mysql_num_rows($query);
				
				if($num) {
					while ($row = @mysql_fetch_object($query)) {
						echo "<tr>";

						echo "<td class='nw tar'>";
							echo "<a href='../main/?{$row->s_genba_id}' target='_blank'>{$row->s_genba_id}</a>";
						echo "</td>";

						echo "<td class='nw tar'>";
							echo "<input type='hidden' class='{$VIEW_ID_FIELD}' value='".$row->s_id."' />";
							show_clm($row->s_id, "s_id");
						echo "</td>";
	
						$clm = "s_date";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_DATE_SH, 20);
							echo "</span>";
						echo "</td>";
	
						$clm = "s_shime_date";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_DATE_SH, 20);
							echo "</span>";
						echo "</td>";
	
						echo "<td class='nw tal'>";
							echo "<span class='list_mode'>";
							echo $moto;
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
	
						$clm = "sy_name_nik";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_WIDTH, 20);
							echo "</span>";
						echo "</td>";
	
						$clm = "s_invoice";
						echo "<td class='nw tar'>";
							echo "<span class='list_mode'>";
								show_clm(number_format($row->$clm), $clm, $F_NUM, 20);
							echo "</span>";
						echo "</td>";

						
						$clm = "g_tantou_id";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								//show_clm($row->$clm, $clm, $F_WIDTH, 20);
								echo get_tantou($row->$clm);
							echo "</span>";
						echo "</td>";
						
						$clm = "g_tantou_sub_id";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								//show_clm($row->$clm, $clm, $F_WIDTH, 20);
								echo get_tantou($row->$clm);
							echo "</span>";
						echo "</td>";
						
						$clm = "g_moto_tantou";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_WIDTH, 20);
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

						$clm = "s_biko";
						echo "<td class='nw tac'>";
							echo "<span class='list_mode'>";
								show_clm($row->$clm, $clm, $F_WIDTH, 20);
							echo "</span>";
						echo "</td>";
						
			//----------------------個別領域 ここまで----------------------------
						echo "</tr>";
						
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
	$m_shime_group = $parm[5];

	
	//スポットで締める場合
	if($m_shime_group == 50) {
		$sql_m = "SELECT * FROM matsushima_slip
					INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
					INNER JOIN matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id
					LEFT OUTER JOIN matsushima_shiharai_month ON matsushima_shiharai_month.id = matsushima_moto.m_shiharai_m
					LEFT OUTER JOIN matsushima_shiharai_day ON matsushima_shiharai_day.id = matsushima_moto.m_shiharai_day
					WHERE s_id in ({$cbno})";
		@$query_m = mysql_query($sql_m);
		@$num_m = mysql_num_rows($query_m);

		if(!$query_m) {
			echo "fail1";
			exit();
		}
		//対象SLIPカウンター
		$counter = 0;
	
		//表示するレコードがある場合
		if($num_m) {
			while ($row_m = @mysql_fetch_object($query_m)) {
	
				//元請け取得(前元請け対象)
				$moto_id = $row_m->moto_id;
				$shiharai_month = $row_m->smonth;
				$shiharai_day = $row_m->sday;
				
				if($num_m) {
					//入金日計算
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
					
					//請求インサート処理
					$sql = "INSERT INTO matsushima_inv (i_moto_id, i_year, i_month, i_inv_date, i_receipt_yotei_date,inv_kubun) VALUES ('{$moto_id}', '{$shime_year}', '{$shime_month}', '{$shime_date}', '{$shiharai_date}','スポット請求({$row_m->g_genba})')";
					@$query = mysql_query($sql);
					if(!$query_m) {
						echo "fail2";
						exit();
					}
					$last_id = mysql_insert_id();
	
					//請求IDセット
					$sql = "UPDATE matsushima_slip 
							INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
							SET s_inv_id = '{$last_id}' WHERE g_moto_id = '{$moto_id}' AND s_id = ({$row_m->s_id})";
					@$query = mysql_query($sql);
					if(!$query_m) {
						echo "fail3";
						exit();
					}
				}
			}
		}
	}
	//通常締め処理
	else {
		$JYOKEN = "";
		$sql_m = "SELECT * FROM matsushima_moto 
					LEFT OUTER JOIN matsushima_shiharai_month ON matsushima_shiharai_month.id = matsushima_moto.m_shiharai_m
					LEFT OUTER JOIN matsushima_shiharai_day ON matsushima_shiharai_day.id = matsushima_moto.m_shiharai_day
					WHERE 1 {$JYOKEN}";
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
	
				//元請け取得(前元請け対象)
				$moto_id = $row_m->moto_id;
				$shiharai_month = $row_m->smonth;
				$shiharai_day = $row_m->sday;
				
				//SQL
				$sql = "SELECT * FROM matsushima_slip
						INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
						WHERE g_moto_id = '{$moto_id}' AND s_id in ({$cbno})";
				@$query = mysql_query($sql);
				if(!$query_m) {
					echo "fail";
					exit();
				}
				@$num = mysql_num_rows($query);
				
				if($num) {
					//入金日計算
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
					
					//請求インサート処理
					$sql = "INSERT INTO matsushima_inv (i_moto_id, i_year, i_month, i_inv_date, i_receipt_yotei_date,inv_kubun) VALUES ('{$moto_id}', '{$shime_year}', '{$shime_month}', '{$shime_date}', '{$shiharai_date}','月次請求')";
					@$query = mysql_query($sql);
					if(!$query_m) {
						echo "fail";
						exit();
					}
					$last_id = mysql_insert_id();
	
					//請求IDセット
					$sql = "UPDATE matsushima_slip 
							INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
							SET s_inv_id = '{$last_id}' WHERE g_moto_id = '{$moto_id}' AND s_id in ({$cbno})";
					@$query = mysql_query($sql);
					if(!$query_m) {
						echo "fail";
						exit();
					}
				}
			}
		}
	}
	echo '<p class="tal">';
	echo '<p>締め処理を確定しました。</p><p><a href="../inv/">請求書管理</a>から確認して下さい。</p>';
	echo '</p>';
	
}

//締めグループ表示
else if($flag == "SHOW_SHIME_GROUP") {

	$sql = "SELECT * FROM matsushima_moto
			INNER JOIN matsushima_shime ON matsushima_shime.id = matsushima_moto.m_shime_group
			WHERE m_shime_group is not null AND m_shime_group != '' AND m_shime_group != 0 GROUP BY m_shime_group ORDER BY  matsushima_shime.corder";
	@$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if(!$query) {
		echo "fail";
		exit();
	}
	if($num) {
		echo "<select id='shime_group'>";
		echo "<option value='0'>締めグループを選択して下さい</option>";
		while ($row = @mysql_fetch_object($query)) {
			echo "<option value='{$row->sday}'>{$row->name}</option>";
		}
		echo "<option value='50'>スポット請求</option>";
		echo "<option value='60'>未請求現場チェック(締め日以前)</option>";
		echo "<option value='70'>未請求現場チェック(締め日以降)</option>";
		echo "<option value='80'>未請求現場チェック(締め日未記入)</option>";
		echo "</select>";
	}
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

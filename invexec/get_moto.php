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
if($flag == "GET_MOTO") {
	//引数
	$page = $parm[1]; //ページ

	$JYOKEN = "";

	$m_shime_group = $parm[3];
	
	
	//通常締め処理
	if($m_shime_group < 60) {
		//スポットの場合
		if($m_shime_group == 50)
			$JYOKEN .= "";
		else if($m_shime_group != "")
			$JYOKEN .= " AND (sday = '{$m_shime_group}')";
		
		$shime_date = $parm[4];
		$shime_year = $parm[5];
		$shime_month = $parm[6];
	
		//ページ管理用 最終ページ計算
		//表示用SQL
		$sql_m = "SELECT * FROM matsushima_moto 
					INNER JOIN matsushima_shime ON matsushima_shime.id = matsushima_moto.m_shime_group
					WHERE 1 {$JYOKEN} ORDER BY moto, branch";
		@$query_m = mysql_query($sql_m);
		@$num_m = mysql_num_rows($query_m);
	
		if(!$query_m) {
			echo "fail";
			exit();
		}
	
		//対象SLIPカウンター
		$counter = 0;
	
		//表示するレコードがある場合
		echo "<select id='sel_moto_id'>";
		echo "<option value='0'>選択して下さい。</option>";
		
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
						
						WHERE 1 {$JYOKEN} 
						
						GROUP BY g_moto_id
						ORDER BY s_shime_date";
				@$query = mysql_query($sql);
				@$num = mysql_num_rows($query);
	
				if($num) {
					while ($row = @mysql_fetch_object($query)) {
						echo "<option value='$row->g_moto_id'>".$moto.$branch."</option>";
						
						$counter++;
					}
				}
			}
		}
	
		//表示するレコードが0の場合
		if(!$counter) {
		}
		echo "</select>";
		
	}

	//支店グループ排除
	//通常締め処理
	$JYOKEN = "";

	$m_shime_group = $parm[3];

	if($m_shime_group < 60) {
		//スポットの場合
		if($m_shime_group == 50)
			$JYOKEN .= "";
		else if($m_shime_group != "")
			$JYOKEN .= " AND (sday = '{$m_shime_group}')";
		
		$shime_date = $parm[4];
		$shime_year = $parm[5];
		$shime_month = $parm[6];
	
		//ページ管理用 最終ページ計算
		//表示用SQL
		$sql_m = "SELECT * FROM matsushima_moto 
					INNER JOIN matsushima_shime ON matsushima_shime.id = matsushima_moto.m_shime_group
					WHERE 1 {$JYOKEN}
					AND branch != '' AND branch is not null GROUP BY moto ORDER BY kana, moto, moto_id
					";
		@$query_m = mysql_query($sql_m);
		@$num_m = mysql_num_rows($query_m);
	
		if(!$query_m) {
			echo "fail";
			exit();
		}
	
		//対象SLIPカウンター
		$counter = 0;
	
		//表示するレコードがある場合
		echo "<select id='sel_moto_nobranch'>";
		echo "<option value='0'>選択して下さい。</option>";
		
		if($num_m) {

			while ($row_m = @mysql_fetch_object($query_m)) {
	
				//元請け取得(前元請け対象)
				$moto_sei = $row_m->moto;
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
				$JYOKEN .= " AND (moto = '{$moto_sei}')";
	
				//既に締めてあるSLIPを除外
				$JYOKEN .= " AND (s_inv_id = 0 OR s_inv_id = ''  OR s_inv_id is null)";
	
				//SQL
				$sql = "SELECT * FROM matsushima_slip 
						INNER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip.s_genba_id
						INNER JOIN matsushima_kouji_syu ON matsushima_kouji_syu.sy_id = matsushima_slip.s_seko_kubun_id
						INNER JOIN matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id
						
						WHERE 1 {$JYOKEN} 
						
						GROUP BY moto
						ORDER BY s_shime_date";
				@$query = mysql_query($sql);
				@$num = mysql_num_rows($query);
	
				if($num) {
					while ($row = @mysql_fetch_object($query)) {
						echo "<option value='".$row_m->moto."'>".$moto."</option>";
						
						$counter++;
					}
				}
			}
		}
	
		//表示するレコードが0の場合
		if(!$counter) {
		}
		echo "</select>";
	}




}

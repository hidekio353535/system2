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
if($flag == "GET_HAT") {
	//引数
	$page = $parm[1]; //ページ

	$JYOKEN = "";
	
	$shime_date = $parm[3];
	$shime_year = $parm[4];
	$shime_month = $parm[5];

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

	echo "<select id='sel_hat_id'>";
	echo "<option value='0'>選択して下さい。</option>";
	
	
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
					
					WHERE 1 {$JYOKEN} 
					GROUP BY s_seko_id
					ORDER BY s_st_date";
			@$query = mysql_query($sql);
			@$num = mysql_num_rows($query);

			if($num) {
				while ($row = @mysql_fetch_object($query)) {
					echo "<option value='$row->s_seko_id'>".$seko."</option>";

					$counter++;
				}
			}
		}
	}
	echo "</select>";

}

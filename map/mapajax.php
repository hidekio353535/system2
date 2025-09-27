<?php
require_once("../php/db_connect.php");
//更新

$gmap_lat = $_REQUEST['gmap_lat'];
$gmap_lng = $_REQUEST['gmap_lng'];
$gmap_status = $_REQUEST['gmap_status'];
$g_id = $_REQUEST['g_id'];

$sql = "update matsushima_genba
SET
gmap_lat = '".$gmap_lat."',
gmap_lng = '".$gmap_lng."',
gmap_status = '".$gmap_status."'
WHERE 
g_id = ".$g_id."
AND
(gmap_status != 'OK' OR gmap_status is null)
";
$query = mysql_query($sql);
echo $query ." - " .$sql;

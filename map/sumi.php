<?php
require_once("../php/db_connect.php");
//更新

$g_id = $_REQUEST['g_id'];
$val = $_REQUEST['val'];

if(!$g_id) {
    echo "fail";
}

$sql = "update matsushima_genba
SET
gmap_sumi = '".$val."'
WHERE 
g_id = ".$g_id."
";
$query = mysql_query($sql);
echo $query ." - " .$sql;

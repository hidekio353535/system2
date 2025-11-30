<?php
session_start();
$data = $_SESSION["pdf"];

$g_id = "(".$_SESSION["g_id"].")";

$moto = $_SESSION["moto"];
$moto = preg_replace('/株式会社/','(株)',$moto);
$moto = preg_replace('/ |　|/','',$moto) . "様";

$genba = $_SESSION["genba"];
$genba = preg_replace('/ |　|/','',$genba);

$address = $_SESSION["address"];
$address = preg_replace('/ |　|/','',$address);
$address = mb_substr($address, 0, 5);

$filename = "見積書-".$genba.$g_id."-".$moto;
$filename = urlencode($filename);

//header('Content-Type: application/pdf');
header("Content-Type: application/force-download");
header('Content-Disposition: attachment;filename='.$filename.'.pdf');
echo($data);
exit;
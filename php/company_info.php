<?php
//会社情報
$sql = "SELECT * FROM matsushima_company_info LIMIT 1";
$query = mysql_query($sql);
$cinfo = mysql_fetch_object($query);

$COMPANY_NAME = $cinfo->name;
$COMPANY_POSTAL = $cinfo->postal;
$COMPANY_ADDRESS = $cinfo->address;
$COMPANY_TEL = $cinfo->tel;
$COMPANY_FAX = $cinfo->fax;
$COMPANY_CEO = $cinfo->ceo;
$COMPANY_KYOKA = $cinfo->kyoka;
$COMPANY_BANK = $cinfo->bank;
$COMPANY_BRANCH = $cinfo->branch;
$COMPANY_ACCOUNT = $cinfo->account;
$COMPANY_MEIGI = $cinfo->meigi;
$COMPANY_NAME_ENG = $cinfo->eng_name;
$COMPANY_INVOICE_NO = $cinfo->my_invoice_no;

?>

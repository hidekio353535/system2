<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アンチ2枚架　現場リスト</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
</head>
<body>

<div class="container-fluid">
<div class="row">
<div class="col">

<h1>アンチ2枚架　現場リスト</h1>
<p class="mt-2 mb-3">※現場IDの大きい順</p>


<table class="table table-sm">
    <tr>
        <th>現場ID</th>
        <th>現場名</th>
        <th>現場住所</th>
        <th>職人</th>
    </tr>
<?php
require_once("../php/db_connect.php");

$sql = "
SELECT * FROM matsushima_nippou_meisan as nm
			 inner join matsushima_slip_hat as msh ON nm.np_rel_id = msh.s_id
             inner join matsushima_seko ON s_seko_id = seko_id
             inner join matsushima_genba ON msh.s_genba_id = g_id
			 WHERE 
			 nm.np_name LIKE '%アンチ2枚架%'
			 and
			 nm.np_kazu > 0
			 and
			 nm.np_kazu is not null
			 GROUP BY msh.s_genba_id
             ORDER BY g_id DESC
";

@$query = mysql_query($sql);
while ($row = @mysql_fetch_object($query)) {

    echo "<tr>";

        echo "<td>";
        echo "<a href='../main/?{$row->g_id}' target='_blank'>";
        echo $row->g_id;
        echo "</td>";
        echo "<td>";
        echo $row->g_genba;
        echo "</td>";
        echo "<td>";
        echo $row->g_genba_address;
        echo "</td>";
        echo "<td>";
        echo $row->seko;
        echo "</td>";

    echo "</td>";

}
?>
</table>

</div>
</div>

</div>
</body>
</html>
<?php

require_once("../php/db_connect.php");

$shops_arr = array();
//払し あり、なしループ
for($p = 0;$p < 3;$p++) {
    if($p == 0) {
        $sql = "select 
        *, 
        min(slip.s_st_date) as a,
        max(slip.s_st_date) as b,
        max(slip_hat.s_st_date) as c,
        max(slip_jv.s_st_date) as d,
        max(slip.s_end_date) as e,
        max(slip_hat.s_end_date) as f,
        max(slip_jv.s_end_date) as g,
        0 as barasi
    FROM matsushima_genba 
    INNER JOIN matsushima_slip as slip ON g_id = slip.s_genba_id
    LEFT OUTER JOIN matsushima_slip_hat as slip_hat ON g_id = slip_hat.s_genba_id
    LEFT OUTER JOIN matsushima_slip_jv as slip_jv ON g_id = slip_jv.s_genba_id
    LEFT OUTER JOIN matsushima_moto ON g_moto_id = moto_id
    WHERE 
    gmap_invisible = 0
    AND
    NOT EXISTS (select * from matsushima_slip_hat as a where matsushima_genba.g_id = a.s_genba_id AND (a.s_seko_kubun_id = 1 OR a.s_seko_kubun_id = 3) AND a.s_st_date is not null AND a.s_st_date != '0000-00-00')
    AND
    slip.s_st_date is not null
    AND
    slip.s_st_date != '0000-00-00'
    AND
    slip.s_st_date <= CURDATE()
    GROUP BY g_id
    ";

    }
    else if($p == 1) {
        $sql = "select 
        *, 
        min(slip.s_st_date) as a,
        max(slip.s_st_date) as b,
        max(slip_hat.s_st_date) as c,
        max(slip_jv.s_st_date) as d,
        max(slip.s_end_date) as e,
        max(slip_hat.s_end_date) as f,
        max(slip_jv.s_end_date) as g,
        1 as barasi
    FROM matsushima_genba 
    INNER JOIN matsushima_slip as slip ON g_id = slip.s_genba_id
    LEFT OUTER JOIN matsushima_slip_hat as slip_hat ON g_id = slip_hat.s_genba_id
    LEFT OUTER JOIN matsushima_slip_jv as slip_jv ON g_id = slip_jv.s_genba_id
    LEFT OUTER JOIN matsushima_moto ON g_moto_id = moto_id
    WHERE 
    gmap_invisible = 0
    AND
    EXISTS (select * from matsushima_slip_hat as a where matsushima_genba.g_id = a.s_genba_id AND a.s_seko_kubun_id = 3 AND a.s_st_date is not null AND a.s_st_date != '0000-00-00')
    AND
    slip.s_st_date is not null
    AND
    slip.s_st_date != '0000-00-00'
    AND
    slip.s_st_date <= CURDATE()
    GROUP BY g_id
    ";

    }
    else if($p == 2) {
        $sql = "select 
        *, 
        min(slip.s_st_date) as a,
        max(slip.s_st_date) as b,
        max(slip_hat.s_st_date) as c,
        max(slip_jv.s_st_date) as d,
        max(slip.s_end_date) as e,
        max(slip_hat.s_end_date) as f,
        max(slip_jv.s_end_date) as g,
        2 as barasi
    FROM matsushima_genba 
    INNER JOIN matsushima_slip as slip ON g_id = slip.s_genba_id
    LEFT OUTER JOIN matsushima_slip_hat as slip_hat ON g_id = slip_hat.s_genba_id
    LEFT OUTER JOIN matsushima_slip_jv as slip_jv ON g_id = slip_jv.s_genba_id
    LEFT OUTER JOIN matsushima_moto ON g_moto_id = moto_id
    WHERE 
    gmap_invisible = 0
    AND
    EXISTS (select * from matsushima_slip_hat as a where matsushima_genba.g_id = a.s_genba_id AND a.s_seko_kubun_id = 1 AND a.s_st_date is not null AND a.s_st_date != '0000-00-00')
    AND
    slip.s_st_date is not null
    AND
    slip.s_st_date != '0000-00-00'
    AND
    slip.s_st_date <= CURDATE()
    GROUP BY g_id
    ";
    }

    $query = mysql_query($sql);
    $num = mysql_num_rows($query);
    while ($row = mysql_fetch_object($query)) {

        $list = array();
        if($row->a && $row->a != "0000-00-00") {
            $list[] = $row->a;
        }
        if($row->b && $row->b != "0000-00-00") {
            $list[] = $row->b;
        }
        if($row->c && $row->c != "0000-00-00") {
            $list[] = $row->c;
        }
        if($row->d && $row->d != "0000-00-00") {
            $list[] = $row->d;
        }
        if($row->e && $row->e != "0000-00-00") {
            $list[] = $row->e;
        }
        if($row->f && $row->f != "0000-00-00") {
            $list[] = $row->f;
        }
        if($row->g && $row->g != "0000-00-00") {
            $list[] = $row->g;
        }

        $end_list = array();
        if($row->e && $row->e != "0000-00-00") {
            $end_list[] = $row->e;
        }
        if($row->f && $row->f != "0000-00-00") {
            $end_list[] = $row->f;
        }
        if($row->g && $row->g != "0000-00-00") {
            $end_list[] = $row->g;
        }

        array_multisort( array_map( "strtotime", $list ), SORT_ASC, $list );
        $st_date = $list[0];
        $end_date = end($list);

        array_multisort( array_map( "strtotime", $end_list ), SORT_ASC, $end_list );
        $endend_date = end($end_list);
        
        $addr = preg_replace('/付近/','',$row->g_genba_address);

        //s_end_date e 受注のs_end_dateは最優先
        if($row->e && $row->e != '0000-00-00') {
            if(strtotime(date('Y-m-d')) > strtotime($row->e)) {
                continue;
            }
        }
        else if($row->barasi == 1) {
            //払しあり
            if(strtotime(date('Y-m-d')) > strtotime($end_date)) {
                continue;
            }
        }
        else if($row->barasi == 2) {
            //架払
            if($endend_date) {
                if(strtotime(date('Y-m-d')) > strtotime($endend_date)) {
                    continue;
                }    
            }
            else if(strtotime(date('Y-m-d')) > strtotime($st_date . " +1 year")) {
                continue;
            }
        }
        else if(!$row->barasi) {
            //払しが無い
            //1年 以内
            if(strtotime(date('Y-m-d')) > strtotime($st_date . " +1 year")) {
                continue;
            }
        }

        //職人情報取得
        $sql_sy = "SELECT * 
                    FROM matsushima_slip_hat
                    INNER JOIN matsushima_kouji_syu_hat ON s_seko_kubun_id = sy_id
                    LEFT OUTER JOIN matsushima_seko ON s_seko_id = seko_id
                    WHERE s_genba_id = '{$row->g_id}' ORDER BY sy_order";
        $query_sy = mysql_query($sql_sy);
        $hat_info = array();
        while ($row_sy = mysql_fetch_object($query_sy)) {
            $hat_info[] = array("s_st_date"=>rm_null($row_sy->s_st_date), "s_end_date"=>rm_null($row_sy->s_end_date), "sy_name"=>rm_null($row_sy->sy_name), "seko"=>rm_null($row_sy->seko));
        }
        
        $shops_arr[] = array("gid"=>$row->g_id,"gmap_lat"=>$row->gmap_lat,"gmap_lng"=>$row->gmap_lng,"gmap_status"=>$row->gmap_status,"name" => $row->g_genba, "addr" => $addr, "moto" => $row->moto, "url" => "/system2/main/?{$row->g_id}", "st_date"=>$st_date, "end_date"=>$end_date, "endend_date"=>$endend_date,"barasi"=>$row->barasi, "gmap_invisible"=>$row->gmap_invisible, "hat_info"=>$hat_info, "gmap_sumi"=>$row->gmap_sumi);
    }
}

function rm_null($v) {
    if(!$v || $v == 'null') {
        return '';
    }
    else if($v == '0000-00-00') {
        return '';
    }
    return $v;
}

/*
$cnt = 0;
echo "<table>";
?>
    <tr>
        <th>cnt</th>
        <th>gid</th>
        <th>name</th>
        <th>adr</th>
        <th>barasi</th>
        <th>st_date</th>
        <th>end_date</th>
        <th>endend_date</th>
    </tr>
<?php
foreach($shops_arr as $d) {
    $cnt++;
    echo "<tr>";
    echo "<td>".$cnt."</td>";
    echo "<td>".$d['gid']."</td>";
    echo "<td>".$d['name']."</td>";
    echo "<td>".$d['addr']."</td>";
    echo "<td>".$d['barasi']."</td>";
    echo "<td>".$d['st_date']."</td>";
    echo "<td>".$d['end_date']."</td>";
    echo "<td>".$d['endend_date']."</td>";
    echo "</td>";
}
echo "</table>";
//exit;
*/

$shops_arr_json = json_encode($shops_arr); //jsonにしてJSに渡す
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">    
    <title>google map</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    
    <script src="../js/jquery-1.8.2.min.js"></script>
    <style>
        .title {
            font-size:20px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        label {
            font-weight: bold;
        }
        .nw {
            white-space: nowrap;
        }
        .seko-table th, .seko-table td {
            padding: 0;
            margin:0;
        }
    </style>
</head>

<body>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCn7soGjAtIYhna4-J6Dx_fx-RSPNZalVc"></script>
        <div id="map" style="width: 100%; height: 80vh;"></div>
        <div class="container-fluid">
            <div id="fail"></div>
        </div>

        <script>

            $(function() {
                initMap();
            });

            'use strict';
            function initMap() {
                var addresses = <?php echo $shops_arr_json; ?>; // jsonにした配列を受け取り
                var bounds = new google.maps.LatLngBounds();
                var latlng = []; //緯度経度の値をセット
                var lat = []; //緯度の値をセット
                var lng = []; //経度の値をセット
                var maxLat = -90;
                var maxLng = -180;
                var minLat = 90;
                var minLng = 180;
                var marker = []; //マーカーの位置情報をセット
                var infoWindow = [];
                var failarr = [];
                var successarr = [];
                var myLatLng; //地図の中心点をセット用
                var geocoder;
                var baraarr = ['無し','有り','架払'];
                geocoder = new google.maps.Geocoder(); // ジオコードリクエストを送信するGeocoderの作成
                const map = new google.maps.Map(document.getElementById('map')); //地図を作成する

                //現在位置
                var current_marker = null;
                navigator.geolocation.getCurrentPosition(success, fail);
                function success(pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    var latlng = new google.maps.LatLng(lat, lng); //中心の緯度, 経度

                    var cur_pos = {
                        "gid":"",
                        "gmap_lat":lat,
                        "gmap_lng":lng,
                        "gmap_status":"OK",
                        "name":"現在位置",
                        "addr":"",
                        "moto":"",
                        "url":"",
                        "st_date":"",
                        "end_date":"",
                        "endend_date":"",
                        "barasi":"",
                        "gmap_invisible":0,
                        "hat_info":"",
                        "gmap_sumi":""
                    }

                    addresses.push(cur_pos);
                    geo(aftergeo)
                }
                function fail() {
                    geo(aftergeo)
                }

                //geo(aftergeo);

                // マーカーをクリックしたときのイベント登録
                function markerEvent(i) {
                    marker[i].addListener('click', function() {
                        for (const j in marker) {
                            //マーカーをクリックしたときに他の情報ウィンドウを閉じる
                            infoWindow[j].close(map, marker[j]);
                        }

                        //クリックされたマーカーの吹き出し（情報ウィンドウ）を表示
                        infoWindow[i].open(map, marker[i]);
                    });
                }

                function geo(callback) {
                    var cRef = addresses.length;

                    for (var i = 0; i < addresses.length; i++) {
                        (function(i) {
                                    if (addresses[i]['gmap_status'] === "OK") { // ステータスがOKの場合
                                        lat[i] = addresses[i]['gmap_lat']; //緯度を取得
                                        lng[i] = addresses[i]['gmap_lng']; //経度を取得
                                        if (maxLat < lat[i]) {
                                            maxLat = lat[i]
                                        }
                                        if (maxLng < lng[i]) {
                                            maxLng = lng[i]
                                        }
                                        if (minLat > lat[i]) {
                                            minLat = lat[i]
                                        }
                                        if (minLng > lng[i]) {
                                            minLng = lng[i]
                                        }
                                        //latlng[i] = results[0].geometry.location; // マーカーを立てる位置をセット
                                        latlng[i] = new google.maps.LatLng(lat[i],lng[i]);

                                        if(addresses[i]['name'] == '現在位置') {
                                            marker[i] = new google.maps.Marker({
                                                position: latlng[i], // マーカーを立てる位置を指定
                                                map: map, // マーカーを立てる地図を指定
                                                name: addresses[i]['name'],
                                                icon: {
                                                    url: '00652.png' // お好みの画像までのパスを指定
                                                }
                                            });
                                        }
                                        else if(Number(addresses[i]['gmap_sumi'])) {
                                            marker[i] = new google.maps.Marker({
                                                position: latlng[i], // マーカーを立てる位置を指定
                                                map: map, // マーカーを立てる地図を指定
                                                name: addresses[i]['name'],
                                                icon: {
                                                    url: 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png' // お好みの画像までのパスを指定
                                                }
                                            });
                                        }
                                        else {
                                            marker[i] = new google.maps.Marker({
                                                position: latlng[i], // マーカーを立てる位置を指定
                                                map: map, // マーカーを立てる地図を指定
                                                name: addresses[i]['name'],
                                                icon: {
                                                    url: 'https://maps.google.com/mapfiles/ms/micons/red-dot.png'
                                                }
                                            });
                                        }
                                        var seko_html = get_seko(i);

                                        if(addresses[i]['name'] == '現在位置') {
                                            infoWindow[i] = new google.maps.InfoWindow({
                                                content: `<div class="custom-info">現在地</div>`
                                            });
                                        }
                                        else {
                                            var sumi_chk = ``;
                                            var gmap_sumi = Number(addresses[i]['gmap_sumi']);
                                            if(gmap_sumi) {
                                                sumi_chk = `checked`;
                                            }

                                            var sumi_html = `<input type="checkbox" class="gmap_sumi" ${sumi_chk} data-id="${addresses[i]['gid']}"> 訪問済み`;

                                            // 各データごとに吹き出し（情報ウィンドウ）を作成
                                            infoWindow[i] = new google.maps.InfoWindow({
                                                content: `<div class="custom-info">
                                            <div class="custom-info-item name">
                                            <label>現場名：</label>${addresses[i]['name']} (<a href="${addresses[i]['url']}" target="_blank">${addresses[i]['gid']}</a>)
                                            </div>
                                            <div class="custom-info-item address">
                                            <label>住所：</label>${addresses[i]['addr']}
                                            </div>
                                            <div class="custom-info-item moto">
                                            <label>元請：</label>${addresses[i]['moto']}
                                            </div>
                                            <div class="custom-info-item date">
                                            <label>工事期間：</label>${addresses[i]['st_date']} 〜 ${addresses[i]['end_date']}
                                            </div>
                                            <div class="custom-info-item seko">
                                            ${seko_html}
                                            </div>
                                            <div class="custom-info-item sumi">
                                            ${sumi_html}
                                            </div>
                                            </div>` // 吹き出しに表示する内容
                                            });
                                        }
                                            // 各マーカーにクリックイベントを追加
                                            markerEvent(i);
                                            successarr.push(i);
                                    } else { // 失敗した場合
                                        failarr.push(i);
                                    } //if文の終了
                                    if (--cRef <= 0) {
                                        callback(); //全て取得できたらaftergeo実行
                                    }
                                    var sw = new google.maps.LatLng(maxLat, minLng);
                                    var ne = new google.maps.LatLng(minLat, maxLng);
                                    var bounds = new google.maps.LatLngBounds(sw, ne);
                                    map.fitBounds(bounds); //複数マーカーをマップに表示させる
                        })(i);
                    } //for文の終了

                } //function geo終了  
                function aftergeo() {
                    var opt = {
                        maxZoom: 15 // 地図の最大ズームを指定
                    };
                    map.setOptions(opt); //オプションをmapにセット

                    //失敗ありの場合
                    if(failarr.length) {
                        var title = document.createElement("h2");
                        title.className = 'title';
                        title.textContent = '地図に表示出来なかった現場一覧';
                        var table = document.createElement("table");
                        table.className = 'table table-sm';
                        var tr = document.createElement("tr");
                        var th1 = document.createElement("th");
                        th1.textContent = '現場ID';
                        tr.appendChild(th1);
                        var th2 = document.createElement("th");
                        th2.textContent = '現場名';
                        tr.appendChild(th2);
                        var th3 = document.createElement("th");
                        th3.textContent = '住所';
                        tr.appendChild(th3);
                        var th4 = document.createElement("th");
                        th4.textContent = '工事開始日';
                        tr.appendChild(th4);
                        var th5 = document.createElement("th");
                        th5.textContent = '工事終了日';
                        tr.appendChild(th5);
                        var th6 = document.createElement("th");
                        th6.textContent = '払し';
                        tr.appendChild(th6);
                        var th7 = document.createElement("th");
                        th7.textContent = '非表示';
                        tr.appendChild(th7);
                        var th8 = document.createElement("th");
                        th8.textContent = '訪問済';
                        tr.appendChild(th8);
                        table.appendChild(tr);

                        for (var f = 0; f < failarr.length; f += 1) {
                            tr = document.createElement("tr");
                            var td1 = document.createElement("td");
                            td1.textContent = addresses[failarr[f]]['gid'];
                            td1.innerHTML = '<a href="'+addresses[failarr[f]]['url']+'" target="_blank">'+addresses[failarr[f]]['gid']+'</a>';
                            tr.appendChild(td1);
                            var td2 = document.createElement("td");
                            td2.textContent = addresses[failarr[f]]['name'];
                            tr.appendChild(td2);
                            var td3 = document.createElement("td");
                            td3.textContent = addresses[failarr[f]]['addr'] ;
                            tr.appendChild(td3);
                            var td4 = document.createElement("td");
                            td4.className = "nw";
                            td4.textContent = addresses[failarr[f]]['st_date'];
                            tr.appendChild(td4);
                            var td5 = document.createElement("td");
                            td5.className = "nw";
                            td5.textContent = addresses[failarr[f]]['end_date'] ;
                            tr.appendChild(td5);
                            var td6 = document.createElement("td");
                            td6.textContent = baraarr[Number(addresses[failarr[f]]['barasi'])];
                            tr.appendChild(td6);
                            var td7 = document.createElement("td");
                            td7.className = "text-center";
                            td7.innerHTML = "<input type='checkbox' class='gmap_invisible' data-id='"+addresses[failarr[f]]['gid']+"'>"
                            tr.appendChild(td7);

                            var sumi_chk = "";
                            var gmap_sumi = Number(addresses[failarr[f]]['gmap_sumi']);
                            if(gmap_sumi) {
                                sumi_chk = "checked";
                            }

                            var td8 = document.createElement("td");
                            td8.className = "text-center";
                            td8.innerHTML = "<input type='checkbox' class='gmap_sumi' "+sumi_chk+" data-id='"+addresses[failarr[f]]['gid']+"'>"
                            tr.appendChild(td8);
                            table.appendChild(tr);
                        }
                        var elm = document.getElementById("fail");
                        elm.appendChild(title);
                        elm.appendChild(table);

                        var reload_flag = false;
                        var cnt = 0;
                        for (var i = 0; i < addresses.length; i++) {
                            if(addresses[i]['gmap_status'] == 'OVER_QUERY_LIMIT') {
                                reload_flag = true;
                                cnt += 1;
                                setTimeout(function func(i) {
                                    geocoder.geocode({
                                            'address': addresses[i]['addr']
                                        },
                                        function(results, status) { // 結果
                                            if (status === google.maps.GeocoderStatus.OK) { // ステータスがOKの場合
                                                lat[i] = results[0].geometry.location.lat(); //緯度を取得
                                                lng[i] = results[0].geometry.location.lng(); //経度を取得

                                                $.ajax({
                                                    async:		true,
                                                    cache:		false,
                                                    url:		"mapajax.php",
                                                    data:		{g_id:addresses[i]['gid'],gmap_lat:lat[i], gmap_lng:lng[i], gmap_status:status},
                                                    type:		"post",
                                                    headers: 	{"pragma": "no-cache"},
                                                    success:	function(data, textStatus) {
                                                    },
                                                    complete:	function(data, textStatus) {
                                                    }
                                                })
                                            }
                                        }
                                    )
                                },200*cnt, i)
                            }
                        }
                        if(reload_flag) {
                            console.log("btn")
                            $("#fail").prepend('<form><button type="submit" class="mt-3">google側でエラーが検出されました。このボタンを押して再読み込みして下さい</button></form>');
                        }
                    }
                    if(successarr.length) {
                        var title = document.createElement("h2");
                        title.className = 'title';
                        title.textContent = '地図に表示した現場一覧';
                        var table = document.createElement("table");
                        table.className = 'table table-sm';
                        var tr = document.createElement("tr");
                        var th1 = document.createElement("th");
                        th1.textContent = '現場ID';
                        tr.appendChild(th1);
                        var th2 = document.createElement("th");
                        th2.textContent = '現場名';
                        tr.appendChild(th2);
                        var th3 = document.createElement("th");
                        th3.textContent = '住所';
                        tr.appendChild(th3);
                        var th4 = document.createElement("th");
                        th4.textContent = '工事開始日';
                        tr.appendChild(th4);
                        var th5 = document.createElement("th");
                        th5.textContent = '工事終了日';
                        tr.appendChild(th5);
                        var th6 = document.createElement("th");
                        th6.textContent = '払し';
                        tr.appendChild(th6);
                        var th7 = document.createElement("th");
                        th7.textContent = '非表示';
                        tr.appendChild(th7);
                        var th8 = document.createElement("th");
                        th8.textContent = '訪問済';
                        tr.appendChild(th8);
                        table.appendChild(tr);

                        for (var f = 0; f < successarr.length; f += 1) {

                            //現在位置 スキップ
                            if(addresses[successarr[f]]['name'] == '現在位置') {
                                continue;
                            }


                            tr = document.createElement("tr");
                            var td1 = document.createElement("td");
                            td1.textContent = addresses[successarr[f]]['gid'];
                            td1.innerHTML = '<a href="'+addresses[successarr[f]]['url']+'" target="_blank">'+addresses[successarr[f]]['gid']+'</a>';
                            tr.appendChild(td1);
                            var td2 = document.createElement("td");
                            td2.textContent = addresses[successarr[f]]['name'];
                            tr.appendChild(td2);
                            var td3 = document.createElement("td");
                            td3.textContent = addresses[successarr[f]]['addr'] ;
                            tr.appendChild(td3);
                            var td4 = document.createElement("td");
                            td4.className = "nw";
                            td4.textContent = addresses[successarr[f]]['st_date'];
                            tr.appendChild(td4);
                            var td5 = document.createElement("td");
                            td5.className = "nw";
                            td5.textContent = addresses[successarr[f]]['end_date'] ;
                            tr.appendChild(td5);
                            var td6 = document.createElement("td");
                            td6.textContent = baraarr[Number(addresses[successarr[f]]['barasi'])];
                            tr.appendChild(td6);
                            var td7 = document.createElement("td");
                            td7.className = "text-center";
                            td7.innerHTML = "<input type='checkbox' class='gmap_invisible' data-id='"+addresses[successarr[f]]['gid']+"'>"
                            tr.appendChild(td7);

                            var sumi_chk = "";
                            var gmap_sumi = Number(addresses[successarr[f]]['gmap_sumi']);
                            if(gmap_sumi) {
                                sumi_chk = "checked";
                            }

                            var td8 = document.createElement("td");
                            td8.className = "text-center";
                            td8.innerHTML = "<input type='checkbox' class='gmap_sumi' "+sumi_chk+" data-id='"+addresses[successarr[f]]['gid']+"'>"
                            tr.appendChild(td8);
                            table.appendChild(tr);
                        }
                        var elm = document.getElementById("fail");
                        elm.appendChild(title);
                        elm.appendChild(table);

                        $("#fail").prepend("<div class='text-right'><button onclick='location.reload();return false;'>再読み込み</button></div>");
                    }      
                    $(document).on("click", ".gmap_invisible", function() {
                        var g_id = $(this).attr("data-id");
                        var val = 0;
                        if($(this).prop("checked")) {
                            val = 1;
                        }
                        $.ajax({
                            async:		true,
                            cache:		false,
                            url:		"invisible.php",
                            data:		{g_id:g_id, val:val},
                            type:		"post",
                            headers: 	{"pragma": "no-cache"},
                            success:	function(data, textStatus) {
                            },
                            complete:	function(data, textStatus) {
                            }
                        })
                    });          
                    $(document).on("click", ".gmap_sumi", function() {
                        var g_id = $(this).attr("data-id");
                        var val = 0;
                        if($(this).prop("checked")) {
                            val = 1;
                        }
                        $.ajax({
                            async:		true,
                            cache:		false,
                            url:		"sumi.php",
                            data:		{g_id:g_id, val:val},
                            type:		"post",
                            headers: 	{"pragma": "no-cache"},
                            success:	function(data, textStatus) {
                            },
                            complete:	function(data, textStatus) {
                            }
                        })
                    });          
                } //function aftergeo終了

                function get_seko(i) {
                    if(!addresses[i]['hat_info'].length) {
                        return '<p>職人割当無し</p>';
                    }
                    var html = "";
                    html += "<table class='table seko-table'>";
                    html += "<tr>";
                    html += "<th>工事</th>";
                    html += "<th>職人</th>";
                    html += "<th>開始日</th>";
                    html += "<th>終了日</th>";
                    html += "<tr>";
                    for(var hat of addresses[i]['hat_info']) {
                        html += "<tr>";
                        html += "<td>"+hat['sy_name']+"</td>";
                        html += "<td>"+hat['seko']+"</td>";
                        html += "<td>"+hat['s_st_date']+"</td>";
                        html += "<td>"+hat['s_end_date']+"</td>";
                        html += "</tr>";
                    }
                    html += "</table>";

                    return html;
                }
            }; //function initMap終了
        </script>
</body>

</html>
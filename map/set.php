<?php

require_once("../php/db_connect.php");

$sql = "select * from matsushima_genba 
WHERE 
g_id >= 0
AND
(
gmap_status is null
OR
gmap_status = 'OVER_QUERY_LIMIT'
)

";
$query = mysql_query($sql);
$num = mysql_num_rows($query);
while ($row = mysql_fetch_object($query)) {
    
    $addr = preg_replace('/付近/','',$row->g_genba_address);
    
    $shops_arr[] = array("gid"=>$row->g_id,"name" => $row->g_genba, "addr" => $addr, "moto" => "", "url" => "http://granz.sakura.ne.jp/system2/main/?{$row->g_id}");
}

$shops_arr_json = json_encode($shops_arr); //jsonにしてJSに渡す
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">    
    <title>google map</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCn7soGjAtIYhna4-J6Dx_fx-RSPNZalVc"></script>
    <script src="../js/jquery-1.8.2.min.js"></script>

    <style>
        .title {
            font-size:20px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
        <div id="map" style="width: 100%; height: 80vh;"></div>
        <script>
            'use strict';

setTimeout(function() {
location.reload();
},1000*60*3);            

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
                var myLatLng; //地図の中心点をセット用
                var geocoder;
                geocoder = new google.maps.Geocoder(); // ジオコードリクエストを送信するGeocoderの作成
                const map = new google.maps.Map(document.getElementById('map')); //地図を作成する
                geo(aftergeo);


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
                            setTimeout(function func(i) {
                            geocoder.geocode({
                                    'address': addresses[i]['addr']
                                },
                                function(results, status) { // 結果
                                    console.log("i:"+i)
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
                                                    console.log(data);
                                            },
                                            complete:	function(data, textStatus) {
                                            }
                                        })


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
                                        latlng[i] = results[0].geometry.location; // マーカーを立てる位置をセット

                                        marker[i] = new google.maps.Marker({
                                            position: results[0].geometry.location, // マーカーを立てる位置を指定
                                            map: map, // マーカーを立てる地図を指定
                                            name: addresses[i]['name'],
                                        });

                                        // 各データごとに吹き出し（情報ウィンドウ）を作成
                                        infoWindow[i] = new google.maps.InfoWindow({
                                            content: `<div class="custom-info">
                                        <div class="custom-info-item name">
                                        ${addresses[i]['name']} (<a href="${addresses[i]['url']}" target="_blank">${addresses[i]['gid']}</a>)
                                        </div>
                                        <div class="custom-info-item address">
                                        ${addresses[i]['addr']}
                                        </div>
                                        <div class="custom-info-item moto">
                                        ${addresses[i]['moto']}
                                        </div>
                                    </div>` // 吹き出しに表示する内容
                                        });

                                        // 各マーカーにクリックイベントを追加
                                        markerEvent(i);
                                    } else { // 失敗した場合
                                        failarr.push(i);

                                        $.ajax({
                                            async:		true,
                                            cache:		false,
                                            url:		"mapajax.php",
                                            data:		{g_id:addresses[i]['gid'], gmap_lat:"", gmap_lng:"", gmap_status:status},
                                            type:		"post",
                                            headers: 	{"pragma": "no-cache"},
                                            success:	function(data, textStatus) {
                                                    console.log(data);
                                            },
                                            complete:	function(data, textStatus) {
                                            }
                                        })

                                        console.log(status)
                                    } //if文の終了
                                    if (--cRef <= 0) {
                                        callback(); //全て取得できたらaftergeo実行
                                    }
                                    var sw = new google.maps.LatLng(maxLat, minLng);
                                    var ne = new google.maps.LatLng(minLat, maxLng);
                                    var bounds = new google.maps.LatLngBounds(sw, ne);
                                    map.fitBounds(bounds); //複数マーカーをマップに表示させる
                                } //function(results, status)の終了
                            ) //geocoder.geocodeの終了
                            },1000*i, i);
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
                        table.appendChild(tr);

                        for (var f = 0; f < failarr.length; f += 1) {
                            tr = document.createElement("tr");
                            var td1 = document.createElement("td");
                            td1.textContent = addresses[failarr[f]]['gid'];
                            tr.appendChild(td1);
                            var td2 = document.createElement("td");
                            td2.textContent = addresses[failarr[f]]['name'];
                            tr.appendChild(td2);
                            var td3 = document.createElement("td");
                            td3.textContent = addresses[failarr[f]]['addr'];
                            tr.appendChild(td3);
                            table.appendChild(tr);
                        }
                        var elm = document.getElementById("fail");
                        elm .appendChild(title);
                        elm.appendChild(table);
                    }
                } //function aftergeo終了
        </script>
    <div class="container-fluid">
        <div id="fail"></div>
    </div>
</body>

</html>

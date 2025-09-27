/**********************************************************************
 *
 *  共通 js
 *
 **********************************************************************/

/**********************************************************************
 *
 *  グローバル変数
 *
 **********************************************************************/
var page = 1;
var taxper = 0.05;

//フィールドに変更があったかをコントロール
var change_flag 		= false;
var through_flag = false;

//Datepickerの表示月数
var numOfMonths = 2;

//ローディングテキスト
var loading_text = "<p class='tac'>読み込み中です... <img src='../img/loadinfo_net.gif' /></p>";

var readonly_bg = "#DDD";

/**********************************************************************
 *
 *  共通初期化
 *
 **********************************************************************/
$(document).ready(function(){

	//バックスペースキー無効化
	key_event_bs();

	$( ".button" ).button();

	// Hover states on the static widgets
	$( "#dialog-link, #icons li" ).hover(
		function() {
			$( this ).addClass( "ui-state-hover" );
		},
		function() {
			$( this ).removeClass( "ui-state-hover" );
		}
	);

	$( "#dialog1" ).dialog({
		autoOpen: false,
		width: 400,
		buttons: [
			{
				text: "Ok",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});

	$( "#dialog2" ).dialog({
		autoOpen: false,
		width: 400
	});

	$( "#dialog3" ).dialog({
		autoOpen: false,
		width: 400,
		buttons: [
			{
				text: "Ok",
				click: function() {
					$( this ).dialog( "close" );
				}
			},
			{
				text: "Cancel",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		]
	});

	// Link to open the dialog
	$( "#dialog-link1" ).click(function( event ) {
		confirm_box1("#dialog-link1");
		event.preventDefault();
	});
	$( "#dialog-link2" ).click(function( event ) {
		confirm_box2("#dialog-link2");
		event.preventDefault();
	});
	$( "#dialog-link3" ).click(function( event ) {
		confirm_box3("#dialog-link3");
		event.preventDefault();
	});
	$( "#dialog-link4" ).click(function( event ) {
		show_status("成功メッセージ");
		event.preventDefault();
	});
	$( "#dialog-link5" ).click(function( event ) {
		show_error("エラーメッセージ!!");
		event.preventDefault();
	});
});

//OKのみ		
function confirm_box1(_str) {
	$( "#dialog1" ).dialog( "open" ).html(_str);
}
//時間で消える
function confirm_box2(_str) {
	$( "#dialog2" ).dialog( "open" ).html(_str);
	setTimeout( function() {
		$( "#dialog2" ).dialog( "close");
	}, 1500);
}
//OK & cancel
function confirm_box3(_str) {
	$( "#dialog3" ).dialog( "open" ).html(_str);
}

//status_area
function show_status(_str) {
	$("#status_msg").html(_str);
	$("#status_area").fadeIn("fast");	

	setTimeout( function() {
		$( "#status_area" ).fadeOut("fast");
	}, 5000);
}
//error_area
function show_error(_str) {
	$("#error_msg").html(_str);
	$("#error_area").fadeIn("fast");	

	setTimeout( function() {
		$( "#error_area" ).fadeOut("fast");
	}, 5000);
}

function show_success(_str) {
	show_status(_str);
	confirm_box2(_str);
}
function show_fail(_str, _textStatus) {
	show_error(_str);
	confirm_box1(_str);
	//write_debug(_textStatus);
}


/**********************************************************************
 *
 * 	ダイアログオープン
 *
 **********************************************************************/
function open_dialog() {

	window_resize();

	//内容フェードイン
	$("#dialog-area-outer").show();

	//リサイズ処理
	var resizeTimer = null;
	$(window).bind('resize', function() {
		if (resizeTimer) clearTimeout(resizeTimer);
		resizeTimer = setTimeout(window_resize, 100);
	});

	//領域内クリクでスルー
	$("#dialog-area").unbind("click");
	$("#dialog-area").click(function(event) {
		event.stopPropagation();
	});

/*	
	//領域外クリクで終了イベント
	//$("#dialog-area-outer").unbind("click");
	$("#dialog-area-outer").click(function(event) {
		event.stopPropagation();
		//オプショナルダイアログも閉じる
		close_optional();
		//ダイアログを閉じる
		close_dialog();
	});
	
	//クローズボタンイベント
	$(".dialog-close").click(function(event) {
		event.stopPropagation();
		close_dialog();
	});
*/
}

/**********************************************************************
 *
 * 	ダイアログクローズ
 *
 **********************************************************************/
function close_dialog() {

	//ダイアログ用イベントunbind
	$("#dialog-area-outer").unbind("click");
	$("#dialog-area").unbind("click");

	$("#dialog-area-outer").hide();
	//inner初期化
	$("#dialog-area-inner").html("");
}

/**********************************************************************
 *
 * 	ダイアログリサイズ
 *
 **********************************************************************/
function window_resize() {
	var ritsu = 0.9;
	$("#dialog-area")
		.width($(window).width()*ritsu)
		.height($(window).height()*ritsu)
		.css("margin-top",$(window).height()*(1-ritsu)/2);
}

/**********************************************************************
 *
 *  デバッグウィンドウ 本番時はコメントアウト
 *
 **********************************************************************/
function write_debug(_msg) {
	$("#debugArea").append("<pre>"+_msg+"</pre><br />");
}

/**********************************************************************
 *
 *  datepicker セット関数
 *
 **********************************************************************/
function set_datepicker(_elm) {
	$(_elm).datepicker({
		showButtonPanel: true,    //「今日」「閉じる」ボタンを表示する
		firstDay: 1,    //週の先頭を月曜日にする（デフォルトは日曜日）
		//年月をドロップダウンリストから選択できるようにする場合
		changeYear: true,
		changeMonth: true,
numberOfMonths: numOfMonths,
		autoSize: true,
		minDate: new Date(2010, 1 - 1, 1)
	});
}

/**********************************************************************
 *
 * 	バックスペースキー無効化制御
 *
 **********************************************************************/
function key_event_bs() {
	$(window).bind("keydown", function(e) {
		// [Alt] + [←]    
		if (e.altKey && (e.keyCode == 37)) {
			e.stopImmediatePropagation();
		}
		// [Alt] + [→]    
		if (e.altKey && (e.keyCode == 39)) {
			e.stopImmediatePropagation();
		}
		// [Backspace] colsはtextarea判別
		if (e.keyCode == 8
			&& (
			($(e.target).attr("type") != 'text'
			&& $(e.target).attr("cols") == undefined
			&& $(e.target).attr("type") != 'password')
			|| $(e.target).attr("readonly") == "readonly")
			) {
			//ブラウザ規定の動作をキヤンセル
			e.preventDefault();
		}
	});
}

/**********************************************************************
 *
 * 	本日日付文字列を生成する
 *
 **********************************************************************/
function get_today() {
	var today = new Date();
	var mon = eval(today.getMonth()) + 1;
	var dtstr = today.getFullYear() + "-" + mon + "-" + today.getDate();

	return dtstr;
}

/**********************************************************************
 *
 * 	本日をセットする
 *
 **********************************************************************/
function set_today(_elm, _idx) {

	$("."+_elm).eq(_idx).val( get_today() );
	
	//変更フラグ更新
	change_flag 	= true;
	
//----------------------個別領域 ここから----------------------------
	//支払日の場合翌月末をセット
	if(_elm == "s_shiharai_date") {
		td1 = new Date();
		y1 = td1.getFullYear();
		m1 = td1.getMonth() + 1;
		td2 = new Date(y1, m1, 0);
		d2 = td2.getDate();	
		$("."+_elm).eq(_idx).val(y1+"-"+m1+"-"+d2);
	}

//----------------------個別領域 ここまで----------------------------
	
}

/**********************************************************************
 *
 *  Focusコントロール 配色は dbform.cssを参照
 *
 **********************************************************************/
function inputFocus() {   
    $('input[title]').each(function() {   
        if($(this).val() === '') {   
            $(this).val($(this).attr('title'));    
        }   
           
        $(this).focus(function() {   
            if($(this).val() == $(this).attr('title')) {   
                $(this).val('').addClass('focused');       
            }   
        });   
        $(this).blur(function() {   
            if($(this).val() === '') {   
                $(this).val($(this).attr('title')).removeClass('focused');     
            }   
        });   
    });   
}

/**********************************************************************
 *
 *  チェックボックス操作
 *
 **********************************************************************/
function alluncheck(_elm) {

	$(_elm).each(function(i) {
		$(this).removeAttr("checked");
	});
}

function allcheck(_elm) {

	$(_elm).each(function(i) {
		$(this).attr("checked","checked");
	});
}

function get_checkbox_val(_elm) {
	var tmp_str = "";
	$(_elm).each(function(i) {
		var tmp = $(this).attr("checked");
		if(tmp == "checked") {
			if($(this).val() != "") {
				tmp_str += $(this).val() + ",";
			}
		}
	});
	//最後のコロンを取る
	tmp_str = tmp_str.replace(/\,$/,'');
	
	return tmp_str;
}

/**********************************************************************
 *
 *  js版エスケープ処理
 *
 **********************************************************************/
function h(ch) {
	
	//半角カナ数字変換
	ch = toHankakuNum(ch);
	if(ch == null || ch == '')
		return '';
	 
	//エスケープ処理
    ch = ch.replace(/&/g,"&amp;") ;
    ch = ch.replace(/"/g,"&quot;") ;
    ch = ch.replace(/'/g,"&#039;") ;
    ch = ch.replace(/</g,"&lt;") ;
    ch = ch.replace(/>/g,"&gt;") ;

    //円マーク
	ch = ch.replace(/\\/g,"￥") ;
	//;の二回以上のくり返しを変換 ;;はsqlの区切り
    ch = ch.replace(/([;])\1+/g,"$1") ;
	
	//数値なら , 取る
	var tmp_ch = ch.replace(/\,/g,'');
	if(!isNaN(tmp_ch)) {
		ch = tmp_ch;
	}
	
    return ch ;
}

/**********************************************************************
 *
 *  全角英数字・記号を半角に置換
 *
 **********************************************************************/
function toHankakuNum(src) {
	
  if(src == null || src == "")
  	return '';	

  //トリミング
  src = String(src).trim();
	
  var str = '';
  var len = src.length;
  for (var i = 0; i < len; i++) {
    var c = src.charCodeAt(i);
    if (c >= 65281 && c <= 65374 && c != 65340) {
      str += String.fromCharCode(c - 65248);
    } else if (c == 8217) {
      str += String.fromCharCode(39);
    } else if (c == 8221) {
      str += String.fromCharCode(34);
    } else if (c == 12288) {
      str += String.fromCharCode(32);
    } else if (c == 65507) {
      str += String.fromCharCode(126);
    } else if (c == 65509) {
      str += String.fromCharCode(92);
    } else {
      str += src.charAt(i);
    } 
  }

  //半角カタカナ全角変換 
  str = OneByteCharToFullSize(str);  

  return str;
};

// 全角カナの文字テーブル
var fullSizeCharacter = new Array(
  "。", "「", "」", "、", "・", "ヲ", "ァ", "ィ", "ゥ", "ェ",
  "ォ", "ャ", "ュ", "ョ", "ッ", "ー", "ア", "イ", "ウ", "エ",
  "オ", "カ", "キ", "ク", "ケ", "コ", "サ", "シ", "ス", "セ",
  "ソ", "タ", "チ", "ツ", "テ", "ト", "ナ", "ニ", "ヌ", "ネ",
  "ノ", "ハ", "ヒ", "フ", "ヘ", "ホ", "マ", "ミ", "ム", "メ",
  "モ", "ヤ", "ユ", "ヨ", "ラ", "リ", "ル", "レ", "ロ", "ワ",
  "ン", "゛", "゜"
);

// カタカナ文字であるか判別する
function IsKatakanaCode(c)
{
  return (c >= 65377 && c <= 65439);
}

// カタカナで「カ」～「ト」であるか判別する
function IsCode_KA_TO(c)
{
  return (c >= 65398 && c <= 65412);
}

// カタカナで「ハ」～「ホ」であるか判別する
function IsCode_HA_HO(c)
{
  return (c >= 65418 && c <= 65422);
}

// 半角カタカナを全角カタカナに変換する
function OneByteCharToFullSize(src)
{
  // 引数のチェック
  if(src == null)
    return "";

  var i, code, next;
  var str = new String;
  var len = src.length;
  for(i = 0; i < len; i++)
  {
    var c = src.charCodeAt(i); // 文字をキャラクターコードにする
    if(IsKatakanaCode(c))
    {
      // ==================
      // カタカナ文字の場合
      // ==================
      code = fullSizeCharacter[c - 65377];
      if(i < len - 1)
      {
        next = src.charCodeAt(i+1);
        if(next == 65438 && c == 65395) // "ヴ"の文字を正しく置換する
        {
          code = "ヴ";
          i++;
        }
        else if(next == 65438 && (IsCode_KA_TO(c) || IsCode_HA_HO(c))) // "濁音"の文字を正しく置換する
        {
          code = String.fromCharCode(code.charCodeAt(0)+1);
          i++;
        }
        else if (next == 65439 && IsCode_HA_HO(c)) // "半濁音"の文字を正しく置換する
        {
          code = String.fromCharCode(code.charCodeAt(0)+2);
          i++;
        }
      }
      str += code;
    }
    else
    {
      // ================
      // 通常の文字の場合
      // ================
      str += src.charAt(i);
    }
  }

  return str;
}

/**********************************************************************
 *
 *  日付チェック
 *
 **********************************************************************/
function DateCheck(date){
	// "-","/"で区切られているとき
    hiduke = date;
	if ((! hiduke.match(/^\d{2,4}\/\d{1,2}\/\d{1,2}$/)) && (! hiduke.match(/^\d{2,4}\-\d{1,2}\-\d{1,2}$/))){
        return false
    }

	a = new Array();
    if (hiduke.match(/^\d{2,4}\-\d{1,2}\-\d{1,2}$/)){
        a = hiduke.split("-")
	}else if (hiduke.match(/^\d{2,4}\/\d{1,2}\/\d{1,2}$/)){
        a = hiduke.split("/")
    }else{
        a[0] = date.substring(0,4)
        a[1] = date.substring(4,6)
        a[2] = date.substring(6,8)
    }

	//月のチェック
    if (a[1]*1 > 12 || a[1]*1 < 1){
        return false
    }
	//日のチェック
	//３１日までのとき
    if (a[1]*1 == 1 || a[1]*1 == 3 || a[1]*1 == 5 || a[1]*1 == 7 || a[1]*1 == 8 || a[1]*1 == 10 || a[1]*1 == 12){
        if (a[2]*1 > 31 || a[2]*1 < 1){
            return false
        }
    }else{
	//2月のチェック
        if (a[1]*1 == 2){
            if (a[0] % 4 == 0){
                if (a[2]*1 > 29 || a[2]*1 < 1){
                    return false
                }
            }else{
                if (a[2]*1 > 28 || a[2]*1 < 1){
                    return false
                }
            }
        }else{
		//３０日が末日のチェック
            if (a[2]*1 > 30 || a[2]*1 < 1){
                return false
            }
        }
    }
    return true
}  

/**********************************************************************
 *
 *  数値判定関数
 *
 **********************************************************************/
function is_num(_str) {
	
	//１度文字列へ変換後トリム
	_str = String(_str).trim();
	
	if(_str == "")
		return false;
	else if(_str == null)
		return false;
	else if(_str == "undifined")
		return false;
	else
		return isFinite(_str);
}

/**********************************************************************
 *
 *  数値以外は0を返す
 *
 **********************************************************************/
function to_num(_str) {
	
	if(_str == "")
		return 0;
	else if(_str == null)
		return 0;
	else if(_str == "undifined")
		return 0;
	
	_str = String(_str).replace(/\,/g,'');
	
	if(!is_num(_str)) {
		return 0;
	}
	else {
		return eval(_str);
		
	}
}

/**********************************************************************
 *
 *  trim関数
 *
 **********************************************************************/

String.prototype.trim = function() {
	
	return this.replace(/^\s+|\s+$/g, "");
}

/**********************************************************************
 *
 *  Number format関数
 *
 **********************************************************************/
function addFigure(str) {
	var num = new String(str).replace(/,/g, "");
	while(num != (num = num.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
	return num;
}

/**********************************************************************
 *
 *  カンマ区切り関数
 *
 **********************************************************************/
function myFormatNumber(x) { // 引数の例としては 95839285734.3245
	
  if(isNaN(x))
  		return x;
	
    var s = "" + x; // 確実に文字列型に変換する。例では "95839285734.3245"
    var p = s.indexOf("."); // 小数点の位置を0オリジンで求める。例では 11
    if (p < 0) { // 小数点が見つからなかった時
        p = s.length; // 仮想的な小数点の位置とする
    }
    var r = s.substring(p, s.length); // 小数点の桁と小数点より右側の文字列。例では ".3245"
    for (var i = 0; i < p; i++) { // (10 ^ i) の位について
        var c = s.substring(p - 1 - i, p - 1 - i + 1); // (10 ^ i) の位のひとつの桁の数字。例では "4", "3", "7", "5", "8", "2", "9", "3", "8", "5", "9" の順になる。
        if (c < "0" || c > "9") { // 数字以外のもの(符合など)が見つかった
            r = s.substring(0, p - i) + r; // 残りを全部付加する
            break;
        }
        if (i > 0 && i % 3 == 0) { // 3 桁ごと、ただし初回は除く
            r = "," + r; // カンマを付加する
        }
        r = c + r; // 数字を一桁追加する。
    }
    return r; // 例では "95,839,285,734.3245"
}

/**********************************************************************
 *
 * 	現在の検索条件を表示する
 *
 **********************************************************************/
function show_search_jyoken(_search_str_arr, _search_elm_arr) {
	//初期化
	$("#search_jyoken").html("");
	for(var i in _search_elm_arr) {
		if(_search_elm_arr[i].match(/_sel_/))
			var tmp_str = h($(_search_elm_arr[i]+" option:selected").text());
		else
			var tmp_str = h($(_search_elm_arr[i]).val());

		if(tmp_str == "")
			tmp_str = "指定無し";

		var tmp_title = _search_str_arr[i];

		$("#search_jyoken").append(tmp_title + "：<span style='color:green'>" + tmp_str + "</span>&nbsp;&nbsp;");
	}
	
}

/**********************************************************************
 *
 * 	検索ajax select表示
 *
 **********************************************************************/
function show_search_select(_tbl, _cls) {

	//検索条件リストボックス表示
	var flag = "SEARCH_SELECT";

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});

	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, _tbl, _cls]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
				$("#"+_cls+"_span").html(data);
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});
}

/**********************************************************************
 *
 * 	オプショナルのダイアログを表示する関数
 *
 **********************************************************************/
function show_optional(_tbl, _elm, _idx, _rtbl, _rclm) {

	var flag = "OPTIONAL";
	
	//テキストボックスの位置を取得
	var pos = $("."+_elm).eq(_idx).offset();

	$("#optional-area").css("top",pos.top + 20).css("left",pos.left).slideDown("fast");
	$("#optional-area-inner").html("<p class='tac mt50'>読み込み中です...");

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
		
	$.ajax({
		async:		true,
		cache:		false,
		url:		"../php/ajax_common.php",
		data:		{parm:[flag, _tbl, _elm, _idx, _rtbl, _rclm]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#optional-area-inner").html(data);
		},
		complete:	function(data, textStatus) {
			
			$("#dialog-area").click(function(event) {
				close_optional();
				event.stopPropagation();
			});
			
			$("body").unbind("click");
			$("body").click(function(event) {
				close_optional();
				event.stopPropagation();
			});
			
			//オプショナルエリアでのイベントを抑止
			$("#optional-area").unbind("click");
			$("#optional-area").click(function(event) {
				event.stopPropagation();
			});
			
			//閉じるボタンイベント
			$("#optional-area .closeButton").unbind("click");
			$("#optional-area .closeButton").click(function(event) {
				close_optional();
				event.stopPropagation();
			});
		},
		error:		function(data, textStatus) {
			write_debug(textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	オプショナルを選択した時の関数
 *
 **********************************************************************/
function set_optional(_val, _elm, _idx) {
	$("."+_elm).eq(_idx).val(_val);
	
	//changeイベントを送出
	$("."+_elm).eq(_idx).trigger("change");
	
	close_optional();
}
/**********************************************************************
 *
 * 	オプショナルダイアログを閉じる関数
 *
 **********************************************************************/
function close_optional() {
	
	//イベント解除
	$("body").unbind("click");
	$("#optional-area").unbind("click");
	$("#optional-area .closeButton").unbind("click");
	//フェードアウト
	$("#optional-area").slideUp("fast");
	
}

/**********************************************************************
 *
 * 	文字列から都道府県を抜く
 *
 **********************************************************************/
function del_pref(tmp_addr) {
	
	tmp_addr = tmp_addr.replace(/北海道|青森県|岩手県|宮城県|秋田県|山形県|福島県|茨城県|栃木県|群馬県|埼玉県|千葉県|東京都|神奈川県|新潟県|富山県|石川県|福井県|山梨県|長野県|岐阜県|静岡県|愛知県|三重県|滋賀県|京都府|大阪府|兵庫県|奈良県|和歌山県|鳥取県|島根県|岡山県|広島県|山口県|徳島県|香川県|愛媛県|高知県|福岡県|佐賀県|長崎県|熊本県|大分県|宮崎県|鹿児島県|沖縄県/,'');

	return tmp_addr;
}

/**********************************************************************
 *
 * 	郵便番号から住所補完
 *
 **********************************************************************/
function getPostal(_postal_elm, _address_elm) {
	
	var pos = $("."+_postal_elm).val();
	//ハイフンを削除
	pos = pos.replace("-","");
	
	//ハイフン付きの郵便番号に戻す
	rep_pos = pos.replace(/^(\d{3})(\d{4})$/, '$1-$2');
	$(".g_postal").val(rep_pos);

	if(pos != '') {

		$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
			if(originalOptions.type.toLowerCase() == 'post'){        
				options.data = jQuery.param($.extend(originalOptions.data||{}, {
				timeStamp: new Date().getTime()
				}));
			}
		});

		$.ajax({
			async:		true,
			cache:		false,
			url:		"../share/php/get_postal.php",
			data:		{"parm[]": [pos]},
			type:		"post",
			headers: 	{"pragma": "no-cache"},
			success:	function(data, textStatus) {
				
				if(data == "") {
						show_fail("該当する住所が郵便番号データベースにありませんでした");
				} else {
					//住所フィールドが空白なら郵便番号で補完
					if($("."+_address_elm).val() == "") {
						$("."+_address_elm).val(data);
					} else {
						//住所フィールドが空白でない場合は確認メッセージ
						var r = confirm("住所フィールドに住所が登録されています。郵便番号から一致した住所に置き換えてもよろしいですか？");	
						if(r)
							$("."+_address_elm).val(data);
					}
				}
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
			}
		});	
	}
}

/**********************************************************************
 *
 * 検索条件に当月をセットする関数
 *
 **********************************************************************/
function set_this_month() {

	td1 = new Date();
	y1 = td1.getFullYear();
	m1 = td1.getMonth() + 1;
	td2 = new Date(y1, m1, 0);
	d2 = td2.getDate();	

	$("#search_st_date").val(y1+"-"+m1+"-01");
	$("#search_end_date").val(y1+"-"+m1+"-"+d2);

	$("#search_sel_year").val(y1);	
	$("#search_sel_month").val(m1);	

}

function get_this_month() {

	td1 = new Date();
	y1 = td1.getFullYear();
	m1 = td1.getMonth() + 1;

	return m1;
}

/**********************************************************************
 *
 * 検索条件に当年をセットする関数
 *
 **********************************************************************/
function set_this_year() {
	td1 = new Date();
	y1 = td1.getFullYear();

	$("#search_sel_year").val(y1);	
}

function get_this_year() {
	td1 = new Date();
	y1 = td1.getFullYear();

	return y1;	
}

/**********************************************************************
 *
 * 検索条件に指定月をセットする関数
 *
 **********************************************************************/
function set_month() {

	y1 = eval($("#search_sel_year").val());
	m1 = eval($("#search_sel_month").val());
	if(m1 == "0") {
		m1 = 1;
		m2 = 12;	
	} else {
		m2 = m1;
	}
	td2 = new Date(y1, m2, 0);
	d2 = td2.getDate();	

	$("#search_st_date").val(y1+"-"+m1+"-01");
	$("#search_end_date").val(y1+"-"+m2+"-"+d2);

}

/**********************************************************************
 *

 * 	伝票一括印刷
 *
 **********************************************************************/
function print_all(_print_type, _elm) {

	var tmp_str = get_checkbox_val(_elm);

	if(tmp_str == "") {
		show_fail("印刷対象がチェックされていません");
	} else {

		//印刷
		print_pdf(_print_type, tmp_str);

		//全てのチェックをクリア
		alluncheck(_elm);
	}
}

/**********************************************************************
 *
 * 伝票を表示する関数 _type: estimate, hat_uchiwake, hattyu, invoice
 *
 **********************************************************************/
function print_pdf(_type, _cbno) {

	subwin=window.open("../print/"+_type+"?cbno="+_cbno ,"sub"+_type,"width=950,height=700, scrollbars=yes, resizable=yes");
	
}

function set_readonly(_obj) {
	$(_obj).addClass("readonly-bg");
}
function set_disable(_obj) {
	$(_obj).addClass("readonly-bg").attr("disabled","disabled");
}


/**********************************************************************
 *
 * 	行表示cookie処理
 *
 **********************************************************************/
function set_row_size_changer() {
	$("#per_page").change(function() {

		//ページリセット
		page = 1;
		show_list();

		//cookieへ書き込み
		$.cookie('row_size', $(this).val(), {path: '/'});

	});
}

/**********************************************************************
 *
 * 	行表示cookie初期化
 *
 **********************************************************************/
function init_row_size() {
	
	//cookieから読み込み
	var rowsize = $.cookie('row_size');
	if(rowsize == "" || rowsize == null) {
		$("#per_page").val("15");
	} else {
		$("#per_page").val(rowsize);
	}
}

//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------

function month_add(_p) {
	
	var month = to_num($("#shime_month").val());
	var year = to_num($("#shime_year").val());
	month += _p;
	
	if(month >= 13) {
		month = 1;
		year++;
	}
	else if(month <= 0) {
		month = 12;
		year--;
	}
	
	$("#shime_month").val(month);
	$("#shime_year").val(year);
	
}

/**********************************************************************
 *
 *  ajax実行
 *
 **********************************************************************/
function ajax_exec(_func, _fail, _ajaxfile, _parm) {
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		  if(originalOptions.type.toLowerCase() == 'post'){       
			   options.data = jQuery.param($.extend(originalOptions.data||{}, {
			   timeStamp: new Date().getTime()
			   }));
		 }
	 });
	
	$.ajax({
		 async:          true,
		 cache:          false,
		 url:          	_ajaxfile,
		 data:          _parm,
		 type:          "post",
		 headers:      {"pragma": "no-cache"},
		 success:     function(data, textStatus) {
		 	_func(data, _parm);
		 },
		 complete:     function(data, textStatus) {
		 },
		 error:          function(data, textStatus) {
		 	_func(textStatus);
		 }
	});
}

/**********************************************************************
 *
 *  ajax json実行
 *
 **********************************************************************/
function ajax_exec_json(_func, _fail, _ajaxfile, _parm) {
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		  if(originalOptions.type.toLowerCase() == 'post'){       
			   options.data = jQuery.param($.extend(originalOptions.data||{}, {
			   timeStamp: new Date().getTime()
			   }));
		 }
	 });
	
	$.ajax({
		 async:          true,
		 cache:          false,
		 url:          	_ajaxfile,
		 data:          _parm,
		 type:          "post",
		 headers:      {"pragma": "no-cache"},
		 dataType:     "json",
		 success:     function(data, textStatus) {
		 	_func(data, _parm);
		 },
		 complete:     function(data, textStatus) {
		 },
		 error:          function(data, textStatus) {
		 	_func(textStatus);
		 }
	});
}

/**********************************************************************
 *
 * ajax失敗処理
 *
 **********************************************************************/
function ajax_fail(textStatus) {
	show_fail("ajaxでの表示に失敗しました。", textStatus);
	write_debug(textStatus);
}

/**********************************************************************
 *
 *  変数
 *
 **********************************************************************/
var orderc = "";
var editMode = 1;
//----------------------個別領域 ここから----------------------------
var MAIN_TABLE = "matsushima_slip_est";
var MAIN_ID_FIELD = "s_id";
//----------------------個別領域 ここまで----------------------------

/**********************************************************************
 *
 * 	ロード時処理
 *
 **********************************************************************/
$(document).ready(function(){
	
	//引数取得
	str=location.search.substring(1)

	//引数ありの場合は現場編集
	if(str != '') {
	
	} 
	else {
	
	}

	//menu操作
	$("ul.dropdown > li").eq(0).css("background","#FFFFDC");

	//全てチェック
	$("#all-check").click(function() {
		allcheck(".chk");
	});
	
	//チェックを全て外す
	$("#all-uncheck").click(function() {
		alluncheck(".chk");
	});
		
	//検索処理
	$("#search_btn").click(function() {
		page = 1;
		show_list();
	});
	
	//検索リセット処理
	$("#reset_btn").click(function() {
		resetSearch();
	});

	//更新処理
	$("#all-reload").click(function() {
		show_list();
	});

	$("#all-print-s").click(function() {
		var cbno = get_checkbox_val('.chk');
		if(cbno == "") {
			show_fail("チェックされていません。");
		}
		else {
			subwin1=window.open("../print/print0.php?sid="+cbno ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
			subwin1.focus();
		}
	});

	//表示行数コントロール
	init_row_size();
	set_row_size_changer();

//----------------------個別領域 ここから----------------------------
	
	//datepicker
	$("#search_st_date").eq(0).datepicker({
		inline: true,
		showButtonPanel: true,
		changeMonth: true,
		numberOfMonths: numOfMonths
	});
	$("#search_end_date").eq(0).datepicker({
		inline: true,
		showButtonPanel: true,
		changeMonth: true,
		numberOfMonths: numOfMonths
	});

//----------------------個別領域 ここまで----------------------------

	$("#shime_year").val(get_this_year());
	$("#shime_month").val(get_this_month());

	//条件セレクトエリア表示
	set_jyoken();

	//表示
	show_list();
	
});

/**********************************************************************
 *
 * 検索条件のりセット関数
 *
 **********************************************************************/
function resetSearch() {
	//ページ初期化
	page = 1;
	orderc = "";

//----------------------個別領域 ここから----------------------------
	$("#search_id").val("");
	$("#search_g_id").val("");
	$("#search_kw1").val("");
	$("#search_kw2").val("");
	$("#shime_year").val(get_this_year());
	$("#shime_month").val(get_this_month());
	$("#search_sel_kubun").val(0);
	$("#search_sel_moto").val(0);
	$("#search_sel_branch").val(0);
	$("#search_sel_tantou").val(0);
	$("#search_st_date").val("");
	$("#search_end_date").val("");
	$("#date_sel").val("0");

//----------------------個別領域 ここまで----------------------------
	
	//表示
	show_list();
}

/**********************************************************************
 *
 * 	一覧画面を表示する関数
 *
 **********************************************************************/
function show_list() {
	$(".main-area").html(loading_text).show();
	$(".search-area").show();
	$(".button-area").show();
	
	$("#all-delete-main").show();
	$("#edit-mode").show();	
	
	$("#all-print-s").show();

//----------------------個別領域 ここから----------------------------
	var search_id = to_num($("#search_id").val());
	if(!search_id)
		search_id = "";
	var search_g_id = to_num($("#search_g_id").val());
	if(!search_g_id)
		search_g_id = "";
	
	var search_kw1 = h($("#search_kw1").val());
	var search_kw2 = h($("#search_kw2").val());
	
	var shime_year = h($("#shime_year").val());
	var shime_month = h($("#shime_month").val());
	var search_sel_kubun = h($("#search_sel_kubun").val());
	var search_sel_moto = h($("#search_sel_moto").val());
	var search_sel_branch = h($("#search_sel_branch").val());
	var search_sel_tantou = h($("#search_sel_tantou").val());

	var search_st_date = h($("#search_st_date").val());
	var search_end_date = h($("#search_end_date").val());
	var date_sel = h($("#date_sel").val());

	var per_page = h($("#per_page").val());

	//検索条件表示設定
	var jyoken = "";

	//期間
	if(search_st_date != "" && search_end_date == "") {
		jyoken += "<b>期間：</b>";
		jyoken += search_st_date;
		jyoken += "～&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	else if(search_st_date == "" && search_end_date != "") {
		jyoken += "<b>期間：～</b>";
		jyoken += search_end_date;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	else if(search_st_date != "" && search_end_date != "") {
		jyoken += "<b>期間：</b>";
		jyoken += search_st_date;
		jyoken += "&nbsp;～&nbsp;";
		jyoken += search_end_date;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	//期間
	if(search_st_date != "" || search_end_date != "") {
		jyoken += "<b>対象期間：</b>";
		jyoken += $("#date_sel option:selected").text();
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	
	//ID
	if(search_g_id != "") {
		jyoken += "<b>現場ID：</b>";
		jyoken += search_g_id;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	if(search_id != "") {
		jyoken += "<b>見積ID：</b>";
		jyoken += search_id;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//キーワード1
	if(search_kw1 != "") {
		jyoken += "<b>キーワード1：</b>";
		jyoken += search_kw1;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//キーワード2
	if(search_kw2 != "") {
		jyoken += "<b>キーワード1：</b>";
		jyoken += search_kw2;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//元請
	if(search_sel_moto != 0) {
		jyoken += "<b>元請：</b>";
		jyoken += $("#search_sel_moto option:selected").text();
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//支店
	if(search_sel_branch != 0) {
		jyoken += "<b>支店グループ：</b>";
		jyoken += $("#search_sel_branch option:selected").text();
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//担当
	if(search_sel_tantou != 0) {
		jyoken += "<b>担当：</b>";
		jyoken += $("#search_sel_tantou option:selected").text();
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//商談状況
	if(search_sel_kubun != 0) {
		jyoken += "<b>商談状況：</b>";
		jyoken += $("#search_sel_kubun option:selected").text();
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	
	//条件セット
	if(jyoken == "") {
		$("#result").hide();
	}
	else {
		$("#result").show();
		$("#jyoken-area").html(jyoken);
	}

//----------------------個別領域 ここまで----------------------------
	
	var flag = "SHOW_LIST";

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
		data:		{parm:[flag, page, orderc,search_id,search_kw1,search_kw2,shime_year,shime_month,search_sel_moto,search_st_date,search_end_date,date_sel,search_sel_tantou,search_sel_kubun,per_page,search_sel_branch, search_g_id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
				$(".main-area").html(data);
		},
		complete:	function(data, textStatus) {

			//非同期の関係でchange_flagがリセットされない場合があるので、ここで完全にリセットする
			change_flag = false;

			//テーブルを斑に
			$(".list-table tr:odd").addClass("madara");

			//ボタンコントロール
			$( ".button" ).button();

			//印刷ボタンイベントハンドリング
			$(".ui-icon-print").unbind("click");
			$(".ui-icon-print").click(function(e) {
				var idx = $(".ui-icon-print").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				subwin2=window.open("../print/print0.php?sid="+id ,"sub2", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin2.focus();
			});

			//印刷ボタンイベントハンドリング
			$(".ui-icon-print2").unbind("click");
			$(".ui-icon-print2").click(function(e) {
				var idx = $(".ui-icon-print2").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();

				subwin2=window.open("../print/print0.php?sid="+id ,"sub2", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin2.focus();
				already_print(1, id, idx);

			});

			//検索結果合計の表示
			if(jyoken != "") {
				
				var kekka = "";
				//レコード数
				kekka += "<b>現場数：</b>";
				kekka += $("#allrecord").val();
				kekka += "件&nbsp;&nbsp;&nbsp;&nbsp;";
				//見積額
				kekka += "<b>見積額：</b>";
				kekka += $("#ttl_est").val();
				kekka += "&nbsp;&nbsp;&nbsp;&nbsp;";
				
				$("#jyoken-result-area").html(kekka);
			}
			else {
				$("#jyoken-result-area").html("");
			}

		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
		}
	});
}

function already_print(_slipno, _id, _idx) {

	var flag = "ALREADY_PRINT";

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
		data:		{"parm[]": [flag, _slipno, _id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			
			if(_slipno == 1 || _slipno == 2) {
				var btn = $(".ui-icon-print2").eq(_idx).attr("src");
				if(btn.match(/sumi/))
					$(".ui-icon-print2").eq(_idx).attr("src","../img/b_print.png");
				else
					$(".ui-icon-print2").eq(_idx).attr("src","../img/b_sumi.png");
			}
			
			
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("印刷済み処理が失敗しました。", textStatus);
		}
	});
}


/**********************************************************************
 *
 * 	ページセット
 *
 **********************************************************************/
function set_page(_p) {
	
	var tmp = to_num(_p);
	if(!tmp)
		tmp = 1;
		
	page = tmp;
	show_list();
}

/**********************************************************************
 *
 * 	ソート処理
 *
 **********************************************************************/
function sort_order(_p) {
	
	if(orderc.match(/DESC/))
		orderc = _p;
	else
		orderc = _p+" DESC";

	show_list();
}


/**********************************************************************
 *
 * 	条件フィールド表示
 *
 **********************************************************************/
function set_jyoken() {

	var flag = "SET_JYOKEN";

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
		data:		{parm:[flag]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#jyoken_area").html(data);
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("データの取得に失敗しました。", textStatus);
		}
	});
}

//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------
//----------------------個別領域 ここから----------------------------
//----------------------個別領域 ここまで----------------------------

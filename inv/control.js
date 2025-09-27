/**********************************************************************
 *
 *  変数
 *
 **********************************************************************/
var orderc = "";
var editMode = 1;
//----------------------個別領域 ここから----------------------------
var MAIN_TABLE = "matsushima_inv";
var MAIN_ID_FIELD = "i_id";
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
	$("ul.dropdown > li").eq(1).css("background","#FFFFDC");

	//全てチェック
	$("#all-check").click(function() {
		allcheck(".chk");
	});
	
	//チェックを全て外す
	$("#all-uncheck").click(function() {
		alluncheck(".chk");
	});
	
	//一括削除
	$("#all-delete-main").click(function() {
		all_delete_main();
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

	$("#all-print-s").click(function() {
		var cbno = get_checkbox_val('.chk');
		if(cbno == "") {
			show_fail("チェックされていません。");
		}
		else {
			subwin4=window.open("../print/invoice.php?sid="+cbno ,"sub4", "width=950,height=700, scrollbars=yes, resizable=yes");
			subwin4.focus();
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
	
	//エンター制御
	$(document).on("keypress","input",function (e) {
        if (e.which === 13) {
            return false;
        }
    });
	
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
	$("#search_moto_id").val("");
	$("#search_g_id").val("");
	$("#search_kw1").val("");
	$("#search_kw2").val("");
	$("#shime_year").val(get_this_year());
	$("#shime_month").val(get_this_month());
	$("#search_sel_moto").val(0);
	$("#search_sel_branch").val(0);

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
//	$("#all-print-m").hide();

//----------------------個別領域 ここから----------------------------
	var search_id = to_num($("#search_id").val());
	if(!search_id)
		search_id = "";
	
	var search_moto_id = to_num($("#search_moto_id").val());
	if(!search_moto_id)
		search_moto_id = "";
	
	var search_g_id = to_num($("#search_g_id").val());
		if(!search_g_id)
			search_g_id = "";
		
	var search_kw1 = h($("#search_kw1").val());
	var search_kw2 = h($("#search_kw2").val());
	
	var shime_year = h($("#shime_year").val());
	var shime_month = h($("#shime_month").val());
	var search_sel_moto = h($("#search_sel_moto").val());
	var search_sel_branch = h($("#search_sel_branch").val());

	var per_page = h($("#per_page").val());
	//無制限モード
	/*
	if(_unlimited) {
		per_page = 1000;
	}
	*/
	//検索条件表示設定
	//検索条件表示設定
	var jyoken = "";

	//期間
	if(shime_year != "" && shime_month != "") {
		jyoken += "<b>対象年月：</b>";
		jyoken += shime_year + "年"+shime_month+"月";
		jyoken += "～&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	
	//ID
	if(search_id != "") {
		jyoken += "<b>請求ID：</b>";
		jyoken += search_id;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//moto ID
	if(search_moto_id != "") {
		jyoken += "<b>元請ID：</b>";
		jyoken += search_moto_id;
		jyoken += "&nbsp;&nbsp;&nbsp;&nbsp;";
	}

	//現場 ID
	if(search_g_id != "") {
		jyoken += "<b>現場ID：</b>";
		jyoken += search_g_id;
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
		data:		{parm:[flag, page, orderc,search_id,search_kw1,search_kw2,shime_year,shime_month,search_sel_moto,search_sel_branch, per_page, search_moto_id, search_g_id]},
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
			
			//編集モード初期化
			editMode = 1;
			edit_mode_switch();
			$("#edit-mode").unbind("click");
			$("#edit-mode").click(function() {
				edit_mode_switch();
			});
			
			//編集モードの保存
			$(".main_update_btn").unbind("click");
			$(".main_update_btn").click(function() {
				update_list();
			});

			//編集ボタンイベントハンドリング
			$(".ui-icon-pencil").unbind("click");
			$(".ui-icon-pencil").click(function(e) {
				var idx = $(".ui-icon-pencil").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				edit_main(id);
			});

			//削除ボタンイベントハンドリング
			$(".ui-icon-trash").unbind("click");
			$(".ui-icon-trash").click(function(e) {
				var idx = $(".ui-icon-trash").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				delete_main(id);
			});

			//印刷ボタンイベントハンドリング
			$(".ui-icon-print").unbind("click");
			$(".ui-icon-print").click(function(e) {
				var idx = $(".ui-icon-print").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				subwin4=window.open("../print/invoice.php?sid="+id ,"sub4", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin4.focus();
			});

			//印刷ボタンイベントハンドリング
			$(".ui-icon-print-m").unbind("click");
			$(".ui-icon-print-m").click(function(e) {
				var idx = $(".ui-icon-print-m").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				subwin1=window.open("../print/print1.php?sidall="+id ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin1.focus();
			});

			$("#all-print-m").click(function() {
				var cbno = get_checkbox_val('.chk');
				if(cbno == "") {
					show_fail("チェックされていません。");
				}
				else {
					subwin1=window.open("../print/print1.php?sidall="+cbno ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
					subwin1.focus();
				}
			});


			//datepicker
			$(".i_inv_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});

			$(".i_send_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});

			$(".i_receipt_yotei_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});

			$(".i_receipt_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});
			var result = "";

			result += "<b>全件数：</b>";
			result += $("#ttl-num").val();
			result += "&nbsp;&nbsp;&nbsp;&nbsp;";
			result += "<b>総請求額：</b>";
			result += addFigure($("#ttl-inv").val());
			result += "&nbsp;&nbsp;&nbsp;&nbsp;";
			result += "<b>総入金額：</b>";
			result += addFigure($("#ttl-rec").val());
			result += "&nbsp;&nbsp;&nbsp;&nbsp;";
			result += "<b>総手数料等額：</b>";
			result += addFigure($("#ttl-com").val());
			result += "&nbsp;&nbsp;&nbsp;&nbsp;";
			result += "<b>総差額：</b>";
			result += addFigure($("#ttl-sagaku").val());
			result += "&nbsp;&nbsp;&nbsp;&nbsp;";

			$("#jyoken-result-area").html(result);

		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
		}
	});
}

/**********************************************************************
 *
 * MAIN画面編集モードスイッチ トグル動作
 *
 **********************************************************************/
function edit_mode_switch() {
	
	if(editMode) {
		$(".edit_mode").hide();
		$(".list_mode").show();
		$(".main_update_btn").hide();
		editMode = 0;
	} else {
		$(".edit_mode").show();
		$(".list_mode").hide();
		$(".main_update_btn").show();
		editMode = 1;
	}
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
 * 	編集画面を表示する関数
 *
 **********************************************************************/
function edit_main(_id) {
	$(".main-area").html(loading_text).show();
	$(".search-area").hide();

	$("#all-delete-main").hide();
	$("#edit-mode").hide();	

	$("#all-print-s").hide();
	$("#all-print-m").show();

	var flag = "EDIT_MAIN";

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
		data:		{parm:[flag, _id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$(".main-area").html(data);
		},
		complete:	function(data, textStatus) {

			//focus設定を使う
			inputFocus();

			//ボタンコントロール
			$( ".button" ).button();

			//フィールドに変更があったかをコントロール
			change_flag = false;
			$("input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//変更コントロール
			$(".back_btn").unbind("click");
			$(".back_btn").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄して一覧に戻ってもよろしいですか？");
					if(r) {
						through_flag = true;
						show_list();
					}
				} else {
					through_flag = true;
					show_list();
				}
			});

			//他のページに遷移する場合の保存確認
			$(window).bind('beforeunload', function(e) {
				if(change_flag)
					return "保存されていない項目があります。変更内容を破棄して一覧に戻ってもよろしいですか？";
			});

			//削除ボタンイベントハンドリング
			$(".ui-icon-trash").unbind("click");
			$(".ui-icon-trash").click(function(e) {
				var idx = $(".ui-icon-trash").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				del_inv_part(id, _id);
			});

			//印刷ボタンイベントハンドリング
			$(".ui-icon-print").unbind("click");
			$(".ui-icon-print").click(function(e) {
				var idx = $(".ui-icon-print").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				subwin1=window.open("../print/print1.php?sid="+id ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin1.focus();
			});

			//印刷ボタンイベントハンドリング
			$(".ui-icon-print-m").unbind("click");
			$(".ui-icon-print-m").click(function(e) {
				var idx = $(".ui-icon-print-m").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				subwin1=window.open("../print/print1.php?sid="+id ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin1.focus();
			});

			$("#all-print-m").click(function() {
				var cbno = get_checkbox_val('.chk');
				if(cbno == "") {
					show_fail("チェックされていません。");
				}
				else {
					subwin1=window.open("../print/print1.php?sid="+cbno ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
					subwin1.focus();
				}
			});

//----------------------個別領域 ここから----------------------------

			//readonly
			//set_readonly(obj);
			//disabled
			//set_disable(obj);
//----------------------個別領域 ここまで----------------------------

		},
		error:		function(data, textStatus) {
			show_fail("データの取得に失敗しました。", textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	MAIN LISTを更新
 *
 **********************************************************************/
function update_list() {
	
//----------------------個別領域 ここから----------------------------
//	var chk_main 	= input_check_main();

	if(1) {
//----------------------個別領域 ここまで----------------------------
		r = confirm("保存してよろしいですか？");
		if(r) {	
			var sql = "";
//----------------------個別領域 ここから----------------------------
			$("."+MAIN_ID_FIELD).each(function(i, elm) {
				var id = $(this).val();

				sql += "UPDATE "+MAIN_TABLE+" SET ";
				sql += "i_inv_date = '"+ h($(".i_inv_date").eq(i).val()) +"'";
				sql += ",";
				sql += "i_chosei_name = '"+ h($(".i_chosei_name").eq(i).val()) +"'";
				sql += ",";
				sql += "i_chosei = '"+ h($(".i_chosei").eq(i).val()) +"'";
				sql += ",";
				sql += "i_receipt_yotei_date = '"+ h($(".i_receipt_yotei_date").eq(i).val()) +"'";
				sql += ",";
				sql += "i_receipt_date = '"+ h($(".i_receipt_date").eq(i).val()) +"'";
				sql += ",";
				sql += "i_send_date = '"+ h($(".i_send_date").eq(i).val()) +"'";
				sql += ",";
				sql += "i_receipt_kingaku = '"+ h($(".i_receipt_kingaku").eq(i).val()) +"'";
				sql += ",";
				sql += "i_biko = '"+ h($(".i_biko").eq(i).val()) +"'";
				sql += ",";
				sql += "i_commission = '"+ h($(".i_commission").eq(i).val()) +"'";
				sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";

			});
//----------------------個別領域 ここまで----------------------------

			sql = sql.replace(/;;$/,'');
			//write_debug(sql);

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
				url:		"../php/sql.php",
				data:		{"parm[]": [sql]},
				type:		"post",
				headers: 	{"pragma": "no-cache"},
				success:	function(data, textStatus) {
					if(data != "fail") {
						show_success("保存が成功しました。");

//----------------------個別領域 ここから----------------------------
						show_list();
//----------------------個別領域 ここまで----------------------------
						
					}
					else {
						show_fail("保存が失敗しました。", textStatus);
					}
				},
				complete:	function(data, textStatus) {
				},
				error:		function(data, textStatus) {
					show_fail("保存が失敗しました。", textStatus);
				}
			});
		}
	}
}

/**********************************************************************
 *
 * 	MAINを更新
 *
 **********************************************************************/
function update_main() {
	
//----------------------個別領域 ここから----------------------------
	var chk_main 	= input_check_main();

	if(!chk_main) {
//----------------------個別領域 ここまで----------------------------
		r = confirm("保存してよろしいですか？");
		if(r) {	
			var sql = "";
			var id = $("."+MAIN_ID_FIELD).eq(0).val();
//----------------------個別領域 ここから----------------------------
			//新規か更新か
			if(id) { //更新
				sql += "UPDATE "+MAIN_TABLE+" SET ";
				sql += "i_moto_id = '"+ h($(".i_moto_id").eq(0).val()) +"'";
				sql += ",";
				sql += "i_year = '"+ h($(".i_year").eq(0).val()) +"'";
				sql += ",";
				sql += "i_month = '"+ h($(".i_month").eq(0).val()) +"'";
				sql += ",";
				sql += "i_inv_date = '"+ h($(".i_inv_date").eq(0).val()) +"'";
				sql += ",";
				sql += "i_receipt_yotei_date = '"+ h($(".i_receipt_yotei_date").eq(0).val()) +"'";
				sql += ",";
				sql += "i_receipt_date = '"+ h($(".i_receipt_yotei_date").eq(0).val()) +"'";
				sql += ",";
				sql += "i_send_date = '"+ h($(".i_send_date").eq(0).val()) +"'";
				sql += ",";
				sql += "i_receipt_kingaku = '"+ to_num(h($(".i_receipt_kingaku").eq(0).val())) +"'";
				sql += ",";
				sql += "i_biko = '"+ h($(".i_biko").eq(0).val()) +"'";
				sql += ",";
				sql += "i_commission = '"+ to_num(h($(".i_commission").eq(0).val())) +"'";
				sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
				
			}
			else {
				//新規
				sql += "INSERT INTO "+MAIN_TABLE+" (i_moto_id, i_year, i_month, i_inv_date, i_receipt_yotei_date, i_receipt_date, i_send_date, i_receipt_kingaku,i_biko, i_commission) VALUES ( ";
				sql += "'"+h($(".i_moto_id").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".i_year").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".i_month").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".i_inv_date").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".i_receipt_yotei_date").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".i_receipt_date").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".i_send_date").eq(0).val())+"'";
				sql += ",";
				sql += "'"+to_num(h($(".i_receipt_kingaku").eq(0).val()))+"'";
				sql += ",";
				sql += "'"+h($(".i_biko").eq(0).val())+"'";
				sql += ",";
				sql += "'"+to_num(h($(".i_commission").eq(0).val()))+"'";
				sql += ");;";
			}
//----------------------個別領域 ここまで----------------------------

			sql = sql.replace(/;;$/,'');
			//write_debug(sql);

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
				url:		"../php/sql.php",
				data:		{"parm[]": [sql]},
				type:		"post",
				headers: 	{"pragma": "no-cache"},
				success:	function(data, textStatus) {
					if(data != "fail") {
						show_success("保存が成功しました。");
						
						//新規の場合はIDをセット
						var new_id = to_num(data);
						if(new_id) {
							$("."+MAIN_ID_FIELD).eq(0).val(new_id);	
							$("."+MAIN_ID_FIELD+"_span").eq(0).text(new_id);	
						}
						else {
							new_id = id;
						}

//----------------------個別領域 ここから----------------------------
						//更新
						edit_main(new_id);
						
//----------------------個別領域 ここまで----------------------------
						
					}
					else {
						show_fail("保存が失敗しました。", textStatus);
					}
				},
				complete:	function(data, textStatus) {
				},
				error:		function(data, textStatus) {
					show_fail("保存が失敗しました。", textStatus);
				}
			});
		}
	}
	else {
		show_fail("入力内容を確認して下さい。");
	}
}

/**********************************************************************
 *
 * 	MAINの入力チェック
 *
 **********************************************************************/
function input_check_main() {

	var tmp = "";
	var err_flag = false;

//----------------------個別領域 ここから----------------------------

	if($(".i_moto_id").eq(0).val() == "") {
		err_flag = true;
		$(".i_moto_id_err").eq(0).html("必須項目です");
	}

//----------------------個別領域 ここまで----------------------------

	if(!err_flag) {
		//エラーメッセージクリア	
		$(".error-field").html("");
	}

	return err_flag;
}

/**********************************************************************
 *
 * 	MAIN一括削除
 *
 **********************************************************************/
function all_delete_main() {

	var cbno = get_checkbox_val(".chk");
	
	if(cbno == "") {
		show_fail("締め解除対象がチェックされていません");
	} else {
		//削除処理
		delete_main(cbno);

		//チェックをクリア
		alluncheck(".chk");
	}
}

/**********************************************************************
 *
 * 	MAIN削除
 *
 **********************************************************************/
function delete_main(_id) {

	r = confirm("締めを解除しても本当によろしいですか？");

	if(r && _id != "") {
		//MAIN削除
		var sql = "";
//----------------------個別領域 ここから----------------------------
		sql += "UPDATE matsushima_slip SET s_inv_id = 0 WHERE s_inv_id in ("+_id+");;";
		sql += "DELETE FROM "+MAIN_TABLE+" WHERE "+MAIN_ID_FIELD+" in ("+_id+");;";
		
//----------------------個別領域 ここまで----------------------------
		//最後のセミコロンを取る
		sql = sql.replace(/\;;$/,'');
		//write_debug(sql); 

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
			url:		"../php/sql.php",
			data:		{"parm[]": [sql]},
			type:		"post",
			headers: 	{"pragma": "no-cache"},
			success:	function(data, textStatus) {
				if(data != "fail") {
					show_success("締めを解除しました。");
					show_list();
				}
				else {
					show_fail("締めを解除が失敗しました。", textStatus);
				}
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
				show_fail("締めを解除が失敗しました。", textStatus);
			}
		});

	}
}

/**********************************************************************
 *
 * 	請求から一部の現場を外す
 *
 **********************************************************************/
function del_inv_part(_id, _i_id) {

	r = confirm("締めを解除しても本当によろしいですか？");

	if(r && _id != "") {
		//MAIN削除
		var sql = "";
//----------------------個別領域 ここから----------------------------
		sql += "UPDATE matsushima_slip SET s_inv_id = 0 WHERE s_id = "+_id+";;";

		//sql += "DELETE FROM "+MAIN_TABLE+" WHERE "+MAIN_ID_FIELD+" in ("+_id+");;";
//----------------------個別領域 ここまで----------------------------
		//最後のセミコロンを取る
		sql = sql.replace(/\;;$/,'');
		//write_debug(sql); 

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
			url:		"../php/sql.php",
			data:		{"parm[]": [sql]},
			type:		"post",
			headers: 	{"pragma": "no-cache"},
			success:	function(data, textStatus) {
				if(data != "fail") {
					show_success("締めを解除しました。");
					edit_main(_i_id);
				}
				else {
					show_fail("締めを解除が失敗しました。", textStatus);
				}
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
				show_fail("締めを解除が失敗しました。", textStatus);
			}
		});

	}
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

function calc_main() {
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

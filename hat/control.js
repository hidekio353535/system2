/**********************************************************************
 *
 *  変数
 *
 **********************************************************************/
var orderc = "";
var editMode = 1;
//----------------------個別領域 ここから----------------------------
var MAIN_TABLE = "matsushima_hat";
var MAIN_ID_FIELD = "h_id";
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
	$("ul.dropdown > li").eq(2).css("background","#FFFFDC");

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
			subwin4=window.open("../print/hattyu.php?sid="+cbno ,"sub4", "width=950,height=700, scrollbars=yes, resizable=yes");
			subwin4.focus();
		}
	});
	
	$("#all-print-m").click(function() {
		var cbno = get_checkbox_val('.chk');
		if(cbno == "") {
			show_fail("チェックされていません。");
		}
		else {
			subwin1=window.open("../print/print2.php?cbno="+cbno ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
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
	$("#search_kw1").val("");
	$("#search_kw2").val("");
	$("#shime_year").val(get_this_year());
	$("#shime_month").val(get_this_month());
	$("#search_sel_seko").val(0);

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
	$("#all-print-m").hide();

//----------------------個別領域 ここから----------------------------
	var search_id = to_num($("#search_id").val());
	if(!search_id)
		search_id = "";

	var search_kw1 = h($("#search_kw1").val());
	var search_kw2 = h($("#search_kw2").val());
	
	var shime_year = h($("#shime_year").val());
	var shime_month = h($("#shime_month").val());
	var search_sel_seko = h($("#search_sel_seko").val());

	var per_page = h($("#per_page").val());
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
		data:		{parm:[flag, page, orderc,search_id,search_kw1,search_kw2,shime_year,shime_month,search_sel_seko,per_page]},
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
				subwin4=window.open("../print/hattyu.php?sid="+id ,"sub4", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin4.focus();
			});


			//datepicker
			$(".h_hat_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});

			$(".h_send_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});

			$(".h_receipt_yotei_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});

			$(".h_receipt_date").each(function(i, elm) {
				$(this).datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true,
numberOfMonths: numOfMonths
				});
			});

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
				del_hat_part(id, _id);
			});

			//印刷ボタンイベントハンドリング
			$(".ui-icon-print").unbind("click");
			$(".ui-icon-print").click(function(e) {
				var idx = $(".ui-icon-print").index(this);
				var id = $("."+MAIN_ID_FIELD).eq(idx).val();
				subwin1=window.open("../print/print2.php?cbno="+id ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
				subwin1.focus();
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
				sql += "h_hat_date = '"+ h($(".h_hat_date").eq(i).val()) +"'";
				sql += ",";
				sql += "h_chosei_name = '"+ h($(".h_chosei_name").eq(i).val()) +"'";
				sql += ",";
				sql += "h_chosei = '"+ h($(".h_chosei").eq(i).val()) +"'";
				sql += ",";
				sql += "h_receipt_yotei_date = '"+ h($(".h_receipt_yotei_date").eq(i).val()) +"'";
				sql += ",";
				sql += "h_receipt_date = '"+ h($(".h_receipt_date").eq(i).val()) +"'";
				sql += ",";
				sql += "h_send_date = '"+ h($(".h_send_date").eq(i).val()) +"'";
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
				sql += "h_seko_id = '"+ h($(".h_seko_id").eq(0).val()) +"'";
				sql += ",";
				sql += "h_year = '"+ h($(".h_year").eq(0).val()) +"'";
				sql += ",";
				sql += "h_month = '"+ h($(".h_month").eq(0).val()) +"'";
				sql += ",";
				sql += "h_hat_date = '"+ h($(".h_hat_date").eq(0).val()) +"'";
				sql += ",";
				sql += "h_receipt_yotei_date = '"+ h($(".h_receipt_yotei_date").eq(0).val()) +"'";
				sql += ",";
				sql += "h_receipt_date = '"+ h($(".h_receipt_yotei_date").eq(0).val()) +"'";
				sql += ",";
				sql += "h_send_date = '"+ h($(".h_send_date").eq(0).val()) +"'";
				sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
				
			}
			else {
				//新規
				sql += "INSERT INTO "+MAIN_TABLE+" (h_seko_id, h_year, h_month, h_hat_date, h_receipt_yotei_date, h_receipt_date, h_send_date) VALUES ( ";
				sql += "'"+h($(".h_seko_id").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".h_year").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".h_month").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".h_hat_date").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".h_receipt_yotei_date").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".h_receipt_date").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".h_send_date").eq(0).val())+"'";
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

	if($(".h_seko_id").eq(0).val() == "") {
		err_flag = true;
		$(".h_seko_id_err").eq(0).html("必須項目です");
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
		sql += "UPDATE matsushima_slip_hat SET s_hat_id = 0 WHERE s_hat_id in ("+_id+");;";
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
 * 	発注から一部の現場を外す
 *
 **********************************************************************/
function del_hat_part(_id, _h_id) {

	r = confirm("締めを解除しても本当によろしいですか？");

	if(r && _id != "") {
		//MAIN削除
		var sql = "";
//----------------------個別領域 ここから----------------------------
		sql += "UPDATE matsushima_slip_hat SET s_hat_id = 0 WHERE s_id = "+_id+";;";

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
					edit_main(_h_id);
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

function calc_main() {
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

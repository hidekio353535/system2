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
	$("ul.dropdown > li").eq(2).css("background","#FFFFDC");

	//新規現場作成
	$("#new_btn").click(function() {
		edit_main(0);
	});
	
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

	//datepicker
	$("#shime_date").eq(0).datepicker({
		inline: true,
		showButtonPanel: true,
		changeMonth: true,
numberOfMonths: numOfMonths
	});

	$("#shime_date").val(get_today());
	$("#shime_year").val(get_this_year());
	$("#shime_month").val(get_this_month());

	$(".main-area").hide();
	$(".button-area").hide();

	get_hat();
	$(document).on("change", "#shime_month, #shime_year", function() {
		get_hat();
	});
	$(document).on("click", ".shime_exec_btn", function() {
		get_hat();
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
	$("#shime_group").val("");
	$("#shime_date").val("");
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
//----------------------個別領域 ここから----------------------------
	var shime_group = h($("#shime_group").val());
	var shime_date = h($("#shime_date").val());
	var shime_year = h($("#shime_year").val());
	var shime_month = h($("#shime_month").val());
	var sel_hat_id = $("#sel_hat_id").val();
	
	
	//入力チェック
	if(shime_group == "0") {
		show_fail("締めグループが選択されていません。");
		return;
	}

	if(shime_year == "") {
		show_fail("締め年月の年に値がありません。");
		return;
	}

	if(shime_month == "") {
		show_fail("締め年月の月に値がありません。");
		return;
	}

	if(to_num(shime_month) < 1 || to_num(shime_month) > 12) {
		show_fail("締め年月の月の値が正しくありません。");
		return;
	}
	
	if(shime_date == "") {
		show_fail("締日に値がありません。");
		return;
	}

	$(".search-area").show();
	$(".button-area").show();
	$(".main-area").html(loading_text).show();

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
		data:		{parm:[flag, page, orderc, shime_date, shime_year, shime_month, sel_hat_id]},
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
			
			//編集ボタンイベントハンドリング
			$(".shime_exec_btn").unbind("click");
			$(".shime_exec_btn").click(function(e) {
				shime_exec();
			});
		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
		}
	});
}

/**********************************************************************
 *
 * 締め実行
 *
 **********************************************************************/
function shime_exec() {
	var cbno = get_checkbox_val(".chk");

	var shime_date = h($("#shime_date").val());
	var shime_year = h($("#shime_year").val());
	var shime_month = h($("#shime_month").val());

	var flag = "SHIME_EXEC";

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
		data:		{parm:[flag, cbno, shime_date, shime_year, shime_month]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
				$(".main-area").html(data);
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
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
 * 	編集画面を表示する関数
 *
 **********************************************************************/
function edit_main(_id) {
	$(".main-area").html(loading_text).show();
	$(".search-area").hide();
	$(".button-area").hide();

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

			//保存ボタンイベント
			$(".update_btn").unbind("click");
			$(".update_btn").click(function() {
				update_main();
				change_flag = false;	
			});

			//デートピッカー
			$(".sp_date").eq(0).datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
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
				sql += "seko = '"+ h($(".seko").eq(i).val()) +"'";
				sql += ",";
				sql += "seko_nik = '"+ h($(".seko_nik").eq(i).val()) +"'";
				sql += ",";
				sql += "account = '"+ h($(".account").eq(i).val()) +"'";
				sql += ",";
				sql += "meigi = '"+ h($(".meigi").eq(i).val()) +"'";
				sql += ",";
				sql += "is_bankshow = '"+ h($(".is_bankshow").eq(i).val()) +"'";
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
				sql += "seko = '"+ h($(".seko").eq(0).val()) +"'";
				sql += ",";
				sql += "seko_nik = '"+ h($(".seko_nik").eq(0).val()) +"'";
				sql += ",";
				sql += "account = '"+ h($(".account").eq(0).val()) +"'";
				sql += ",";
				sql += "meigi = '"+ h($(".meigi").eq(0).val()) +"'";
				sql += ",";
				sql += "is_bankshow = '"+ h($(".is_bankshow").eq(0).val()) +"'";
				sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
				
			}
			else {
				//新規
				sql += "INSERT INTO "+MAIN_TABLE+" (seko, seko_nik, account, meigi, is_bankshow) VALUES ( ";
				sql += "'"+h($(".seko").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".seko_nik").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".account").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".meigi").eq(0).val())+"'";
				sql += ",";
				sql += "'"+h($(".is_bankshow").eq(0).val())+"'";
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

	if($(".seko").eq(0).val() == "") {
		err_flag = true;
		$(".seko_err").eq(0).html("必須項目です");
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
		show_fail("削除対象がチェックされていません");
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

	r = confirm("削除しても本当によろしいですか？");

	if(r && _id != "") {
		//MAIN削除
		var sql = "";
//----------------------個別領域 ここから----------------------------
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
					show_success("削除しました。");
					show_list();
				}
				else {
					show_fail("削除が失敗しました。", textStatus);
				}
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
				show_fail("削除が失敗しました。", textStatus);
			}
		});

	}
}

function calc_main() {
}

function get_hat() {
//----------------------個別領域 ここから----------------------------
	var shime_group = h($("#shime_group").val());
	var shime_date = h($("#shime_date").val());
	var shime_year = h($("#shime_year").val());
	var shime_month = h($("#shime_month").val());

//----------------------個別領域 ここまで----------------------------
	
	var flag = "GET_HAT";

 	$.ajax({
		async:		true,
		cache:		false,
		url:		"get_hat.php",
		data:		{parm:[flag, page, orderc, shime_date, shime_year, shime_month]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
				$("#shime_hat_span").html(data);
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
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

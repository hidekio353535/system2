/**********************************************************************
 *
 *  変数
 *
 **********************************************************************/
//----------------------個別領域 ここから----------------------------
var MAIN_TABLE = "matsushima_nai_3";
var MAIN_ID_FIELD = "nai3_id";
var clm 		= new Array("nai3_id","nai3","nai3_nik");
var mtitle = "■工事内容3マスタ管理";
//----------------------個別領域 ここまで----------------------------

var orderc = "";
var editMode = 1;

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
	//タイトル
	$("#mtitle").html(mtitle);
	//menu操作
	$("ul.dropdown > li").eq(4).css("background","#FFFFDC");

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

	//表示行数コントロール
	init_row_size();
	set_row_size_changer();


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
	$("#search_kw1").val("");
	$("#search_kw2").val("");
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

	var per_page = h($("#per_page").val());

//----------------------個別領域 ここから----------------------------
	var search_id = to_num($("#search_id").val());
	if(!search_id)
		search_id = "";

	var search_kw1 = h($("#search_kw1").val());
	var search_kw2 = h($("#search_kw2").val());

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
		data:		{parm:[flag, page, orderc,search_id,search_kw1,search_kw2,per_page]},
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

			//保存ボタンイベント
			$(".update_btn").unbind("click");
			$(".update_btn").click(function() {
				update_main();
				change_flag = false;	
			});

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
				for(var cnt=0;cnt<clm.length;cnt++) {
					sql += clm[cnt] + " = '"+ h($("."+clm[cnt]).eq(i).val()) +"'";
					sql += ",";
				}
				sql = sql.replace(/,$/,'');
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
				for(var cnt=1;cnt<clm.length;cnt++) {
					sql += clm[cnt] + " = '"+ h($("."+clm[cnt]).eq(0).val()) +"'";
					sql += ",";
				}
				sql = sql.replace(/,$/,'');
				sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
				
			}
			else {
				//新規
				sql += "INSERT INTO "+MAIN_TABLE+" (";
				for(var cnt=1;cnt<clm.length;cnt++) {
					sql += clm[cnt];
					sql += ",";
				}
				sql = sql.replace(/,$/,'');
				sql += ") VALUES ( ";

				for(var cnt=1;cnt<clm.length;cnt++) {
					sql += "'"+h($("."+clm[cnt]).eq(0).val())+"'";
					sql += ",";
				}
				sql = sql.replace(/,$/,'');
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

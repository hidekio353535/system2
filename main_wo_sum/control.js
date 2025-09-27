/**********************************************************************
 *
 *  変数
 *
 **********************************************************************/
var orderc = "";
var editMode = 1;
var tenkai_flag = false;

//----------------------個別領域 ここから----------------------------
var MAIN_TABLE = "matsushima_genba";
var MAIN_ID_FIELD = "g_id";

var SLIP_TABLE = new Array(4);
var SLIP_ID_FIELD = new Array(4);
var SLIP_REL_FIELD = new Array(4);

var MEISAI_TABLE = new Array(4);
var MEISAI_ID_FIELD = new Array(4);
var MEISAI_REL_FIELD = new Array(4);

SLIP_TABLE[0] = "matsushima_slip_est";
SLIP_ID_FIELD[0] = "s_id";
SLIP_REL_FIELD[0] = "s_genba_id";

SLIP_TABLE[1] = "matsushima_slip";
SLIP_ID_FIELD[1] = "s_id";
SLIP_REL_FIELD[1] = "s_genba_id";

SLIP_TABLE[2] = "matsushima_slip_hat";
SLIP_ID_FIELD[2] = "s_id";
SLIP_REL_FIELD[2] = "s_genba_id";

SLIP_TABLE[3] = "matsushima_slip_jv";
SLIP_ID_FIELD[3] = "s_id";
SLIP_REL_FIELD[3] = "s_genba_id";

MEISAI_TABLE[0] = "matsushima_meisai_est";
MEISAI_ID_FIELD[0] = "m_id";
MEISAI_REL_FIELD[0] = "m_s_id";

MEISAI_TABLE[1] = "matsushima_meisai";
MEISAI_ID_FIELD[1] = "m_id";
MEISAI_REL_FIELD[1] = "m_s_id";

MEISAI_TABLE[2] = "matsushima_meisai_hat";
MEISAI_ID_FIELD[2] = "m_id";
MEISAI_REL_FIELD[2] = "m_s_id";

MEISAI_TABLE[3] = "matsushima_meisai_jv";
MEISAI_ID_FIELD[3] = "m_id";
MEISAI_REL_FIELD[3] = "m_s_id";

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
	
	//条件セレクトエリア表示
	set_jyoken();

//----------------------個別領域 ここまで----------------------------

	//表示
	if(str != "") {
		if(str.match(/date/)) {
			var dt = str.split('&date=');

			edit_main(dt[0]);

			//カレンダーからの初期値を入力
			setTimeout( function() {
				$(".slip-area:eq(2) .s_st_date").eq(0).val(dt[1]);
				//$(".slip-area:eq(2) .s_seko_kubun_id").eq(0).val(2);
				$(".slip-area:eq(2) .s_date").eq(0).val(get_today());
			}, 5000);
			
		}
		else {
			edit_main(str);
		}
	}
	else {
		show_list();
	}
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
	$("#sp_status_id_sel").val(0);
	$("#search_st_date").val("");
	$("#search_end_date").val("");
	$("#date_sel").val("order_date");
	$("#search_sel_moto").val(0);
	$("#search_sel_tantou").val(0);
	$("#search_sel_status").val(0);
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

//----------------------個別領域 ここから----------------------------
	var search_id = to_num($("#search_id").val());
	if(!search_id)
		search_id = "";

	var search_kw1 = h($("#search_kw1").val());
	var search_kw2 = h($("#search_kw2").val());
	var sp_status_id_sel = h($("#sp_status_id_sel").val());
	var search_st_date = h($("#search_st_date").val());
	var search_end_date = h($("#search_end_date").val());
	var date_sel = h($("#date_sel").val());
	
	var search_sel_moto = h($("#search_sel_moto").val());
	var search_sel_tantou = h($("#search_sel_tantou").val());
	var search_sel_status = h($("#search_sel_status").val());
	
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
		data:		{parm:[flag, page, orderc,search_id,search_kw1,search_kw2,search_st_date,search_end_date,date_sel,search_sel_moto,search_sel_tantou,search_sel_status,per_page]},
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
			
			//ファイルアップロード
			if(_id) {
				
				show_file(_id);
			}
//----------------------個別領域 ここから----------------------------

			//readonly
			//set_readonly(obj);
			//disabled
			//set_disable(obj);

			//slip 表示
			edit_slip(_id, 0);
			edit_slip(_id, 1);
			edit_slip(_id, 2);
			edit_slip(_id, 3);

//----------------------個別領域 ここまで----------------------------

		},
		error:		function(data, textStatus) {
			show_fail("データの取得に失敗しました。", textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	編集画面を表示する関数
 *
 **********************************************************************/
function edit_slip(_id, _slipno) {
	$(".slip-area").eq(_slipno).html(loading_text).show();
	var flag = "EDIT_SLIP";

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
		data:		{parm:[flag, _id, _slipno]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$(".slip-area").eq(_slipno).html(data);
		},
		complete:	function(data, textStatus) {
			//focus設定を使う
			inputFocus();

			//ボタン装飾
			$( ".button" ).button();

			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".slip-area:eq("+_slipno+") input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//編集ボタンイベントハンドリング
			$(".slip-area:eq("+_slipno+") .ui-icon-pencil").unbind("click");
			$(".slip-area:eq("+_slipno+") .ui-icon-pencil").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .ui-icon-pencil").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();
				edit_meisai(id, _slipno);
			});

			//印刷表示ボタンイベントハンドリング
			$(".slip-area:eq("+_slipno+") .ui-icon-print").unbind("click");
			$(".slip-area:eq("+_slipno+") .ui-icon-print").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .ui-icon-print").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				print_denpyo(_slipno, id);

			});

			//印刷ボタンイベントハンドリング
			$(".slip-area:eq("+_slipno+") .ui-icon-print2").unbind("click");
			$(".slip-area:eq("+_slipno+") .ui-icon-print2").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .ui-icon-print2").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				print_denpyo(_slipno, id);
				already_print(_slipno, id, idx);

			});

			//削除ボタンイベントハンドリング
			$(".slip-area:eq("+_slipno+") .ui-icon-trash").unbind("click");
			$(".slip-area:eq("+_slipno+") .ui-icon-trash").click(function(e) {
				r = confirm("本当に削除してよろしいですか？");
				if(r) {
					var idx = $(".slip-area:eq("+_slipno+") .ui-icon-trash").index(this);
					var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();
					
					if(id != "") {
	
						var sql = "";
						sql += "DELETE FROM "+MEISAI_TABLE[_slipno]+" WHERE "+MEISAI_REL_FIELD[_slipno]+" = "+id+";;";
						sql += "DELETE FROM "+SLIP_TABLE[_slipno]+" WHERE "+SLIP_ID_FIELD[_slipno]+" = "+id+";;";

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
									$(".slip-area:eq("+_slipno+") #slip_tbody>tr").eq(idx).remove();
									show_success("削除しました。");
									//slipの表示コントロール
									slip_field_ctr(_slipno);
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
						
					} else {
						$(".slip-area:eq("+_slipno+") #slip_tbody>tr").eq(idx).remove();
						//slipの表示コントロール
						slip_field_ctr(_slipno);
					}
				}
			});

			//行追加ボタン
			$(".slip-area:eq("+_slipno+") #insert_slip_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") #insert_slip_btn").bind("click", function() {
				$(".slip-area:eq("+_slipno+") #slip_tbody>tr:last").clone(true).insertBefore($(".slip-area:eq("+_slipno+") #slip_tbody>tr:last")).show();
				//slipの表示コントロール
				slip_field_ctr(_slipno);
			});

			//受注操作
			$(".slip-area:eq("+_slipno+") .jyutyu_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .jyutyu_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .jyutyu_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					set_jyutyu(id, idx);
			});

			//受注操作
			$(".slip-area:eq("+_slipno+") .hattyu_jv_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_jv_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_jv_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();
	
				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					set_hat_jv(id, idx);
			});
			
			//通常発注に変更
			$(".slip-area:eq("+_slipno+") .hattyu_jv_to_normal_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_jv_to_normal_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_jv_to_normal_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();
				var k = $(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(idx).val();
	
				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					change_to_normal(id, idx, k);
			});

			//JV発注に変更
			$(".slip-area:eq("+_slipno+") .hattyu_normal_to_jv_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_normal_to_jv_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_normal_to_jv_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();
	
				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					change_to_jv(id, idx);
			});
			

			//コピー操作
			$(".slip-area:eq("+_slipno+") .copy_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .copy_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .copy_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();
				
				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					copy_est(id);
			});


			//締めパターン表示
			if(_slipno == 1) {
				var moto_id = h($(".g_moto_id").eq(0).val());
				set_m_pat_area(moto_id);
			}
			
			//発注操作
			$(".slip-area:eq("+_slipno+") .hattyu_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					edit_hattyu(id, _slipno);
			});

			//発注操作編集
			$(".slip-area:eq("+_slipno+") .hattyu_mod_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_mod_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_mod_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					edit_mod_hattyu(_id, _slipno);
			});

			//東リース発注操作編集
			$(".slip-area:eq("+_slipno+") .hattyu_mod_azuma_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_mod_azuma_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_mod_azuma_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					edit_mod_hattyu_azuma(_id, _slipno);
			});

			//JV発注操作編集
			$(".slip-area:eq("+_slipno+") .hattyu_mod_jv_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_mod_jv_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_mod_jv_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					edit_mod_hattyu_jv(_id, _slipno);
			});

			//発注操作 東リース
			$(".slip-area:eq("+_slipno+") .hattyu_azuma_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_azuma_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_azuma_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();
				var k = $(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(idx).val();

				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else
					edit_azuma_hattyu(_id, id, _slipno, k);
			});

			//JV発注操作編集 東リース
			$(".slip-area:eq("+_slipno+") .hattyu_mod_jv_azuma_btn").unbind("click");
			$(".slip-area:eq("+_slipno+") .hattyu_mod_jv_azuma_btn").click(function(e) {
				var idx = $(".slip-area:eq("+_slipno+") .hattyu_mod_jv_azuma_btn").index(this);
				var id = $(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).eq(idx).val();

				if(change_flag)
					alert("未保存のデータがあります。\nこの処理を行うにはまず保存して下さい。");
				else			
					edit_mod_hattyu_jv_azuma(_id, _slipno);
			});

			//slipの表示コントロール
			slip_field_ctr(_slipno);
			
			//発注限界額のセット
/*			
			if(_slipno == "1") {
				var hat_limit = 0;
				$(".slip-area:eq("+_slipno+") .s_invoice").each(function(i, elm) {
					hat_limit += to_num(h($(this).val()));
				});
				//発注限界額計算 100円マージン
				var g_hat_per = to_num(h($("#g_hat_per").val()));
				
				//受注額が0かつ%0の場合
				if(hat_limit == 0 && g_hat_per == 0) {
					hat_limit = 0;
				}
				//受注額のみ0
				else if(hat_limit == 0) {
					hat_limit = 0;
				}
				//0%の場合の処理
				else if(g_hat_per == 0) {
					hat_limit = Math.ceil(hat_limit);
				}
				else {
					hat_limit = Math.ceil(hat_limit * g_hat_per / 100) + 100; 
				}
				$("#hat_limit").val(hat_limit);
			}
*/
		},
		error:		function(data, textStatus) {
			show_fail("データの取得に失敗しました。", textStatus);
		}
	});
}

function print_denpyo(_slipno, _id) {
	switch(_slipno) {
		case 0:
			subwin0=window.open("../print/print"+_slipno+".php?sid="+_id ,"sub0", "width=950,height=700, scrollbars=yes, resizable=yes");
			subwin0.focus();
			break;
		case 1:
			subwin1=window.open("../print/print"+_slipno+".php?sid="+_id ,"sub1", "width=950,height=700, scrollbars=yes, resizable=yes");
			subwin1.focus();
			break;
		case 2:
			subwin2=window.open("../print/print"+_slipno+".php?cbno="+_id ,"sub2", "width=950,height=700, scrollbars=yes, resizable=yes");
			subwin2.focus();
			break;
		case 3:
			subwin3=window.open("../print/hat_jv.php?sid="+_id ,"sub3", "width=950,height=700, scrollbars=yes, resizable=yes");
			subwin3.focus();
			break;
	}
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
			
			if(_slipno == 1 || _slipno == 2 || _slipno == 3) {
				var btn = $(".slip-area:eq("+_slipno+") .ui-icon-print2").eq(_idx).attr("src");
				if(btn.match(/sumi/))
					$(".slip-area:eq("+_slipno+") .ui-icon-print2").eq(_idx).attr("src","../img/b_print.png");
				else
					$(".slip-area:eq("+_slipno+") .ui-icon-print2").eq(_idx).attr("src","../img/b_sumi.png");
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
 * 	MEISAIを表示する関数
 *
 **********************************************************************/
function edit_meisai(_id, _slipno) {

	//ローディング画像表示
	$("#dialog-area-inner").html(loading_text).show();

	flag = "EDIT_MEISAI";

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
		data:		{parm:[flag, _id, _slipno]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#dialog-area-inner").html(data);
			open_dialog();
		},
		complete:	function(data, textStatus) {

			//トップへスクロールする
			$('html,body').animate({ scrollTop: 0 }, 'fast');
				
			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".meisai-table input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//閉じるボタン
			$(".closeDiag").unbind("click");
			$(".closeDiag").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//領域外クリクで終了イベント
			$("#dialog-area-outer").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						//オプショナルダイアログも閉じる
						close_optional();
						//ダイアログを閉じる
						close_dialog();
					}
				} else {
					through_flag = true;
					//オプショナルダイアログも閉じる
					close_optional();
					//ダイアログを閉じる
					close_dialog();
				}
			});
			
			//クローズボタンイベント
			$(".dialog-close").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//ボタン装飾
			$( ".button" ).button();
			
			//ソートコントロール
			meisai_field_ctr(_slipno);

			//更新ボタン meisai_update_btn
			$(".update_meisai_btn").unbind("click");
			$(".update_meisai_btn").bind("click", function() {
				//明細更新処理
				calc_meisai();
				var r = update_meisai(_id, _slipno);
				
				if(r) { //明細更新がtrueなら(発注限界を超えている場合は更新しない)
					//SLIPの金額を書き換える
					switch(_slipno) {
						case 0:
						case 1:
							$(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).each(function(i, elm) {
								if($(this).val() == _id) {
									$(".slip-area:eq("+_slipno+") .s_moto_invoice").eq(i).val(to_num(h($("#meisai_total").val())));
								}
							});
							break;
						case 2:
						case 3:
							$(".slip-area:eq("+_slipno+") ."+SLIP_ID_FIELD[_slipno]).each(function(i, elm) {
								if($(this).val() == _id)
									$(".slip-area:eq("+_slipno+") .s_hattyu").eq(i).val(to_num(h($("#meisai_total").val())));
							});
							break;
					}
				}
			});

			//行追加ボタン
			$(".insert_meisai_btn").unbind("click");
			$(".insert_meisai_btn").bind("click", function() {
				
				//追加行数取得
				var add_line_cnt = h($("#add_line_cnt").val());
				if(!is_num(add_line_cnt))
					add_line_cnt = 1;
				else
					add_line_cnt = eval(add_line_cnt);

				add_meisai_last(add_line_cnt);
				//ソートコントロール
				meisai_field_ctr(_slipno);
			});

			//途中挿入ボタン insert_line_middle_btn
			$(".insert_line_middle_btn").unbind("click");
			$(".insert_line_middle_btn").bind("click", function() {
				var idx = $(".insert_line_middle_btn").index(this);
				$("#meisai_tbody>tr:last").clone(true).insertAfter($("#meisai_tbody>tr:eq("+idx+")")).show();
				//ソートコントロール
				meisai_field_ctr(_slipno);
			});

			//上移動ボタン insert_line_middle_btn
			$(".up_line_btn").unbind("click");
			$(".up_line_btn").bind("click", function() {
				var elm = $(this).parent().parent();
				if($(elm).prev("tr")) {
					$(elm).insertBefore($(elm).prev("tr")[0]);	
				}
				//ソートコントロール
				meisai_field_ctr(_slipno);
			});

			//下移動ボタン insert_line_middle_btn
			$(".down_line_btn").unbind("click");
			$(".down_line_btn").bind("click", function() {
				var elm = $(this).parent().parent();
				if($(elm).next("tr")) {
					$(elm).insertAfter($(elm).next("tr")[0]);	
				}
				//ソートコントロール
				meisai_field_ctr(_slipno);
			});

			//削除ボタン insert_line_middle_btn
			$(".del_line_btn").unbind("click");
			$(".del_line_btn").bind("click", function() {
				var idx = $(".del_line_btn").index(this);
				var id = $("."+MEISAI_ID_FIELD[_slipno]).eq(idx).val();
				
				//削除処理
				r = confirm("削除してもよろしいですか？");
				if(r) {
					del_meisai(_slipno, id);
					//行削除
					$(this).parent().parent().empty();
				}
				//ソートコントロール
				meisai_field_ctr(_slipno);
				calc_meisai();
			});

			//一括削除ボタンイベント
			$(".meisai_del_btn").unbind("click");
			$(".meisai_del_btn").click(function() {
				r = confirm("削除してもよろしいですか？");
				if(r) {
					get_del_meisai(_slipno);	
					//行削除
					//$(this).parent().parent().empty();
				}
				//ソートコントロール
				meisai_field_ctr(_slipno);
				calc_meisai();
			});

			//チェックボックスイベント登録
			$(".allcheck").unbind("click");
			$(".allcheck").click(function(e) {
				allcheck(".meisai-table .chk:not(:last)");
			});
			
			$(".alluncheck").unbind("click");
			$(".alluncheck").click(function(e) {
				alluncheck(".meisai-table .chk:not(:last)");
			});
			
			//消費税チェックボックスイベント
			$("#tax_flag").click(function() {
				calc_meisai();
			});
			
			//エンターキーイベント
			set_key_press_event();
			
			//計算
			calc_meisai();
			
			//計算領域横幅セット
			$(".calc-area").eq(0).width($(".meisai-table").eq(0).width()).css("text-align","right");
				
//----------------------個別領域 ここまで----------------------------
		},
		error:		function(data, textStatus) {
			write_status("MEISAI ajaxエラー");
			show_fail("MEISAI ajaxエラー");
			//write_debug(textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	meisai calc
 *
 **********************************************************************/
function calc_meisai() {

	//各行小計
	$(".m_id").each(function(i, elm) {
		var kazu = to_num(h($(".m_kazu").eq(i).val())) * 1000; //丸め誤差補正
		var tanka = to_num(h($(".m_tanka").eq(i).val()));
		var shokei = Math.round(kazu * tanka / 1000); //丸め誤差補正
		
		$(".m_kingaku").eq(i).val(shokei);
	});

	//小計
	var meisai_shokei = 0;
	$(".m_kingaku").each(function(i, elm) {
		meisai_shokei += to_num(h($(this).val()));
	});
	$("#meisai_shokei").val(meisai_shokei);

	//消費税計算の有無
	var tax_flag = $("#tax_flag").attr("checked");
	if(tax_flag == "checked") {
		//トータル
		var meisai_total = Math.floor(meisai_shokei * (1 + taxper));
		$("#meisai_total").val(meisai_total);

		//消費税
		var meisai_tax = meisai_total - meisai_shokei;
		$("#meisai_tax").val(meisai_tax);
	}
	else {
		//トータル
		$("#meisai_total").val(meisai_shokei);

		//消費税
		$("#meisai_tax").val(0);
		
	}
}


/**********************************************************************
 *
 * 	SLIPの表示コントロール
 *
 **********************************************************************/
function slip_field_ctr(_slipno) {
	//デートピッカー
	switch(_slipno) {
//----------------------個別領域 ここから----------------------------
		case 0:
			$(".slip-area:eq("+_slipno+") .s_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			
			//readonly
			$(".slip-area:eq("+_slipno+") .s_moto_invoice:not(:last)").addClass("readonly-bg").attr("readonly","readonly");

			//disabled
			//set_disable(obj);

			break;
		case 1:
			$(".slip-area:eq("+_slipno+") .s_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_st_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_end_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_shime_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});

			//readonly
			$(".slip-area:eq("+_slipno+") .s_moto_invoice:not(:last)").addClass("readonly-bg").attr("readonly","readonly");

			//disabled
			//set_disable(obj);

			break;
		case 2:
			$(".slip-area:eq("+_slipno+") .s_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_st_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_end_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_shime_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});

			//readonly
			//set_readonly(obj);
			//disabled
			//set_disable(obj);

			break;
		case 3:
			$(".slip-area:eq("+_slipno+") .s_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_st_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			$(".slip-area:eq("+_slipno+") .s_end_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			break;
		
//----------------------個別領域 ここまで----------------------------
	}

	//テーブルを斑に
	$(".slip-table tr:odd").addClass("madara");
	$(".slip-table tr:even").removeClass("madara");
}

/**********************************************************************
 *
 * 	MEISAIの表示コントロール
 *
 **********************************************************************/
function meisai_field_ctr(_slipno) {
	$(".sorder:not(:last)").each(function(i, elm) {
		$(this).val(i);
	});

	//テーブルを斑に
	$(".meisai-table tr:odd").addClass("madara");
	$(".meisai-table tr:even").removeClass("madara");

	switch(_slipno) {
//----------------------個別領域 ここから----------------------------
		case 0:
		case 1:
		case 2:
		case 3:
			$(".m_kazu").unbind("change");
			$(".m_kazu").change(function(i, elm) {
				calc_meisai();
			});

			$(".m_tanka").unbind("change");
			$(".m_tanka").change(function(i, elm) {
				calc_meisai();
			});
		
			/*
			$(".m_date:not(:last)").datepicker({
				inline: true,
				showButtonPanel: true,
				changeMonth: true,
numberOfMonths: numOfMonths
			});
			*/
			//readonly
			$(".m_kingaku:not(:last), #meisai_shokei, #meisai_tax, #meisai_total").addClass("readonly-bg").attr("readonly","readonly");
			
			//disabled
			//set_disable(obj);

			break;
//----------------------個別領域 ここまで----------------------------
	}
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
			
			var clm = new Array("g_status","g_tantou_id","g_moto_id","g_nai1_id","g_nai2_id","g_nai3_id","g_m2");
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

	//発注限界チェック
/*
	var rr = check_hat_limit(0);
	if(rr) {
		alert("発注額が限界値を超えている為、保存処理が出来ません。\n発注金額を確認して下さい。");
		return;	
	}
*/
//----------------------個別領域 ここから----------------------------
	var chk_main 	= input_check_main();
	var chk_slip0 	= input_check_slip(0);
	var chk_slip1 	= input_check_slip(1);
	var chk_slip2 	= input_check_slip(2);
	var chk_slip3 	= input_check_slip(3);

	if(!chk_main && !chk_slip0 && !chk_slip1 && !chk_slip2 && !chk_slip3) {
//----------------------個別領域 ここまで----------------------------
		r = confirm("保存してよろしいですか？");
		if(r) {	
			var sql = "";
			var clm = new Array("g_status","g_genba","g_genba_address","g_tantou_id","g_moto_id","g_nai1_id","g_nai2_id","g_nai3_id","g_biko","g_moto_tantou","g_moto_tantou_tel","g_m2");
			var id = $("."+MAIN_ID_FIELD).eq(0).val();
//----------------------個別領域 ここから----------------------------
			//新規か更新か
			if(id) { //更新
				sql += "UPDATE "+MAIN_TABLE+" SET ";

				for(var cnt=0;cnt<clm.length;cnt++) {
					sql += clm[cnt] + " = '"+ h($("."+clm[cnt]).eq(0).val()) +"'";
					sql += ",";
				}
				sql = sql.replace(/,$/,'');
				sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
				
			}
			else {
				//新規
				sql += "INSERT INTO "+MAIN_TABLE+" (";
				for(var cnt=0;cnt<clm.length;cnt++) {
					sql += clm[cnt];
					sql += ",";
				}
				sql = sql.replace(/,$/,'');
				sql += ") VALUES ( ";

				for(var cnt=0;cnt<clm.length;cnt++) {
					sql += "'"+h($("."+clm[cnt]).eq(0).val())+"'";
					sql += ",";
				}
				sql = sql.replace(/,$/,'');
				sql += ");;";

			}
//----------------------個別領域 ここまで----------------------------

//----------------------個別領域 ここから----------------------------
			//SLIP更新
			sql +=update_slip(id, 0);
			sql +=update_slip(id, 1);
			sql +=update_slip(id, 2);
			sql +=update_slip(id, 3);
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
						//SLIP 更新
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
		show_fail("入力内容を確認して下さい。必須入力項目があります。");
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

	if($(".g_genba").eq(0).val() == "") {
		err_flag = true;
		$(".g_genba_err").eq(0).html("必須項目です");
	}
	else {
		$(".g_genba_err").eq(0).html("");
	}

	if($(".g_moto_id").eq(0).val() == "0") {
		err_flag = true;
		$(".g_moto_id_err").eq(0).html("必須項目です");
	}
	else {
		$(".g_moto_id_err").eq(0).html("");
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
 * 	SLIPの入力チェック
 *
 **********************************************************************/
function input_check_slip(_slipno) {
	var tmp = "";
	var err_flag = false;

//----------------------個別領域 ここから----------------------------
	$(".slip-area:eq("+_slipno+") .s_id:not(:last)").each(function(i, elm) {
		
		switch(_slipno) {
			case 0:
				if( 
					is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) &&
					(
						!is_kara($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) 			||
						!is_kara($(".slip-area:eq("+_slipno+") .s_moto_invoice").eq(i).val()) 	||
						!is_kara($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) 
					)
				)
					err_flag = true;
				
				break;
			case 1:
				if( 
					is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) &&
					(
						!is_kara($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) 			||
						!is_kara($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_shime_date").eq(i).val()) 	||
						!is_kara($(".slip-area:eq("+_slipno+") .s_moto_invoice").eq(i).val()) 	||
						!is_kara($(".slip-area:eq("+_slipno+") .s_invoice").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) 
					)
				)
					err_flag = true;
				
				break;
			case 2:
				if( 
					is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) &&
					(
						!is_kara($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) 			||
						!is_kara($(".slip-area:eq("+_slipno+") .s_seko_id").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_shime_date").eq(i).val()) 	||
						!is_kara($(".slip-area:eq("+_slipno+") .s_hattyu").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) 
					)
				)
					err_flag = true;
				
				break;
			case 3:
				if( 
					is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) &&
					(
						!is_kara($(".slip-area:eq("+_slipno+") .s_hattyu").eq(i).val()) 		||
						!is_kara($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) 
					)
				)
					err_flag = true;
				
				break;
		}
	});
//----------------------個別領域 ここまで----------------------------

	return err_flag;
}

function is_kara(_p) {
	if(_p == "")
		return true;
	else if(_p == 0 || _p == "0")
		return true;
	else
		return false;
}

/**********************************************************************
 *
 * 	発注可能かのチェック
 *
 **********************************************************************/
function check_hat_limit(_sid) {
/*
	var hat_limit = $("#hat_limit").val();

	//発注総額計算
	var hat_kingaku = 0;
	$(".slip-area:eq(2) .s_hattyu").each(function(i, elm) {
		
		//明細編集で金額を更新した場合は、slipと混在した計算が必要
		if(_sid != 0 && _sid == to_num($(".slip-area:eq(2) .s_id").eq(i).val())) {
			hat_kingaku += to_num(h($("#meisai_total").val()));
		}
		else {
			hat_kingaku += to_num(h($(this).val()));
		}

	});
	
	//金額比較
	
	if(hat_limit == 0) { //受注額が入っていない
		return false;
	}
	else if(hat_limit < hat_kingaku) {
		return true;		
	}
	return false;
*/
}

/**********************************************************************
 *
 * 	slipを更新
 *
 **********************************************************************/
function update_slip(_id, _slipno) {

	var sql = "";
	
	//新規現場の場合
	if(_id == "")
		_id = "temp_main_id";
	
//----------------------個別領域 ここから----------------------------
	$(".slip-area:eq("+_slipno+") ."+ SLIP_ID_FIELD[_slipno]+":not(:last)").each(function(i,elm) {

		//新規か更新か
		if(to_num($(this).val())) { //更新
			sql += "UPDATE "+SLIP_TABLE[_slipno]+" SET ";
			switch(_slipno) {
				case 0:
					sql += "s_seko_kubun_id = '"+ h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
					sql += ",";
					sql += "s_date = '"+ h($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_moto_invoice = '"+ to_num(h($(".slip-area:eq("+_slipno+") .s_moto_invoice").eq(i).val())) + "'";
					sql += ",";
					sql += "s_biko = '"+ h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
					break;
				case 1:
					sql += "s_seko_kubun_id = '"+ h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
					sql += ",";
					sql += "s_date = '"+ h($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_st_date = '"+ h($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_end_date = '"+ h($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_shime_date = '"+ h($(".slip-area:eq("+_slipno+") .s_shime_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_moto_invoice = '"+ to_num(h($(".slip-area:eq("+_slipno+") .s_moto_invoice").eq(i).val())) + "'";
					sql += ",";
					sql += "s_invoice = '"+ to_num(h($(".slip-area:eq("+_slipno+") .s_invoice").eq(i).val()))+ "'";
					sql += ",";
					sql += "s_biko = '"+ h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
					break;
				case 2:
					sql += "s_seko_kubun_id = '"+ h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
					sql += ",";
					sql += "s_date = '"+ h($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_seko_id = '"+ h($(".slip-area:eq("+_slipno+") .s_seko_id").eq(i).val()) + "'";
					sql += ",";
					sql += "s_st_date = '"+ h($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_end_date = '"+ h($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_shime_date = '"+ h($(".slip-area:eq("+_slipno+") .s_shime_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_hattyu = '"+ to_num(h($(".slip-area:eq("+_slipno+") .s_hattyu").eq(i).val())) + "'";
					sql += ",";
					sql += "s_biko = '"+ h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
					sql += ",";
					
					if($(".slip-area:eq("+_slipno+") .s_is_jv").eq(i).attr("checked") == "checked")
						sql += "s_is_jv = '1'";
					else
						sql += "s_is_jv = '0', s_jv_rel_id = '0'";
					
					break;
				case 3:
					sql += "s_seko_kubun_id = '"+ h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
					sql += ",";
					sql += "s_st_date = '"+ h($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_end_date = '"+ h($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) + "'";
					sql += ",";
					sql += "s_hattyu = '"+ to_num(h($(".slip-area:eq("+_slipno+") .s_hattyu").eq(i).val())) + "'";
					sql += ",";
					sql += "s_biko = '"+ h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
					break;
			}
			sql += " WHERE "+SLIP_ID_FIELD[_slipno]+"="+h($(this).val())+";;";
			
			//JVが更新された場合日付を合わす
			if(_slipno == 3) {
				sql += "UPDATE matsushima_slip_hat SET s_st_date = '"+ h($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) + "', s_end_date = '"+ h($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) + "' WHERE s_jv_rel_id = '"+h($(this).val())+"';;";
			}
			
		} else { //新規
			//空行はスキップ
			switch(_slipno) {
				case 0:
					//空行をスキップ
					if(!is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val())) {
						sql += "INSERT INTO "+SLIP_TABLE[_slipno]+" ("+SLIP_REL_FIELD[_slipno]+", s_seko_kubun_id, s_date, s_moto_invoice, s_biko) VALUES(";
						sql += "'" + h(_id) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + to_num(h($(".slip-area:eq("+_slipno+") .s_moto_invoice").eq(i).val())) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
						sql += ");;";
					}
					break;
				case 1:
					//空行をスキップ
					if(!is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val())) {
						sql += "INSERT INTO "+SLIP_TABLE[_slipno]+" ("+SLIP_REL_FIELD[_slipno]+", s_seko_kubun_id, s_date, s_st_date, s_end_date, s_shime_date, s_moto_invoice, s_invoice, s_biko) VALUES(";
						sql += "'" + h(_id) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_shime_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + to_num(h($(".slip-area:eq("+_slipno+") .s_moto_invoice").eq(i).val()))+ "'";
						sql += ",";
						sql += "'" + to_num(h($(".slip-area:eq("+_slipno+") .s_invoice").eq(i).val())) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
						sql += ");;";
					}
					break;
				case 2:
					//空行をスキップ
					if(!is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val())) {
						sql += "INSERT INTO "+SLIP_TABLE[_slipno]+" ("+SLIP_REL_FIELD[_slipno]+", s_seko_kubun_id, s_date, s_seko_id, s_st_date, s_end_date, s_shime_date, s_hattyu, s_biko, s_is_jv) VALUES(";
						sql += "'" + h(_id) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_seko_id").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_shime_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + to_num(h($(".slip-area:eq("+_slipno+") .s_hattyu").eq(i).val())) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
						sql += ",";
						
						if($(".slip-area:eq("+_slipno+") .s_is_jv").eq(i).attr("checked") == "checked")
							sql += "1";
						else
							sql += "0";
						
						sql += ");;";
					}
					break;
				case 3:
					//空行をスキップ
					if(!is_kara($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val())) {
						sql += "INSERT INTO "+SLIP_TABLE[_slipno]+" ("+SLIP_REL_FIELD[_slipno]+", s_seko_kubun_id, s_st_date, s_end_date, s_hattyu, s_biko) VALUES(";
						sql += "'" + h(_id) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_seko_kubun_id").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_st_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_end_date").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + to_num(h($(".slip-area:eq("+_slipno+") .s_hattyu").eq(i).val())) + "'";
						sql += ",";
						sql += "'" + h($(".slip-area:eq("+_slipno+") .s_biko").eq(i).val()) + "'";
						sql += ");;";
					}
					break;
			}
		}
	});
//----------------------個別領域 ここまで----------------------------
	return sql;
}

/**********************************************************************
 *
 * 	meisaiを更新
 *
 **********************************************************************/
function update_meisai(_id, _slipno) {

/*
	//発注限界チェック
	if(_slipno == 2) {
		var rr = check_hat_limit(_id);
		if(rr) {
			alert("発注額が受注金額の40%を超えている為、保存処理が出来ません。\n発注金額を確認して下さい。");
			return false;	
		}
	}
*/
	var sql = "";
//----------------------個別領域 ここから----------------------------
	$(".meisai-table ."+ MEISAI_ID_FIELD[_slipno]+":not(:last)").each(function(i,elm) {

		//新規か更新か
		if(to_num($(this).val())) { //更新
			sql += "UPDATE "+MEISAI_TABLE[_slipno]+" SET ";
			switch(_slipno) {
				case 0:
				case 1:
				case 2:
				case 33:
					sql += "m_meisho = '"+ h($(".meisai-table .m_meisho").eq(i).val()) + "'";
					sql += ",";
					sql += "sorder = '"+ to_num(h($(".meisai-table .sorder").eq(i).val())) + "'";
					sql += ",";
					sql += "m_kazu = '"+ to_num(h($(".meisai-table .m_kazu").eq(i).val())) + "'";
					sql += ",";
					sql += "m_unit = '"+ h($(".meisai-table .m_unit").eq(i).val()) + "'";
					sql += ",";
					sql += "m_tanka = '"+ to_num(h($(".meisai-table .m_tanka").eq(i).val())) + "'";
					sql += ",";
					sql += "m_kingaku = '"+ h($(".meisai-table .m_kingaku").eq(i).val()) + "'";
					sql += ",";
					sql += "m_biko = '"+ h($(".meisai-table .m_biko").eq(i).val()) + "'";
					break;
			}
			sql += " WHERE "+MEISAI_ID_FIELD[_slipno]+"="+h($(this).val())+";;";
			
		} else { //新規
			//空行はスキップ
			switch(_slipno) {
				case 0:
				case 1:
				case 2:
				case 33:
					//空白行
					if($(".meisai-table .m_meisho").eq(i).val() != "" || $(".meisai-table .m_meisho").eq(i).val() != "") {
						sql += "INSERT INTO "+MEISAI_TABLE[_slipno]+" ("+MEISAI_REL_FIELD[_slipno]+", sorder, m_meisho, m_kazu, m_unit, m_tanka, m_kingaku, m_biko) VALUES(";
						sql += "'" + h(_id) + "'";
						sql += ",";
						sql += "'" + to_num(h($(".meisai-table .sorder").eq(i).val())) + "'";
						sql += ",";
						sql += "'" + h($(".meisai-table .m_meisho").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + to_num(h($(".meisai-table .m_kazu").eq(i).val())) + "'";
						sql += ",";
						sql += "'" + h($(".meisai-table .m_unit").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + to_num(h($(".meisai-table .m_tanka").eq(i).val())) + "'";
						sql += ",";
						sql += "'" + h($(".meisai-table .m_kingaku").eq(i).val()) + "'";
						sql += ",";
						sql += "'" + h($(".meisai-table .m_biko").eq(i).val()) + "'";
						sql += ");;";
					}
					break;
			}
		}
	});

	//合計の更新
	switch(_slipno) {
		case 0:
			sql += "UPDATE matsushima_slip_est SET s_moto_invoice = '"+to_num(h($("#meisai_total").val()))+"' WHERE s_id = '"+h($(".slip_s_id").eq(0).val())+"';;";
			break;
		case 1:
			sql += "UPDATE matsushima_slip SET s_moto_invoice = '"+to_num(h($("#meisai_total").val()))+"' WHERE s_id = '"+h($(".slip_s_id").eq(0).val())+"';;";
			break;
		case 2:
			sql += "UPDATE matsushima_slip_hat SET s_hattyu = '"+to_num(h($("#meisai_total").val()))+"' WHERE s_id = '"+h($(".slip_s_id").eq(0).val())+"';;";
			break;
		case 33:
			sql += "UPDATE matsushima_slip_jv SET s_hattyu = '"+to_num(h($("#meisai_total").val()))+"' WHERE s_id = '"+h($(".slip_s_id").eq(0).val())+"';;";
			break;
	}
	
	//消費税保存
	var tax_flag = $("#tax_flag").attr("checked");
	if(tax_flag == "checked") {
		switch(_slipno) {
			case 0:
			case 1:
			case 2:
			case 33:
				sql += "UPDATE "+SLIP_TABLE[_slipno]+" SET ";
				sql += "s_tax = '"+ h($("#tax_flag").val()) + "'";
				//sql += ",";
				sql += " WHERE "+SLIP_ID_FIELD[_slipno]+"="+h(_id)+";;";
		}
	}
	else {
		switch(_slipno) {
			case 0:
			case 1:
			case 2:
			case 33:
				sql += "UPDATE "+SLIP_TABLE[_slipno]+" SET ";
				sql += "s_tax = '0'";
				//sql += ",";
				sql += " WHERE "+SLIP_ID_FIELD[_slipno]+"="+h(_id)+";;";
		}
	}
	
//----------------------個別領域 ここまで----------------------------

	//JVメンバーの保存の為からでajax
	//sqlが空かチェック
	//if(sql == "")
	//	return;

	sql = sql.replace(/;;$/,'');
	//write_debug(sql);

	if(sql != '') {
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
					edit_meisai(_id, _slipno);
					
					edit_meisai(_id, 2);
	
					var g_id = h($(".g_id").eq(0).val());
					edit_slip(g_id, _slipno);
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
	
	//JV担当の処理
	if(_slipno == 3) {
		
		var seko_cbno = "";
		$(".jv_seko").each(function(i, elm) {
			if($(this).attr("checked") == "checked") {
				seko_cbno += $(this).val();
				seko_cbno += ',';
			}
		});
		seko_cbno = seko_cbno.replace(/,$/,'');
		
		if(seko_cbno != '') {
			var flag = "M_JV_SEKO";
		
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
				data:		{parm:[flag, _id, seko_cbno]},
				type:		"post",
				headers: 	{"pragma": "no-cache"},
				success:	function(data, textStatus) {
					var g_id = h($(".g_id").eq(0).val());
					edit_slip(g_id, _slipno);
					close_dialog();
				},
				complete:	function(data, textStatus) {
				},
				error:		function(data, textStatus) {
					show_fail("表示に失敗しました。", textStatus);
				}
			});
		}
	}
	return true;
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

		sql += "DELETE FROM matsushima_hat_sousa WHERE z_g_id in ("+_id+");;";
		sql += "DELETE "+MEISAI_TABLE[0]+" FROM "+MEISAI_TABLE[0]+" INNER JOIN "+SLIP_TABLE[0]+" ON "+SLIP_TABLE[0]+"."+SLIP_ID_FIELD[0]+" = "+MEISAI_TABLE[0]+"."+MEISAI_REL_FIELD[0]+" WHERE "+SLIP_REL_FIELD[0]+" in ("+_id+");;";
		sql += "DELETE "+MEISAI_TABLE[1]+" FROM "+MEISAI_TABLE[1]+" INNER JOIN "+SLIP_TABLE[1]+" ON "+SLIP_TABLE[1]+"."+SLIP_ID_FIELD[1]+" = "+MEISAI_TABLE[1]+"."+MEISAI_REL_FIELD[1]+" WHERE "+SLIP_REL_FIELD[1]+" in ("+_id+");;";
		sql += "DELETE "+MEISAI_TABLE[2]+" FROM "+MEISAI_TABLE[2]+" INNER JOIN "+SLIP_TABLE[2]+" ON "+SLIP_TABLE[2]+"."+SLIP_ID_FIELD[2]+" = "+MEISAI_TABLE[2]+"."+MEISAI_REL_FIELD[2]+" WHERE "+SLIP_REL_FIELD[2]+" in ("+_id+");;";
		sql += "DELETE "+MEISAI_TABLE[3]+" FROM "+MEISAI_TABLE[3]+" INNER JOIN "+SLIP_TABLE[3]+" ON "+SLIP_TABLE[3]+"."+SLIP_ID_FIELD[3]+" = "+MEISAI_TABLE[3]+"."+MEISAI_REL_FIELD[3]+" WHERE "+SLIP_REL_FIELD[3]+" in ("+_id+");;";
		sql += "DELETE FROM "+SLIP_TABLE[0]+" WHERE "+SLIP_REL_FIELD[0]+" in ("+_id+");;";
		sql += "DELETE FROM "+SLIP_TABLE[1]+" WHERE "+SLIP_REL_FIELD[1]+" in ("+_id+");;";
		sql += "DELETE FROM "+SLIP_TABLE[2]+" WHERE "+SLIP_REL_FIELD[2]+" in ("+_id+");;";
		sql += "DELETE FROM "+SLIP_TABLE[3]+" WHERE "+SLIP_REL_FIELD[3]+" in ("+_id+");;";
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

/**********************************************************************
 *
 * 	MEISAI一括削除
 *
 **********************************************************************/
function get_del_meisai(_slipno) {

	var cbno = "";
	var check_flag = false;
	
	//最終のひな形行は除外
	$(".meisai-table .chk:not(:last)").each(function(i) {
		var tmp = $(this).attr("checked");
		if(tmp == "checked") {
			
			//保存されていない行でチェックされた場合
			check_flag = true;
			
			if($(this).val() != "") {
				cbno += $(this).val() + ",";
			}
			//行クリア
			$(this).parent().parent().empty();
		}
	});
	//最後のコロンを取る
	cbno = cbno.replace(/\,$/,'');
	
	if(cbno == "" && !check_flag) {
		show_fail("削除対象がチェックされていません");
	} else {
		//削除処理
		del_meisai(_slipno, cbno);

		//チェックをクリア
		alluncheck(".meisai-table .chk:not(:last)");
	}
}

/**********************************************************************
 *
 * 	MEISAI削除
 *
 **********************************************************************/
function del_meisai(_slipno, _cbno) {
	if(_cbno != "") {
		var sql = "DELETE FROM "+MEISAI_TABLE[_slipno]+" WHERE "+MEISAI_ID_FIELD[_slipno]+" in ("+_cbno+")";
	
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
					edit_meisai(_id, _slipno);
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

/**********************************************************************
 *
 * 	最終行へ _cnt 行追加
 *
 **********************************************************************/
function add_meisai_last(_cnt) {
	//複数行追加のコントロール
	for(var i=1;i<=_cnt;i++) {
		$(".meisai-table #meisai_tbody>tr:last").clone(true).insertBefore($(".meisai-table #meisai_tbody>tr:last")).show();
	}
}

/**********************************************************************
 *
 * 	明細でのエンターキーで下のフォームに移動するイベント関数
 *
 **********************************************************************/
function set_key_press_event() {
	var hCode = 13; //Enter
	var left = 37;
	var up = 38;
	var right = 39;
	var down = 40;

	$(".meisai-table input").unbind("keypress");
	$(".meisai-table input").keypress(function (e) {
		if(e.which == hCode) {

			var idx = $(".meisai-table input").index(this);
			//ジャンプする数
			idx += 10;
			
			$(".meisai-table input").eq(idx).focus();

			return false;
		}
	});
}

function calc_main() {
}
function calc_slip() {
}

/**********************************************************************
 *
 * 	受注処理
 *
 **********************************************************************/
function set_jyutyu(_id, _idx) {
	
	//元請け取得
	var moto_id = h($(".g_moto_id").eq(0).val());
	
	var r = confirm("受注処理を実行してよろしいですか？");
	
	if(r) {
//----------------------個別領域 ここから----------------------------
	//区分を受注に変更
	$(".slip-area:eq(0) .s_seko_kubun_id").eq(_idx).val(2);
	
	//受注ボタンを非表示
	$(".slip-area:eq(0) .jyutyu_btn").eq(_idx).remove();
	
	//現場のステータスを受注に変更
	$(".g_status").eq(0).val(2);
	
	//受注ajax呼び出し
	var flag = "M_JYUTYU";

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
		data:		{parm:[flag, _id, moto_id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			show_success("受注処理を実行しました。");
		},
		complete:	function(data, textStatus) {
			//slip再表示
			var g_id = h($(".g_id").eq(0).val());
			edit_slip(g_id, 1);
		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
		}
	});


//----------------------個別領域 ここまで----------------------------
	}
}

/**********************************************************************
 *
 * 	JVからの受注処理
 *
 **********************************************************************/
function set_hat_jv(_id, _idx) {
		
	var r = confirm("発注処理を実行してよろしいですか？");
	
	if(r) {
//----------------------個別領域 ここから----------------------------
	
	//受注ajax呼び出し
	var flag = "M_HAT_FROM_JV";

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
			show_success("発注処理を実行しました。");
		},
		complete:	function(data, textStatus) {
			//slip再表示
			var g_id = h($(".g_id").eq(0).val());
			edit_slip(g_id, 2);
		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
		}
	});

//----------------------個別領域 ここまで----------------------------
	}
}

/**********************************************************************
 *
 * 	JVから通常受注へ変更処理
 *
 **********************************************************************/
function change_to_normal(_id, _idx, _k) {
		
	var r = confirm("JVから通常受注に変更してよろしいですか？");
	
	if(r) {
//----------------------個別領域 ここから----------------------------
	
	//受注ajax呼び出し
	var flag = "CHANGE_TO_NORMAL";

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
		data:		{parm:[flag, _id, _k]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			show_success("変更処理を実行しました。");
		},
		complete:	function(data, textStatus) {
			//slip再表示
			var g_id = h($(".g_id").eq(0).val());
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
		}
	});

//----------------------個別領域 ここまで----------------------------
	}
}

/**********************************************************************
 *
 * 	通常からJV受注へ変更処理
 *
 **********************************************************************/
function change_to_jv(_id, _idx) {
		
	var r = confirm("通常受注からJVに変更してよろしいですか？");
	
	if(r) {
//----------------------個別領域 ここから----------------------------
	
	//受注ajax呼び出し
	var flag = "CHANGE_TO_JV";

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
			show_success("変更処理を実行しました。");
		},
		complete:	function(data, textStatus) {
			//slip再表示
			var g_id = h($(".g_id").eq(0).val());
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
		},
		error:		function(data, textStatus) {
			show_fail("表示に失敗しました。", textStatus);
		}
	});

//----------------------個別領域 ここまで----------------------------
	}
}


function set_m_pat_area(_moto_id) {
	//請求パターン取得
	var flag = "M_PAT";

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
		data:		{parm:[flag, _moto_id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#m_pat_area").html(data);
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});
}

/**********************************************************************
 *
 * 	ひな形挿入
 *
 **********************************************************************/
function hinagata(_p) {

	//現在の行数を確認
	var line_num = $(".m_id:not(:last)").length;
	
	//項目追加
	switch(_p) {
		case 1:
			var line = 2;
			//既に入力されているか確認
			var al_flag = false;
			$(".m_id:not(:last)").each(function(i,elm) {
				if(i >= line)
					return false;
				
				if($(".m_meisho").eq(i).val() != "")
					al_flag = true;
			});
			var r = true;
			if(al_flag) {
				r = confirm("既に値が入力されています。上書きしてもよろしいですか？");
			}

			if(r) {
				//行数が足りない場合は行を自動追加
				if(line_num < line) {
					var add_line = line - line_num;
					//行追加	
					add_meisai_last(add_line);
				}
				
				$(".m_meisho").eq(0).val("外部足場(延架)");
				$(".m_unit").eq(0).val("㎡");
				$(".m_tanka").eq(0).val("580");
	
				$(".m_meisho").eq(1).val("メッシュシート");
				$(".m_unit").eq(1).val("㎡");
				$(".m_tanka").eq(1).val("150");

				meisai_field_ctr(0);
				calc_meisai();
			}
			break;
		case 2:
			var line = 3;
			//既に入力されているか確認
			var al_flag = false;
			$(".m_id:not(:last)").each(function(i,elm) {
				if(i >= line)
					return false;
				
				if($(".m_meisho").eq(i).val() != "")
					al_flag = true;
			});
			var r = true;
			if(al_flag) {
				r = confirm("既に値が入力されています。上書きしてもよろしいですか？");
			}

			if(r) {
				//行数が足りない場合は行を自動追加
				if(line_num < line) {
					var add_line = line - line_num;
					//行追加	
					add_meisai_last(add_line);
				}
				
				$(".m_meisho").eq(0).val("外部足場(延架)");
				$(".m_unit").eq(0).val("㎡");
				$(".m_tanka").eq(0).val("580");
	
				$(".m_meisho").eq(1).val("メッシュシート");
				$(".m_unit").eq(1).val("㎡");
				$(".m_tanka").eq(1).val("150");

				$(".m_meisho").eq(2).val("ステップ");
				$(".m_unit").eq(2).val("基");
				$(".m_tanka").eq(2).val("5000");

				meisai_field_ctr(0);
				calc_meisai();
			}
			break;
	}

}


function add_line_val(_p) {
	
	var add_line_cnt = to_num($("#add_line_cnt").val());	
	add_line_cnt += _p;
	$("#add_line_cnt").val(add_line_cnt);
}


function copy_est(_id) {

	var r = confirm("コピー処理を実行してよろしいですか？");
	if(r) {

		var flag = "M_COPY";
	
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
				var g_id = h($(".g_id").eq(0).val());
				edit_slip(g_id, 0);
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
 * 	発注操作を表示する関数
 *
 **********************************************************************/
function edit_hattyu(_id, _slipno) {

	//ローディング画像表示
	$("#dialog-area-inner").html(loading_text).show();
	
	var g_nai1_id = h($(".g_nai1_id").eq(0).val());

	flag = "EDIT_HATTYU";

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
		data:		{parm:[flag, _id, g_nai1_id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#dialog-area-inner").html(data);
			open_dialog();
		},
		complete:	function(data, textStatus) {

			//トップへスクロールする
			$('html,body').animate({ scrollTop: 0 }, 'fast');
				
			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".meisai-table input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//閉じるボタン
			$(".closeDiag").unbind("click");
			$(".closeDiag").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//領域外クリクで終了イベント
			$("#dialog-area-outer").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						//オプショナルダイアログも閉じる
						close_optional();
						//ダイアログを閉じる
						close_dialog();
					}
				} else {
					through_flag = true;
					//オプショナルダイアログも閉じる
					close_optional();
					//ダイアログを閉じる
					close_dialog();
				}
			});
			
			//クローズボタンイベント
			$(".dialog-close").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//ボタン装飾
			$( ".button" ).button();
			
			//発注確定
			$("#hat_btn1").click(function() {
				hat_exec(1);
			});
			$("#hat_btn2").click(function() {
				hat_exec(2);
			});
			$("#hat_btn3").click(function() {
				hat_exec(3);
			});

			$("#hat_btn4").click(function() {
				hat_exec(4);
			});
			$("#hat_btn5").click(function() {
				hat_exec(5);
			});
			$("#hat_btn6").click(function() {
				hat_exec(6);
			});

			/*
			$("#shi1").change(function() {
				hat_calc();
			});
			*/
			//計算
			hat_calc();

//----------------------個別領域 ここまで----------------------------
		},
		error:		function(data, textStatus) {
			write_status("MEISAI ajaxエラー");
			show_fail("MEISAI ajaxエラー");
			//write_debug(textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	発注操作編集を表示する関数
 *
 **********************************************************************/
function edit_mod_hattyu(_id, _slipno) {

	//ローディング画像表示
	$("#dialog-area-inner").html(loading_text).show();
	
	flag = "EDIT_MOD_HATTYU";

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
			$("#dialog-area-inner").html(data);
			open_dialog();
		},
		complete:	function(data, textStatus) {

			//トップへスクロールする
			$('html,body').animate({ scrollTop: 0 }, 'fast');
				
			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".meisai-table input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//閉じるボタン
			$(".closeDiag").unbind("click");
			$(".closeDiag").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//領域外クリクで終了イベント
			$("#dialog-area-outer").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						//オプショナルダイアログも閉じる
						close_optional();
						//ダイアログを閉じる
						close_dialog();
					}
				} else {
					through_flag = true;
					//オプショナルダイアログも閉じる
					close_optional();
					//ダイアログを閉じる
					close_dialog();
				}
			});
			
			//クローズボタンイベント
			$(".dialog-close").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});
			
			//最新受注額取得
			$("#get_new_jyutyu").click(function(event) {

				var flag = "GET_NEW_JYUTYU";

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
						$("#s_moto_invoice").val(data);
					},
					complete:	function(data, textStatus) {
					},
					error:		function(data, textStatus) {
					}
				});
			});


			//ボタン装飾
			$( ".button" ).button();

			/*
			$("#shi1").change(function() {
				set_sousa_mod();
			});
			*/
			set_sousa_mod();
			
			$("#update_sousa_btn").click(function() {
				update_sousa_mod();
			});

//----------------------個別領域 ここまで----------------------------
		},
		error:		function(data, textStatus) {
			write_status("MEISAI ajaxエラー");
			show_fail("MEISAI ajaxエラー");
			//write_debug(textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	JV発注操作編集を表示する関数
 *
 **********************************************************************/
function edit_mod_hattyu_jv(_id, _slipno) {

	//ローディング画像表示
	$("#dialog-area-inner").html(loading_text).show();
	
	flag = "EDIT_MOD_HATTYU_JV";

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
			$("#dialog-area-inner").html(data);
			open_dialog();
		},
		complete:	function(data, textStatus) {

			//トップへスクロールする
			$('html,body').animate({ scrollTop: 0 }, 'fast');
				
			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".meisai-table input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//閉じるボタン
			$(".closeDiag").unbind("click");
			$(".closeDiag").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//領域外クリクで終了イベント
			$("#dialog-area-outer").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						//オプショナルダイアログも閉じる
						close_optional();
						//ダイアログを閉じる
						close_dialog();
					}
				} else {
					through_flag = true;
					//オプショナルダイアログも閉じる
					close_optional();
					//ダイアログを閉じる
					close_dialog();
				}
			});
			
			//クローズボタンイベント
			$(".dialog-close").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//最新受注額取得
			$("#get_new_jyutyu").click(function(event) {

				var flag = "GET_NEW_JYUTYU";

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
						$("#s_moto_invoice").val(data);
					},
					complete:	function(data, textStatus) {
					},
					error:		function(data, textStatus) {
					}
				});
			});

			//ボタン装飾
			$( ".button" ).button();

			set_sousa_mod();
			
			$("#update_sousa_jv_btn").click(function() {
				update_sousa_mod_jv();
			});

//----------------------個別領域 ここまで----------------------------
		},
		error:		function(data, textStatus) {
			write_status("MEISAI ajaxエラー");
			show_fail("MEISAI ajaxエラー");
			//write_debug(textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	東リース発注操作編集を表示する関数
 *
 **********************************************************************/
function edit_mod_hattyu_azuma(_id, _slipno) {

	//ローディング画像表示
	$("#dialog-area-inner").html(loading_text).show();
	
	flag = "EDIT_MOD_HATTYU_AZUMA";

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
			$("#dialog-area-inner").html(data);
			open_dialog();
		},
		complete:	function(data, textStatus) {

			//トップへスクロールする
			$('html,body').animate({ scrollTop: 0 }, 'fast');
				
			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".meisai-table input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//閉じるボタン
			$(".closeDiag").unbind("click");
			$(".closeDiag").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//領域外クリクで終了イベント
			$("#dialog-area-outer").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						//オプショナルダイアログも閉じる
						close_optional();
						//ダイアログを閉じる
						close_dialog();
					}
				} else {
					through_flag = true;
					//オプショナルダイアログも閉じる
					close_optional();
					//ダイアログを閉じる
					close_dialog();
				}
			});
			
			//クローズボタンイベント
			$(".dialog-close").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});
			
			//最新受注額取得
			$("#get_new_jyutyu").click(function(event) {

				var flag = "GET_NEW_JYUTYU";

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
						$("#s_moto_invoice").val(data);
					},
					complete:	function(data, textStatus) {
					},
					error:		function(data, textStatus) {
					}
				});
			});


			//ボタン装飾
			$( ".button" ).button();

			$("#update_sousa_azuma_btn").click(function() {
				update_sousa_mod_azuma();
			});

			//再計算
			hat_calc_mod_azuma();

//----------------------個別領域 ここまで----------------------------
		},
		error:		function(data, textStatus) {
			write_status("MEISAI ajaxエラー");
			show_fail("MEISAI ajaxエラー");
			//write_debug(textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	JV発注操作編集を表示する関数
 *
 **********************************************************************/
function edit_mod_hattyu_jv_azuma(_id, _slipno) {

	//ローディング画像表示
	$("#dialog-area-inner").html(loading_text).show();
	
	flag = "EDIT_MOD_HATTYU_JV_AZUMA";

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
			$("#dialog-area-inner").html(data);
			open_dialog();
		},
		complete:	function(data, textStatus) {

			//トップへスクロールする
			$('html,body').animate({ scrollTop: 0 }, 'fast');
				
			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".meisai-table input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//閉じるボタン
			$(".closeDiag").unbind("click");
			$(".closeDiag").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//領域外クリクで終了イベント
			$("#dialog-area-outer").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						//オプショナルダイアログも閉じる
						close_optional();
						//ダイアログを閉じる
						close_dialog();
					}
				} else {
					through_flag = true;
					//オプショナルダイアログも閉じる
					close_optional();
					//ダイアログを閉じる
					close_dialog();
				}
			});
			
			//クローズボタンイベント
			$(".dialog-close").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//ボタン装飾
			$( ".button" ).button();
			
			$("#update_sousa_azuma_jv_btn").click(function() {
				update_sousa_mod_jv_azuma();
			});

			//再計算
			hat_calc_mod_azuma();

//----------------------個別領域 ここまで----------------------------
		},
		error:		function(data, textStatus) {
			write_status("MEISAI ajaxエラー");
			show_fail("MEISAI ajaxエラー");
			//write_debug(textStatus);
		}
	});
}


function set_sousa_mod() {
	//再計算
	hat_calc();

	//変更後金額挿入
	var ztype = to_num($("[name='ztype']:checked").val());

	var kake = $("#kake").val();
	var fusagi = $("#fusagi").val();
	var harai = $("#harai").val();
	var mori = $("#mori").val();
	var tate = $("#tate").val();

	var sql = "";

	switch(ztype) {
		case 1:
		case 4:
			$(".s_id_after").each(function(i, elm) {
				if($(".sy_name_nik_after").eq(i).val() == "架") {
					$(".s_hattyu_after").eq(i).val(kake);
				}
				else if($(".sy_name_nik_after").eq(i).val() == "塞") {
					$(".s_hattyu_after").eq(i).val(fusagi);
					sql += "UPDATE matsushima_slip_hat SET s_hattyu = '"+fusagi+"' WHERE s_id = '"+$(this).val()+"';;";
				}
				else if($(".sy_name_nik_after").eq(i).val() == "払") {
					$(".s_hattyu_after").eq(i).val(harai);
				}
			});
			break;			
		case 2:
		case 5:
			$(".s_id_after").each(function(i, elm) {
				if($(".sy_name_nik_after").eq(i).val() == "架") {
					$(".s_hattyu_after").eq(i).val(kake);
				}
				else if($(".sy_name_nik_after").eq(i).val() == "塞") {
					$(".s_hattyu_after").eq(i).val(fusagi);
				}
				else if($(".sy_name_nik_after").eq(i).val() == "盛") {
					$(".s_hattyu_after").eq(i).val(mori);
				}
				else if($(".sy_name_nik_after").eq(i).val() == "払") {
					$(".s_hattyu_after").eq(i).val(harai);
				}
			});
			break;			
		case 3:
		case 6:
			$(".s_id_after").each(function(i, elm) {
				if($(".sy_name_nik_after").eq(i).val() == "架") {
					$(".s_hattyu_after").eq(i).val(tate);
				}
				else if($(".sy_name_nik_after").eq(i).val() == "払") {
					$(".s_hattyu_after").eq(i).val(harai);
				}
			});
			break;			
	}
}


function update_sousa_mod() {
	//変更後金額挿入
	var ztype = to_num($("[name='ztype']:checked").val());

	if(!hat_mod_exec(ztype))
		return;

	var g_id = h($(".g_id").eq(0).val());

	var kake = $("#kake").val();
	var fusagi = $("#fusagi").val();
	var harai = $("#harai").val();
	var mori = $("#mori").val();
	var tate = $("#tate").val();

	var sql = "";

	$(".s_id_after").each(function(i, elm) {
		if($(".s_hattyu_after").eq(i).val() != "")
			sql += "UPDATE matsushima_slip_hat SET s_hattyu = '"+to_num(h($(".s_hattyu_after").eq(i).val()))+"' WHERE s_id = '"+$(this).val()+"';;";
	});

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
		},
		complete:	function(data, textStatus) {
			edit_slip(g_id, 1);
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
			show_success("発注を更新しました。");
			close_dialog();
		},
		error:		function(data, textStatus) {
			show_fail("保存が失敗しました。", textStatus);
		}
	});

}

function update_sousa_mod_azuma() {

	var g_id = h($(".g_id").eq(0).val());

	var sql = "";

	$(".s_id_after").each(function(i, elm) {
		if($(".s_hattyu_after").eq(i).val() != "")
			sql += "UPDATE matsushima_slip_hat SET s_hattyu = '"+to_num(h($(".s_hattyu_after").eq(i).val()))+"' WHERE s_id = '"+$(this).val()+"';;";
	});
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
		},
		complete:	function(data, textStatus) {
			hat_azuma_mod_exec();
			//edit_slip(g_id, 1);
			//edit_slip(g_id, 2);
			//edit_slip(g_id, 3);
			//show_success("発注を更新しました。");
			//close_dialog();
		},
		error:		function(data, textStatus) {
			show_fail("保存が失敗しました。", textStatus);
		}
	});

}

function update_sousa_mod_jv_azuma() {

	var g_id = h($(".g_id").eq(0).val());
	var sql = "";

	$(".s_id_after").each(function(i, elm) {
		if($(".s_hattyu_after").eq(i).val() != "")
			sql += "UPDATE matsushima_slip_jv SET s_hattyu = '"+to_num(h($(".s_hattyu_after").eq(i).val()))+"' WHERE s_id = '"+$(this).val()+"';;";
	});
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
		},
		complete:	function(data, textStatus) {
			hat_azuma_mod_exec();
			//edit_slip(g_id, 1);
			//edit_slip(g_id, 2);
			//edit_slip(g_id, 3);
			//show_success("発注を更新しました。");
			//close_dialog();
		},
		error:		function(data, textStatus) {
			show_fail("保存が失敗しました。", textStatus);
		}
	});

}

function update_sousa_mod_jv() {
	//変更後金額挿入
	var ztype = to_num($("[name='ztype']:checked").val());

	if(!hat_mod_exec(ztype))
		return;

	var g_id = h($(".g_id").eq(0).val());

	var kake = $("#kake").val();
	var fusagi = $("#fusagi").val();
	var harai = $("#harai").val();
	var mori = $("#mori").val();
	var tate = $("#tate").val();

	var sql = "";

	$(".s_id_after").each(function(i, elm) {
		if($(".s_hattyu_after").eq(i).val() != "")
			sql += "UPDATE matsushima_slip_jv SET s_hattyu = '"+to_num(h($(".s_hattyu_after").eq(i).val()))+"' WHERE s_id = '"+$(this).val()+"';;";
	});

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
		},
		complete:	function(data, textStatus) {
			edit_slip(g_id, 1);
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
			show_success("発注を更新しました。");
			close_dialog();
		},
		error:		function(data, textStatus) {
			show_fail("保存が失敗しました。", textStatus);
		}
	});

}

/**********************************************************************
 *
 * 	発注操作東リースを表示する関数
 *
 **********************************************************************/
function edit_azuma_hattyu(_gid, _s_id, _slipno, _k) {

	//ローディング画像表示
	$("#dialog-area-inner").html(loading_text).show();
	
	flag = "EDIT_AZUMA_HATTYU";

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
		data:		{parm:[flag, _gid, _s_id, _k]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#dialog-area-inner").html(data);
			open_dialog();
		},
		complete:	function(data, textStatus) {

			//トップへスクロールする
			$('html,body').animate({ scrollTop: 0 }, 'fast');
				
			//フィールドに変更があったかをコントロール
			change_flag = false;
			$(".meisai-table input").each(function(i, elm) {
				$(elm).change(function() {
					change_flag = true;
				});
			});

			//閉じるボタン
			$(".closeDiag").unbind("click");
			$(".closeDiag").click(function() {
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//領域外クリクで終了イベント
			$("#dialog-area-outer").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						//オプショナルダイアログも閉じる
						close_optional();
						//ダイアログを閉じる
						close_dialog();
					}
				} else {
					through_flag = true;
					//オプショナルダイアログも閉じる
					close_optional();
					//ダイアログを閉じる
					close_dialog();
				}
			});
			
			//クローズボタンイベント
			$(".dialog-close").click(function(event) {
				event.stopPropagation();
				//フォームに変更があった場合注意
				if(change_flag) {
					r = confirm("保存されていない項目があります。変更内容を破棄してもよろしいですか？");
					if(r) {
						through_flag = true;
						close_dialog();
					}
				} else {
					through_flag = true;
					close_dialog();
				}
			});

			//ボタン装飾
			$( ".button" ).button();
			
			//発注確定
			$("#hat_azuma_btn").click(function() {
				hat_azuma_exec();
			});
			//JVとして発注確定
			$("#hat_azuma_jv_btn").click(function() {
				hat_azuma_exec_jv();
			});
			
			//計算
			hat_calc_azuma();

//----------------------個別領域 ここまで----------------------------
		},
		error:		function(data, textStatus) {
			write_status("MEISAI ajaxエラー");
			show_fail("MEISAI ajaxエラー");
			//write_debug(textStatus);
		}
	});
}

function hat_calc_mod_azuma() {

	var shi1 = to_num(h($("#shi1").val()));
	$(".s_id_after").each(function(i, elm) {
		var s_invoice_azuma = to_num(h($(".s_invoice_azuma").eq(i).val()));
		var s_hattyu_after = Math.floor(s_invoice_azuma * shi1 / 100);
		$(".s_hattyu_after").eq(i).val(s_hattyu_after);
	});
}

function hat_exec(_syu) {

	var g_id = h($(".g_id").eq(0).val());
	var tate = to_num(h($("#tate").val()));
	var kake = to_num(h($("#kake").val()));
	var fusagi = to_num(h($("#fusagi").val()));
	var mori = to_num(h($("#mori").val()));
	var harai = to_num(h($("#harai").val()));
	var shi1 = to_num(h($("#shi1").val()));
	var sid = to_num($("#hat_sousa_s_id").val());

	var s_moto_invoice = to_num($("#s_moto_invoice").val());
	var sales3p = to_num($("#sales3p").val());
	var s_hattyu = to_num($("#s_hattyu").val());
	var tate_hi = to_num($("#tate_hi").val());
	var harai_hi = to_num($("#harai_hi").val());
	var ashi_m2 = to_num($("#ashi_m2").val());
	var ashi_tanka = to_num($("#ashi_tanka").val());
	var ashi_total = to_num($("#ashi_total").val());
	var sheet_m2 = to_num($("#sheet_m2").val());
	var sheet_tanka = to_num($("#sheet_tanka").val());
	var sheet_total = to_num($("#sheet_total").val());
	var geya_m2 = to_num($("#geya_m2").val());
	var geya_tanka = to_num($("#geya_tanka").val());
	var geya_total = to_num($("#geya_total").val());
	var barashi = to_num($("#barashi").val());

	//0円で発注する
	if($("#zerohat").attr("checked") == "checked") {
		tate = 0;
		kake = 0;
		fusagi = 0;
		mori = 0;
		harai = 0;
		barashi = 0;
	}

	
	//発注額を超えていないかチェック
	var ttl = kake + fusagi + mori + harai;
	if(ttl > s_hattyu + 10) {
		alert("発注額が指定の金額を超えています。");
		return;	
	}


	var flag = "HAT_EXEC";

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
		data:		{parm:[flag, g_id, _syu, tate, kake, fusagi, mori, harai, sid, shi1, s_moto_invoice, sales3p, s_hattyu, tate_hi, harai_hi, ashi_m2, ashi_tanka, ashi_total, sheet_m2, sheet_tanka, sheet_total, geya_m2, geya_tanka, geya_total, barashi]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			edit_slip(g_id, 1);
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
			show_success("発注書を作成しました。");
			close_dialog();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("ajaxエラー");
		}
	});
}

function hat_mod_exec(_syu) {

	var g_id = h($(".g_id").eq(0).val());
	var tate = to_num(h($("#tate").val()));
	var kake = to_num(h($("#kake").val()));
	var fusagi = to_num(h($("#fusagi").val()));
	var mori = to_num(h($("#mori").val()));
	var harai = to_num(h($("#harai").val()));
	var shi1 = to_num(h($("#shi1").val()));
	var sid = to_num($("#hat_sousa_s_id").val());


	var s_moto_invoice = to_num($("#s_moto_invoice").val());
	var sales3p = to_num($("#sales3p").val());
	var s_hattyu = to_num($("#s_hattyu").val());
	var tate_hi = to_num($("#tate_hi").val());
	var harai_hi = to_num($("#harai_hi").val());
	var ashi_m2 = to_num($("#ashi_m2").val());
	var ashi_tanka = to_num($("#ashi_tanka").val());
	var ashi_total = to_num($("#ashi_total").val());
	var sheet_m2 = to_num($("#sheet_m2").val());
	var sheet_tanka = to_num($("#sheet_tanka").val());
	var sheet_total = to_num($("#sheet_total").val());
	var geya_m2 = to_num($("#geya_m2").val());
	var geya_tanka = to_num($("#geya_tanka").val());
	var geya_total = to_num($("#geya_total").val());
	var barashi = to_num($("#barashi").val());

	//発注額を超えていないかチェック
	var ttl = 0;
	$(".s_hattyu_after").each(function(i, elm) {
		ttl += to_num(h($(this).val()));
	});

	if(ttl > s_hattyu + 10) {
		alert("発注額が指定の金額を超えています。");
		return false;	
	}

	var flag = "HAT_MOD_EXEC";

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
		data:		{parm:[flag, g_id, _syu, tate, kake, fusagi, mori, harai, sid, shi1, s_moto_invoice, sales3p, s_hattyu, tate_hi, harai_hi, ashi_m2, ashi_tanka, ashi_total, sheet_m2, sheet_tanka, sheet_total, geya_m2, geya_tanka, geya_total, barashi]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("ajaxエラー");
		}
	});
	return true;
}



function hat_azuma_exec() {

	var g_id = h($(".g_id").eq(0).val());
	var s_hattyu = to_num(h($("#s_hattyu").val()));
	var seko_kubun_id = h($("#seko_kubun_id").eq(0).val());
	var seko_kubun = h($("#seko_kubun").eq(0).val());

	var shi1 = to_num(h($("#shi1").val()));
	var s_moto_invoice = to_num(h($("#s_moto_invoice").val()));
	var sales3p = to_num(h($("#sales3p").val()));
	
	if($('#zerohat').attr('checked') == 'checked')
		s_hattyu = 0;
		
	var flag = "HAT_AZUMA_EXEC";

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
		data:		{parm:[flag, g_id, s_hattyu,seko_kubun_id,seko_kubun,shi1,s_moto_invoice,sales3p]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
			show_success("発注書を更新しました。");
			close_dialog();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("ajaxエラー");
		}
	});
}

function hat_azuma_exec_jv() {

	var g_id = h($(".g_id").eq(0).val());
	var s_hattyu = to_num(h($("#s_hattyu").val()));
	var seko_kubun_id = h($("#seko_kubun_id").eq(0).val());
	var seko_kubun = h($("#seko_kubun").eq(0).val());

	var shi1 = to_num(h($("#shi1").val()));
	var s_moto_invoice = to_num(h($("#s_moto_invoice").val()));
	var sales3p = to_num(h($("#sales3p").val()));
	
	if($('#zerohat').attr('checked') == 'checked')
		s_hattyu = 0;
		
	var flag = "HAT_AZUMA_EXEC_JV";

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
		data:		{parm:[flag, g_id, s_hattyu,seko_kubun_id,seko_kubun,shi1,s_moto_invoice,sales3p]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
			show_success("発注書を更新しました。");
			close_dialog();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("ajaxエラー");
		}
	});
}

function hat_azuma_mod_exec() {

	var g_id = h($(".g_id").eq(0).val());
	var s_hattyu = to_num(h($("#s_hattyu").val()));
	var seko_kubun_id = h($("#seko_kubun_id").eq(0).val());
	var seko_kubun = h($("#seko_kubun").eq(0).val());

	var shi1 = to_num(h($("#shi1").val()));
	var s_moto_invoice = to_num(h($("#s_moto_invoice").val()));
	var sales3p = to_num(h($("#sales3p").val()));
	
	if($('#zerohat').attr('checked') == 'checked')
		s_hattyu = 0;
		
	var flag = "HAT_AZUMA_MOD_EXEC";

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
		data:		{parm:[flag, g_id, s_hattyu,seko_kubun_id,seko_kubun,shi1,s_moto_invoice,sales3p]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			edit_slip(g_id, 2);
			edit_slip(g_id, 3);
			show_success("発注書を更新しました。");
			close_dialog();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			show_fail("ajaxエラー");
		}
	});
}

function hat_calc() {

	var s_moto_invoice = to_num(h($("#s_moto_invoice").val()));
	var shi1 = to_num(h($("#shi1").val()));
	
	//職方比率が0なら計算しない
	if(!shi1)
		return;
	
	var s_hattyu = Math.floor(s_moto_invoice * shi1 / 100);
	$("#s_hattyu").val(s_hattyu);

	var tate_hi = to_num(h($("#tate_hi").val()));
	var harai_hi = to_num(h($("#harai_hi").val()));
	
	var tate = Math.floor(s_hattyu * tate_hi / 100);
	var harai = s_hattyu - tate;
	$("#tate").val(tate);
	$("#harai").val(harai);
	
	
	//m2計算
	var s_moto_invoice_wotax = (s_moto_invoice / (taxper + 1));
	var ashi_tanka = to_num(h($("#ashi_tanka").val()));
	var sheet_tanka = to_num(h($("#sheet_tanka").val()));
	var m2 = s_moto_invoice_wotax / (ashi_tanka + sheet_tanka);
	var geya_m2 = to_num(h($("#geya_m2").val()));
	var geya_tanka = to_num(h($("#geya_tanka").val()));
	
	$("#ashi_m2").val(m2.toFixed(2));
	$("#sheet_m2").val(m2.toFixed(2));

	var ashi_total = Math.floor(m2 * ashi_tanka * (taxper + 1));
	var sheet_total = Math.floor(m2 * sheet_tanka * (taxper + 1));
	var geya_total = Math.floor(geya_m2 * geya_tanka);
	
	$("#ashi_total").val(ashi_total);
	$("#sheet_total").val(sheet_total);
	$("#geya_total").val(geya_total);
	
	//日報金額計算
	var kake = Math.floor((ashi_total * 0.24) + (sheet_total * 0.12) - 5000 - geya_total);
	//var fusagi = Math.floor(5000 + (sheet_total * 0.12));
	var fusagi = tate - kake - geya_total;
	
	$("#kake").val(kake);
	$("#fusagi").val(fusagi);
	$("#barashi").val(harai);
	$("#mori").val(geya_total);
	
	//営業インセンティブ
	var sales3p = Math.floor(s_moto_invoice * 0.03);
	$("#sales3p").val(sales3p);
	
}

function hat_calc_azuma() {

	var s_moto_invoice = to_num(h($("#s_moto_invoice").val()));
	var shi1 = to_num(h($("#shi1").val()));
	
	//職方比率が0なら計算しない
	if(!shi1)
		return;
	
	var s_hattyu = Math.floor(s_moto_invoice * shi1 / 100);
	$("#s_hattyu").val(s_hattyu);

	//営業インセンティブ
	var sales3p = Math.floor(s_moto_invoice * 0.03);
	$("#sales3p").val(sales3p);
	
}

/**********************************************************************
 *
 * 	アップロードされたファイルを削除
 *
 **********************************************************************/
function delFileUpload(_pic, _file) {

	var r = confirm("削除してもよろしいですか？");
	
	if(r) {
		//写真を非表示
		$("#"+_pic).hide();

		var flag = "DELETE_FILE";
	
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
			data:		{parm:[flag, _file]},
			type:		"post",
			headers: 	{"pragma": "no-cache"},
			success:	function(data, textStatus) {
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
			}
		});
	}
}

function show_file(_id) {

	var flag = "SHOW_FILES";
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
			$(".file-upload-area").html(data);
			
			//書類がありの場合
			if($("#shorui_ari").length) {
				stenkai(tenkai_flag);
			}
			
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});
}

function stenkai(_p) {

	if(_p) {
		$(".file-up-table").eq(0).slideDown("1000",function() {
			$("#tenkai").html("<button onclick='stenkai(false)'>-</button>");
			tenkai_flag = true;
		});
	}
	else {
		$(".file-up-table").eq(0).slideUp("1000",function() {
			$("#tenkai").html("<button onclick='stenkai(true)'>+</button>");
			tenkai_flag = false;
		});
	}

	
	
}


function show_fileup() {

	var gid = $(".g_id").eq(0).val();
	
	open_dialog();
	$("#dialog-area-inner").html("<iframe src='../fileup/simpledemo/index.php?gid="+gid+"' width='600' height='600'></iframe>");

	//トップへスクロールする
	$('html,body').animate({ scrollTop: 0 }, 'fast');

	//領域外クリクで終了イベント
	$("#dialog-area-outer").click(function(event) {
		event.stopPropagation();
		//書類エリアを表示
		tenkai_flag = true;

		show_file(gid);
		//ダイアログを閉じる
		close_dialog();
	});
	
	//クローズボタンイベント
	$(".dialog-close").click(function(event) {
		event.stopPropagation();
		//書類エリアを表示
		tenkai_flag = true;

		show_file(gid);
		//フォームに変更があった場合注意
		close_dialog();
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

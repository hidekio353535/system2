// JavaScript Document

var change_flag_nippou = false;
$(function() {
	
	$(document).on("change",".nippou-area input",function() {
		$(this).addClass("update-change");
		change_flag_nippou = true;
		$(".np-btn").hide();
	});
	
	$(document).on("change",".nippou-area .np_kazu",function() {
		var val = toHankakuNum($(this).val());
		$(this).val(val);
		var idx = $(".nippou-area .np_kazu").index(this);
		line_calc(idx);
	});
	$(document).on("change",".nippou-area .np_tanka",function() {
		var val = toHankakuNum($(this).val());
		$(this).val(val);
		var idx = $(".nippou-area .np_tanka").index(this);
		line_calc(idx);
	});
		
	//エンターコントロール
	enter_ctr();
	
});

function line_calc(idx) {
	np_kazu = to_num($(".nippou-area .np_kazu").eq(idx).val());
	np_tanka = to_num($(".nippou-area .np_tanka").eq(idx).val());
	
	// bignumber.min.js での丸め誤差計算
	var np_kin = new BigNumber(np_kazu).times(np_tanka).toPrecision();
	
	np_kin = Math.floor(np_kin);
	
	if(!np_kin) {
		np_kin = "";
	}
	
	$(".nippou-area .np_kin").eq(idx).val(np_kin).trigger("change");
	calc();
}

function calc() {
	
	var total = 0;
	$(".nippou-area .np_kin").each(function(i,v) {
		total += to_num($(this).val());
	});
	
	if(!total) {
		total = "";
	}
	else {
		total = "￥" + numeral(total).format('0,0');
	}
	$(".kin-total").text(total);
}

function save() {

	var r = confirm("保存してもよろしいですか？");
	if(r) {
		//changeイベントを正確に受け取るために少し遅らせる
		setTimeout(function() {
			var update_info = [];

			//更新と削除のフラグを取得
			$(".update-change").each(function(i) {
				update_info[i] = {};

				//値取得時変換
				update_info[i].table = "matsushima_nippou_meisan";
				update_info[i].field_name = h( $(this).attr("data-field_name") );
				update_info[i].id = h( $(this).attr("data-id") );
				update_info[i].id_field = "np_id";
				update_info[i].val = h($(this).val());
			});
			
			//change_flag_nippou クリア
			change_flag_nippou = false;
			$(".np-btn").show();
			
			//db構造配列も渡す
			var parm = {"flag":"SAVE", "update_info":update_info};
	
			$.ajax({
				async:		true,
				cache:		false,
				url:		"../nippou/save.php",
				data:		parm,
				dataType:	"json",
				type:		"post",
				headers: 	{"pragma": "no-cache"},
				success:	function(data, textStatus) {
					if(data.status=="OK") {
						alert("保存しました。");
					}
					else {
						alert("保存に失敗しました。もう一度保存しても成功しない場合は小川までご連絡下さい。");
					}
				},
				complete:	function(data, textStatus) {
				},
				error:		function(data, textStatus) {
						alert("保存に失敗しました。もう一度保存しても成功しない場合は小川までご連絡下さい。");
				}
			});

		}, 500);
	}
}

function enter_ctr() {
	//エンター処理　キーイベント
	var hCode = 13; //Enter
	var left = 37;
	var up = 38;
	var right = 39;
	var down = 40;

	var elm = ".main-nip-table input, .main-nip-table select";

	$(document).on("keypress",elm, function (e) {
		var jump = 6;

		if(e.which == hCode) {

			var idx = $(elm).index(this);
			//ジャンプする数
			idx += jump;

			$(elm).eq(idx).focus().select();

			return false;
		}
	});
	
}

function show_nip_optional(_tbl, _elm, _idx, _rtbl, _rclm) {

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
			
			/*
			$("#dialog-area").click(function(event) {
				close_optional();
				event.stopPropagation();
			});
			*/
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

function m2_tori() {
	var m2 = to_num($(".g_m2").eq(0).val());
	var g_nai2_id = $(".g_nai2_id").eq(0).val();
	var n_kubun = $("#n_kubun").eq(0).text();
	if(m2) {
		if(g_nai2_id == "1" || g_nai2_id == "2") {
			// 1F or 2F
			if(n_kubun == "架払" || n_kubun == "架") {
				//架払 or 架
				$(".main-nip-table .np_name").each(function() {
					if($(this).val() == "外部足場(延床)架2Fまで") {
						$(this).closest("tr").find(".np_kazu").val(m2).trigger("change");
					}
				});
			}
			if(n_kubun == "架払" || n_kubun == "払し") {
				//架払 or 払
				$(".main-nip-table .np_name").each(function() {
					if($(this).val() == "外部足場(延床)払2Fまで") {
						$(this).closest("tr").find(".np_kazu").val(m2).trigger("change");
					}
				});
			}
		}
		else if(g_nai2_id == "3") {
			//3F
			if(n_kubun == "架払" || n_kubun == "架") {
				//架払 or 架
				$(".main-nip-table .np_name").each(function() {
					if($(this).val() == "外部足場(延床)架3F") {
						$(this).closest("tr").find(".np_kazu").val(m2).trigger("change");
					}
				});
			}
			if(n_kubun == "架払" || n_kubun == "払し") {
				//架払 or 払
				$(".main-nip-table .np_name").each(function() {
					if($(this).val() == "外部足場(延床)払3F") {
						$(this).closest("tr").find(".np_kazu").val(m2).trigger("change");
					}
				});
			}
		}
		else if(g_nai2_id == "16") {
			//3F
			if(n_kubun == "架払" || n_kubun == "架") {
				//架払 or 架
				$(".main-nip-table .np_name").each(function() {
					if($(this).val() == "外部足場(延床)架2.5F") {
						$(this).closest("tr").find(".np_kazu").val(m2).trigger("change");
					}
				});
			}
			if(n_kubun == "架払" || n_kubun == "払し") {
				//架払 or 払
				$(".main-nip-table .np_name").each(function() {
					if($(this).val() == "外部足場(延床)払2.5F") {
						$(this).closest("tr").find(".np_kazu").val(m2).trigger("change");
					}
				});
			}
		}
	}
	
}
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
	$("ul.dropdown > li").eq(3).css("background","#FFFFDC");

	$("#shime_year").val(get_this_year());
	$("#shime_month").val(get_this_month());

	$("#report-btn1").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin101=window.open("../report/monthlysalesSokuhou.php?year="+year+"&month="+month ,"sub101", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin101.focus();
	});
	
	$("#report-btn2").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin102=window.open("../report/monthlysales.php?year="+year+"&month="+month ,"sub102", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin102.focus();
	});

	$("#report-btn7").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin107=window.open("../report/jyutyurep_s.php?year="+year+"&month="+month ,"sub107", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin107.focus();
	});

	$("#report-btn3").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin103=window.open("../report/jyutyurep.php?year="+year+"&month="+month ,"sub103", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin103.focus();
	});

	$("#report-btn8").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin108=window.open("../report/hattyurep_s.php?year="+year+"&month="+month ,"sub108", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin108.focus();
	});

	$("#report-btn4").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin104=window.open("../report/hattyurep.php?year="+year+"&month="+month ,"sub104", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin104.focus();
	});

	$("#report-btn5").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin105=window.open("../report/invrep.php?year="+year+"&month="+month ,"sub105", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin105.focus();
	});

	$("#report-btn6").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin106=window.open("../report/hatrep.php?year="+year+"&month="+month ,"sub106", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin106.focus();
	});

	$("#report-btn9").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin109=window.open("../report/invrep_shime.php?year="+year+"&month="+month ,"sub109", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin109.focus();
	});

	$("#report-btn10").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin110=window.open("../report/yotei_rep.php?year="+year+"&month="+month ,"sub110", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin110.focus();
	});
	
	$("#report-btn11").click(function() {
		var year = $("#shime_year").val();
		var month = $("#shime_month").val();
		subwin111=window.open("../report/jyutyurep_by_year.php?year="+year+"&month="+month ,"sub111", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin111.focus();
	});


});


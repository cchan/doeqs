function topAlert(msg,closeit,timetoclosemsec){
	//by default, add option to close
	if(closeit===undefined)closeit=true;
	
	$("#topAlert").css({
		"top":"0px",
		"opacity":"1",
		"margin-left":-$("#topAlert").width()/2
	}).html(msg+((closeit)?" <a href='#' style='font-size:0.7em;' onclick='unTopAlert();'>[close]</a>":""));
	
	//close timeout
	if(timetoclosemsec!==undefined)setTimeout(unTopAlert,timetoclosemsec);
}
function unTopAlert(){
	$("#topAlert").css({
		"opacity":"0",
		"top":"-20px"
	}).html("");
}
function midAlert(msg){
	$("#midAlert").html("<div style='height:100%;'>"+msg+"</div><div style='text-align:right;margin-top:-20px;'><br><a href='#' style='font-size:0.7em;color:#FF0000;' onclick='unMidAlert();'>[CLOSE WINDOW]</a></div>")
		.css({
			"display":"block",
			"opacity":"1",
			'position' : 'absolute',
			'left' : '50%',
			'top' : '50%',
			'margin-left' : -$(this).width()/2,
			'margin-top' : -$(this).height()/2
		});
}
function unMidAlert(){
	$("#midAlert").hide(500).html("");
}

$(function(){
	$(document.createElement("div")).attr("id","topAlert").appendTo("body").clone().attr("id","midAlert").appendTo("body");
});
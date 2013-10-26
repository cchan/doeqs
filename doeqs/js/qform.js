$(function(){
	xhr("qsetselect=1",{loadMsg:"Loading available question sets...",success:function(data){
		data;//{ID:name,ID:name,...}
		//foreach...
	},params:{/*FORMAT:JSON*/}});
	
	//What happens when qset is selected - display qSetHas
	$("#qset").change(function(e){
		if($("#qset").val()=="0"){
			$("#qsethas").html("&nbsp;");
			return;
		}
		xhr($("#qset").serialize(),{loadMsg:"Getting set info...",success:function(data){$("#qsethas").html(data);}});
	});

	//Convenience, selects direct-entry form
	var qf=$("#directentry");
	//When subject changes, change the Bonus subject to same
	qf.find("select[name=Subject]").change(function(){$("#BSubj").html($(this).find("option:selected").html());});
	//What happens when the multiple choice changes - fill in answers.
	function updateAns(pref){//Update answer field.
		if(!pref)var pref=$(this).siblings("div").add($(this).parent("label").parent("div")).attr("id").slice(0,-2);//get parent[from input]/sibling[from select] div which-part indicator (TU or B)
		var index=parseInt(qf.find("input[name="+pref+"MCa]").val());//Index of selected
		qf.find("input[name="+pref+"Answer]").val(//Assign answer displayed value
			["W","X","Y","Z"][index]+") "//Letter
			+qf.find("input[name='"+pref+"MC[]']").eq(index).val()//Value of answer selected
		);
	}
	qf.find("input[name=TUMC],input[name=BMC]").on("click",updateAns);
	qf.find("input[name=TUMCa],input[name=BMCa]").on("keyup",updateAns);
	
	//Changing between MC and SA
	qf.find("select[name=TUQType],select[name=BQType]").change(function(){
		var pref=this.name.slice(0,-5);//Get basic prefix, dynamically, based on name
		if($(this).val()=="mc"){
			qf.find("input[name="+pref+"MC]").eq(0).val(qf.find("input[name="+pref+"Answer]").prop("readonly",true).val());
			$("#"+pref+"MC").show();//wrapper for whole MC thing
			updateAns(pref);//update answers
		}
		else{
			qf.find("input[name="+pref+"Answer]").prop("readonly",false).val(qf.find("[name="+pref+"MC]").val());
			$("#"+pref+"MC").hide();
		}
	});

	//What happens when submit button is pressed
	qf.submit(function(e){
		xhr($("#directentry,#qset").serialize(),{loadMsg:"Submitting...",success:topAlert});
		e.preventDefault();
		return false;
	});
	$("#copypaste").submit(function(e){
		xhr($("#copypaste,#qset").serialize(),{loadMsg:"Submitting...",success:topAlert})//**ensure success:topAlert actually works
		e.preventDefault();
		return false;
	});

	//--todo--this below, about backups.
	function backupQForm(){//Back up ALL THREE forms...
		$.cookie("backupQForm",$("#directentry,#copypaste,#fileupload").serialize());//DONE. Maybe not serialize... JSON?
	}
	function restoreQForm(){
		//hmm unserialize is difficult - try deparam or just use jq plugin
	}
	//var backupInterval=setInterval(backupQForm,2000);//wait what's the point then... it's just overwriting the backup.
	
	$("#question-input-menu li").click(function(){
		if(!$(this).hasClass("currentinput")){
			$(this).addClass("currentinput").siblings("li").removeClass("currentinput");
			$("#question-wrapper form").eq($(this).index()).addClass("currentinput").siblings("form").removeClass("currentinput");
		}
		else{
			$("#question-input-menu li").removeClass("currentinput");
			$("#question-wrapper form").removeClass("currentinput");
		}
	});
});

//--todo--Remove messages when switching name/date - confirm moving-away if typed anything
//--todo-- reject IE9- on sight (no placeholders, for example), suggest to users new dl. (what if can't?)
//--todo--research controlling click propagation
/* //To keep, for horror stories. Sixteen horribly dynamically generated evals. Just to change the answer value automatically.
for(var i=0;i<4;i++){
	var letter=["W","X","Y","Z"][i];
	eval('qf.TUMC['+i+'].onclick=function(){qf.TUAnswer.value=qf.TUMC['+i+'].value+") "+qf.getElementsByClassName("TUMCa")['+i+'].value;};');
	eval('qf.getElementsByClassName("TUMCa")['+i+'].onkeyup=function(){if(qf.TUMC['+i+'].checked)qf.TUAnswer.value="'+letter+') "+this.value;};');
	eval('qf.BMC['+i+'].onclick=function(){qf.BAnswer.value=qf.BMC['+i+'].value+") "+qf.getElementsByClassName("BMCa")['+i+'].value;};');
	eval('qf.getElementsByClassName("BMCa")['+i+'].onkeyup=function(){if(qf.BMC['+i+'].checked)qf.BAnswer.value="'+letter+') "+this.value;};');
}*/

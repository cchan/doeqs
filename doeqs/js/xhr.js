function xhr(topost,options){//Options: string loadmsg, success //assumes not loading entire page - just fetching data
	if(options===undefined)var options={};
	if(options.hasOwnProperty("loadMsg"))topAlert(options.loadMsg,false);
	$.ajax("index.php",$.extend({cache:false,data:topost,type:"POST"},options))
		.done(function(data){
			unTopAlert();
			if(data.substr(0,9)!="<!DOCTYPE")if(options.hasOwnProperty("success"))options.success(data);
			else topAlert("Server response invalid. Maybe your login session timed out - <a href='index.php?p=login' target=_blank_>log in</a> and try again?");
		})//what if success func not there?
		.fail(function(){topAlert("Connection lost. <a href='#' onclick='window.location.reload(true);'>Reload Webpage</a>");});
		//--todo--how about a "try again"?
}

function login(e){
	xhr($("#loginform").serialize(),{
		loadMsg:"Logging in...",
		success:function(data){
			if(data=="success"){
				setTimeout(function(){window.location.reload(true);},1000);
				topAlert("Logged in as <i>"+$("#loginform input[name=uname]").val()+"</i>.");
			}
			else topAlert(data);
		}
	});
	e.preventDefault();
	return false;
}

function logout(){
	xhr("logout=1",{
		loadMsg:"Logging out...",
		success:function(data){
			if(data=="success"){
				setTimeout(function(){window.location="index.php";},1000);
				topAlert("Successfully logged out.");
			}
			else topAlert(data);
		}
	});
}

function getDLOpts(e){
	xhr("downloadoptions=1",{loadMsg:"Getting download options...",success:function(data){
		midAlert(data);
		$("#downloadoptions").submit(function(e){
			window.location.assign("index.php?"+$(this).serialize());
			e.preventDefault();
			return false;
		});
	}});
}

function subBug(e){
	$(this).find(":input").each(function(){
		if($(this).val()!==""){
			xhr($("#bugreport").serialize(),{
				loadMsg:"Sending bug report...",
				success:function(data){
					if(data==="success"){
						$("#bugreport").find(":input").val("");
						topAlert("Thanks for the bug report! We'll be on it shortly.",true,2500);
					}
					else topAlert("Oops, we had a problem getting your report :\\",true,2500);
				}
			});
			return false;
		}
	});
	e.preventDefault();
	return false;
}

$(function(){
	//What happens when "Download" is pressed
	$("#downloadlink").click(getDLOpts);

	//Login enter-key checkers
	$("#uname,#passw").keypress(function(e){if(e.keyCode==13)$("#loginform").submit();});

	//Login/logout buttons
	$("#loginform").submit(login);
	$("#logoutbutton").click(logout);

	//Bug reporting.
	$("#bugreport").submit(subBug);
	
	//show/hide answer on getq
	$(".hiddenanswer a").click(function(e){
		if($(this).html()=="[show]")$(this).html("[hide]").siblings("span").show();
		else $(this).html("[show]").siblings("span").hide();
	}).click();
});
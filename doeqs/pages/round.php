<?php
//Make sure doesn't give the questions of anyone in the round.


//FIRST: do a live-chat program where all the people are listed, then modify so that there's an "admin" that can do stuff,
//then add computer capability to add to the chatstream. Basically. Try referring to Protobowl.
?>
<style type="text/css">
#maintimer{
	width:300px;height:100px;
	font-size:64pt;
}
#extratimer{
	width:200px;height:70px;
	font-size:48pt;
}
#maintimer,#extratimer{
	margin-left:auto;margin-right:auto;
	padding:auto;
	font-weight:bold;
	font-family:Courier;
	text-align:center;
	background-color:#9999FF;
	border:solid 3px #000000;
	border-radius:10px;
	color:#FF0000;
	-webkit-transition:background-color 0.15s,color 0.15s;
	transition:background-color 0.15s,color 0.15s;
}
</style>
<div id="maintimer">0.0</div><div id="extratimer">0.0</div>
<button onclick="timerTossUp();">Toss-Up</button><button onclick="timerBonus();">Bonus</button><button onclick="timerCancelTUB();">Cancel</button><br>
<button onclick="timerStartTime();">Start</button><button onclick="timerStopTime();">Stop</button>
<script type="text/javascript">
function timerTossUp(){timerStart("extratimer",5);}
function timerBonus(){timerStart("extratimer",20);}
function timerCancelTUB(){timerPause("extratimer");$("extratimer").innerHTML="0.0";}

function timerStartTime(){timerStart("maintimer",480);}
function timerStopTime(){timerPause("maintimer");}

window.timertenths=new Array();
window.timerInterval=new Array();
window.timerFinishInterval=new Array();
window.timerFinishFlashCount=new Array();
window.timerStarted=new Array(false,false);
function timerStart(id,timesec){//SetInterval is very unreliable anyway...
	var x=(id=="maintimer")?0:1;
	if(window.timerStarted[x])return;
	//.1 sec delay...
	if($(id).innerHTML=="0.0"){
		window.timertenths[x]=10*timesec;
		$(id).innerHTML=(((window.timertenths[x]/600)>=1)?Math.floor(window.timertenths[x]/600)+":":"")+((window.timertenths[x]%600<100&&(window.timertenths[x]/600)>=1)?"0":"")+((window.timertenths[x]%600)/10)+((window.timertenths[x]%10==0)?".0":"");
	}
	window.timerInterval[x]=setInterval("window.timertenths["+x+"]--;$(\""+id+"\").innerHTML=((window.timertenths["+x+"]/600>=1)?Math.floor(window.timertenths["+x+"]/600)+\":\":\"\")+((window.timertenths["+x+"]%600<100&&(window.timertenths["+x+"]/600)>=1)?\"0\":\"\")+((window.timertenths["+x+"]%600)/10)+((window.timertenths["+x+"]%10==0)?\".0\":\"\");if(window.timertenths["+x+"]<1)timerFinish(\""+id+"\");",100);
	window.timerStarted[x]=true;
}
function timerPause(id){
	clearInterval(window.timerInterval[(id=="maintimer")?0:1]);
	window.timerStarted[(id=="maintimer")?0:1]=false;
}
function timerFinish(id){
	var x=(id=="maintimer")?0:1;
	clearInterval(timerInterval[x]);
	$(id).style.backgroundColor="#FF0000";$(id).style.color="#0000FF";
	setTimeout(function(){$(id).style.backgroundColor="#9999FF";$(id).style.color="#FF0000";},200);
	window.timerFinishFlashCount[x]=3;
	window.timerFinishInterval[x]=setInterval("$(\""+id+"\").style.backgroundColor=\"#FF0000\";$(\""+id+"\").style.color=\"#0000FF\";setTimeout(function(){$(\""+id+"\").style.backgroundColor=\"#9999FF\";$(\""+id+"\").style.color=\"#FF0000\";},200);window.timerFinishFlashCount["+x+"]--;if(window.timerFinishFlashCount["+x+"]==1){$(\""+id+"\").style.backgroundColor=\"#FF0000\";$(\""+id+"\").style.color=\"#0000FF\";clearInterval(window.timerFinishInterval["+x+"]);}",400);
	window.timerStarted[x]=false;
}
</script>
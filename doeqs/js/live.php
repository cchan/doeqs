<?php //title Live Online Round ?>
<div id="live-main-wrapper" style="font-family:Comic Sans MS;">

</div>
<script>
int n=0;
function update(){
	var recieved=$.getJSON("liveproc.php",{n:n});
	n=recieved.n;
	for(int i=0;i<30;i++){
		if(!recieved[i])continue;
		recieved[i][0];//title of item
		//text
		//color
		//where to encode answer?
	}
}

function add(q){//color determined on server side
	q.title;
	q.text;
	q.color;
	$("#live-main-wrapper").html("<div class='live-block'><div></div><div></div></div>");
}
</script>
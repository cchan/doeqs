<?php
require_once 'functions.php';
require_once('classes/recaptchalib.php');

if(posted('logout')){
	logout();
	alert('Successfully logged out.',1);
}
elseif(csrfVerify()){
	if(limit_attempts('login',10,300))
		alert('Too many attempts. (no more than ten in five minutes)',-1);
	elseif(posted('login')){//Is the submit button submitted for all browsers? hm
		//Naturally all this stuff is useless without proper SSL security. Shhhhhhhhhhh.
		if(loginEmailPass($_POST['email'],$_POST['pass'])){
			alert('Successfully logged in!',1);
			reset_attempts('login');
		}
		else{
			logout();
			alert('Incorrect username or password.',-1);
		}
	}
	elseif(posted('signup')){
		//What happens when no access to the captcha server?
		if(!chkCaptcha())
			alert('Invalid reCAPTCHA entry; try again.',-1);
		else{
			$err=newProfileError($_POST['s_email'],$_POST['s_pass'],$_POST['s_confpass']);
			if($err===false){
				alert('Successfully signed up; you can now log in.',1);
				$signup_success=true;
				reset_attempts('login');
			}
			else alert(htmlentities($err),-1);
		}
	}
}

if(userAccess('u'))echo "Currently logged in as <b>{$_SESSION['email']}</b>.";
else{?>
<table id="loginformtable"><tr>
	<td>
	<?=generateForm(['action'=>'login.php','method'=>'POST','autocomplete'=>'off'],[
		'<h2>Sign Up</h2>',
		['prompt'=>'Email:','name'=>'s_email','value'=>isSet($signup_success)?'':ifpost('s_email'),'autofocus'=>'autofocus'],
		['prompt'=>'Password:','name'=>'s_pass','type'=>'password'],
		['prompt'=>'Again:','name'=>'s_confpass','type'=>'password'],
		'Captcha:<br>'.getCaptcha(),
		['name'=>'signup','type'=>'submit','value'=>'Sign Up']
	])?>
	</td>
	<td>
	<?=generateForm(['action'=>'login.php','method'=>'POST'],[
		'<h2>Log In</h2>',
		['prompt'=>'Email:','name'=>'email','value'=>isSet($signup_success)?ifpost('s_email'):ifpost('email'),'autofocus'=>'autofocus'],
		['prompt'=>'Password:','name'=>'pass','type'=>'password'],
		'',
		['name'=>'login','type'=>'submit','value'=>'Log In']
	])?>
	</td>
</tr></table>
<script>if($('[name="email"]').val()​​​​​​​​​​​​​​​​​​​​​.trim().length>0)$('[name="pass"]').focus();</script>
<?php }?>
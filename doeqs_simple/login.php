<?php
require_once 'functions.php';

if(posted("logout")){
	logout();
	$login_success="Successfully logged out.";
}
elseif(csrfVerify()){
	if(posted("login")){//does this happen for all browsers? hm
		if(loginEmailPass($_POST["email"],$_POST["pass"])){//Naturally all this stuff is useless without proper SSL security. Shhhhhhhhhhh.
			$login_success="Successfully logged in!";
		}
		else{
			logout();
			$login_error="Incorrect username or password.";
			$login_error_email=htmlentities($_POST["email"]);
		}
	}
	elseif(posted("signup")){
		
	}
}

if(userAccess("u")){
?>
<div style="color:green;font-weight:bold;"><?php if(isSet($login_success))echo $login_success;?></div>
Currently logged in as <b><?php echo $_SESSION["email"];?></b>.
<?php }else{?>

<style>
#loginformtable{
width:100%;
}
.loginsection{
border:solid 1px #000000;
width:50%;
vertical-align:top;
padding:10px;
}
.loginsection td{
text-align:right;
height:2em;
}
.login-error{
color:red;
font-weight:bold;
}
.login-success{
color:green;
font-weight:bold;
}
</style>
<div class="login-success"><?php if(isSet($login_success))echo $login_success;?></div>
<div class="login-error"><?php if(isSet($login_error))echo $login_error;?></div>

<table id="loginformtable">
<tr>
	<td class="loginsection">
	<form action="login.php" method="POST" disabled>
		<input type="hidden" name='ver' value="<?=csrfCode();?>"/>
		<h3>Sign Up</h3>
<div class="login-error">Signup doesn't work yet.</div>
		<table>
		<tr><td>Email:<td><input type="text" name="email" />
		<tr><td>Password:<td><input type="password" name="pass" />
		<tr><td>Confirm Password:<td><input type="password" name="confpass" />
		<tr><td colspan="2"><input type="submit" name="signup" value="Sign Up" />
	</form>
	</table>

	<td class="loginsection">
	<form action="login.php" method="POST">
		<input type="hidden" name='ver' value="<?=csrfCode();?>"/>
		<h3>Log In</h3>
		<table>
		<tr><td>Email:<td><input id="login_email" type="text" name="email" value="<?php if(isSet($login_error_email))echo $login_error_email;?>"/>
		<tr><td>Password:<td><input id="login_pass" type="password" name="pass"/>
		<tr><td>&nbsp;
		<tr><td colspan="2"><input type="submit" name="login" value="Log In" />
	</form>
	</table>
</table>
<script>
$(function(){
if($("#login_email").val()=='')$("#login_email").focus();
else $("#login_pass").focus();
});
</script>
<?php }?>
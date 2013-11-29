<?php
require "functions.php";

if(csrfVerify()){
	if(posted("login")){//does this happen for all browsers? hm
		//CHECK IN DATABASE
		
		//email pass confpass
		if(hashEquals($_POST["email"],"moo")&&hashEquals($_POST["pass"],"oink"))$_SESSION["user"]="moo";
	}
	elseif(posted("signup")){
		
	}
	elseif(posted("logout"))unset($_SESSION["user"]);
}

if(isSet($_SESSION["user"])){
?>
Currently logged in as <b><?php echo $_SESSION["user"];?></b>.
<form action="login.php" method="POST">
	<input type="hidden" name="ver" value="<?=csrfCode();?>"/>
	<input type="submit" name="logout" value="Log Out" />
</form>
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
</style>


<table id="loginformtable">
<tr>
	<td class="loginsection">
	<form action="login.php" method="POST">
		<input type="hidden" name="ver" value="<?=csrfCode();?>"/>
		<h3>Sign Up</h3>
		<table>
		<tr><td>Email:<td><input type="text" name="email" />
		<tr><td>Password:<td><input type="password" name="pass" />
		<tr><td>Confirm Password:<td><input type="password" name="confpass" />
		<tr><td colspan="2"><input type="submit" name="signup" value="Sign Up" />
	</form>
	</table>

	<td class="loginsection">
	<form id="loginform" action="login.php" method="POST">
		<input type="hidden" name="ver" value="<?=csrfCode();?>"/>
		<h3>Log In</h3>
		<table>
		<tr><td>Email:<td><input type="text" name="email" />
		<tr><td>Password:<td><input type="password" name="pass" />
		<tr><td>&nbsp;
		<tr><td colspan="2"><input type="submit" name="login" value="Log In" />
	</form>
	</table>
</table>
<?php }?>
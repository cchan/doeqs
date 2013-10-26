<?php if(permissionsLevel()==-1){?>
NOTE: Since the registration system doesn't really exist yet, please use the temporary login "test" with password "test"
<form id="loginform">
Name: <input type="text" name="uname"/><br>
Password: <input type="password" name="passw"/><br>
<input type="submit" value="Login"/>
</form>
<?php }else{?>
You're already logged in!
<?php }?>
<br><br><br><br><table width="90%" valign="top" border="0" cellpadding="3" cellspacing="0" align="center">
<tr><td align="center" colspan="2"> &nbsp;</img></td></tr>
 {+if isset($username) && $username+}
 <tr><td colspan="2"><h1 id="mainhead" align="center">You are now LOGGED OUT.</h1></td></tr>
 {+else+}
 <tr><td colspan="2"><h1 id="mainhead" align="center">You have NOT logged in yet.</h1></td></tr>
 {+/if+}
<!-- <tr><td colspan="2"><h3 id="mainhead" align="center">Please log in.</h3></td></tr> -->
  <tr>
    <td colspan="0" align="center">
      <form name="loginform" id=loginform action=login.php method=post>
        <input type=hidden name=cmd value=login>
        <font color=red>{+if isset($errmsg)+}{+$errmsg+}{+/if+}</font>
	<table>
	<tr>
	<td align="right">
        <!-- Username: <input type=text name=email value="{+$email+}" size=32> -->
        	<label>Username: </label>
	</td>
	<td align="left">
		<input type=text name=email value="{+if isset($user)+}{+$user+}{+/if+}" size=30><br><br>
	</td>
	</tr>
	<tr>
	<td align="right">
        	<label>Password: </label>
	</td>
	<td align="left">
        	<input type=password name=pass value="{+if isset($password)+}{+$password+}{+/if+}" size=30><br><br>
	</td>
	</tr>
	<tr>
	<td colspan="2" align="center">
        	<input type=submit value="Login">
	</td>
	</tr>
	</table>
      </form>
    </td>
  </tr>
</table>

{+$summary+}

<SCRIPT language="JavaScript"> 
  document.loginform.email.focus(); 

function init_page()
{
}

</SCRIPT>

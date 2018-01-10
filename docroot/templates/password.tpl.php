<table width="90%" valign="top" border="0" cellpadding="3" cellspacing="0" align="center">
  <tr>
    <td>
      <table border=0 width=100%>
        <td width=100>&nbsp;</td>
        <td><h1 id="mainhead" align="center">Change Password</h1></td>
        <td width=100 align=right><font size=-1>Current user:<br>{+$username+}</font></td>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <BR><BR><BR>
      <table valign="top" border="0" cellpadding="2" cellspacing="2" align="center">
        <tr align=left>
          <td align=center>
            <form name="myform" action="login.php" method="post" onSubmit="return validate();">
              <input type=hidden name=cmd value=change_password>
	      <table border="0" cellpadding="2" cellspacing="2" align="center">
	        <tr><td>Enter OLD password: </td><td><input type=password name=pass0 size=32 value=""></td></tr>
	        <tr><td>Enter NEW password: </td><td><input type=password name=pass1 size=32 value=""></td></tr>
	        <tr><td>Enter NEW again:    </td><td><input type=password name=pass2 size=32 value=""></td></tr>
                <tr><td colspan=2 align=center><input type=Submit value=Go></td></tr>
	      </table>
            </form>
	  </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td>&nbsp;</td></tr>
</table>
<SCRIPT language="JavaScript">document.myform.pass0.focus(); </SCRIPT>

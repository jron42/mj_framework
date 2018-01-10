<table width="90%" valign="top" border="0" cellpadding="3" cellspacing="0" align="center">
  <tr>
    <td>
      <table border=0 width=100%>
        <td width=100>&nbsp;</td>
        <td><h1 id="mainhead" align="center">User Management</h1></td>
        <td width=100 align=right><font size=-1>Current user:<br>{+$username+}</font></td>
      </table>
    </td>
  </tr>
  <tr>
    <td align=left>
      <form name="fetchform" action="page.php" method="get">
        <input type=hidden name=page value=user>
        <input type=hidden name=cmd  value=find>
        Fetch User: <input type=text width=12 name=search id=search value="{+$search+}"> 
        <input type=submit value="Go">
        &nbsp;&nbsp;<font color="#CCCCCC">Enter part of the name to search for</font>
      </form>
      <form name="editform" action="submit.php" method="get" target=RSIFrame>
        <input type=hidden name=cmd    value=userEdit>
        <input type=hidden name=subcmd value=edit>
        <input type=hidden name=userId value="">
      </form>
      <BR>
    </td>
  </tr>
  <tr>
          <td align=left>
            <table valign="top" border="2" cellpadding="0" cellspacing="0">
              <tr style="background-color: #CCCCCC;">
                <th style="width: 65px;">&nbsp;Id&nbsp;</td>
                <th style="width: 250px;">&nbsp;Name&nbsp;</td>
                <th style="width: 150px;">&nbsp;Handle&nbsp;</td>
                <th style="width: 350px;">&nbsp;Email&nbsp;</td>
                <th style="width: 100px;">&nbsp;Status&nbsp;</td>
              </tr>
            </table>
            <div style="overflow:scroll; height:200px;">
              <table valign="top" border="2" cellpadding="0" cellspacing="0">

                {+foreach from=$userList item=user+}
                  <tr>
                    <td align=left style="width: 65px;">&nbsp;&nbsp;
                      <label id="name_{+$user.id+}" onClick="useredit('{+$user.id+}');">{+$user.id+}</label>&nbsp;&nbsp;</td>
                    <td align=left style="width: 250px;">&nbsp;&nbsp;
                      <label id="name_{+$user.fullName+}" onClick="useredit('{+$user.id+}');">{+$user.fullName+}</label>&nbsp;&nbsp;</td>
                    <td align=left style="width: 150px;">&nbsp;&nbsp;
                      <label id="name_{+$user.handle+}" onClick="useredit('{+$user.id+}');">{+$user.handle+}</label>&nbsp;&nbsp;</td>
                    <td align=left style="width: 350px;">&nbsp;&nbsp;
                      <label id="name_{+$user.email+}" onClick="useredit('{+$user.id+}');">{+$user.email+}</label>&nbsp;&nbsp;</td>
                    <td align=left style="width: 100px;">&nbsp;&nbsp;
                      <label id="name_{+$user.status+}" onClick="useredit('{+$user.id+}');">{+$user.status+}</label>&nbsp;&nbsp;</td>
                  </tr>
                {+/foreach+}

              </table>
            </div>
    </td>
  </tr>
  <tr>
    <td colspan="0" align="center">
      <form name="myform" action="submit.php" method="get" onSubmit="return validate();" target=RSIFrame> 
      <input type=hidden name=cmd    value=userEdit>
      <input type=hidden name=subcmd value=save>
      <input type=hidden name=userId value="{+if isset($editUserId)+}{+$editUserId+}{+/if+}">

      <table valign="top" border="0" cellpadding="2" cellspacing="2" align="center">
        <tr align=left>
          <td>&nbsp;Id&nbsp;</td>
	  <td colspan=2>&nbsp;<label id=editUserId>{+if isset($editUserId)+}{+$editUserId+}{+/if+}</label>&nbsp;</td>
        </tr>
        <tr align=left>
          <td>&nbsp;Full Name&nbsp;</td>
	  <td colspan=2>&nbsp;<input type=text name=fullName size=45 value="{+if isset($fullName)+}{+$fullName+}{+/if+}">&nbsp;</td>
        </tr>
        <tr align=left>
          <td>&nbsp;Username&nbsp;</td>
	  <td colspan=2>&nbsp;<input type=text name=handle size=45 value="{+if isset($handle)+}{+$handle+}{+/if+}">&nbsp;</td>
        </tr>
        <tr align=left>
          <td>&nbsp;Email&nbsp;</td>
	  <td colspan=2>&nbsp;<input type=text name=email size=80 value="{+if isset($email)+}{+$email+}{+/if+}">&nbsp;</td>
        </tr>
        <tr align=left>
          <td>&nbsp;Rights Group&nbsp;</td>
	  <td colspan=2>
	    <select name=userLevelOptions>
              {+$userLevelOptions+}
	    </select>
          </td>
        </tr>

        <tr><td>&nbsp;</td></tr>

        <tr align=left>
          <td>&nbsp;Reset Password&nbsp;</td>
	  <td colspan=2>&nbsp;
	    <input type=radio name=resetPassword id=resetPasswordYes value=YES><label for=resetPassword>Yes</label>
	    &nbsp;&nbsp; 
	    <input type=radio name=resetPassword id=resetPasswordNo  value=NO checked><label for=resetPassword>No</label> 
             &nbsp;
          </td>
        </tr>
        <tr align=left>
          <td>&nbsp;Status&nbsp;</td>
	  <td colspan=2>
	    <select name=status>
              <option {+if isset($status) && $status == "active"   +}checked{+/if+} value=active>Active
              <option {+if isset($status) && $status == "admin"    +}checked{+/if+} value=admin>Admin
              <option {+if isset($status) && $status == "inactive" +}checked{+/if+} value=inactive>Inactive
	    </select>
          </td>
        </tr>
      </table>
      <br>
        <center>
          <input type=submit name=SaveBtn value=Save>
          &nbsp;&nbsp;&nbsp;&nbsp;
          <input type=button value="Clear" onClick="document.location='page.php?page=user';">
        </center>
      </form>
    </td>
  </tr>
  <tr><td>&nbsp;</td></tr>
</table>
<SCRIPT language="JavaScript"> 
  //document.fetchform.rptBarCode.focus(); 
  $('#rptBarCode').focus();
</SCRIPT>



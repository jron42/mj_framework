<br>
<center>
<h1>User Maintenance</h1>
</center>

<table width="80%" noborder>
<tr>
<td width=180>
  <form name=userSelectForm>
  <B>Current Users:</B> <input type=button value=New onClick="editNewUser();"><br>
  <select name=userSelectList id=userSelectList size="30" onChange="showUserRecord();" style="width:170px"
  {+$userList+}
  </select>
  </form>
</td>
<td valign=top>

  <div id=userDataDiv>
    <form name=userEditForm action="{+$controllerApp+}" target=RSIFrame onSubmit="return saveUserEdit();"> <!-- target=RSIFrame -->
      <input type=hidden name=path value="UserMaint.UserMaintText">
      <input type=hidden name=cmd  value="saveUserData">
      <input type=hidden name=subcmd  value="">
      <input type=hidden name=userMemberGroups value="">
      <input type=hidden name=uid     value="">
      <table noborder>
        <tr>
	  <td>
            <table id=userDataTbl style="display:none">
              <tr><td>ID:</td><td><label id=uidLabel></label></td></tr>
              {+if ($MJ_USER_FULLNAME == "0") +}
                <tr><td>First Name:</td><td><input type=text name=userFirstName id=userFullName size=64></td></tr>
                <tr><td>Last Name:</td><td><input type=text name=userLastName id=userFullName size=64></td></tr>
	        <input type=hidden name=userFullName id=userFullName value="">
	      {+else+}
                <tr><td>Name:</td><td><input type=text name=userFullName id=userFullName size=64></td></tr>
	        <input type=hidden name=userFirstName id=userFirstName value="">
	        <input type=hidden name=userLastName  id=userLastName  value="">
	      {+/if+}
	      <!-- MJ_VALIDATE_FIELD[{+$MJ_VALIDATE_FIELD+}] MJ_VALIDATE_FIELD_NAME[{+$MJ_VALIDATE_FIELD_NAME+}] MJ_VALIDATE_PROMPT[{+$MJ_VALIDATE_FIELD_PROMPT+}] -->
              {+if isset($MJ_VALIDATE_FIELD) && ($MJ_VALIDATE_FIELD != "") && ($MJ_VALIDATE_FIELD != "email") +}
                <tr><td>{+$MJ_VALIDATE_FIELD_PROMPT+}:</td><td><input type=text name="{+$MJ_VALIDATE_FIELD_NAME+}" id="{+$MJ_VALIDATE_FIELD_NAME+}" size=64></td></tr>
	      {+else+}
	        <input type=hidden name="{+$MJ_VALIDATE_FIELD_NAME+}" id="{+$MJ_VALIDATE_FIELD_NAME+}" value="">
	      {+/if+}
              <tr><td>Email:</td><td><input type=text name=userEmail id=userEmail size=64></td></tr>
              <tr><td>Status:</td><td><select name=userStatus id=userStatus ><option>inactive<option>active<option>admin</select></td></tr>
              <tr><td>Password:</td><td>
                  <input type=password name=userPassword id=userPassword size=24><font size=-2> (only on change)</font></td></tr>
            </table>

            <br><br>
            <table id=userGroupsDiv style="display:none">
              <tr><td>Member Groups</td><td width=100></td><td>Available Groups</td></tr>
              <tr>
                <td align=center><select name=userMemberGroupsSelect id=userMemberGroupsSelect size=10 style="width:130px"></select></td>
                <td width=80 align=center valign=middle>
                  <input type=button value="<----" onClick="addGroupToUser();"><br><br><input type=button value="---->" onClick="removeGroupFromUser();">
                </td>
                <td align=center><select name=availableGroupsSelect id=availableGroupsSelect size=10 style="width:130px">{+if isset($availableGroupsSelect) +}{+$availableGroupsSelect+}{+/if+}</select></td>
              </tr>
	      <tr><td colspan=3 align=center><input type=submit value=Save>&nbsp;&nbsp;&nbsp;&nbsp;<input type=button value=Cancel onClick=cancelUserEdit()></td></tr>
            </table>
          </td>
	  <td align=center id=rightsDetailTd style="display:none">
	    Rights Detail:<br>(info only)<br>
            <select name=rightsList id=rightsList size=22 style="width:130px;"></select>
          </td>
	</tr>
      </table>
    </form>
  </div>

</td>
</table>



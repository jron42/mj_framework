<table width="50%" valign="top" border="0" cellpadding="0" cellspacing="0" align="center">
  <tr>
    <td>
      <table border=0 width=100%>
        <td width=100>&nbsp;</td>
        <td><h1 id="mainhead" align="center">Attendance Add Screen</h1></td>
        <td width=100 align=right><font size=-1>Current user:<br>{+$username+}</font></td>
      </table>
    </td>
  </tr>
  <tr>
    <td align=left>
      <form name="add_attendance" action="submit.php" method="get">
        <table border=0 style="width: 600px;">
          <tr>
            <td><strong>Select User:</strong></td>
            <td>
              <select name="userId">
                {+foreach from=$userList item=theuser+}
                  <option value="{+$theuser.id+}">{+$theuser.handle+}</option>
                {+/foreach+}
              </select>
            </td>
          </tr>
          <tr>
            <td><strong>Enter Date:</strong></td>
            <td><input type="text" name="logDt" value="{+$logDt+}" size="12" maxlength="10" /></td>
          </tr>
          <tr>
            <td><strong>Log In:</strong></td>
            <td><input type="text" name="logInTm" id="logInTm" size="8" /></td>
          </tr>
          <tr>
            <td><strong>Log Out:</strong></td>
            <td><input type="text" name="logOutTm" id="logOutTm" size="8" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>
              <input type="hidden" name="action" value="insert">
              <input type="hidden" name="cmd" value="saveAttendance">
              <input type="hidden" name="refPage" value="add_attendance">
              <input type="submit" value="Save">
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>

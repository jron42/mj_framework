<table width="50%" valign="top" border="0" cellpadding="0" cellspacing="0" align="center">
  <tr>
    <td>
      <table border=0 width=100%>
        <td width=100>&nbsp;</td>
        <td><h1 id="mainhead" align="center">Attendance Timesheet</h1></td>
        <td width=100 align=right><font size=-1>Current user:<br>{+$username+}</font></td>
      </table>
    </td>
  </tr>
  <tr>
    <td align=left>
      <table border=0 style="width: 600px;">
      <form name="fetchform" action="page.php" method="get">
      <tr>
        <td><strong>Select User:</strong></td>
        <td>
          <select name="selectedUserList[]" size=10 multiple="multiple">
            {+foreach from=$userList item=user+}
              <option value="{+$user.id+}">{+$user.handle+}</option>
            {+/foreach+}
          </select>
        </td>
        <td><strong>Type:</strong> {+if isset($type) +}{+$type+}{+/if+}</td>
        <td>
          <input type="radio" name="type" id="HoursLogged" value="HoursLogged" {+if $type == 'HoursLogged'+}checked="true"{+/if+} />Hours Logged<br />
          <input type="radio" name="type" id="SignInOut" value="SignInOut" {+if $type == 'SignInOut'+}checked="true"{+/if+} />Sign In/Out
        </td>
      </tr>
      <tr>
        <td><strong>Start Date:</strong></td>
        <td><input name="startDate" type="text" value="{+$startDate+}" size="12" maxlength="10" /></td>
        <td><strong>End Date:</strong></td>
        <td><input name="endDate" type="text" value="{+$endDate+}" size="12" maxlength="10" /></td>
      </tr>
      <tr>
        <td colspan=2>&nbsp;</td>
        <td colspan=2>
          <input type="hidden" name="page" value="attendance_report">
          <input type="hidden" name="cmd" value="get_attendance_list">
          <input type="submit" value="Go">
        </td>
      </tr></form>
</table>
<br>
{+if isset($tableArray) +}
<table border="1" align="center" cellpadding="5" cellspacing="0" style="width: 600px;">
  <tr>
    {+foreach from=$dateArray item=cell+}
      <th align="center">{+$cell+}</th>
    {+/foreach+}
  </tr>
  {+foreach from=$tableArray item=row+}
    <tr class="drugs" valign="top">
      {+foreach from=$row item=cell+}
        <td>
          {+if $cell != '0.0'+}{+$cell+}
          {+else+}&nbsp;
          {+/if+}</td>
      {+/foreach+}
    </tr>
  {+/foreach+}
</table>
{+/if+}

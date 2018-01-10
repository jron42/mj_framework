<table width="50%" valign="top" border="0" cellpadding="0" cellspacing="0" align="center">
  <tr>
    <td>
      <table border=0 width=100%>
        <td width=100>&nbsp;</td>
        <td><h1 id="mainhead" align="center">Attendance Edit Screen</h1></td>
        <td width=100 align=right><font size=-1>Current user:<br>{+$username+}</font></td>
       
        
        
      </table>
    </td>
  </tr>
  <tr>
    <td align=left>
      <form name="search_attendance" action="page.php" method="get">
        <table border=0 style="width: 600px;">
          <tr>
            <td><strong>Select User:</strong></td>
            <td>
              <select name="userId">
                {+foreach from=$userList item=user+}
                  <option value="{+$user.id+}" {+if $user.id == $userId+}selected="true"{+/if+}>{+$user.handle+}</option>
                {+/foreach+}
              </select>
            </td>
          </tr>
          <tr>
            <td><strong>Enter Date:</strong></td>
            <td><input name="date" type="text" value="{+$logDt+}" size="12" maxlength="10" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>
              <input type="hidden" name="page" value="edit_attendance">
              <input type="hidden" name="cmd" value="search_attendance">
              <input type="submit" value="Search">
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
<br>
<table align=center border=0 style="width: 600px;">
  <tr>
    <td align=left>
      <table valign="top" border="2" cellpadding="0" cellspacing="0" style="width: 600px;">
        <tr style="background-color: #CCCCCC;">
          <th style="width: 150px;" align=center>Attendance ID</th>
          <th style="width: 150px;" align=center>Log Date</th>
          <th style="width: 150px;" align=center>Log In Time</th>
          <th style="width: 150px;" align=center>Log Out Time</th>
        </tr>
      </table>
      <div style="overflow:scroll; height:250px; width:620px;">
        <table valign="top" border="2" cellpadding="0" cellspacing="0" style="width: 600px;">
          {+foreach from=$attendanceList item=attendance+}
          <tr class="drugs" onClick="attendanceEdit('{+$attendance.attendanceId+}');">
            <td style="width: 152px;" align=center>
              <label id="attendanceId_{+$attendance.attendanceId+}"
               onClick="attendanceEdit('{+$attendance.attendanceId+}');">
               {+$attendance.attendanceId+}</label>
            </td>
            <td style="width: 150px;" align=center>{+$attendance.logDt+}</td>
            <td style="width: 150px;" align=center>{+$attendance.logInTm+}</td>
            <td style="width: 150px;" align=center>{+$attendance.logOutTm+}</td>
          </tr>
          {+/foreach+}
        </table>
      </div>
    </td>
  </tr>
</table>
<br>
<!--
*id  		int(4) 
*A_NDA_PLA 	varchar(24) 
*G_T 		varchar(6) 
*ingredient 	varchar(60)
*initial 	varchar(16)
*mailSybl 	varchar(100)
*lastMod 	varchar(32) 
*safetyEvaluator	varchar(44)
*drugClass 	varchar(200)
*sendOut 	char(1)
-->
<br>
<form name="edit_attendance" action="submit.php" method="get">
  <input type="hidden" name="cmd" value="saveAttendance">
  <input type="hidden" name="refPage" value="edit_attendance">
  <input type="hidden" name="userId" id="userId" size="4" />
  <input type="hidden" name="attendanceId" id="attendanceId" size="4" />
  <table align="center" valign="top" border="0" cellpadding="1" cellspacing="1	"> <!--style="width: 600px;" -->
    <tr>
      <td><strong>Handle:</strong></td>
      <td><input type="text" name="handle" id="handle" size=45 disabled=true /></td>
    </tr>
    <tr>
      <td><strong>Log Date:</strong></td>
      <td><input type="text" name="logDt" id="logDt" size=10 /></td>
    </tr>
    <tr>
      <td><strong>Log In:</strong></td>
      <td><input type="text" name="logInTm" id="logInTm" size=8 /></td>
    </tr>
    <tr>
      <td><strong>Log Out:</strong></td>
      <td><input type="text" name="logOutTm" id="logOutTm" size=8 /></td>
    </tr>
    <tr>
      <td><strong>Duration:</strong></td>  
      
      <td><input type="text" name="Duration" value="{+$theDuration+}" id="Duration" size=8 disabled=true/></td>
    </tr>
    <tr>
      <td><strong>Action:</strong></td>
      <td>
        <input type="radio" name="action" id="modify" value="modify" checked="checked" />Modify<br />
        <input type="radio" name="action" id="delete" value="delete" />Delete
      </td>
    </tr>
    <tr>
      <td colspan=2 align="center"><input type="submit" name="submit" value="Save"></td>
    </tr>
  </table>
</form>

<SCRIPT language="JavaScript"> 
  document.fetchform.drugSearch.focus(); 
  //$('#rptBarCode').focus();
</SCRIPT>

<!--
<table style="width: 300px" cellpadding="0" cellspacing="0">
<tr>
  <td>Column 1</td>
  <td>Column 2</td>
</tr>
</table>

<div style="overflow: auto;height: 100px; width: 320px;">
  <table style="width: 300px;" cellpadding="0" cellspacing="0">

        {+assign var='first' value=1 +}
          {+if $first == 1 +}
          {+/if+}
          {+assign var='first' value=0 +}
-->

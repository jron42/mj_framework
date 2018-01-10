<br><br>

<table width="90%" valign="top" border="0" cellpadding="3" cellspacing="0" align="center">
  {+if $onLogin != "1"+}
    <tr>
      <td>
        <table border=0 width=100%>
          <td width=100>&nbsp;</td>
          <td><h1 id="mainhead" align="center">You are now LOGGED IN.</h1></td>
          <td width=100 align=right>
            <font size=-1>Current user:<br>{+$username+}</font>
          </td>
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <table border=0 width=100%>
          <td width=100>&nbsp;</td>
          <td><h3 id="mainhead" align="center">User Daily Summary</h3></td>
          <td width=100 align=right>&nbsp;</td>
        </table>
      </td>
    </tr>
  {+else+}
    <tr>
      <td colspan="3" align="center"><br><br></td>
    <tr>
  {+/if+}
  <tr>
    <td colspan="3" align="center">Usage on {+$thedate+} for {+$username+}</td>
  <tr>
    <td colspan="0" align="center">
      <form name="myform" onSubmit="return false;">
      <table valign="top" border="2" cellpadding="0" cellspacing="0" align="center">
        <tr style="background-color: #CCCCCC;">
          <th>&nbsp;Login Time&nbsp;</th>
          <th>&nbsp;Logout Time&nbsp;</th>
          <th>&nbsp;Duration&nbsp;</th>
        </tr>
	{+foreach from=$summaryList item=summary+}
          <tr>
            <td align=right>{+$summary.logInTm+}&nbsp;</td>
	    {+if $summary.logOutTm != "" && $summary.logOutTm != "00:00:00" +}
              <td align=right>{+$summary.logOutTm+}&nbsp;</td>
              <td align=right>{+$summary.duration+}&nbsp;</td>
	    {+else+}
              <td align=right>&nbsp;</td>
              <td align=right>&nbsp;</td>
	    {+/if+}
          </tr>
        {+/foreach+}
        <tr style="background-color: #CCCCCC;">
          <td align=right colspan=2>Total:&nbsp;</td>
          <td align=right>{+$totalDuration+}&nbsp;</tda
        ></tr>
      </table>
      </form>
    </td>
  </tr>
  <tr><td>&nbsp;</td></tr>
</table>
{+if $onLogin != "1"+}
  <P>
  <form name="dateform" action="page.php" method="get">
    <input type=hidden name=page value=summary>
    Show a different date: <input type=text name=thedate size=11 value="{+$thedate+}">
    <input type=submit value=Go>
  </form>
{+/if+}

{+if $userStatus == "admin"+}
<br><br><br><br><!-- userStatus[{+$userStatus+}] -->
<center>
<font size=-1 color=grey>
<h2>Time Confimation</h2>
<table valign="top" border="0" cellpadding="3" cellspacing="0" align="center">
  <tr><td width=80>DB Time</td><td width=70>{+$dbTime+}</td></tr>
  <tr><td>Server Time</td><td>{+$serverTime+}</td></tr>
  <tr>
    <td>Browser Time</td>
    <td>
      <script>
        var dt      = new Date(); 
        document.write(dt.toLocaleTimeString());

	var webtime = escape(dt.toLocaleTimeString());
	var dbtime  = escape('{+$dbTime+}');
	var srvtime = escape('{+$serverTime+}');
	var url     = "login.php?cmd=log_times&dbtime="+dbtime+"&srvtime="+srvtime+"&webtime="+webtime;
	if (!top.frames['RSIFrame']) alert('error finding RSIFrame');
	top.frames['RSIFrame'].location.href = url;
      </script>
    </td>
  </tr>
</table>
<br>
All times should be within a few seconds of each other.
<br>
These values have been logged. 
</font>
</center>
{+/if+}


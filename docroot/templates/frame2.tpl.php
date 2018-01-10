<html>
<head>
{+if ($userId > 0 && isset($UI_SESSION_TIMEOUT) && $UI_SESSION_TIMEOUT > 0) +}
<META HTTP-EQUIV="refresh" CONTENT="{+$UI_SESSION_TIMEOUT+};URL=login.php?cmd=logout&errmsg=Logged+out+due+to+inactivity.">
{+/if+}

<!-- start frame_head -->
{+$frame_head+}
<!-- stop frame_head -->
</head>

<body marginheight="0" marginwidth="0" topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0" bgcolor="#FFFFFF">
<iframe id="RSIFrame"
  name="RSIFrame"
  style="width:0px; height:0px; border: 0px"
  src="blank.html">
</iframe>
<div id="msg_popup" style="display:none">
  <input type="image" src="img/red_x_16x16.png" class="bClose" value="X" alt="Close popup">
  <h2 id="msg_popup_title"></h2>
  <center><label id="msg_popup_msg"></label></center>
</div>

<!-- start frame_banner -->
{+$frame_banner+}
<!-- stop frame_banner -->

<!-- End Top Logo Bar -->
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"> <!-- style="margin-top:-10"> -->
  <tr>

<!-- start frame_leftMenu -->
{+$frame_leftMenu+}
<!-- stop frame_leftMenu -->

    <td width="10">&nbsp;</td>
    <td width="*" valign="top">
      <!-- START CONTENT AREA -->
      <div id="contentBlock" style="position:relative;height:100%;width:100%;">
	{+$content+}
      </div>
      <!-- END CONTENT AREA -->
    </td>
  </tr>
</table>

<!-- start frame_footer -->
{+$frame_footer+}
<!-- stop frame_footer -->

</body>
</html>

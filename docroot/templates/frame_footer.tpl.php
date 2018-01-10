
<!-- start frame_footer.tpl.php -->
<div id="dialog" title="Basic dialog">
  <!-- <p>This is the default dialog which is useful for displaying information.</p> -->
</div>

{+if isset($footerScript) +}{+$footerScript+}{+/if+}

<script type="text/javascript">
  //$("#logoBar").offset().top
  var msg = "{+if isset($errmsg) +}{+$errmsg+}{+/if+}";
  if (msg == "") msg = "{+if isset($successMsg) +}{+$successMsg+}{+/if+}";
  if (msg != "") mj_alert("NOTICE:",msg);
</script>

{+if isset($rawScript) +}{+$rawScript+}{+/if+}

<!-- end frame_footer.tpl.php -->


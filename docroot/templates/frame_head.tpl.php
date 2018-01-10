<!-- <head>  -->

{+if 0+}
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
{+else+}
<link href="jslib/css/ui-lightness/jquery-ui-1.8.5.custom.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" language="javascript" src="jslib/jquery-1.4.2.js"></script>
<script type="text/javascript" language="javascript" src="jslib/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript" language="javascript" src="jslib/jqModal.js"></script>
<script type="text/javascript" language="javascript" src="jslib/excanvas.min.js"></script>
<script type="text/javascript" language="javascript" src="jslib/qtip/jquery.qtip.js"></script>
<script type="text/javascript" language="javascript" src="jslib/jquery.bpopup-0.7.0.min.js"></script>
{+/if+}
{+if 0+}
<script type="text/javascript" language="javascript" src="jslib/jquery.flot.pie.min.js"></script>
<script type="text/javascript" language="javascript" src="jslib/jquery.flot.time.js"></script>
{+/if+}

<script type="text/javascript" language="javascript" src="jslib/mj_lib.js"></script>
<script type="text/javascript" language="javascript" src="jslib/sprintf.js"></script>

<!-- start headJsFiles -->
{+if is_array($headJsFiles) +}
  {+if count($headJsFiles) > 0 +}
    {+foreach from=$headJsFiles item=fname+}
      {+if (trim($fname) != "")+}
        <script type="text/javascript" language="javascript" src="{+$fname+}"></script>
      {+/if+}
    {+/foreach+}
  {+/if+}
{+elseif $headJsFiles != ""+}
    <script type="text/javascript" language="javascript" src="{+$headJsFiles+}"></script>
{+/if+}
<!-- stop headJsFiles -->

<!-- start headCssFiles -->
{+if is_array($headCssFiles) +}
  {+if count($headCssFiles) > 0 +}
    {+foreach from=$headCssFiles item=fname+}
      {+if (trim($fname) != "")+}
        <link href="{+$fname+}" rel="stylesheet" type="text/css"/>
      {+/if+}
    {+/foreach+}
  {+/if+}
{+elseif $headCssFiles != ""+}
    <link href="{+$headCssFiles+}" rel="stylesheet" type="text/css"/>
{+/if+}
<!-- stop headCssFiles -->

<!-- start headScript -->
{+if isset($headScript) +}{+$headScript+}{+/if+}
<!-- stop headScript -->

<script type="text/javascript">
  $(document).ready(function(){
    //if (window.init_page) init_page();
    //alert('Document Ready!');
    //---------- start readyScript ------------
    {+if isset($readyScript) +}{+$readyScript+}{+/if+}
    //---------- stop readyScript ------------
    //---------- start globalReadyScript ------------
    {+if isset($globalReadyScript) +}{+$globalReadyScript+}{+/if+}
    //---------- stop globalReadyScript ------------
  });
</script>

<title>{+$pageTitle+}</title>

<!-- </head>  -->

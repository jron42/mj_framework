<!-- file: form/field_text.tpl.php -->
<div{+if isset($id) +} id="{+$id+}_field_div"{+/if+}>
{+if isset($prompt) && trim($prompt) != '' +}
  {+if $promptAsLabel == 'true' +}
    <label{+if isset($id) +} id="{+$id+}_field_label"{+/if+}{+if isset($id) +} for="{+$id+}_field"{+/if+} value="{+$prompt+}">
  {+else+}
    {+$prompt+}
  {+/if+}
{+/if+}
{+if $type == "text" || $type == "int" || $type == "float"+}
  <input type=text {+if isset($id) +} id="{+$id+}_field"{+/if+} name="{+$name+}"
         {+if isset($classes) && trim($classes) != ''+} class="{+$classes+}"{+/if+}
         {+if isset($styles) && trim($styles) != '' +} styles="{+$styles+}"{+/if+}
         {+if isset($value) +} value="{+$value+}"{+/if+}
         {+if isset($extra) +} {+$extra+}{+/if+}
  >
{+/if+}
</div>


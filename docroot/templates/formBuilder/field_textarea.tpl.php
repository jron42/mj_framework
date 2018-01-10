<!-- file: form/field_text.tpl.php -->
<div{+if isset($id) +} id="{+$id+}_field_div"{+/if+}>
{+if isset($prompt) && trim($prompt) != '' +}
  {+if $promptAsLabel == 'true' +}
    <label{+if isset($id) +} id="{+$id+}_field_label"{+/if+}{+if isset($id) +} for="{+$id+}_field"{+/if+} value="{+$prompt+}">
  {+else+}
    {+$prompt+}
  {+/if+}
{+/if+}
{+if $type == "textarea" +}
  <textarea {+if isset($id) && trim($id) != '' +} id="{+$id+}"{+/if+} name="{+$name+}"
         {+if isset($classes) && trim($classes) != '' +} class="{+$classes+}"{+/if+}
         {+if isset($styles) && trim($size) != '' +} style="{+$styles+}"{+/if+}
         {+if isset($extra) +} {+$extra+}{+/if+}>{+if isset($value) +}{+$value+}{+/if+}
  </textarea>
{+/if+}
</div>


<!-- file: form/field_radio.tpl.php -->
<div{+if isset($id) +} id="{+$id+}_field_div"{+/if+}>
{+if isset($promptAfter) && trim($promptAfter) != 'true' +}
{+if isset($prompt) && trim($prompt) != '' +}
  {+if isset($promptAsLabel) && $promptAsLabel == 'true' +}
    <label{+if isset($id) +} id="{+$id+}_field_label"{+/if+} for="{+$id+}_field" value="{+$prompt+}">
  {+else+}
    {+$prompt+}
  {+/if+}
{+/if+}
{+/if+}
<input type="radio" name="{+$name+}" {+if isset($id) +} id="{+$id+}_field"{+/if+}
       {+if isset($classes) && trim($classes) != '' +} class="{+$classes+}"{+/if+}
       {+if isset($styles) && trim($styles) != '' +} style="{+$styles+}"{+/if+}
       {+if isset($value) +} value="{+$value+}"{+/if+}
       {+if isset($checked) +}{+$checked+}{+/if+}
> 
{+if isset($promptAfter) && trim($promptAfter) == 'true' +}
{+if isset($prompt) && trim($prompt) != '' +}
  {+if isset($promptAsLabel) && $promptAsLabel == 'true' +}
    <label{+if isset($id) +} id="{+$id+}_field_label"{+/if+} for="{+$id+}_field" value="{+$prompt+}">
  {+else+}
    {+$prompt+}
  {+/if+}
{+/if+}
{+/if+}
</div>


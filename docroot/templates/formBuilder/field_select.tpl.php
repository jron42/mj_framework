<!-- file: form/field_select.tpl.php -->
<div{+if isset($id) +} id="{+$id+}_field_div"{+/if+}>
{+if isset($prompt) && trim($prompt) != '' +}
  {+if isset($promptAsLabel) && $promptAsLabel == 'true' +}
    <label{+if isset($id) +} id="{+$id+}_field_label"{+/if+} for="{+$id+}_field" value="{+$prompt+}">
  {+else+}
    {+$prompt+}
  {+/if+}
{+/if+}
<select {+if isset($id) && trim($id) != '' +} id="{+$id+}_field"{+/if+} name={+$name+}
        {+if isset($size) && trim($size) != ''+} size="{+$size+}"{+/if+}
        {+if isset($classes) && trim($classes) != '' +} class="{+$classes+}"{+/if+}
        {+if isset($styles) && trim($styles) != '' +} style="{+$styles+}"{+/if+}
        {+if isset($extra) +} {+$extra+}{+/if+}
>
  {+if isset($options) +}
    {+if is_array($options) +}
      {+foreach from=$options key=key item=value +}<option vlaue="{+$value+}">{+$key+}</option>{+/foreach+}
    {+else+}
      {+$options+}
    {+/if+}
  {+/if+}
</select>
{+if  isset($id) && trim($id) != '' && isset($value) && trim($value) != '' +} 
  <script>top.mjselect("{+$id+}_field","{+$value+}");</script>
{+/if+}
</div>


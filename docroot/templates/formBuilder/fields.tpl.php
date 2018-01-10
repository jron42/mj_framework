<!-- file: formBuilder/field_text.tpl.php -->
{+if 0 +}
<!-- eg: <input type=text id=foo name=foo class="" style="" size="" value=""> -->
{+if !isset($foo) +}{+assign var="foo" value=""+}{+/if+}
{+/if+}
{+if (!isset($id) && trim($id) == '') && isset($name)+}{+assign var="id" value=$name+}{+/if+}

<div{+if isset($id) +} id="{+$id+}_field_div"{+/if+}>
{+if isset($label) && trim($label) != '' +}
  {+if $promptAsLabel == 'true' +}<label{+if isset($id) +} id="{+$id+}_field_label"{+/if+} for="{+$id+}_field" value="{+$label+}">{+else+}{+$label+}{+/if+}
{+/if+}
{+if $type == "text" || $type == "int" || $type == "float"+}
  <input type=text {+if isset($id) +} id="{+$id+}_field"{+/if+}
         {+if isset($classes) && trim($classes) != ''+} class="{+$classes+}"{+/if+}
         {+if isset($styles) && trim($styles) != '' +} styles="{+$styles+}"{+/if+}
         {+if isset($value) +} value="{+$value+}"{+/if+}
         {+if isset($extra) +} {+$extra+}{+/if+}>
{+elseif $type == "select"+}
  <select {+if isset($id) && trim($id) != '' +} id="{+$id+}_field"{+/if+}
         {+if isset($size) && trim($size) != ''+} size="{+$size+}"{+/if+}
         {+if isset($classes) && trim($classes) != '' +} class="{+$classes+}"{+/if+}
         {+if isset($styles) && trim($styles) != '' +} style="{+$styles+}"{+/if+}
         {+if isset($extra) +} {+$extra+}{+/if+}>
    {+if isset($options) +}
      {+if is_array($options) +}
        {+foreach from=$options key=prompt item=value +}<option vlaue="{+$value+}">{+$prompt+}</option>{+/foreach+}
      {+else+}
        {+$options+}
      {+/if+}
    {+/if+}
  </select>
  {+if  isset($id) && trim($id) != '' && isset($value) && trim($value) != '' +} <script>top.mjselect("{+$id+}_field","{+$value+}");</script>{+/if+}
{+elseif $type == "textarea"+}
  <textarea {+if isset($id) && trim($id) != '' +} id="{+$id+}"{+/if+}
         {+if isset($classes) && trim($classes) != '' +} class="{+$classes+}"{+/if+}
         {+if isset($styles) && trim($size) != '' +} style="{+$styles+}"{+/if+}
         {+if isset($extra) +} {+$extra+}{+/if+}>{+if isset($value) +}{+$value+}{+/if+}</textarea>
{+/if+}
</div>


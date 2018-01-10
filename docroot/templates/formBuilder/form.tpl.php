<!-- file: formBuilder/form.tpl.php -->

<div{+if isset($id) && trim($id) != '' +} id="{+$id+}_form_div"{+/if+}{+if isset($classes) && trim($classes) != '' +} class="{+$classes+}"{+/if+}{+if isset($styles) && trim($styles) != '' +} styles="{+$styles+}"{+/if+}>
<form 
  {+if isset($id) && trim($id) != '' +} id="{+$id+}"{+/if+}
  {+if isset($name) && trim($name) != '' +} name="{+$name+}"{+/if+}
  {+if isset($action) && trim($action) != '' +} action="{+$action+}"{+/if+}
  {+if isset($method) && trim($method) != '' +} method="{+$method+}"{+/if+}
  {+if isset($onSubmit) && trim($onSubmit) != '' +} onSubmit="{+$onSubmit+}"{+/if+}
  {+if isset($target) && trim($target) != '' +} target="{+$target+}"{+/if+}
>
{+if isset($content) && trim($content) != '' +}{+$content+}{+/if+}
</form>
</div>



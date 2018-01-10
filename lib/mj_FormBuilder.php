<?php

/*

# supported field types: 

button
submit
text
password
reset
radio
checkbox
image

# config structure:  see example config in test.mj_FormBuilder.conf.php

*/


class mj_FormBuilder
{
  protected $displayGroups = array(); // structure = array('group1'=>array('id1','id2'),'group2'=>array('id4','idn'))

  protected $structure = "table"; // values: table or div
 
  static protected $defaultTopTpl   = array('template' =>'single_column', 'defaultSlot' => 'content');
  static protected $defaultFormTpl  = array('template' =>'form',          'defaultSlot' => 'content');
  static protected $defaultFieldTempates = array(
    'text'         => 'field_text',
    'textarea'     => 'field_textarea',
    'select'       => 'field_select',
    'selection'    => 'field_select',
    'hidden'       => 'field_hidden',
    'button'       => 'field_button',
    'input_button' => 'field_input_button',
    'reset'        => 'field_reset',
    'radio'        => 'field_radio',
    'boolean'      => 'field_checkbox',
    'checkbox'     => 'field_checkbox',
    'image'        => 'field_image',
    'label'        => 'field_label',
    'markup'       => 'field_markup',
  );

  function __constructor($structure="table")
  {
    $this->structure = $structure;
  }

  /**
   * collect any display group memberships for later processing
   */
  public function addToDisplayGroups($id,$groups)
  {
    if (!is_array($groups))
    {
      if (trim($groups) == '') return;
      else                     $groups = array($groups);
    }
    foreach ($groups as $group)
    {
      if (!isset($this->displayGroups[$group]))             $this->displayGroups[$group] = array($group);
      else if (!in_array($id,$this->displayGroups[$group])) $this->displayGroups[$group][] = $id;
    }
  }

  protected function assign($tpl,$cfg,$value='')
  {
    foreach ($cfg as $name => $val)
    {
      mjlog(DEBUG,__METHOD__,"fieldName[".$cfg['name']."] - name[$name] val[$val]",__LINE__);
      $tpl->assign($name,$val);
    }
    $tpl->assign('value',$value);
  } 

  /** ********************************************************************************
   * generates the UI for a single form field
   */
  function generateField($cfg,$data=array(),$parentTpl=false)
  {
    mjlog(DEBUG,__METHOD__,"field[".$cfg['name']."] - cfg: ". print_r($cfg,true),__LINE__);
    $smarty = new mj_Smarty;
    if (defined('MJ_SITE_NAME')) $smarty->assign('siteName',MJ_SITE_NAME);
    $smarty->assign('context',mj_Config::getContext());

    if (!isset($cfg['id']) || trim($cfg['id']) == '') 
      $cfg['id'] = $cfg['name'];

    // we are dealing with a single field, get its value from the incoming data array (which holds multiple fields)
    $value = isset($data[$cfg['name']]) ? $data[$cfg['name']] : '';
    // collect any display group memberships for later processing
    if (isset($cfg['displayGroups']))
      $this->addToDisplayGroups($cfg['id'],$cfg['displayGroups']);

    // assign all the cfg values as well as the actual value for this field
    $this->assign($smarty,$cfg,$value);

    // have to deal with radios specially as they are actually grouped
    if ($cfg['type'] == 'radio')
    {
      $rez = array();
      if (isset($cfg['radios'])) 
      {
        mjlog(DEBUG,__METHOD__,"field[".$cfg['name']."] is type radio - radios[".$cfg['radios']."]",__LINE__); 
        $values = explode('|',$cfg['radios']);
        mjlog(DEBUG,__METHOD__,"radio values: ". print_r($values,true),__LINE__);
        foreach ($values as $val)
        {
          $key = $val;
          if (strpos($val,'=>') !== false)
          {
            list($key,$val) = explode('=>',$val);
          }
          $rez[$key] = $val;
        }
      }
      mjlog(DEBUG,__METHOD__,"radio rez: ". print_r($rez,true),__LINE__);
      $smarty->assign('radios',$rez);
    }

    $tplname = ((isset($cfg['template']) && $cfg['template'] != '') ? $cfg['template'] : self::$defaultFieldTempates[strtolower($cfg['type'])]).'.tpl.php';
    mjlog(DEBUG,__METHOD__,"name[".$cfg['name']."] type[".$cfg['type']."] cfg['template']=[".(isset($cfg['template']) ? $cfg['template'] : '') ."] template[$tplname]",__LINE__); 
    
    $html = $smarty->fetch($tplname);
    mjlog(DEBUG,__METHOD__,"finshed field name[".$cfg['name']."]",__LINE__); 
    return $html;
  }

  /** ********************************************************************************
   * generates the UI for a single form
   * - not that there are optional fieldsets between the form and the fields. 
   */
  function generateForm($cfg,$data,$parentTpl=false)
  {
    $smarty = new mj_Smarty;
    if (defined('MJ_SITE_NAME')) $smarty->assign('siteName',MJ_SITE_NAME);
    $smarty->assign('context',mj_Config::getContext());

    $fieldHtml = "";

    if ((!isset($cfg['id']) || trim($cfg['id']) == '') && (isset($cfg['name']) || trim($cfg['name']) != ''))
      $cfg['id'] = $cfg['name'];

    // set the basic values associated directly to the form
    foreach ($cfg as $name => $value)
    {
      if (!in_array($name,array('fieldsets')))
        $smarty->assign($name,$cfg[$name]);
    }
    // loop thru the fieldets and outputcall the field outputs
    $tmphtml = '';
    if (isset($cfg['fieldsets']))
    {
      mjlog(DEBUG,__METHOD__,"fieldsets found",__LINE__); 
      foreach ($cfg['fieldsets'] as $formcfg)
      {
        mjlog(DEBUG,__METHOD__,"processing fieldset name[".$formcfg['name']."]",__LINE__); 
        // set the basic values associated directly to the fieldset
        foreach ($formcfg as $name => $value)
        {
          if ($name != 'fields')
            $smarty->assign($name,$formcfg[$name]);
        }
        if (isset($formcfg['fields']))
        {
          foreach ($formcfg['fields'] as $fieldcfg)
          {
            $fieldHtml .= $this->generateField($fieldcfg,$data[$fieldcfg['name']]);
          }
        }
      }
    }
    else // no fieldsets
    {
      mjlog(DEBUG,__METHOD__,"NO fieldsets found - processing fields",__LINE__); 
      if (isset($cfg['fields']))
      {
        foreach ($cfg['fields'] as $fieldcfg)
        {
          $value = isset($data[$fieldcfg['name']]) ? $data[$fieldcfg['name']] : "";
          mjlog(DEBUG,__METHOD__,"calling this->generateField on field: name[".$fieldcfg['name']."] value[$value]",__LINE__); 
          $fieldHtml .= $this->generateField($fieldcfg,$value);
        }
      }
    }
    mjlog(DEBUG,__METHOD__,"finished processing fieldsets and fields",__LINE__); 

    $smarty->assign(self::$defaultFormTpl['defaultSlot'],$fieldHtml);
    $html = $smarty->fetch(((isset($cfg['template']) && $cfg['template'] != '') ? $cfg['template'] : self::$defaultFormTpl['template']).'.tpl.php');

    if (isset($parentTpl) && $parentTpl !== false && $parentTpl !== '' && isset($cfg["parentSlot"]) && $cfg["parentSlot"] !== false && $cfg["parentSlot"] != '') 
      $parentTplassign($cfg["parentSlot"],$html);

    return $html;
  }

  /**
   * generates the UI for a given configuration form group(page)
   */
/*
  function generate($cfg,$data)
  {
    $topSlots = array( "structure", "id", "classes", "styles" );

    $tpl = new mj_Smarty;
    if (defined('MJ_SITE_NAME')) $tpl->assign('siteName',MJ_SITE_NAME);
    $tpl->assign('context',mj_Config::getContext());

    foreach ($topSlots as $slotName)
      $tpl->assign($slotName,$cfg[$slotName]);

    $tmphtml = '';
    foreach ($cfg['forms'] as $formcfg)
    {
      $this->generateForm($formcfg,$data[$formcfg['name']],$tpl)
    }
    $tplname = ((isset($cfg['template']) && $cfg['template'] != '') ? $cfg['template'] : self::$defaultFieldTpl).'.tpl.php';
    $html = $smarty->fetch($tplname);
    return $html;
  }
*/
  /*
   * execute the template construction and return the resuults. Parameters are the same as from the parent function.
   * This function was overridden so that context could be pushed to all templates.
  public function build(
    $template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
  {
    if (defined('MJ_SITE_NAME')) $this->assign('siteName',MJ_SITE_NAME);
    $this->assign('context',mj_Config::getContext());

    return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
  }
   */

}


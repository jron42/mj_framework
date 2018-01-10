<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_UserGeneric.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

//require_once("lib/mj_UserBase.php");
require_once("mj_UserBase.php");

class mj_User extends mj_UserBase
{
  function __construct($id=0)
  {
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"constructing mj_UserBase = mj_UserGeneric.php");
    parent::__construct($id);
  }

  //////////////////////////////////////////////////////////////////////////////
  // Functions to write user data to the database
  //
  protected function writeTable($table,$data)
  {
    if (count($data) == 0) return;

    $first = true;
    $sql = "update $table set ";
    foreach ($data as $name => $value)
    {
      $sql  .= ($first ? '' : ', '). mj_Db::safeStr($name) .' = '. mj_Db::safeStr($value,true); 
      $first = false;
    }
    if (!(self::$userDb->query($sql)))
      mjlog(CRIT,__CLASS__."::".__FUNCTION__,"---------- finished",__LINE__);
  }

  //////////////////////////////////////////////////////////////////////////////
  // get/set functions
  //

  public function __set($name, $value) 
  {
    if (!$this->data) $this->data = array();
    $this->data[$name] = $value;
  }

  public function __get($name) 
  {
    if (!$this->data) return '';
    if (is_array($this->data) && array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    //$trace = debug_backtrace();
    return '';
  }

  public function __isset($name) 
  {
    if (!$this->data) return false;
    return isset($this->data[$name]);
  }

  public function __unset($name) 
  {
    if (!$this->data) return;
    unset($this->data[$name]);
  }

  //////////////////////////////////////////////////////////////////////////////
  // debug functions
  
  function printData()
  {
    if ($this->data === NULL) echo "data is null\n";
    else echo "data is NOT null\n";

    if (is_array($this->data)) echo "data is array\n";
    else echo "data is NOT array\n";

    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,print_r($this->data,true),__LINE__);
  }
}




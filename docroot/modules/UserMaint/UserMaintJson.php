<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

mj_require_once("UserMaint.php");

class UserMaintJson extends mj_ResponseJson
{
  function __construct($moduleName) 
  {
    parent::__construct($moduleName);
  }

  /**
    * /my_application.php?path=UserMaint.UserMaintJson&cmd=getUserData&subcmd=getGroupsAndRights
    */
  function execute($path,$cmd,$subcmd,$errmsg)
  {
    parent::execute($path,$cmd,$subcmd,$errmsg); // you will want to call this in your subclass

    try
    {
      $cmd    = mjhtget("cmd","");
      $subcmd = mjhtget("subcmd","");

      $this->data['data'] = array();

      switch ($cmd)
      {
        case 'getUserData':        $this->getUserData($subcmd); break;
        case 'saveUserData':       $this->saveUserData();       break;
        case 'getUserTmpRights':   $this->getUserTmpRights();   break;
        case 'getAvailableGroups': $this->getAvailableGroups(); break;

	default: $this->data['errmsg'] = "Invalid request cmd[$cmd]"; break;
      }
    }
    catch (Exception $e) 
    {
      mjlog(ERROR,__CLASS__.':'.__FUNCTION__,"Exception caught: ".$e->getMessage(),__LINE__);
      $this->data['errmsg'] = "ERROR: " .$e->getMessage();
      $this->data['data']   = array();
    }
  }

  function getUserData($subcmd)
  {
    if (($uid = mjhtget("uid",0)) <= 0)
     throw new Exception("Invalid UID[".(int)$uid."]"); 

    $rez = array();
    $sql = "select id,fullName,handle,email,status from Users where id = $uid";
    if (($rez = mj_User::$userDb->getRowAssoc($sql)) === false)
     throw new Exception("User record not found for UID[".(int)$uid."]"); 

    $this->data['data'] = $rez;
    if ($subcmd == "getGroupsAndRights")
    {
      $this->data['data']['groups']    = mj_RightsGroups::getGroups_s($uid);
      $this->data['data']['allGroups'] = mj_RightsGroups::getGroups_s();
      $this->data['data']['rights']    = mj_UserRights::getAllPrivs_s($uid);
    }
  }

  function getUserTmpRights()
  {
    $groupStr = mjhtget("groups","");
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"groups[$groupStr]",__LINE__);
    $groups   = explode(",",$groupStr);
    $rights   = array();
    if (count($groups) > 0)
    {
      if (($rez = mj_RightsGroups::getPrivsForGroups($groups)) !== false)
        $rights = $rez;
    }
    $this->data['data']['rights'] = $rights;
  }

  function getAvailableGroups()
  {
    $this->data['data']['allGroups'] = mj_RightsGroups::getGroups_s();
  }

  function saveUserData()
  {
    if (($uid = mjhtget("uid",0)) <= 0)
     throw new Exception("Invalid UID[".(int)$uid."]"); 

    $rez = array();
    $this->data['data'] = $rez;
  }

  function getGroupPrivs()
  {
    alert("hello");
    mj_alert("HEllo", "Hello from Json getGroupPrivs");
    $rights = Array();
    $group = mjhtget("gid", "");
    if(($rez = mj_RightsGroups::getGroupPrivs_s($group)) != false)
       $rights = $rez;
    
    $this->data['data']['rights'] = $rights;
  }
}














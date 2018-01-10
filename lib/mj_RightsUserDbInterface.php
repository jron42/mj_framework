<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_RightsUserDbInterface.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

interface mj_RightsUserDbInterface
{
  public function __construct($userObj,$db);

  public static function isAllowed_s($userId, $privName,$action='none',$errmsg='Access Denied. Please request access from your manager.');
  public        function isAllowed(  $privName,         $action='none',$errmsg='Access Denied. Please request access from your manager.');

  public function getGroups();

  /**
   * Return an array of all the priv strings for the given user.
   */
  public function getAllPrivs();
  public static function getAllPrivs_s($userId);
}

///////////////////////////////////////////////////////////////////////////////////////

class mj_UserRights implements mj_RightsUserDbInterface
{
  static protected $db      = null;
  protected        $userObj = null;
  protected        $privs   = null;
  protected        $groups  = null;

  public function __construct($userObj,$db)
  {
    $this->userObj     = $userObj;
    if ($db) self::$db = $db;
  }

  public static function setDb($db)
  {
    self::$db = $db;
  }

  protected function fetchPrivs()
  {
    if ($this->privs != null) return;

    //$sql = "select distinct lower(privKey) from UserPrivKeys where userId = ".$this->userObj->id;
    $sql = "select distinct lower(privKey) from UserGroupPrivRef privs, UserGroupRef ug where privs.groupId = ug.groupId and ug.userId = ".$this->userObj->id;
    if ($this->userObj->status == "admin") 
      $sql = "select distinct lower(privKey) from UserPrivs"; //  admin gets ALL of them.
    
    $this->privs = self::$db->fetchRowsAsSimpleArray($sql);
  }

  /**
   * Return a boolean t/f if the user is allowed access to this priv.
   * when access is denied "actions" defines additional logic
   *   none     - do nothing, simply return true or false from the function
   *   redirect - redirect to another page and show an access denied message or the given errmsg if provided
   * 
   **/
  public function isAllowed($privName,$action='none',$errmsg='Access Denied. Please request access from your manager.') 
  {
    //mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"privName[$privName] ". mj_trace(3),__LINE__);
    if ((defined('MJ_USE_DB') && MJ_USE_DB != 1)  // there is no database to check so just allow everything.
     || ($this->userObj->data['status'] == "admin"))
    {
       mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"status == admin || !MJ_USE_DBi so return true",__LINE__);
       return true;
    }
    if ($this->privs === null) $this->fetchPrivs();

    $allowed = in_array(strtolower($privName),$this->privs);
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"privName[$privName] allowed[". ($allowed ? "true" : "false") ."]",__LINE__);
    if (!$allowed)
    {
      mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"this->privs: ". print_r($this->privs,true),__LINE__);

      switch(strtoupper($action))
      {
	case 'REDIRECT': mj_redirect(MJ_ERROR_PAGE,$errmsg); exit;

        case 'NONE':
	default:      break;
      }
    }
    return $allowed;
  }

  /**
   * This is a static version to be called on alternate users. It looses almost all of the efficiencies of the instance methods.
   **/
  public static function isAllowed_s($userId, $privName, $action='none',$errmsg='Access Denied. Please request access from your manager.')
  {
    if (defined('MJ_USE_DB') && MJ_USE_DB != 1) return 1; // there is no database to check so just allow everything.

    //$sql = "select distinct lower(privKey) from UserPrivKeys where userId = ".$userId;
    $sql = "select distinct lower(privKey) from UserGroupPrivRef privs, UserGroupRef ug where privs.groupId = ug.groupId and ug.userId = ".$userId;
    $privs = mj_User::$userDb->fetchRowsAsSimpleArray($sql);
    $allowed = in_array($privName,$privs);

    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"privName[$privName] allowed[". ($allowed ? "true" : "false") ."]",__LINE__);
    if (!$allowed)
    {
      mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"this->privs: ". print_r($this->privs,true),__LINE__);

      switch(strtoupper($action))
      {
        case 'REDIRECT': mj_redirect(MJ_ERROR_PAGE,$errmsg); exit;

        case 'NONE':
        default:      break;
      }
    }
    return $allowed;
  }
  
  /**
   * Return an array of all the priv strings for the current user.
   */
  public function getAllPrivs()
  {
    if (defined('MJ_USE_DB') && MJ_USE_DB != 1) return 1; // there is no database to check so just allow everything.
    
    if ($this->privs === null) $this->fetchPrivs();

    return ($this->privs);
  }

  /**
   * Return an array of all the priv strings for the given user.
   */
  public static function getAllPrivs_s($userId)
  {
    if (defined('MJ_USE_DB') && MJ_USE_DB != 1) return array(); // there is no database to check so just allow everything.
    if (!($userId > 0)) return false;
    
    //$sql = "select privKey from UserPrivKeys where userId = ".(int)$userId;
    $sql = "select distinct privKey from UserGroupPrivRef privs, UserGroupRef ug where privs.groupId = ug.groupId and ug.userId = ". (int)$userId;
    //if ($this->userObj->status == "admin") 
    //  $sql = "select privKey from UserPrivs"; //  admin gets ALL of them.
    
    $privs = self::$db->fetchRowsAsSimpleArray($sql);
    return ($privs);
  }

  /**
   * Return the list of group records associated with the current user
   **/
  public function getGroups() 
  { 
    $sql = "select g.* from UserGroups g, UserGroupRef ugr where ugr.userId = ".$this->userObj->id ." and g.id = ugr.groupId";
    $groups = $this->db->fetchRowsAsAssocArray($sql);
    return $groups;
  }
}

mj_UserRights::setDb(mj_DbPool::getDb("user"));















<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_UserBase.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

define('MJ_UD_BASE',          0x1);
define('MJ_UD_SEARCHABLE',    0x2);
define('MJ_UD_NON_SEARCHABLE',0x4);
define('MJ_UD_TEXT',          0x8);
define('MJ_UD_ALL',           0xF);

define("MJ_DB_USER_KEY","1234"); //INSTALLATION_TYPE . DB_KEY . KEY_PHRASE);

mj_require_once('mj_RightsUserDbInterface.php');

class mj_UserBase
{
  //static const $tables = array('NULL');

  static public  $currUser   = NULL;
  static public  $userDb     = NULL;
  static private $inited     = false;
  static private $dbEncToken = "PackyWacky";

  public $id   = 0;
  public $data = NULL; // array containing the user record data

  protected $baseDataFetched = false;
  protected $userRights      = null;

  static protected $userTable       = 'Users';
  static protected $userTableFields = '*';

  ////////////////////////////////////////////////////////////////////////////////

  function __construct($id=0)
  {
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"id[".(int)$id."]",__LINE__);
    if ($id) $this->id = $id;

    if (self::$inited !== true) self::init();
    //$this->userRights = new mj_UserRights($this,self::$userDb);
  }

  static function init()
  {
    // This function sets up the User object with a database connection
    //
    if (self::$inited !== true) 
    {
      mjlog(DEBUG,__CLASS__.'::'.__FUNCTION__,'initializing user class');
      self::$inited = true;
      if (MJ_USE_DB == 1)
      {
        if ((self::$userDb = mj_DbPool::getDb("user")) === false)
        {
          mjlog(ERROR,__CLASS__.'::'.__FUNCTION__,'Failed to get Database connection');
  	  return false;
        }
      }
      mj_UserBase::$dbEncToken .= "785643123";
    }
    else
      mjlog(DEBUG,__CLASS__.'::'.__FUNCTION__,'user class already initialized');
    return true;
  }

  function appSpecificInit() { } // virtual function so child classes and init nicely. called from mj_init

  static protected function getFullNameFieldDbQueryString()
  {
    if (MJ_USER_FULLNAME)
      $sql = " fullname as name "; //, fullname ";
    else
      $sql = " CONCAT_WS(' ',firstName,lastName) as name "; // , CONCAT_WS(' ',firstName,lastName) as fullname ";
    return $sql;
  }

  ////////////////////////////////////////////////////////////////////////////////
  // User rights methods
  //
  public function isAllowed($privKey) { return $this->userRights->isAllowed($privKey); }
  public function getGroups()         { return $this->userRights->getGroups(); }
  public function getAllPrivs()       { return $this->userRights->getAllPrivs(); }

  static public function getAllUsersForPiv($privKey) 
  {
    $fullname = self::getFullNameFieldDbQueryString();

    $sql   = "select id, $fullname from ".mj_UserBase::$userTable." u, UserPrivKeys up "
           . " where u.id = up.userId and ".mj_Db::safeStr($privKey,true)." and u.status = 'active' "
           . "union distinct "
	   . "select id, $fullname from ".mj_UserBase::$userTable." where status = 'admin'";
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"sql[$sql]",__LINE__);
    
    $db = self::$userDb;
    return $db->fetchRowsAsAssocArray($sql);
  }
  
  ////////////////////////////////////////////////////////////////////////////////
  // User validation methods
  //

  function validateUser($email,$pass)
  {
    // this function is for validating the current, primary user
    //
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"user[$email] pass[$pass]",__LINE__);
    $field = (defined('MJ_VALIDATE_FIELD')) ? MJ_VALIDATE_FIELD : 'email';
    $sql   = "select id, status from ".mj_UserBase::$userTable." where $field = ".mj_Db::safeStr($email,true)
           . " and status in ('active','inactive','admin') "
           . "and password = ".mj_Db::encryptSql(trim($pass),mj_UserBase::$dbEncToken);
    $db    = self::$userDb;
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"sql[$sql]",__LINE__);

    global $MJ_NO_LOG;
    $MJ_NO_LOG = (defined(MJ_PASSWORD_LOGGING) && MJ_PASSWORD_LOGGING == 1) ? 0 : 1;  // we don't want to log passwords or seeds
    if (!($rez = $db->query($sql,__CLASS__.'::'.__FUNCTION__)))
    {
      $MJ_NO_LOG = 0;
      mjlog(ERROR,__CLASS__."::".__FUNCTION__,"LOGIN error for user[$email]",__LINE__);
      return false;
    }
    $MJ_NO_LOG = 0;
    if (!($row = $db->fetchRow($rez)))
    {
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"FAILED LOGIN fetch for user[$email]",__LINE__);
      return false;
    }
    $id = (int)$row[0];
    if ($id <= 0) 
    {
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"FAILED LOGIN for user[$email](1)",__LINE__);
      return false;
    }
    $status = $row[1];
    if ($status == 'inactive') 
    {
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"FAILED LOGIN for user[$email](2)",__LINE__);
      return false;
    }
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"LOGIN SUCCESS for user[$email]",__LINE__);
    $this->id = $id;
    $this->fetchBase(true,true);
    return $id;
  }

  static function validate($email,$pass)
  {
    // this function is for validating the current, primary user
    //
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"email[$email]",__LINE__);
    mj_UserBase::$currUser = new mj_User();
    if (($id = mj_UserBase::$currUser->validateUser($email,$pass)) === false || $id == 0)
      mj_UserBase::$currUser = NULL;
    return $id;
  }

  static function changePassword($oldpass, $newpass, $userId=0)
  {
    $field = (defined('MJ_VALIDATE_FIELD')) ? MJ_VALIDATE_FIELD : 'email';

    if ($userId == 0) $userId = self::$currUser->id;

    $db  = self::$userDb;
    $sql = "select $field from ".mj_UserBase::$userTable." where id = $userId and password = ".mj_Db::encryptSql($oldpass,mj_UserBase::$dbEncToken);
    if (!($rez = $db->query($sql)))    return false;
    if (!($row = $db->fetchRow($rez))) return false;
    if (strlen(trim($row[0])) < 4)     return false;

    $sql = "update ".mj_UserBase::$userTable." set password = ".mj_Db::encryptSql($newpass,mj_UserBase::$dbEncToken)." where id = $userId";
    if (!($rez = $db->query($sql)))    return false;

    return true;
  }

  static function setPassword($newpass, $userId=0)
  {
    $field = (defined('MJ_VALIDATE_FIELD')) ? MJ_VALIDATE_FIELD : 'email';

    if ($userId == 0) $userId = self::$currUser->id;

    $sql = "update ".mj_UserBase::$userTable." set password = ".mj_Db::encryptSql($newpass,mj_UserBase::$dbEncToken)." where id = $userId";
    if (!($rez = self::$userDb->query($sql)))    return false;

    return true;
  }

  static function resetPassword($userId=0)
  {
    $db  = self::$userDb;
    $newpass = "password";
    if ($userId == 0) $userId = self::$currUser->id;
    $field = (defined('MJ_VALIDATE_FIELD')) ? MJ_VALIDATE_FIELD : 'email';

    $sql = "update ".mj_UserBase::$userTable." set password = ".mj_Db::encryptSql($newpass,mj_UserBase::$dbEncToken)." where id = $userId";
    if (!($rez = $db->query($sql))) return false;
    return true;
  }

  function fetchBaseQuery()
  {
    $sql = 'select '. self::$userTableFields .' from '. self::$userTable .' where id ='. (int)$this->id;
    return $sql;
  }

  function fetchBase($buildData=true, $refresh=false)
  {
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"---------- buildData[".(int)$buildData."] refresh[".(int)$refresh."]",__LINE__);
    if ($this->baseDataFetched && !$refresh) return;

    $this->baseDataFetched = true;
    $sql = $this->fetchBaseQuery();
    if (!($rez = self::$userDb->fetchIntoArray($sql,$this->data)))
      mjlog(ERROR,__CLASS__."::".__FUNCTION__,'Failed to get User Record',__LINE__);

    // The rights code need to know the user status, if he is an admin or not.
    //
    $this->userRights = new mj_UserRights($this,self::$userDb);

    return $rez;
  }

  function fetch($fetch_mask=MJ_UD_ALL, $replace=false)
  {
    $this->fetchBase(true,true);
  }

  static function getUserFullName($userid, $table='Users')
  {
    //if (self::$currUser != NULL && $userid == self::$currUser->id && self::$_fullName != NULL)
    //  return self::$_fullName;
    if (MJ_USER_FULLNAME)
      $sql = "select fullname from $table where id = ".(Int)$userid;
    else
      $sql = "select CONCAT_WS(' ',firstName,lastName) as name from $table where id = $userid";
    $db  = self::$userDb;
    if (!($rez = $db->query($sql)))    return false;
    if (!($row = $db->fetchRow($rez))) return false;
    return $row[0];
  }

  ////////////////////////////////////////////////////////////////////////////////
  // some functions to help in new user registration
  //

  static function userAlreadyExists($handle, $uid=0)
  {
    $db  = self::$userDb;
    $sql = "select count(*) from ".mj_UserBase::$userTable." where ". MJ_VALIDATE_FIELD ." = ". mj_Db::safeStr($handle,true);
    if ($uid > 0) $sql .= " and id != ". (int)$uid;

    if (!($rez = $db->query($sql)))    return false;
    if (!($row = $db->fetchRow($rez))) return false;

    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"row[0]=[".(int)$row[0]."]exists[".(($row[0] > 0)?"true":"false")."]",__LINE__);
    return ($row[0] > 0);
  }

  // id 	fullName 	handle
  // password 	password_save 	email
  // status 	birthday 	age
  // sex 	timezone 	siteId 
  // joinDate 	lastAcces

  static function insertNewUser($data)
  {
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"data: ".print_r($data,true),__LINE__);
    /*
    $fields = array('handle','email','firstName','lastName','status','joinDate','lastAccess');
    foreach ($fields as $field)
      $data[$field] = $_POST[$field];
    */
    $db  = self::$userDb;
    $sql = "insert ".mj_UserBase::$userTable." (handle, email, fullName, status, joinDate, lastAccess) values ("
         . mj_Db::safeStr($data['handle'],true)   .", "
         . mj_Db::safeStr($data['email'],true)    .", "
         . mj_Db::safeStr($data['fullName'],true) .", "
         . mj_Db::safeStr($data['status'],true)   .", 
	   now(), now())";

    if (($rez = $db->query($sql)) === false) return false;
    if (($newId = $db->insertId()) > 0)
    {
      $pass = isset($data['password']) ? trim($data['password']) : "";
      if ($pass == "") $pass = 'password';
      self::setPassword($pass, $newId);
      return $newId;
    }
    return false;
  }

  static function updateUserData($data)
  {
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"data: ".print_r($data,true),__LINE__);

    if (!($data['id'] > 0)) return false;
    $db  = self::$userDb;
    $sql = "update ".mj_UserBase::$userTable." set "
         . "handle   = ". mj_Db::safeStr($data['handle'],true)   .", "
         . "email    = ". mj_Db::safeStr($data['email'],true)    .", "
         . "fullName = ". mj_Db::safeStr($data['fullName'],true) .", "
         . "status   = ". mj_Db::safeStr($data['status'],true) 
         . " where id = ". (int)$data['id']
	 ;

    if (($rez = $db->query($sql)) === false) return false;
    if (isset($data['password']))
    {
      $pass = trim($data['password']);
      if ($pass != "")
        self::setPassword($pass, $data['id']);
    }
    return true;
  }

/*
  function validateUser($email,$pass)
  {
    // this function is for validating the current, primary user
    //
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"user[$email] pass[$pass]",__LINE__);
    $field = (defined('MJ_VALIDATE_FIELD')) ? MJ_VALIDATE_FIELD : 'email';
    $sql   = "select id from ".mj_UserBase::$userTable." where $field = ".mj_Db::safeStr($email,true)." and status in ('active','admin') "
           . "and password = ".mj_Db::encryptSql(trim($pass),mj_UserBase::$dbEncToken);
    $db    = self::$userDb;
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"sql[$sql]",__LINE__);

    global $MJ_NO_LOG;
    $MJ_NO_LOG = (defined(MJ_PASSWORD_LOGGING) && MJ_PASSWORD_LOGGING == 1) ? 0 : 1;  // we don't want to log passwords or seeds
    if (!($rez = $db->query($sql,__CLASS__.'::'.__FUNCTION__)))
    {
      $MJ_NO_LOG = 0;
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"FAILED LOGIN query for user[$email]",__LINE__);
      return false;
    }
    $MJ_NO_LOG = 0;
    if (!($row = $db->fetchRow($rez)))
    {
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"FAILED LOGIN fetch for user[$email]",__LINE__);
      return false;
    }
    $id = (int)$row[0];
    if ($id <= 0)
    {
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"FAILED LOGIN id for user[$email]",__LINE__);
      return false;
    }
    else
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"LOGIN SUCCESS for user[$email]",__LINE__);
    $this->id = $id;
    $this->fetchBase(true,true);
    return $id;
  }
*/

  ////////////////////////////////////////////////////////////////////////////////
  // convenience functions
  //

  //calculate years of age (input string: YYYY-MM-DD)
  static function age($birthday)  // =NULL)
  {
    //if ($birthday === NULL) 
    //  $birthday = $this->data['birthday'];

    if     (strpos($birthday,'-') !== false) list($year,$month,$day) = explode("-",$birthday);
    elseif (strpos($birthday,'/') !== false) list($month,$day,$year) = explode("/",$birthday);
    else return false;

    $year_diff  = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff   = date("d") - $day;
    if ($day_diff < 0 || $month_diff < 0)
      $year_diff--;
    return $year_diff;
  }

  ////////////////////////////////////////////////////////////////////////////////
  static function encryptPasswordColumn()
  {
    $db = self::$userDb;
    $db->encryptColumn(mj_UserBase::$userTable,'password',mj_UserBase::$dbEncToken);
  }

  static function encryptSql($str)
  {
    return mj_Db::encryptSql($str,mj_UserBase::$dbEncToken);
  }

}




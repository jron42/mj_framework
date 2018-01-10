<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

mj_require_once("UserMaint.php");

class UserMaintText extends mj_ResponseText
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
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"path[$path] cmd[$cmd] subcmd[$subcmd] errmsg[$errmsg]",__LINE__);
    parent::execute($path,$cmd,$subcmd,$errmsg); // you will want to call this in your subclass

    try
    {
      //
      //************* do stuff !!!!
      //
      $this->data = "";

      switch ($cmd)
      {
        case 'saveUserData': $this->saveUserData(); break;

	default: $this->data = "Invalid request cmd[$cmd]"; break;
      }
    }
    catch (Exception $e) 
    {
      mjlog(ERROR,__CLASS__.':'.__FUNCTION__,"Exception caught: ".$e->getMessage(),__LINE__);
      $this->data['data'] = "";
    }
  }
/*
  function getUserData($subcmd)
  {
    if (($uid = mjhtget("uid",0)) <= 0)
     throw new Exception("Invalid UID[".(int)$uid."]"); 

    $rez = array();
    $sql = "select id,fullName,email,status from Users where id = $uid";
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

  public static function validEmail($string) 
  {
    //First of all check the datatype to confirm a string has been passed in
    if ( is_string( $string ) ) 
    {
        //Regular expression pattern.
        //Pattern breakdown:
            //** [a-zA-Z0-9_] - any character between a-z, A-Z or 0-9
            //** + - require one or more of the preceeding item.
            //** @{1} - Simply means 1 '@' symbol required.
            //** [a-zA-Z]+ - any character between a-z, A-Z (1 or more required).
            //** \.{1} - Single '.' required. Backslash escapes the '.'
            //** [a-zA-Z]+ - One or more of the these characters required.
        $pattern = "/^[a-zA-Z0-9\._]+@{1}[a-zA-Z]+\.{1}[a-zA-Z]+/";

        //If the pattern matches then return true, else email is invalid, return false.
        return ((preg_match($pattern, $string)) ? true : false);
    }
    return false;
  }
*/

  public static function validEmail($email, $skipDNS = true)
  {
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex)
    {
 	  $isValid = false;
    }
    else
    {
	  $domain = substr($email, $atIndex+1);
	  $local = substr($email, 0, $atIndex);
	  $localLen = strlen($local);
	  $domainLen = strlen($domain);
	  if ($localLen < 1 || $localLen > 64)
	  {
		 // local part length exceeded
		 $isValid = false;
	  }
	  else if ($domainLen < 1 || $domainLen > 255)
	  {
		 // domain part length exceeded
		 $isValid = false;
	  }
	  else if ($local[0] == '.' || $local[$localLen-1] == '.')
	  {
		 // local part starts or ends with '.'
		 $isValid = false;
	  }
	  else if (preg_match('/\\.\\./', $local))
	  {
		 // local part has two consecutive dots
		 $isValid = false;
	  }
	  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
	  {
		 // character not valid in domain part
		 $isValid = false;
	  }
	  else if (preg_match('/\\.\\./', $domain))
	  {
		 // domain part has two consecutive dots
		 $isValid = false;
	  }
	  else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
	  {
		 // character not valid in local part unless 
		 // local part is quoted
		 if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
		 {
			$isValid = false;
		 }
	  }

	  if(!$skipDNS)
	  {
		  if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
		  {
			 // domain not found in DNS
			 $isValid = false;
		  }
	  }
    }
    return $isValid;
  }
 
  public static function validText($string) 
  {
    //First of all check the datatype to confirm a string has been passed in
    if ( is_string( $string ) ) 
    {
      return true;
      $pattern = "/^[a-zA-Z ]+/";
      return ((preg_match($pattern, $string)) ? true : false);
    }
    return false;
  }

  public static function safehtget($field,$def,$validate=array())
  {
    $val = mj_Db::safeStr(trim(mjhtget($field,$def)));
    
    if ($validate["req"] && !(strlen($val) > 0))
      throw new Exception("ERROR: Invalid $field - required");
    
    if ($validate["minlen"] > 0 && strlen($val) < $validate["minlen"]) 
      throw new Exception("ERROR: Invalid $field - must be more than ". $validate["minlen"] ." characters");
    
    if ($validate["maxlen"] > 0 && strlen($val) > $validate["maxlen"]) 
      throw new Exception("ERROR: Invalid $field - must be less than ". $validate["maxlen"] ." characters");
    
    switch ($validate["type"])
    {
      case "email": if (self::validEmail($val) === false) throw new Exception("ERROR: Invalid $field - Invalid email address"); break;
      case "text":  if (self::validText($val)  === false) throw new Exception("ERROR: Invalid $field - Invalid $field"); break;
    }

    return $val;
  }

  function saveUserData()
  {
    $LINE = "";
    $subcmd = mjhtget("subcmd","");

    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"subcmd[$subcmd]",__LINE__);
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"REQUEST: ". print_r($_REQUEST,true),__LINE__);
    try
    {
      $uid       = mjhtget("uid",0); // ) <= 0) throw new Exception("Invalid User ID[".(int)$uid."]"); 
      $name      = self::safehtget("userFullName", "",array("req"=>true,"minlen"=>3,"maxlen"=>45,"type"=>"text"));
      $firstName = self::safehtget("userFirstName","",array("req"=>false,"minlen"=>0,"maxlen"=>45,"type"=>"text"));
      $lastName  = self::safehtget("userLastName", "",array("req"=>false,"minlen"=>0,"maxlen"=>45,"type"=>"text"));
      $email     = self::safehtget("userEmail",    "",array("req"=>true,"minlen"=>7,"maxlen"=>128,"type"=>"email"));
      $handle    = self::safehtget(MJ_VALIDATE_FIELD_NAME, "",array("req"=>true,"minlen"=>5,"maxlen"=>45,"type"=>"text"));
      $status    = self::safehtget("userStatus",   "",array("req"=>true,"minlen"=>5,"maxlen"=>8,"type"=>"text"));
      $password  = mjhtget("userPassword", "",array(0,16,"password"));
      $groupStr  = mjhtget("userMemberGroups","");
      $groups    = explode(',',$groupStr);

      // perform some validation

      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"-- 1",__LINE__);
      if (mj_User::userAlreadyExists($handle,$uid)) 
        { throw new Exception("Sorry, a user with that ".MJ_VALIDATE_FIELD." already exists."); $LINE = __LINE__; }
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"-- 2",__LINE__);

      $data = array(
        'id'       	  => $uid,
        'fullName' 	  => $name,
        'email'    	  => $email,
        MJ_VALIDATE_FIELD => $handle,
        'status'   	  => $status,
        'password' 	  => $password
      );
      mj_User::$userDb->beginTransaction();
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"-- 3",__LINE__);

      if ($uid > 0)
        mj_User::updateUserData($data);
      else
      {
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"-- 4",__LINE__);
        if (trim($password) == "") $password = 'password';
        if (strlen($password) < 5) { throw new Exception("Password must be between 5 and 16 characters long."); $LINE = __LINE__; }
        $uid = mj_User::insertNewUser($data);
      }
      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"-- 5",__LINE__);

      if ($uid < 1) { throw new Exception("Error creating User record. email[$email]"); $LINE = __LINE__; }

      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"-- 6",__LINE__);
      mj_RightsGroups::saveUserGroups_s($uid, $groups);
      mj_User::$userDb->commit();

      mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"-- 7",__LINE__);
      $this->data = "<script>top.reloadUserEditPage('SUCCESS: User record saved for $name'); </script>";
    }
    catch (Exception $e) 
    {
      mj_User::$userDb->rollback();
      mjlog(ERROR,__CLASS__.':'.__FUNCTION__,"Exception caught: ".$e->getMessage(),__LINE__);
      $this->data = "<script>top.mj_alert('ERROR: Problem saving user record.','".$e->getMessage()."');</script>";
    }
  }

}


/*
      $db = &mj_UserBase::$userDb;

      $data = array(
        'fullName' => array(Mj_Db::safeStr($name,true), '',   mj_Db::BOTH),
        'email'    => array(Mj_Db::safeStr($email,true), '',  mj_Db::BOTH),
        'handle'   => array(Mj_Db::safeStr("",true), '',      mj_Db::BOTH),
        'status'   => array(Mj_Db::safeStr($status,true), '', mj_Db::BOTH),
        'joinDate' => array("now()", '',                      mj_Db::INSERT),
      );
      $where = "where id = ". (int)uid;
 
      if ($uid > 0)
      {
        if ($db->update('Users',$data,$where,__CLASS__.'::'.__FUNCTION__.'('.__LINE__.')') === false)
	  { throw new Exception("Error saving User record. uid[".(int)$uid."]"); $LINE = __LINE__; }
      }
      else
      {
        if (($uid = $db->insert('Users',$data,true,__CLASS__.'::'.__FUNCTION__.'('.__LINE__.')')) === false)
	  { throw new Exception("Error creating User record. email[$email]"); $LINE = __LINE__; }
      }
*/












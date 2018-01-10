<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
<<<<<<< mj_RightsGroupDbInterface.php
 * $Revision: 1.2 $
 * $Id: mj_RightsGroupDbInterface.save.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
=======
 * $Revision: 1.2 $
 * $Id: mj_RightsGroupDbInterface.save.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
>>>>>>> 1.11
 ********************************************************************************/

interface mj_RightsGroupDbInterface
{
  public function __construct($groupName);

  public static function addPriv_s(   $userId, $privName);
  public static function removePriv_s($userId, $privName);

  public static function addUser_s(   $userId, $gid);
  public static function removeUser_s($userId, $gid);

  public static function getGroup_s($userId);
  public static function getGroupPrivs_s($gid);

  public function addPriv(   $privName);
  public function removePriv($privName);
  public function getPrivs();

  public        function saveUserGroups(  $groups);
  public static function saveUserGroups_s($userId, $groups);

  public static function getPrivsForGroups($groupIdArray);
  public static function saveGroupRights($groupId, $privKeys);//added by Lal Dika
  public Static function updateUserGroup($gid, $gDesc); //added by Lal Dika
  public static function saveUserGroup($gDesc); //added by Lal Dika
  public static function getGroupData($gid); //added by Lal Dika - for getting a group by gid
}

class mj_RightsGroups //implements mj_RightsGroupDbInterface
{
  static protected $db = null;

  public function __construct($groupName)
  {
  }

  static public function init_s($db)
  {
    self::$db = $db;
  }

  public static function addPriv_s($userId, $privName)
  {
  }

  public static function removePriv_s($userId, $privName)
  {
  }

  public static function addUser_s($userId, $gid)
  {
  }

  public static function removeUser_s($userId, $gid)
  {
  }

  public static function getGroupUsers_s($groupId=0, $type='simple') // types: simple, object
  {
    if ($groupId > 0)
    {
      $sql = "
        select u.id, u.fullname as name, u.email from Users u, UserGroupRef gf 
        where gf.UserId = u.id and gf.groupId = ". (int)$groupId ."
        order by u.fullname
      ";
    }
    $groups = self::$db->fetchRowsAsAssocArray($sql);
    return $groups;
  }

  public static function getGroups_s($userId=0)
  {
    if ($userId > 0)
      $sql = "select g.id, g.name from UserGroups g, UserGroupRef gf where gf.UserId = $userId and g.id = gf.groupId order by g.name";
    else
      $sql = "select id, name from UserGroups order by name";

    $groups = self::$db->fetchRowsAsAssocArray($sql);
    return $groups;
  }

  public static function getGroupsAsOptions_s()
  {
    $sql = "select id, name from UserGroups order by name";
    $groups = self::$db->fetchAsOptions($sql);
    return $groups;
  }

  //implemented by Lal Dika
  public static function getGroupPrivs_s($gid)
  {
    $rights = array();
    
    if($gid != "")
       $sql = "SELECT ugf.privKey, up.name from UserGroupPrivRef ugf, UserPrivs up where ugf.privKey = up.privKey and ugf.groupId =  " . $gid
            . " ORDER BY up.name";
    $rights = self::$db->fetchRowsAsAssocArray($sql);	
    return $rights;
  }

  public function addPriv($privName)
  {
  }

  public function removePriv($privName)
  {
  }

  //implemented by Lal Dika
  public function getPrivs()
  {
    $rights = array();
    $sql = "SELECT privKey, name from UserPrivs order by privKey";
    $rights = self::$db->fetchRowsAsAssocArray($sql);
    return $rights;
  }

  public function saveUserGroups($groups)
  {

  }

  public static function saveUserGroups_s($userId, $groups)
  {
    $LINE = "";
    mjlog(DEBUG,__CLASS__.":".__FUNCTION__,"userId[$userId] groups[".implode(",",$groups)."] trace: \n". mj_trace(),__LINE__);

    try
    {
      if (!($userId > 0))     { $LINE = __LINE__; throw new Exception("Invalid UserId."); } 
      if (!is_array($groups)) { $LINE = __LINE__; throw new Exception("Invalid group data."); } 

      // update the UserGroupRef table
      //
      $sql = "delete from UserGroupRef where userId = ". (int)$userId;
      if (self::$db->query($sql) === false) { $LINE = __LINE__; throw new Exception("Failed removing old keys."); }

      $sql = "insert into UserGroupRef (userId, groupId) values ";
      $comma = "";
      foreach ($groups as $groupId)
      {
        $sql   .= $comma ."(". (int)$userId .','. (int)$groupId .")";
	$comma  = ",";
      }
      if (self::$db->query($sql) === false) { $LINE = __LINE__; throw new Exception("Failed updating keys."); }

      // update the UserPrivKeys table
      //
      $sql = "delete from UserPrivKeys where userId = ". (int)$userId;
      if (self::$db->query($sql) === false) { $LINE = __LINE__; throw new Exception("Failed removing old keys."); }

      $ingroups = '"'. implode('","',$groups) .'"';
      $sql = "insert into UserPrivKeys "
           . "select distinct ". (int)$userId .", privKey from UserGroupPrivRef where groupId in ($ingroups)";
      if (self::$db->query($sql) === false) { $LINE = __LINE__; throw new Exception("Failed updating keys."); }
    }
    catch (Exception $e)
    {
      mjlog(ERROR,__CLASS__.":".__FUNCTION__,"Exception caught: ".$e->getMessage(),$LINE);
      return $e->getMessage();
    }
    return true;
  }

  //added by Lal Dika
  //getting a group by gid
  public static function getGroupData($gid)
  {
    $sql = "SELECT id, name FROM UserGroups WHERE id = " . $gid;
    $rez = self::$db->query($sql);
    $row = self::$db->fetchArray($rez);
    return $row;
  }

  //getting a group Id by name - returns false if not found
  public static function getGroupIdByName($name)
  {
    mjlog(DEBUG,__CLASS__.":".__FUNCTION__,"name[$name]",__LINE__);
    if (self::$groupNameToIdCache == null)
    {
      mjlog(DEBUG,__CLASS__.":".__FUNCTION__,"NO CACHE - loading list",__LINE__);
      self::$groupNameToIdCache = self::$db->fetchRowsAsNameValue("select lower(name), id from UserGroups");
    }

    if (isset(self::$groupNameToIdCache[$name]) && ((int)self::$groupNameToIdCache[$name]) > 0) 
    {
      mjlog(DEBUG,__CLASS__.":".__FUNCTION__,"cached value found returning [".self::$groupNameToIdCache[$name]."]",__LINE__);
      return self::$groupNameToIdCache[$name];
    }

    //$sql = "SELECT id FROM UserGroups WHERE name = ". mj_Db::safeStr($name,true);
    //if ($rez = self::$db->query($sql))
    //  if ($row = self::$db->fetchRow($rez))
    //    return (((int)$row[0]) > 0 ? $row[0] : false);
    mjlog(WARNING,__CLASS__.":".__FUNCTION__,"cached value for group name[$name] NOT found returning FALSE",__LINE__);
    return false;
  }

  //added by Lal Dika
  //save group rights
  function saveGroupRights($groupId, $privKeys)
  {
    $LINE = "";
    try
    {
       if (!($groupId > 0)) { $LINE = __LINE__; throw new Exception("Invalid groupId."); }
       if (!is_array($privKeys))  { $LINE = __LINE__; throw new Exception("Invalid privKey list."); }

       //update the UserGroupPrivRef table
       $sql = "delete from UserGroupPrivRef where groupId = " . (int)$groupId;
       if (self::$db->query($sql) === false) { $LINE = __LINE__; throw new Exception("Failed removing old privKeys."); }

       foreach($privKeys as $privKey) 
       {
	  $sql1 = "insert into UserGroupPrivRef (groupId, privKey) values (" . (int)$groupId . "," . "'$privKey')"; 
       
          if (self::$db->query($sql1) === false) { $LINE = __LINE__; throw new Exception("Failed updating privKeys."); } 
       }
    }
    catch (Exception $e)
    {
       mjlog(ERROR, __CLASS__.":".__FUNCTION__,"Exception caught: ".$e->getMessage(),$LINE);
       return $e->getMessage();
    }
    return true;
  }

  //added by Lal Dika
  public static function updateUserGroup($gid, $gDesc)
  {
    $sql = "UPDATE UserGroups SET name = " . $gDesc . " WHERE id = " . $gid;
    if (self::$db->query($sql) === false) { $LINE = __LINE__; throw new Exception("Failed updating UserGroups table."); }
  }

  //added by Lal Dika
  public static function saveUserGroup($gDesc)
  {
    $sql = "INSERT INTO UserGroups (name) VALUES (" . $gDesc . ")";
    if (self::$db->query($sql) === false) { $LINE = __LINE__; throw new Exception("Failed insertingting UserGroups table."); }

    //get the last group id
    $newId = self::$db->insertId(); // NO NO NO !!!!! "SELECT id FROM UserGroups ORDER BY id DESC LIMIT 1";
    //$rez = self::$db->query($sql);
    //$row = self::$db->fetchArray($rez);
    //return $row['id'];
    return $newId;
  }

  public static function getPrivsForGroups($groupIdArray)
  {
    if (count($groupIdArray) == 0) return false;

    $comma = "";
    $groupstr = "";
    $rez = array();
    foreach ($groupIdArray as $group)
    {
      if (((int)$group) > 0)
      {
        $groupstr .= $comma . (int)$group;
        $comma = ",";
      }
    }
    $sql = "select distinct privKey from UserGroupPrivRef where groupId in ($groupstr)";
    $rez = self::$db->fetchRowsAsSimpleArray($sql);
    return $rez;
  }
}

mj_RightsGroups::init_s(mj_User::$userDb);












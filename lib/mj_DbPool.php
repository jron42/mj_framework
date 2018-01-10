<?php
/********************************************************************************
 * Copywrite © 2010-2017 John Morgan
 * This file provided from the personal library of John Morgan and ownership of
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_Db.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

/**
 * Given a named connection this class will open a new connection or pass back an existing one.
 * This class should be used for obtaining all DB handles.
 *
 * @package mj_lib
 */
class mj_DbPool
{
  static private $dbs = array();

  static function init()
  {
  }

  /**
   * Given a named connection this class will open a new connection or pass back an existing one.
   *
   * @return mixed returns boolean false if failed, otherwise it returns a mj_Db object
   */
  static public function getDb($dbname)
  {
    //mjlog(DEBUG,__METHOD__,"called from: ". mj_trace(2));
    if (array_key_exists($dbname,self::$dbs))
    {
      mjlog(DEBUG,__METHOD__,"returning existing database conn: cfgname[$dbname]");
      return self::$dbs[$dbname];
    }
    else
    {
      $db = new mj_Db();
      //if (($name = get_class($db)) === false) echo "mj_DbPool::getDb: Failed to get Database connection\n";
      //else echo __METHOD__.": userDb[$name]\n";
      mjlog(DEBUG,__METHOD__,"about to call db->connectConfig: cfgname[$dbname]");
      if (($conn = $db->connectConfig($dbname)) === false)
      {
        mjlog(ERROR,__METHOD__,"Unable to connect to database: cfgname[$dbname]");
        return false;
      }
      else
      {
        mjlog(DEBUG,__METHOD__,"new connection to database created: cfgname[$dbname]");
        self::$dbs[$dbname] = $db;
        return $db;
      }
    }
    mjlog(ERROR,__METHOD__,"should never get here");
    return false;
  }
}



<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_Db.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

// simple class wrapper for the i database functions
//
// Main functions of interest:
//
// connect($server, $username, $password)
// selectDb($dbname)
// query($sql)
// errno()
// error()
// insertId()
// fetchRow(&$resource)
// fetchAssoc(&$resource)
// numRows(&$resource)
// realEscapeString()
//

require_once('mj_DbPool.php');

/**
 * Basic database wrapper class
 * This class should never be instantiated directly. All instantiations should come vis the mj_DbPool class
 * Methods that are simple/straight wrappers for underlying i_ calls are not documented.
 * The connection is maintained by the class instantiation, method calls to underlying i_ functions that take a connection parameter do not accept conn as a param
 *
 * @package mj_lib
 */
class mj_Db
{
  // const values for use by the insert/update convenience functions
  //
  const QUOTE     = "'";
  const DBL_QUOTE = '"';
  const NO_QUOTE  = '';
  const INSERT    = 0x01;
  const UPDATE    = 0x02;
  const BOTH      = 0x03;

  protected $conn               = NULL;
  protected $currentDb          = "";
  static private $dbEncToken    = "SnarfPuke";
  static private $useEncryption = false;

  function __construct()
  {
  }

  /**
   * This function called from mj_init and should not be called from any other code.
   */
  static function init($token)
  {
    $cfg = &mj_Config::db('base');
    self::$dbEncToken   .= $cfg['encToken'] . $token;
    self::$useEncryption = $cfg['useEncryption'] == '1';

    mjlog(DEBUG,__METHOD__,"dbEncToken[".self::$dbEncToken."] useEncryption[".self::$useEncryption."]",__LINE__);
  }

  /**
   * escape a string for safe writing to the database.
   * It utilizes i_escape_string() while adding some convenience.
   *
   * @param string $str string to be escaped
   * @param boolean $quote if true the returned value will be wrapped with ''
   * @param boolean $setnull if true a null or empty string will return the string "NULL" for the intended db insert/update
   *
   * @return string escaped string
   */
  static function safeStr($str,$quote=false,$setnull=false)
  {
    $str = trim($str);
    if ($setnull && $str == '') return "NULL";
    if ($quote) return "'".mysqli_escape_string($str)."'";
    return mysqli_escape_string($str);
  }

  /**
   * convert the given date string to one acceptible for DB update
   *
   * @param string $dtstr date string to be converted
   * @param boolean $quote if true the returned value will be wrapped with ''
   *
   * @return string date string ready for DB insertion
   */
  static function toDbDate($dtstr, $quote=false)
  {
    if ($quote)
      return "'". date("Y-m-d",strtotime($dtstr)) ."'";
    else
      return date("Y-m-d",strtotime($dtstr));
  }

  /**
   * perform a DB query, can be select, insert or update
   *
   * @param string $sql the query to perform
   * @param string $from for logging purposes this is description text for the code location such as method and line number
   *
   * @return boolean|mixed returns false on failure, a DB resource handle for the query results on success.
   */
  function query($sql,$from="")
  {
    //Send a i query
    if (!$this->conn)
    {
      mjlog(ERROR,__METHOD__,"(from:$from) no connection to query",__LINE__);
      return false;
    }
    if ($from == "") $from = mj_traceLogFrom();
    mjlog(DEBUG,__METHOD__,"(from:$from) ".$sql,__LINE__);
    $thetime = -microtime(true); // used in case we are timing queries
    if (!($rez = mysqli_query($sql,$this->conn)))
    {
      mjlog(ERROR,__METHOD__,"(from:$from) failed query: ".mysqli_error($this->conn)."\nsql: $sql",__LINE__);
      if (mysqli_errno($this->conn))
      {
        mjlog(WARNING,__METHOD__,"(from:$from) failed query(".mysqli_errno($this->conn).'): '.mysqli_error($this->conn),__LINE__);
        mjlog(WARNING,__METHOD__,mj_trace());
      }
    }
    else if (defined('MJ_LOG_DB_QUERIES') && MJ_LOG_DB_QUERIES == 1)
    {
      $thetime += microtime(true);
      $logit = true;
      $slowmsg = '';
      $testt = ((float)MJ_LOG_DB_QUERY_THRESHOLD) - $thetime;

      if (defined('MJ_LOG_DB_QUERY_THRESHOLD') && ((float)MJ_LOG_DB_QUERY_THRESHOLD) > ((float)$thetime))
        $logit = false;
      else
        $slowmsg = 'SLOW QUERY: ';

      //mjlog(NOTICE,__METHOD__,sprintf('MJ_LOG_DB_QUERY_THRESHOLD[%f] Seconds[%f] diff[%f] logit[%s]',
      //      MJ_LOG_DB_QUERY_THRESHOLD,$thetime,$testt,($logit?"true":"false")),__LINE__);
      if ($logit)
      {
        mjlog(NOTICE,__METHOD__,$slowmsg . sprintf('Seconds[%f] SQL[%s]', $thetime, $sql),__LINE__);
        if (defined('MJ_LOG_DB_QUERY_TRACE') && MJ_LOG_DB_QUERY_TRACE == 1)
          mjlog(WARNING,__METHOD__,mj_trace());
      }
    }

    return $rez;
  }

  /** Get number of affected rows in previous MySQLi operation
   */
  function affectedRows() { return mysqli_affected_rows($this->conn); }

  function &clientEncoding() { return mysqli_client_encoding($this->conn); }
    //Returns the name of the character set

  function close() { return mysqli_close($this->conn); }

  /**
   * connect to the DB.
   * This function should not be called by application code as the mj_DbPool class should be used to get DB connection handles
   */
  function &connect($server, $username, $password, $new_link=false, $client_flags=0)
  {
    //Open a connection to a MySQLi Server
    mjlog(DEBUG,__METHOD__,"connect($server,$username,$password,".(int)$new_link.")");
    $this->conn = mysqli_connect($server, $username, $password, $new_link, $client_flags);
    if (!$this->conn)
    {
      mjlog(ERROR,__METHOD__,"failed to connect");
      mjlog(ERROR,__METHOD__,"failed connect(".mysqli_errno($this->conn).'): '.mysqli_error($this->conn),__LINE__);
      mjlog(ERROR,__METHOD__,mj_trace());
    }
    return $this->conn;
  }

  /**
   * connect to the DB.
   * This function should not be called by application code as the mj_DbPool class should be used to get DB connection handles
   */
  function &connectConfig($configName)
  {
    // Open a connection to a MySQLi Server

    mjlog(DEBUG,__METHOD__,"connectConfig($configName)");
    if (($data = mj_Config::db($configName)) === false)
    {
      mjlog(ERROR,__METHOD__,"($configName) - config not found!");
      exit(1);
    }
    mjlog(DEBUG,__METHOD__,"connectConfig(server[".$data["server"]."] username[".$data["user"]."] password[".$data["pass"]."] db[".$data["defDb"]."])");
    if ($this->conn = $this->connect($data["server"],$data["user"],$data["pass"]))
    {
      if ($data["defDb"] != "")
      {
        if ($this->selectDb($data["defDb"]) === false)
	{
	  mjlog(ERROR,__METHOD__,"Failed to connect to database[".$data["defDb"]."]");
          exit(1);
	}
      }
      return $this->conn;
    }
    return $this->conn;
  }

  /**
   * connect to the DB.
   * This function should not be called by application code as the mj_DbPool class should be used to get DB connection handles
   */
  function &pconnect($server, $username, $password, $new_link=false, $client_flags=0)
  {
    //Open a persistent connection to a MySQLi server
    return mysqli_pconnect($server, $username, $password, $new_link, $client_flags);
  }

  /**
   * connect to the DB.
   * This function should not be called by application code as the mj_DbPool class should be used to get DB connection handles
   */
  function &pconnectConfig($configName)
  {
    //Open a persistent connection to a MySQLi server
    //return mysqli_pconnect($cfg.server, $cfg.username, $cfg.password, $cfg.new_link, $cfg.client_flags);
  }

  function creatDb() { return mysqli_create_db($dbname,$this->conn); }
    //Create a MySQLi database

  function dataSeek($resouce, $row_number) { return mysqli_data_seek($resouce, $row_number); }
    //Move internal result pointer

  function dbName(&$resource) { return mysqli_db_name($resource,$index); }
    //Get result data

  function dropDb($dbname) { return mysqli_drop_db($dbname,$this->conn); }
    //Drop (delete) a MySQLi database

  function errno() { return mysqli_errno($this->conn); }
    //Returns the numerical value of the error message from previous MySQLi operation

  function error() { return mysqli_error($this->conn); }
    //Returns the text of the error message from previous MySQLi operation

  function beginTransaction() { mysqli_query('begin',$this->conn); }
  function endTransaction($action='commit') { mysqli_query($action,$this->conn); }
  function commit()   { $this->endTransaction('commit'); }
  function rollback() { $this->endTransaction('rollback'); }

  function escapeString($str) { return mysqli_escape_string($str); }
    //Escapes a string for use in a query

  function fetchArray(&$resource, $result_type=MYSQLI_ASSOC) { return mysqli_fetch_array($resource, $result_type); }
    //Fetch a result row as an associative array, a numeric array, or both

  function fetchAssoc(&$resource) { return mysqli_fetch_assoc($resource); }
    //Fetch a result row as an associative array

  function &fetchField(&$resource, $field_offset=0) { return mysqli_fetch_field($resource, $field_offset=0); }
    //Get column information from a result and return as an object

  function &fetchLengths(&$resource) { return mysqli_fetch_lengths($resource); }
    //Get the length of each output in a result

  function &fetchObject(&$resource, $class_name, $params=NULL) { return mysqli_fetch_object($resource, $class_name, $params); }
    //Fetch a result row as an object

  function fetchRow(&$resource) { return mysqli_fetch_row($resource); }
    //Get a result row as an enumerated array

  function &fieldFlags(&$resource, $field_offset=0) { return mysqli_field_flags($resource, $field_offset); }
    //Get the flags associated with the specified field in a result

  function &fieldLen(&$resource, $field_offset=0) { return mysqli_field_len($resource, $field_offset); }
    //Returns the length of the specified field

  function &fieldName(&$resource, $field_offset=0) { return mysqli_field_name($resource, $field_offset); }
    //Get the name of the specified field in a result

  function &fieldSeek(&$resource, $field_offset=0) { return mysqli_field_seek($resource, $field_offset); }
    //Set result pointer to a specified field offset

  function &fieldTable(&$resource, $field_offset=0) { return mysqli_field_table($resource, $field_offset); }
    //Get name of the table the specified field is in

  function &fieldType(&$resource, $field_offset=0) { return mysqli_field_type($resource, $field_offset); }
    //Get the type of the specified field in a result

  function freeResult(&$resource) { return mysqli_free_result($resource); }
    //Free result memory

  function getClientInfo() { return mysqli_get_client_info(); }
    //Get MySQLi client info

  function getHostInfo() { return mysqli_get_host_info($this->conn); }
    //Get MySQLi host info

  function getProtoInfo() { return mysqli_get_proto_info($this->conn); }
    //Get MySQLi protocol info

  function getServerInfo() { return mysqli_get_server_info($this->conn); }
    //Get MySQLi server info

  function info() { return mysqli_info($this->conn); }
    //Get information about the most recent query

  function insertId() { return mysqli_insert_id($this->conn); }
    //Get the ID generated in the last query

  function listDbs() { return mysqli_list_dbs($this->conn); }
    //List databases available on a MySQLi server

  function listFields($database_name, $table_name) { return mysqli_list_fields($database_name, $table_name, $this->conn); }
    //List MySQLi table fieldsbase_name , string $table_name

  function listProcesses() { return mysqli_list_processes($this->conn); }
    //List MySQLi processes

  function listLables($database_name) { return mysqli_list_tables($database_name, $this->conn); }
    //List tables in a MySQLi database

  function numFields(&$resource) { return mysqli_num_fields($resource); }
    //Get number of fields in result

  function numRows(&$resource) { return mysqli_num_rows($resource); }
    //Get number of rows in result

  function ping() { return mysqli_ping($this->conn); }
    //Ping a server connection or reconnect if there is no connection

  function realEscapeString() { return mysqli_real_escape_string($str,$this->conn); }
    //Escapes special characters in a string for use in an SQL statement

  function &result(&$resource, $row, $field=0 ) { return mysqli_result($resource, $row, $field); }
    //Get result data

  function selectDb($dbname)
  {
    //Select a MySQLi database within the current connection
    //
    mjlog(DEBUG,__METHOD__,"select database [$dbname]");

    if ($this->currentDb == $dbname) return true;

    if (!($rez = mysqli_select_db($dbname,$this->conn)))
      mjlog(ERROR,__METHOD__,"Failed to select database [$dbname]");
    else
      $this->currentDb = $dbname;
    return $rez;
  }

  function setCharset($charsetstr) { return mysqli_set_charset($charsetstr,$this->conn); }
    //Sets the client character set

  function stat() { return mysqli_stat($this->conn); }
    //Get current system status

  function threadId() { mysqli_thread_id($this->conn); }
    //Return the current thread ID

  function &unbufferedQuery($sql) { return mysqli_unbuffered_query($sql,$this->conn); }
    //Send an SQL query to MySQLi without fetching and buffering the result rows

  ////////////////////////////////////////////////////////////////////////////////
  // DB convenience functions
  //

  /**
   * convenience function supporting an insert based on an array of data
   *
   * @param string $table name of the table to insert to
   * @param mixed $data reference to array containing the data to insert
   * @param boolean $getID if true, success returns the new ID for the row
   * @param string $from logging.. from where was this function called.
   *
   * @return boolean|int boolean false on failure, bool true or int ID on success
   */
  function insert($table,&$data,$getID=false,$from="")
  {
    //mjlog(DEBUG,__METHOD__,"table[$table] getID[$getID] ".print_r($data,true),__LINE__);
    mjlog(DEBUG,__METHOD__,"table[$table] getID[$getID] from[$from]",__LINE__);

    $fields = "";
    $values = "";
    $first = true;

    foreach ($data as $name => $val)
    {
      if (is_array($val))
      {
        list($value,$quote,$inup) = $val;
        if ($inup & self::INSERT)
        {
          if (!$first)
          {
            $fields .= ",";
            $values .= ",";
          }
          else $first = false;

          $fields .= $name;
          $values .= $quote.$value.$quote;
        }
      }
      else
      {
        if (!$first)
        {
          $fields .= ",";
          $values .= ",";
        }
        else $first = false;

        $fields .= $name;
        $values .= $val;
      }
    }
    $sql = "insert $table ($fields) values ($values)";
    if (!$this->query($sql,$from)) return false;
    return $getID ? $this->insertId() : true;
  }

  /**
   * convenience function supporting an update based on an array of data
   *
   * @param string $table name of the table to update
   * @param mixed $data reference to array containing the data to insert
   * @param string $where where clause to be used for insert
   * @param string $from logging.. from where was this function called.
   *
   * @return boolean|int boolean false on failure, bool true or int ID on success
   */
  function update($table,&$data,$where,$from="")
  {
    mjlog(DEBUG,__METHOD__,"table[$table] where[$where] from:[$from]",__LINE__);
    $fields = "";
    $first = true;

    foreach ($data as $name => $val)
    {
      if (is_array($val))
      {
        list($value,$quote,$inup) = $val;
        if ($inup & self::UPDATE)
        {
          $fields .= ($first ? "" : ", ") . $name ." = ".$quote. $value .$quote;
          $first = false;
        }
      }
      else
      {
        $fields .= ($first ? "" : ", ") . $name ." = ". $val;
        $first = false;
      }
    }
    $sql = "update $table set $fields $where";
    if (!$this->query($sql,$from)) return false;
    return $this->affectedRows();
  }

  /**
   * convenience function supporting an insert or update based on an array of data
   * note: need to add a getID param for the case of an insert
   *
   * @param string $table name of the table to update
   * @param mixed $data reference to array containing the data to insert
   * @param string $where where clause to be used for update
   * @param string $from logging.. from where was this function called.
   *
   * @return mixed returns an array with 2 values. First tells you if the action was "update" or "insert". Second is the result related to the paricular operation
   */
  function insertUpdate($table,&$data,$where,$from="")
  {
    mjlog(DEBUG,__METHOD__,"table[$table] where[$where] from[$from]",__LINE__);
    $rez = $this->update($table,$data,$where,$from);
    if ($rez !== false && $rez > 0)
    {
      mjlog(DEBUG,__METHOD__,"update successful, returning",__LINE__);
      return array("update",$rez);
    }
    else
      mjlog(DEBUG,__METHOD__,"update failed, trying insert next",__LINE__);
    return array("insert",$this->insert($table,$data,$from));
  }

  ////////////////////////////////////////////////////////////////////////////////

  /**
   * execute a query and call calback for each row fetched
   *
   * @param string $sql the sql statement to execute
   * @param string $cb name of the callback function which is of the form $cb($assoc_array);
   *
   * @return boolean success == t/f
   */
  function fetchRowsToCallback($sql,$cb) // call calback for each row fetched
  {
    mjlog(DEBUG,__METHOD__,$sql,__LINE__);
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return false;
    }
    while ($row = $this->fetchAssoc($rez)) $cb($row);
    return true;
  }

 /**
   * returns the resulting rows of a query into a referenced arrayas given by the $target param
   * Allows for easy accumulation of multiple queries
   * if $merge is false the target array will be nuked before adding the new records. If not the new records will be added to the existing array entries.
   *
   * @param string $sql The query to be exeuted
   * @param array $target reference to the array which will receive the data
   * @param boolean $merge true == nuke the array before adding, false == merge with existing data
   *
   * @return boolean success == t/f
   */
  function fetchIntoArray($sql,&$target,$merge=false)
  {
    mjlog(DEBUG,__METHOD__,$sql,__LINE__);
    $target = array();
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return false;
    }
    if (!($row = $this->fetchAssoc($rez)))
    {
      mjlog(DEBUG,__METHOD__,"fetchAssoc failed, returning!!",__LINE__);
      return false;
    }
    //mjlog(DEBUG,__METHOD__,print_r($row,true),__LINE__);
    if ($merge) array_replace($target,$row);
    else        $target = $row;
    mjlog(DEBUG,__METHOD__,"return true",__LINE__);
    return true;
  }

  /**
   * gather all the result rows (for ONLY row[0]) into an array and return the array
   *
   * @param type $sql The query to be executed
   *
   * @return array one dimensional array of values
   */
  function fetchRowsAsSimpleArray($sql)
  {
    // gather all the result rows (for ONLY row[0]) into an array and return the array
    //
    mjlog(DEBUG,__METHOD__,$sql,__LINE__);
    $data = array();
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return $data;
    }
    while ($row = $this->fetchRow($rez)) $data[] = $row[0];
    return $data;
  }

  /**
   * gather all the result NON-ASSOCIATIVE rows into an array and return the array
   *
   * @param type $sql The query to be executed
   *
   * @return array returns an array of data.
   */
  function fetchRowsAsArray($sql)
  {
    mjlog(DEBUG,__METHOD__,$sql,__LINE__);
    $data = array();
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return $data;
    }
    while ($row = $this->fetchRow($rez)) $data[] = $row;
    return $data;
  }

  /**
   * gather all the result ASSOCIATIVE rows into a simple array and return the array
   *
   * @param type $sql The query to be executed
   *
   * @return array returns an array of data.
   */
  function fetchRowsAsAssocArray($sql)
  {
    mjlog(DEBUG,__METHOD__,$sql,__LINE__);
    $data = array();
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return $data;
    }
    while ($row = $this->fetchAssoc($rez)) $data[] = $row;
    return $data;
  }
  //function fetchRowsAsLookupArray($sql) { return $this->fetchRowsAsAssocArray($sql); }

  /**
   * gather all the result ASSOCIATIVE *rows* into an array keyed by keycol and return the array
   *
   * @param type $keycol result column to be used as array key
   * @param type $sql The query to be executed
   *
   * @return array returns an array of data.
   */
  function fetchRowsAsKeyedAssocArrays($keycol, $sql)
  {
    mjlog(DEBUG,__METHOD__,$sql,__LINE__);
    $data = array();
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return $data;
    }
    while ($row = $this->fetchAssoc($rez)) $data[$row[$keycol]] = $row;
    return $data;
  }

  /**
   * gather all the result rows into an assiciative array structured as row[0] => row[1]
   * only the first 2 columns in the result set will be used.
   *
   * @param type $sql The query to be executed, only row[0] and row[1] returned, row[0] must be unique
   *
   * @return array returns an array of data.
   */
  function fetchRowsAsNameValue($sql)
  {
    mjlog(DEBUG,__METHOD__,$sql,__LINE__);
    $data = array();
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return $data;
    }
    while ($row = $this->fetchRow($rez)) $data[$row[0]] = $row[1];
    return $data;
  }
  function fetchRowsAsNameValue1($table,$keycol,$valcol)
  {
    $sql = "select $keycol,$valcol from $table order by $keycol";
    return $this->fetchRowsAsNameValue($sql);
  }
  function fetchRowsAsNameValue2($table)
  {
    $sql = "select id,name from $table order by id";
    return $this->fetchRowsAsNameValue($sql);
  }

  /**
   * return the query results formatted as html option menu entries.
   * Yeah, this doesn't really belong at the DB level but its just done too often and so this convenience function is useful.
   * errors are logged, no status is returned, only am empty string
   *
   * @param type $sql query to execute. must only return 2 fields. Query needs to be ordered value, prompt
   * @param type $selected return the option as selected that matches the value passed in
   * @param type $selectionByValue test for selected will be made against the value if true and the name if false
   *
   * @return string a string full of options.
   */
  function fetchAsOptions($sql,$selected="",$selectionByValue=1)
  {
    mjlog(DEBUG,__METHOD__,$sql." : def[$selected]",__LINE__);
    $options = "";
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return "";
    }
    while ($row = $this->fetchRow($rez))
    {
      if ($selectionByValue)
        $sel = ($row[0] == $selected) ? " selected" : "";
      else
        $sel = ($row[1] == $selected) ? " selected" : "";
      $options .= '<option value="'.$row[0].'"'.$sel.'>'.$row[1].'</option>';
    }
    return $options;
  }

  /**
   * return a singular value from a query. useful for getting something like the email address for given ID
   * The call can be made either with the table|column|where OR the full query
   * For convenience, fetchValue2() can be called with just the full query.
   *
   * @param type $table name of the table to query
   * @param type $column column to return the value of
   * @param type $where where clause to use, only a single wor will be returned
   * @param type $fullquery give the full query string instead of the 3 parts..
   *
   * @return boolean|mixed false on query error, octherwise value from row[0] of the query result
   */
  function fetchValue($table,$column,$where,$fullquery="")
  {
    if ($fullquery == "") $sql = "select $column from $table where $where";
    else                  $sql = $fullquery;
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      mjlog(ERROR,__METHOD__,"Query failed: ".$this->errno().":".$this->error(),__LINE__);
      return false;
    }
    if (!($row = $this->fetchRow($rez)))
    {
      mjlog(DEBUG,__METHOD__,"Fetch failed, returning!!",__LINE__);
      mjlog(ERROR,__METHOD__,"Fetch failed: ".$this->errno().":".$this->error(),__LINE__);
      return false;
    }
    return $row[0];
  }
  /**
   * convenience call to fetchValue()
   */
  function fetchValue2($fullquery) { return $this->fetchValue("","","",$fullquery); }

  /**
   * another way to call a query, just pass in the sql and get a single associative row back
   *
   * @param type $sql
   *
   * @return boolean|mixed returns false on query failure, otherwise the row is returned
   */
  function getRowAssoc($sql)
  {
    if (!($rez = $this->query($sql)))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      mjlog(ERROR,__METHOD__,"Query failed: ".$this->errno().":".$this->error(),__LINE__);
      return false;
    }
    if (!($row = $this->fetchArray($rez)))
    {
      mjlog(DEBUG,__METHOD__,"Fetch failed, returning!!",__LINE__);
      mjlog(ERROR,__METHOD__,"Fetch failed: ".$this->errno().":".$this->error(),__LINE__);
      return false;
    }
    return $row;
  }

  /**
   * another way to call a query, but don't really use it any more. not helpful :-P
   *
   * @deprecated
   *
   * @param type $table
   * @param type $columns
   * @param type $where
   *
   * @return boolean|mixed returns false on query failure, otherwise the row is returned
   */
  function getRowAssoc2($table,$columns,$where)
  {
    $sql = "select $columns from $table where $where";
    return getRowAssoc2($sql);
  }

  /**
   * return the field names of a query restult in a simple array
   */
  static function getQueryColumnNames($rez)
  {
    $fields = array();
    $nfields = mysqli_num_fields($rez);

    for ($ii = 0; $ii < $nfields; $ii++)
    {
      $fields[] = mysqli_field_name($rez, $ii);
    }
    return $fields;
  }

  ////////////////////////////////////////////////////////////////////////////////
  // DB encryption functions
  //

  // set function to provide proper sql for converting value to DB encrypted value
  static function encryptSql($value,$token,$overKey=NULL)
  {
    if (self::$useEncryption === true)
    {
      //$key = $overKey == NULL ? self::$dbEncToken.$token : $overKey;
      $key = self::$dbEncToken . $token;
      if (isset(self::$encyptionType) && self::$encyptionType == "md5")
        $str = "'".md5('$value')."'";
      else
        $str = "HEX(AES_ENCRYPT('$value','$key'))"; //$pass = mj_encrypt($pass,'132456',1);
    }
    else
      $str = "'".$value."'";
    return $str;
  }

  // set function to provide proper sql for converting DB encrypted value back to clear text
  static function decryptSql($field,$token,$overKey=NULL)
  {
    if (self::$useEncryption === true)
    {
      //$key = $overKey == NULL ? self::$dbEncToken.$token : $overKey;
      $key = self::$dbEncToken . $token;
      $str = "AES_DECRYPT(UNHEX($field),'$key') as $field";
    }
    else
      $str = $field;
    return $str;
  }

  ///////////////////////////////////
  // used for converting an entire column to an encrypted value
  function encryptColumn($tablename,$colname,$token)
  {
    $sql = "update $tablename set $colname = HEX(AES_ENCRYPT($colname,'".self::$dbEncToken.$token."'))";
    if (!$this->query($sql))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return false;
    }
    return true;
  }

  // used for returning an encrypted column back to clear text
  function decryptColumn($tablename,$colname,$token)
  {
    $sql = "update $tablename set $colname = AES_DECRYPT(UNHEX($colname),'".self::$dbEncToken.$token."')";
    if (!$this->query($sql))
    {
      mjlog(DEBUG,__METHOD__,"Query failed, returning!!",__LINE__);
      return false;
    }
    return true;
  }

}

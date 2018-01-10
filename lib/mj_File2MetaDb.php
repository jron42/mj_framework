<?php

/**
 * This is a helper class that writes metadata to a files table in the DB
 */
class mj_FileMetaDb
{

  /**
   * This function creates a dummy record so that we can have an ID for the new file. 
   * This is useful if we want to use the fid as part of the file name
   */
  static public function createDummyMetaRecord()
  {
    $data = array();
    $data['name']       = 'dummy';
    $data['entityType'] = '4';  // Dummy
    $data['fileSize']   = '0';
    $data['mimeType']   = '';
    $data['type']       = '';
    $data['dirPath']    = '';
    $data['urlPath']    = '';
    $data['extra']      = '';
    $data['cfg']        = '';

    $newId = self::saveMeta(0,$data);
    return $newId;
  }

  /**
   * Main function for saving metadata associated with a file
   * A single function handles both inserts and updates.
   */
  static public function saveMeta($id,$data)
  {
    //mjlog(DEBUG,__METHOD__,"id[".(int)$id."] meta: ". print_r($data,true),__LINE__);

    $db = mj_DbPool::getDb('user');
    $comma = ',';
    if ($id > 0)
    {
      $sql = "update Files set updateDT = now()";
      if (isset($data) && is_array($data) && count($data) > 0)
      {
        if (isset($data['name']))       { $sql .= $comma . "name = ".        $db->safeStr($data['name'],       $quoteString=true);  $comma = ', ';}
        if (isset($data['mimeType']))   { $sql .= $comma . "mimeType = ".    $db->safeStr($data['mimeType'],   $quoteString=true);  $comma = ', ';}
        if (isset($data['type']))       { $sql .= $comma . "type = ".        $db->safeStr($data['type'],       $quoteString=true);  $comma = ', ';}
        if (isset($data['fileSize']))   { $sql .= $comma . "fileSize = ".    $db->safeStr($data['fileSize'],   $quoteString=true);  $comma = ', ';}
        if (isset($data['caption']))    { $sql .= $comma . "caption = ".     $db->safeStr($data['caption'],    $quoteString=true);  $comma = ', ';}
        if (isset($data['dirPath']))    { $sql .= $comma . "dirPath = ".     $db->safeStr($data['dirPath'],    $quoteString=true);  $comma = ', ';}
        if (isset($data['urlPath']))    { $sql .= $comma . "urlPath = ".     $db->safeStr($data['urlPath'],    $quoteString=true);  $comma = ', ';}
        if (isset($data['entityId']))   { $sql .= $comma . "entityId = ".    $db->safeStr($data['entityId'],   $quoteString=true);  $comma = ', ';}
        if (isset($data['entityType'])) { $sql .= $comma . "entityType = ".  $db->safeStr($data['entityType'], $quoteString=true);  $comma = ', ';}
        $serdata = serialize($data);
        $sql .= $comma . "meta = ". $db->safeStr($serdata, $quoteString=true);
      }
      $sql .= " where id = "  . (int)$id;
      mjlog(DEBUG,__METHOD__,"sql: $sql",__LINE__);
      if (!($rez = $db->query($sql)))
        { throw new Exception("Failed to save file meta data in DB (line:".__LINE__.")"); }
      //if ($db->numRows($rez) == 0)
      //  { throw new Exception("Failed to update file meta data in DB (line:".__LINE__.")"); }
      return $id;
    }
    else
    {
      $sql = "insert Files (userId,name,mimeType,type,dirPath,urlPath,entityType,entityId,fileSize,meta,createDT,updateDT) values ("
         . (int)mj_User::$currUser->id            .','
         . mj_Db::safeStr($data['name'],true)     .','
         . mj_Db::safeStr($data['mimeType'],true) .','
         . mj_Db::safeStr($data['type'],true)     .','
         . mj_Db::safeStr($data['dirPath'],true)  .','
         . mj_Db::safeStr($data['urlPath'],true)  .','
         . (int)$data['entityType']  .','
         . mj_Db::safeStr((isset($data['entityId']) ? $data['entityId'] : 0),true)  .','
         . mj_Db::safeStr((isset($data['fileSize']) ? $data['fileSize'] : 0),true)  .','
         . mj_Db::safeStr(serialize($data),true)  .','
	 .'now(),now())';
      mjlog(DEBUG,__METHOD__,"sql: $sql",__LINE__);
      if (!($rez = $db->query($sql)))
        { throw new Exception("Failed to save file meta data to DB (line:".__LINE__.")"); }
      $id = $db->insertId();
      mjlog(DEBUG,__METHOD__,"new insert id[".(int)$id."]",__LINE__);
      return (int)$id;
    }
    return (int)0;
  }

  public function fetchMeta($id)
  {
    mjlog(DEBUG,__METHOD__,"id[".(int)$id."]");
    $db = mj_DbPool::getDb('user');
    $sql = "select * from Files where id = ". (int)$id;
    if (!($rez = $db->query($sql)))
      { throw new Exception("File meta data fetch query failed (line:".__LINE__.")"); }
    if (!($row = $db->fetchAssoc($rez)))
      { throw new Exception("File meta data fetch failed (line:".__LINE__.")"); }
    //mjlog(DEBUG,__METHOD__,"row: ". print_r($row,true));

    $row['meta'] = unserialize($row['meta']);
    $row['meta']['id'] = $id;
  
    //mjlog(DEBUG,__METHOD__,"data: ". print_r($row,true));
    return $row;
  }
 
  public function deleteMeta($id)
  {
    $db = mj_DbPool::getDb('user');
    $sql = "delete from Files where id = ". (int)$id;
    if (!($rez = $db->query($sql)))
      { throw new Exception("File meta data delete failed (line ".__LINE__.")"); }
  }
}


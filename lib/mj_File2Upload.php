<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_FileUploadBase.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

/*
 _FILES: Array
(
    [picture] => Array
        (
            [name] => traveltips.pdf
            [type] => application/pdf
            [tmp_name] => /private/var/tmp/phpTrHn8U
            [error] => 0
            [size] => 0
        )

)
*/

////////////////////////////////////////////////////////////////////////////////
/// Metadata handling
////////////////////////////////////////////////////////////////////////////////

class mj_File2Upload
{
  protected $id     = NULL;
  protected $cfg    = array();
  protected $mjfile = NULL;

  protected $metaWriterClass = "mj_File2MetaDb";
  protected $meta = array();

  static protected $uploadErrors = array();
  protected $fetchErr = "";

  public function getId() { return $this->id; }

  static public function getUploadErrors($field=NULL) 
  { 
    if (isset($field) && $field != '' && isset(self::$uploadErrors[$field]))
      return self::$uploadErrors[$field]; 
    else 
      return self::$uploadErrors;
  } 

  static public function getUploadErrorMsg($code) 
  { 
    $message = "Unknown upload error";
    switch ($code) 
    { 
      case UPLOAD_ERR_INI_SIZE:   $message = "The uploaded file exceeds the upload_max_filesize (".ini_get('upload_max_filesize').") directive in php.ini"; 			  break; 
      case UPLOAD_ERR_FORM_SIZE:  $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"; break; 
      case UPLOAD_ERR_PARTIAL:    $message = "The uploaded file was only partially uploaded";   break; 
      case UPLOAD_ERR_NO_FILE:    $message = "No file was uploaded"; 				break; 
      case UPLOAD_ERR_NO_TMP_DIR: $message = "Missing a temporary folder"; 			break; 
      case UPLOAD_ERR_CANT_WRITE: $message = "Failed to write file to disk"; 			break; 
      case UPLOAD_ERR_EXTENSION:  $message = "File upload stopped by extension";		break; 
      case UPLOAD_ERR_OK:         $message = "File upload ok";					break; 
    } 
    return $message; 
  } 

  /**
   * delete the upload tmp file and the metafile if one was created
   */
  static private function _cleanUpload($upname, $metaname='')
  {
    mjlog(DEBUG,__METHOD__,"upname[$upname] metaname[$metaname]",__LINE__);
    if (file_exists($upname)) unlink($upname);
    if ($metaname != '' && file_exists($metaname)) unlink($metaname);
  }

  /**
   * factory function to create an upload object and process then call it to process the  upload
   */
  static public function processUpload($formFieldName, $cfg='', $extrameta='', $entityType='', $entityId=0)
  {
    mjlog(DEBUG,__METHOD__,"formFieldName[$formFieldName] entityType[$entityType] entityId[$entityId] cfg: ". print_r($cfg,true),__LINE__);
    $fobj = new mj_File2Upload;
    return $fobj->_processUpload($formFieldName, $cfg, $extrameta, $entityType, $entityId);
  }

  /**
   * main function for processing an image upload
   * processing steps
   * - check name of original file for != ""
   * - check size > 0 and < max upload size
   * - check type is supported type
   * - check file extension for supportedd type
   * - generate new filename
   * - delete upload file
   *
   * @params string $upname name of the uploaded images form field
   * @params mixed $xtra associative array of data to write to the meta file, things such as userID or groupID are appropriate
   *
   * @return mj_Image|boolean false if error, pointer to an mj_Image object on success
   */
  public function _processUpload($formFieldName, $cfg='', $extrameta='', $entityType='', $entityId=0)
  {
    $LINE = 0;
    $log  = "";
    $id   = 0;

    // setup extrameta as an empty array if its not set.
    if (!is_array($extrameta)) $extrameta = array();
    if (is_array($cfg)) $this->cfg = $cfg;
    else                $cfg       = $this->cfg;

    $saveMetaData = @mj_isset($cfg["saveMetaData"],false);

    mjlog(DEBUG,__METHOD__,"\n\n============================= formFieldName[$formFieldName] entityType[$entityType] entityId[$entityId]\n",__LINE__);
    mjlog(DEBUG,__METHOD__,"config: ".print_r($this->cfg,true),__LINE__);
    mjlog(DEBUG,__METHOD__,"_FILES: ".print_r($_FILES,true),__LINE__);

    try
    {
      //mjlog(DEBUG,__METHOD__,"upload error: formFieldName[$formFieldName] error[".$_FILES[$formFieldName]['error']."]"
      //. "msg[".self::getUploadErrorMsg($_FILES[$formFieldName]['error'])."]",__LINE__);
      if (!(isset($_FILES[$formFieldName]['error']) && $_FILES[$formFieldName]['error'] == UPLOAD_ERR_OK)) 
      {
        $err = "File upload error: ". self::getUploadErrorMsg($_FILES[$formFieldName]['error']);
        mjlog(DEBUG,__METHOD__,"upload error being thrown: field[$formFieldName] errno[".(int)$_FILES[$formFieldName]['error'] ."] err[$err]",__LINE__);
        $LINE = __LINE__; 
        throw new Exception($err);
      }

      $origFileName = '';
      $tmp_name     = '';
      if (isset($_FILES[$formFieldName]['name'])) $origFileName = $_FILES[$formFieldName]['name'];
      else { $LINE = __LINE__; throw new Exception("Missing upload file name: ". $origFileName); }

      mjlog(DEBUG,__METHOD__,"get original file name: formFieldName[$formFieldName] origFileName[$origFileName]",__LINE__);

      if (isset($_FILES[$formFieldName]['tmp_name'])) $tmp_name = $_FILES[$formFieldName]['tmp_name'];
      else { $LINE = __LINE__; throw new Exception("Missing upload file name: ". $tmp_name); }

      mjlog(DEBUG,__METHOD__,"get original file name: formFieldName[$formFieldName] tmp_name[$tmp_name]",__LINE__);

      if (trim($tmp_name) == "" || !file_exists($tmp_name))
        { $LINE = __LINE__; throw new Exception("Missing upload file: $origFileName - tmp_name[$tmp_name]"); }

      $tmpFileSize  = $_FILES[$formFieldName]['size'];
      $tmpFileType  = trim($_FILES[$formFieldName]['type']);

      mjlog(DEBUG,__METHOD__,"move file: tmp_name[$tmp_name] tmpFileSize[$tmpFileSize]",__LINE__);

      // we want to create the new name and lock it in as atomically as possible

      //if ((isset($cfg['naming']) && $cfg['naming'] != 'keep') && ($newFileName = $this->generateNewFileName()) === false)
      //  { $LINE = __LINE__; throw new Exception("Failed to get new file name"); }
      $mjfile   = new mj_File2($this->cfg);

      $newId = mj_FileMetaDb::createDummyMetaRecord();
      if (!($newId > 0))
        { $LINE = __LINE__; throw new Exception("Failed to create new file meta record: $origFileName - tmp_name[$tmp_name]"); }
      $mjfile->id = $newId;

      mjlog(DEBUG,__METHOD__,"new ID[$newId]",__LINE__);

      $uploadMeta['origFileName']  = $origFileName;
      $uploadMeta['formFieldName'] = $formFieldName;

      $newFileName  = $mjfile->generateNewFileName($origFileName); // This needs to be set based on params - hack for now
      $path         = $mjfile->generatePath($newFileName);
      mjlog(DEBUG,__METHOD__,"path[$path]",__LINE__);
      $dirPath      = @mj_getValue($this->cfg['dir']['basePath'],'') . $path;
      $urlPath      = @mj_getValue($this->cfg['dir']['urlPath'],'')  . $path;
      mjlog(DEBUG,__METHOD__,"path[$path] dirpath[$dirPath] urlpath[$urlPath] newFileName[$newFileName]",__LINE__);
      $new_name     = $mjfile->buildFullPathAndFileName($dirPath,$newFileName); // MJ_ROOT_PATH .'ht/'.$cfg['dir']['basePath'] . $path . $newFileName;
      $mjfile->checkPath(dirname($new_name));

      mjlog(DEBUG,__METHOD__,"path[$path] dirpath[$dirPath] urlpath[$urlPath] newFileName[$newFileName] new_name[$new_name]",__LINE__);

      $this->meta['cfgSource'] = 'mj_File2Upload.php::_processUpload()'; //  just a notice for debugging purposes

      $this->meta['name']       = $newFileName;
      $this->meta['fileSize']   = $tmpFileSize;
      $this->meta['mimeType']   = $tmpFileType;
      $this->meta['type']       = mj_File2BaseValues::$mimeToExtension[$tmpFileType];
      $this->meta['dirPath']    = $dirPath;
      $this->meta['urlPath']    = $urlPath;
      $this->meta['extra']      = $uploadMeta;
      $this->meta['cfg']        = $this->cfg;
      $this->meta['entityType'] = $entityType;
      $this->meta['entityId']   = $entityId;

      mjlog(DEBUG,__METHOD__,"move file: tmp_name[$tmp_name] new_name[$new_name]",__LINE__);

      if (!move_uploaded_file($tmp_name, $new_name))
        { $LINE = __LINE__; throw new Exception("Failed To Write File: ". $new_name); }
      mjlog(DEBUG,__METHOD__,"file moved - continue",__LINE__);

      mjlog(DEBUG,__METHOD__,"meta: ".print_r($this->meta,true),__LINE__);

      if (mj_File2::typeIsImage($new_name) && !mj_File2Image::isValidImage($new_name)) { $LINE = __LINE__; throw new Exception("Invalid Image Type"); }

      $readExif = @mj_getValue($cfg['image']['readExif'],'false');
      $resize   = @mj_getValue($cfg['image']['resize'],'false');
      $type     = $this->meta['type'];
      mjlog(DEBUG,__METHOD__,
            "\nmj_File2Image::isValidImage[".tfString(mj_File2Image::isValidImage($new_name))."] "
           ."\nmj_File2::typeIsImage[".tfString(mj_File2::typeIsImage($new_name))."] "
           ."\ncfg['image']['readExif'][".$readExif."] "
           ."\ncfg['image']['resize'][".$resize."] "
           ."\nthis->meta['type'][".$type."] "
           ."\ncfg['image']['types']: ".print_r($cfg['image']['types'],true),__LINE__);
      if (mj_File2::typeIsImage($new_name) && ($readExif || $resize) && in_array($type,$cfg['image']['types']))
      {
        mjlog(DEBUG,__METHOD__,"------------- file is type image",__LINE__);
        $mjfile->setMeta($this->meta);
       
        $image = new mj_File2Image($this->cfg,$mjfile);
        $image->processImage($new_name);
        mjlog(DEBUG,__METHOD__,"------------- after: image->processImage",__LINE__);
      }
      else
      {
        mjlog(DEBUG,__METHOD__,"------------- file is NOT type image",__LINE__);
      }

      if (($id = $mjfile->writeMetaData()) === false)
        { $msg = 'Failed to save metadata'; mjlog(WARNING,__METHOD__,$msg,__LINE__); $LINE = __LINE__; throw new Exception($msg); }

      if (!(isset($this->id) && $this->id > 0))
      {
        $this->id         = $id;
        $this->meta       = $mjfile->getMeta();
        $this->meta['id'] = $id;
      }

      if (file_exists($tmp_name)) unlink($tmp_name);
      mjlog(DEBUG,__METHOD__,"Upload complete, new id[".$this->id."] filename[$new_name] size[".$tmpFileSize."] mime[".$tmpFileType."]",__LINE__);
      mjlog(DEBUG,__METHOD__,"trace: ". mj_trace(),__LINE__);

      return $mjfile;
    }
    catch (Exception $e)
    {
      $err = $e->getMessage();
      self::$uploadErrors[$formFieldName] = $err;
      self::_cleanUpload($tmp_name);
      mjlog(NOTICE,__METHOD__,"Exception caught: $err - uploadErrors: ". print_r(self::$uploadErrors,true),$LINE);
      return false;
    }
    mjlog(ERROR,__METHOD__,"Should not have gotten here",__LINE__);
    return false; // should never get here!
  }

  function writeMetaData($saveMetaData)
  {
    return true;
  }

}

function tfString($bool)
{
  return ($bool) ? 'true' : 'false';
}


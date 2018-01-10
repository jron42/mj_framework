<?php
/*
phpinfo();
exit();
MJ_ROOT_PATH	/Users/jmorgan/Sites/AppBackends/photoLocation/shotspotz/
*/

$LINE = 0;

$mj_root_path = getenv('MJ_ROOT_PATH');

$upload_field_name = 'testfile';

require_once($mj_root_path ."lib/mj_init.php");

////////////////////////////////////////////////////////////////////////////////

function getFileUpConfig($isImage=false)
{
  static $cfg = array();

  $cfg['cfgSource'] = 'example2.upload.php - getFileUpConfig';

  $cfg['file']  = array();
  $cfg['dir']   = array();
  $cfg['meta']  = array();

  $cfg['file']['naming']       = "keep";    // possible values: rand|md5|tmpname|keep|func|date
  $cfg['file']['allowedTypes'] = array("pdf","txt"); // possible values: "pfd|txt" later: "jpg|jpeg|png|gif|pjpeg"
  $cfg['file']['validateFunc'] = "";        // name of a validation function to call if desired

  $cfg['dir']['storage']  = "localfs";      // possible values: localfs|s3
  $cfg['dir']['urlPath']  = "upload/";      // this can be absolute or relative - must end with /
  $cfg['dir']['basePath'] = "/ht/upload/";  // this can be absolute or relative - must end with /
  $cfg['dir']['tree']     = "none";         // none|simple // not used yet
  $cfg['dir']['depth']    = "";             // integer - calculated paths should be this deep

  return $cfg;
}

function getImageUpConfig()
{
    $cfg = array();
    $cfg['cfgSource'] = 'example2.upload.php - getImageUpConfig';

    $cfg['file']  = array();
    $cfg['dir']   = array();
    $cfg['meta']  = array();
    $cfg['image'] = array();
    $cfg['image']['sizes'] = array();

    $cfg['file']['naming']       = "fid";  // possible values: rand|md5|tmpname|keep|func|date|fid
    $cfg['file']['allowedTypes'] = array("txt","pdf","jpg","jpeg","png");   // possible values: "pfd|txt" later: "jpg|jpeg|png|gif|pjpeg"
    $cfg['file']['validateFunc'] = "";      // name of a validation function to call if desired

    $cfg['dir']['storage']  = "localfs";      // possible values: localfs|s3
    $cfg['dir']['urlPath']  = "upload/";      // this can be absolute or relative - must end with /
    $cfg['dir']['basePath'] = "/ht/upload/";  // this can be absolute or relative - must end with /
    $cfg['dir']['treeType'] = "uidfid";       // none|uidfid 
    $cfg['dir']['depth']    = "";             // integer - calculated paths should be this deep

    $cfg['saveMetaData']    = "db";       // possible values: db|file|none - default = db - "file" is not complete
    $cfg['meta']['save']    =  true;      // possible values: true|false
    $cfg['meta']['storage'] = "db";       // possible values: db|file|s3
    $cfg['meta']['urlPath'] = "upload/";  // this can be absolute or relative - must end with /

    $cfg['image']['types']     = array('jpg','jpeg','png');
    $cfg['image']['readExif']  = true;
    $cfg['image']['resize']    = true;        // if true you must have a 'sizes' section as below
    $cfg['image']['watermark'] = 'SpotShotz'; // add a watermark if not = "" - not yet functional

    $cfg['image']['sizes']['large']['bounded'] = 'width'; // possible values: height/width/both - which size will be max for image
    $cfg['image']['sizes']['large']['height']  =    0;
    $cfg['image']['sizes']['large']['width']   = 1000;
    $cfg['image']['sizes']['large']['quality'] =   85;
    $cfg['image']['sizes']['large']['maintainAspect'] = true;

    $cfg['image']['sizes']['med']['bounded']   = 'width'; // possible values: height/width/both - which size will be max for image
    $cfg['image']['sizes']['med']['height']    =   0;
    $cfg['image']['sizes']['med']['width']     = 700;
    $cfg['image']['sizes']['med']['quality'] =  75;
    $cfg['image']['sizes']['med']['maintainAspect'] = true;

    $cfg['image']['sizes']['thumb']['bounded'] = 'width'; // possible values: height/width/both - which size will be max for image
    $cfg['image']['sizes']['thumb']['height']  =   0;
    $cfg['image']['sizes']['thumb']['width']   =  200;
    $cfg['image']['sizes']['thumb']['quality'] =  75;
    $cfg['image']['sizes']['thumb']['maintainAspect'] = true;

    return $cfg;
}

$fid  = 0;
//$cfg = getConfig();
//$cfg = mj_File2::setDefaultConfig();
$cfg = getImageUpConfig();

try
{
  mjlog(DEBUG,"example2.upload.php","\n\n=============================\n\n\n",__LINE__);

  require_once(MJ_ROOT_PATH ."lib/mj_File2.php");

  if (isset($_POST['doupload']))
  {
    mjlog(DEBUG,"test_upload.php","cfg: ".print_r($cfg,true),__LINE__);
    mjlog(DEBUG,"test_upload.php","=============================",__LINE__);
    mjlog(DEBUG,"test_upload.php","_FILES: ".print_r($_FILES,true),__LINE__);
  
    mjlog(DEBUG,"test_upload.php","============================= start upload",__LINE__);
  
    $msg = "<br><br>Upload results:<br><br><table>";
  
    // test uploading a file
    //
    $extraMeta = array("importantThing" => "some important thing data");
    
    if (($fileobj = mj_File2Upload::processUpload($upload_field_name,$cfg,$extraMeta)) !== false)
    {
      $fid = $fileobj->getId();
      mjlog(DEBUG,"test_upload.php","successful upload: new fid[".(int)$fid."]",__LINE__);
  
      $msg .= '<tr><td>fileId:</td><td>'.   $fileobj->getId()."</td></tr>";
/*
      mjlog(DEBUG,"test_upload.php", "fieldName = ". $fileobj->getFormFieldName());
      mjlog(DEBUG,"test_upload.php", "fileId    = ". $fileobj->getId());
      mjlog(DEBUG,"test_upload.php", "fileName  = ". $fileobj->getName());
      mjlog(DEBUG,"test_upload.php", "url       = ". $fileobj->getUrl());
  
      $msg .= '<tr><td colspan=2>upload</td></tr>';
      $msg .= '<tr><td>fieldName:</td><td>'.$fileobj->getFormFieldName()."</td></tr>";
      $msg .= '<tr><td>fileId:</td><td>'.   $fileobj->getId()."</td></tr>";
      $msg .= '<tr><td>fileName:</td><td>'. $fileobj->getName()."</td></tr>";
      $msg .= '<tr><td>url:</td><td>'.      $fileobj->getUrl()."</td></tr>";
*/
    }
    else
    {
      $err = "processUpload failed - field[$upload_field_name] error: ". mj_File2Upload::getUploadErrors($upload_field_name);
      mjlog(DEBUG,"test_upload.php",$err,__LINE__);
      $msg .= '<tr><td>error:</td><td>'.mj_File2Upload::getUploadErrors($upload_field_name)."</td></tr>";
      throw new Exception($err);
    }
  
    // test accessing a file after uploaded for later display
    //
    mjlog(DEBUG,"example2.upload.php","\n\n=============================\n=============================\n=============================\n\n\n",__LINE__);

    $fobj = new mj_File2($cfg,$fid);
    //{
    //if (($fobj = new mj_File2($cfg,$fid)) !== false)
    //{
    //  if ($fobj->fetchMeta($fid) !== false)
    //  {
mjlog(DEBUG,"example2.upload.php","\n++++++++++++++++++++++++++++++++++\n++++++++++++++++++++++++++++++++++",__LINE__);
      $url = $fobj->getUrl(mj_File2::URL_LOCAL);
      $msg .= '<tr><td colspan=2>&nbsp;</td></tr>';
      $msg .= '<tr><td colspan=2>post upload<br><br></td></tr>';
      $msg .= '<tr><td>fieldName:</td><td>'.$fobj->getFormFieldName()."</td></tr>";
      $msg .= '<tr><td>fileId:</td><td>'.   $fobj->getId()."</td></tr>";
      $msg .= '<tr><td>fileName:</td><td>'. $fobj->getName()."</td></tr>";
      $msg .= '<tr><td>path:</td><td>'.$fobj->getFullPathAndFileName()."</td></tr>";
      $msg .= '<tr><td>url:</td><td><a href="'.$url.'" target=_blank>'.$url."</a></td></tr>";

      if ($fobj->isResizeableImage())
      {
        $imeta = $fobj->getImageMeta();
        mjlog(DEBUG,__METHOD__,"imeta: ". print_r($imeta,true),__LINE__);
        foreach ($imeta['sizes'] as $size => $sizecfg)
        {
          mjlog(DEBUG,__METHOD__,"size[$size]",__LINE__);
          $url = $fobj->getImageUrl(mj_File2::URL_LOCAL,$size);
          $msg .= "<tr><td>---- $size:</td><td><a href=\"".$url.'" target=_blank>'.$url."</a></td></tr>";
        }
      }
      else mjlog(DEBUG,__METHOD__,"File is NOT isResizeableImage",__LINE__);
mjlog(DEBUG,"example2.upload.php","++++++++++++++++++++++++++++++++++",__LINE__);

    //  }
    //  else
    //    $msg .= '<tr><td>error:</td><td>'. $fobj->getErrMsg()."</td></tr>";
    //}
  
    $msg .= "</table><br><br>";
    mjlog(DEBUG,"test_upload.php","============================= complete",__LINE__);
  }
}
catch (Exception $e)
{
  $err = $e->getMessage();
  mjlog(NOTICE,__CLASS__."::".__FUNCTION__,"Exception caught: ".$e->getMessage(),$LINE);
  mjlog(NOTICE,__CLASS__."::".__FUNCTION__,"Exception stack: ".mj_trace(),$LINE);
  exit();
}
?>
<html>

<body>

<h2>Example upload for pdf and txt files</h2>

<?php if (isset($msg)) echo $msg; ?><P>

<form name=uploadform method=POST action="example2.upload.php" enctype="multipart/form-data">

  <input type=hidden name=doupload value="true">
  <input type=hidden name=token value="<?php echo mj_Session::getToken(); ?>">
 
  <input type="file" name="testfile" value="/Users/jmorgan/work/zai/docs/traveltips.pdf">

  <input type=submit>
</form>

</body>
</html>




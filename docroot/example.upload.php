<?php

$LINE = 0;

require_once(getenv('MJ_ROOT_PATH').'/lib/mj_Logger.php');

try
{
require_once(getenv('MJ_ROOT_PATH')."/lib/mj_init.php");
require_once(getenv('MJ_ROOT_PATH')."/lib/mj_FileUploadBase.php");
require_once(getenv('MJ_ROOT_PATH')."/lib/mj_FileUpload.php");

mjlog(DEBUG,"test_upload.php","\n\n=============================\n\n\n");

if (isset($_POST['doupload']))
{
  $cfg['file']['naming']       = "keep";    // possible values: rand|md5|tmpname|keep|func|date
  $cfg['file']['allowedTypes'] = "pdf|txt"; // possible values: "pfd|txt" later: "jpg|jpeg|png|gif|pjpeg"
  $cfg['file']['validateFunc'] = "";        // name of a validation function to call if desired

  $cfg['dir']['basePath'] = "/Users/jmorgan/Sites/zai/apl2core/ht/upload/";  // this can be absolute or relative - must end with /
  $cfg['dir']['tree']     = "none";  // none|simple // not used yet
  $cfg['dir']['depth']    = "";      // integer - calculated paths should be this deep

  $cfg['storage'] = "localfs";  // possible values: localfs|s3
  $cfg['baseURL'] = "upload/";  // this can be absolute or relative - must end with /

  mjlog(DEBUG,"test_upload.php","cfg: ".print_r($cfg,true));
  mjlog(DEBUG,"test_upload.php","=============================");
  mjlog(DEBUG,"test_upload.php","_FILES: ".print_r($_FILES,true));

  mjlog(DEBUG,"test_upload.php","============================= start upload");

  $fid = 0;
  $msg = "<br><br>Upload results:<br><br><table>";

  // test uploading a file
  //
  $extraMeta = array("importantThing" => "some important thing data");
  if (($fileobj = mj_File::upload('testfile',$cfg,$extraMeta)) !== false)
  {
    $fid = $fileobj->getId();

    mjlog(DEBUG,"test_upload.php", "fieldName = ". $fileobj->getFormFieldName());
    mjlog(DEBUG,"test_upload.php", "fileId    = ". $fileobj->getId());
    mjlog(DEBUG,"test_upload.php", "fileName  = ". $fileobj->getName());
    mjlog(DEBUG,"test_upload.php", "url       = ". $fileobj->getUrl());

    $msg .= '<tr><td colspan=2>upload</td></tr>';
    $msg .= '<tr><td>fieldName:</td><td>'.$fileobj->getFormFieldName()."</td></tr>";
    $msg .= '<tr><td>fileId:</td><td>'.   $fileobj->getId()."</td></tr>";
    $msg .= '<tr><td>fileName:</td><td>'. $fileobj->getName()."</td></tr>";
    $msg .= '<tr><td>url:</td><td>'.      $fileobj->getUrl()."</td></tr>";
  }
  else
  {
    mjlog(DEBUG,"test_upload.php", mj_File::getUploadErrmsg('testfile'));
    $msg .= '<>url:</td><td>'.mj_File::getUploadErrmsg('testfile')."</td></tr>";
  }

  // test accessing a file after uploaded for later display
  //
  if (($fobj = new mj_File($cfg)) !== false)
  {
    if ($fobj->fetchMeta($fid) !== false)
    {
      $msg .= '<tr><td colspan=2>&nbsp;</td></tr>';
      $msg .= '<tr><td colspan=2>post upload</td></tr>';
      $msg .= '<tr><td>fieldName:</td><td>'.$fobj->getFormFieldName()."</td></tr>";
      $msg .= '<tr><td>fileId:</td><td>'.   $fobj->getId()."</td></tr>";
      $msg .= '<tr><td>fileName:</td><td>'. $fobj->getName()."</td></tr>";
      $msg .= '<tr><td>url:</td><td>'.      $fobj->getUrl()."</td></tr>";
    }
    else
      $msg .= '<tr><td>error:</td><td>'. $fobj->getErrMsg()."</td></tr>";
  }

  $msg .= "</table><br><br>";
  mjlog(DEBUG,"test_upload.php","============================= complete");
}
}
catch (Exception $e)
{
  $err = $e->getMessage();
  mjlog(NOTICE,__CLASS__."::".__FUNCTION__,"Exception caught: ".$e->getMessage(),$LINE);
  mjlog(NOTICE,__CLASS__."::".__FUNCTION__,"Exception stack: ".mj_trace(),$LINE);
}
?>
<html>

<body>

<?php if (isset($msg)) echo $msg; ?>

<form name=uploadform method=POST action="example.upload.php" enctype="multipart/form-data">

  <input type=hidden name=doupload value="true">
 
  <input type="file" name="testfile" value="/Users/jmorgan/work/zai/docs/traveltips.pdf">

  <input type=submit>
</form>

</body>
</html>




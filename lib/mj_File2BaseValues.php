<?php
/**
 * Basically just some base values for easy refernce and to keep the logic code uncluttered with crap. 
 */

class mj_File2BaseValues
{
  /**
   * array of function pointers to handle file naming on upload
   * $name = $this->$_nameFuncs[$this->cfg['fileNaming']]]();
   */
  static protected $_nameFuncs = array(
    'rand'    => 'genRandomFileName',
    'ms5'     => 'genMd5FileName',
    'tmpname' => 'genTmpnameFileName',
    'keep'    => 'genOrigFileName',
    'fid'     => 'genFidFileName',
  );

  /**
   * array of function pointers to handle directory tree generation on file upload
   * $name = $this->$_treeFuncs[$this->cfgi['dir']['tree']]]();
   */
  static protected $_pathFuncs = array(
    'uid'    => 'generatePathUid',
    'uidfid' => 'generatePathUidFid',
    'chars'  => 'generatePathByNameChars',
    'random' => 'generateRandomString',
  );

  /** 
   * mapping of supported mime types to known extensions
   */
  static public $mimeToExtension = array(
    'image/jpg'    => 'jpg',
    'image/jpeg'   => 'jpg',
    'image/pjpeg'  => 'jpg',
    'image/gif'    => 'gif',
    'image/png'    => 'png',
    'image/bmp'    => 'bmp',
    'application/pdf'      => 'pdf',
    'application/acrobat'  => 'pdf',
    'application/x-pdf'    => 'pdf',
    'applications/vnd.pdf' => 'pdf',
    'text/plain'           => 'txt',
    'text/pdf'             => 'pdf',
    'text/x-pdf'           => 'pdf'
  );
  static public $rezeable = array('jpg','png');

  ////////////////////////////////////////////////////////////////////////////////
  /// EXIF shit
  ////////////////////////////////////////////////////////////////////////////////

  static public $exifImageTypes = array(
    '',
    'IMAGETYPE_GIF',
    'IMAGETYPE_JPEG',
    'IMAGETYPE_PNG',
    'IMAGETYPE_SWF',
    'IMAGETYPE_PSD',
    'IMAGETYPE_BMP',
    'IMAGETYPE_TIFF_II (intel byte order)',
    'IMAGETYPE_TIFF_MM (motorola byte order)',
    'IMAGETYPE_JPC',
    'IMAGETYPE_JP2',
    'IMAGETYPE_JPX',
    'IMAGETYPE_JB2',
    'IMAGETYPE_SWC',
    'IMAGETYPE_IFF',
    'IMAGETYPE_WBMP',
    'IMAGETYPE_XBM',
    'IMAGETYPE_ICO',
  );

  static public $exifImageTypesToMimeTypes = array(
    'IMAGETYPE_GIF'  => 'image/gif',
    'IMAGETYPE_JPEG' => 'mage/jpeg',
    'IMAGETYPE_PNG'  => 'image/png',
    'IMAGETYPE_SWF'  => 'application/x-shockwave-flash',
    'IMAGETYPE_PSD'  => 'image/psd',
    'IMAGETYPE_BMP'  => 'image/bmp',
    'IMAGETYPE_TIFF_II (intel byte order)'    => 'image/tiff',
    'IMAGETYPE_TIFF_MM (motorola byte order)' => 'image/tiff',
    'IMAGETYPE_JPC'  => 'application/octet-stream',
    'IMAGETYPE_JP2'  => 'image/jp2',
    'IMAGETYPE_JPX'  => 'application/octet-stream',
    'IMAGETYPE_JB2'  => 'application/octet-stream',
    'IMAGETYPE_SWC'  => 'application/x-shockwave-flash',
    'IMAGETYPE_IFF'  => 'image/iff',
    'IMAGETYPE_WBMP' => 'image/vnd.wap.wbmp',
    'IMAGETYPE_XBM'  => 'image/xbm',
    'IMAGETYPE_ICO'  => 'image/vnd.microsoft.icon',
    'IMAGETYPE_WEBP' => 'image/webp',
  );

  public static function typeIsImage($filename)
  {
      $etype = @exif_imagetype($filename);
      return ($etype > 0);
  }
/*
      return image_type_to_mime_type( $imagetype );
    try
    {
    {

    }
    catch (Exception $e)
    {
      $err = $e->getMessage();
      self::$uploadErrors[$upname] = $err;
      mjlog(NOTICE,__METHOD__,"Exception caught: ".$err,$LINE);
      self::_cleanUpload($tmp_name);
      return false;
    }
*/

/*
1	IMAGETYPE_GIF
2	IMAGETYPE_JPEG
3	IMAGETYPE_PNG
4	IMAGETYPE_SWF
5	IMAGETYPE_PSD
6	IMAGETYPE_BMP
7	IMAGETYPE_TIFF_II (intel byte order)
8	IMAGETYPE_TIFF_MM (motorola byte order)
9	IMAGETYPE_JPC
10	IMAGETYPE_JP2
11	IMAGETYPE_JPX
12	IMAGETYPE_JB2
13	IMAGETYPE_SWC
14	IMAGETYPE_IFF
15	IMAGETYPE_WBMP
16	IMAGETYPE_XBM
17	IMAGETYPE_ICO

IMAGETYPE_GIF	image/gif
IMAGETYPE_JPEG	image/jpeg
IMAGETYPE_PNG	image/png
IMAGETYPE_SWF	application/x-shockwave-flash
IMAGETYPE_PSD	image/psd
IMAGETYPE_BMP	image/bmp
IMAGETYPE_TIFF_II (intel byte order)	image/tiff
IMAGETYPE_TIFF_MM (motorola byte order)	image/tiff
IMAGETYPE_JPC	application/octet-stream
IMAGETYPE_JP2	image/jp2
IMAGETYPE_JPX	application/octet-stream
IMAGETYPE_JB2	application/octet-stream
IMAGETYPE_SWC	application/x-shockwave-flash
IMAGETYPE_IFF	image/iff
IMAGETYPE_WBMP	image/vnd.wap.wbmp
IMAGETYPE_XBM	image/xbm
IMAGETYPE_ICO	image/vnd.microsoft.icon
IMAGETYPE_WEBP	image/webp
*/


  static public function setDefaultConfig()
  {
    $cfg = array();
    $cfg['cfgSource'] = 'mj_File2BaseValues.php';

    $cfg['file']  = array();
    $cfg['dir']   = array();
    $cfg['meta']  = array();
    $cfg['image'] = array();
    $cfg['image']['sizes'] = array();

    $cfg['file']['naming']       = "keep";  // possible values: rand|md5|tmpname|keep|func|date
    $cfg['file']['allowedTypes'] = array("txt","pdf","jpg","jpeg","png");   // possible values: "pfd|txt" later: "jpg|jpeg|png|gif|pjpeg"
    $cfg['file']['validateFunc'] = "";      // name of a validation function to call if desired

    $cfg['dir']['storage']  = "localfs";      // possible values: localfs|s3
    $cfg['dir']['urlPath']  = "upload/";      // this can be absolute or relative - must end with /
    $cfg['dir']['basePath'] = "/ht/upload/";  // this can be absolute or relative - must end with /
    $cfg['dir']['tree']     = "none";         // none|uidfid 
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

}


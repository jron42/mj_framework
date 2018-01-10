<?php
///
// function getUploadedImagePath($imagename)
// function generateMd5FileName($tmpfilename)
// function concatFileName($imagepath, $filemd5, $type, $size, $funique)
// function generateBaseFileName($filemd5, $type, $size, $first, $funique)
// function getUploadedImagePathById($imageId,$type="none")
// function resize_image($inputFileName, $new_side, $imagetype, $checkHeight)
// function rotate_image($inputFileName, $imagetype, $direction)
// function rotate_images($imageId, $direction)
// function convert_png($inputFileName)
// function save_images($tmpfilename, $type, $caption = "", $setUser = false, $isAssociated = false)
// function delete_image($imageId)
// function delete_image2($table, $id)
/*
 * static public function getURL($name, $sizeDesc, $imgtype)
 * function getFullPathForImage($name, $base)
 * function buildFileName($name, $type, $sizeStr)
 * function resize($inputFileName, $new_side, $imagetype, $checkHeight)
 * function writeImageSet($tmpfilename, $type, $caption = "", $setUser = false, $isAssociated = false)
 * function delete($files)
 */

/*
 * Functions I need to have:
 * - calculate an upload name for the NEW image
 *   - should I support keeping original filename?
 * - get names for known image
 * - validate type actually is image
 * - generate sized images
 * - delete sized images
 * - get physical image path
 * - get image url
 * - get uploaded file
 * - resize
 * - rotate 
 */

$LINE = 0;

/**
 * base class for handling image uploads and is not intended as a full image processing utility even tho it allows for 
 * some basic processing such as cropping and rotation on a previously uploaded image. 
 * This class depends on a control array being passed in that details configuration parameters for processing.
 * This control is passed in only once
 * Images will be stored with the following naming convention:
 *   <base path|url><n-dirs><<base name>_<unique 9999>>_<size desc>.<jpg|gif|png>
 *   base name + unique are determined first time and then stored together as the final name
 *   n-dirs - come from the first 4 single chars
 * Config controls are as follows:
 *   cfg['physPath']   - physical path to the top level image directory
 *   cfg['baseURL']    - basic URL (relative or full) to top level image directory
 *   cfg['fileNaming'] - file naming convention - choices are random string, md5 hash, use tmpname or keep original 
 *                       values: "str|md5|tmpname|keep"
 *   cfg['nameLen']    - if method is rand then use this many chars for the name
 *   cfg['extensions'] - valid image types for processing - values: "jpg|jpeg|png|gif|pjpeg|tiff|bmp"
 *   cfg['sizes']      - images of each of the sizes listed will be generated when one is uploaded
 *                       each entry is formatted width x height and seperated by | (no spaces) eg: sizes = '10x10|100x100|600x600'
 *   cfg['dirDepth']   - number if directory levels to use for saving images. dir names are single chars
 *
 * The basic flow is that:
 * 1. an image is loaded in (either directly or by upload)
 * 2. $image is directly cropped, rotated, whatever..  but not yet resized.
 * 3. metafile is written if necessary (currently, only on upload).
 * 4. new original is written at (conf max) full size w/ no watermark. basically, if the user wants to revert, he can't.
 * 5. after writing original image, non-watermarked sizes are then resized to $imagetmp and written to disk 
 * 6. after writing original image, non-watermarked sizes are then resized to $imagetmp and written to disk 
 * 7. $image is watermarked, then watermarked sizes are resized to $imagetmp and written to disk 
 */

class mj_File2Image 
{
  static protected $cfg = NULL; 
  protected $_err = NULL;

  protected $mjfile       = NULL;
  protected $image        = NULL;
  protected $imagetmp     = NULL;
  protected $coreFileName = NULL; // basic filename part for use in building all other names
  protected $coreFileExt  = NULL; // basic filename part for use in building all other names

  protected $origFileName = NULL;
  protected $tmpFileName  = NULL;

  protected $width  = 0;    // initial image width
  protected $height = 0;    // initial image height
  protected $gdtype = NULL; // EXIF image type as retreivec from EXIF data
  protected $mime   = NULL; // actual mime type retrieved from the image
  protected $type   = NULL; // type as something we can deal with easily in code png|jpg|bmp|gif

  ////////////////////////////////////////////////////////////////////////////////
  /// configuration arrays
  ////////////////////////////////////////////////////////////////////////////////

  /** 
   * mapping of EXIF types to known extensions
   */
  static protected $EXIF_TYPES = array(
    IMAGETYPE_GIF     => 'gif',
    IMAGETYPE_JPEG    => 'jpg',
    IMAGETYPE_PNG     => 'png',
  //IMAGETYPE_SWF     => 'swf',
  //IMAGETYPE_PSD     => 'psd',
    IMAGETYPE_BMP     => 'bmp'
  //IMAGETYPE_TIFF_II => 'tif', // (intel byte order)
  //IMAGETYPE_TIFF_MM => 'tif'  // (motorola byte order)
  //IMAGETYPE_JPC     => 'bmp',
  //IMAGETYPE_JP2     => 'bmp',
  //IMAGETYPE_JPX     => 'bmp',
  //IMAGETYPE_JB2     => 'bmp',
  //IMAGETYPE_SWC     => 'bmp',
  //IMAGETYPE_IFF     => 'bmp',
  //IMAGETYPE_WBMP    => 'bmp',
  //IMAGETYPE_XBM     => 'bmp',
  //IMAGETYPE_ICO     => 'ico'
  );

  /**
   * array of function pointers to handle reading of images
   * $im = $this->$readImageFuncs['jpg']();
   */
  protected static $readImageFuncs = array(
    'jpg' => 'imagecreatefromjpeg',
    'gif' => 'imagecreatefromgif',
    'png' => 'imagecreatefrompng',
    'bmp' => 'imagecreatefromwbmp'
  );

  protected static $writeImageFuncs = array(
    'jpg' => 'imagejpeg',
    'gif' => 'imagegif',
    'png' => 'imagepng',
    'bmp' => 'imagejpg'
  );

  ////////////////////////////////////////////////////////////////////////////////
  /// class functions
  ////////////////////////////////////////////////////////////////////////////////

  function __construct($cfg=NULL, $mjfile=NULL)
  {
    $this->cfg    = (isset($cfg) && is_array($cfg)) ? $cfg : self::setDefaultConfig();
    $this->mjfile = $mjfile;
    $this->meta   = $mjfile->getMeta();

    //parent::__construct();
  }

  function setDefaultConfig() { return array(); }

  ////////////////////////////////////////////////////////////////////////////////
  /// image processing functions
  ////////////////////////////////////////////////////////////////////////////////

  /**
   * 
   */
  function destroyWorkingImageData()
  {
    imagedestroy($this->image);
    $this->image = NULL;

    $this->coreFileName = NULL; // basic filename part for use in building all other names
    $this->origFileName = NULL;
    $this->tmpFileName  = NULL;
 
    $this->width  = 0;    // initial image width
    $this->height = 0;    // initial image height
    $this->gdtype = NULL; // EXIF image type as retreivec from EXIF data
    $this->mime   = NULL; // actual mime type retrieved from the image
    $this->type   = NULL; // type as something we can deal with easily in code png|jpg|bmp|gif
  }

  /**
   * load the given file
   * 
   */
  function readImage($filename)
  {
    if (($info = getimagesize($filename)) === false) return false;
    $this->width  = $info[0]; 
    $this->height = $info[1];
    $this->gdtype = $info[2];
    $this->attr   = $info[3];
    $this->mime   = $info['mime'];
    $this->type   = mj_File2::$mimeToExtension[$this->mime];

    $this->image = NULL;
    if (isset(self::$readImageFuncs[$this->type]) && function_exists(self::$readImageFuncs[$this->type]))
    {
      $func = self::$readImageFuncs[$this->type];
      $this->image = $func($filename);
    }
    else
    {
      $this->_err = 'image.readFailed';
      mjlog(ERROR,__METHOD__,"Failed to load image[$filename] mime[".$this->mime."]",__LINE__);
      return false;
    }
    return true;
  }

  /**
   * resize an image to the given width and height 
   *
   * @param int $width target width
   * @param int $height target height
   * @param bool $keepAspect maintain aspect ratio or force to new dimensions
   * @param bool $preferHeight if keeping aspect ratio, which dimension to prefer, true is height, false is width

   * @return bool t/f success or failure
   */
  function resize($newWidth, $newHeight=0, $keepAspect=true, $preferHeight=true)
  {
    mjlog(DEBUG,__METHOD__,"newWidth[$newWidth] newHeight[$newHeight] keepAspect[$keepAspect] preferHeight[$preferHeight]",__LINE__);

    if (!$newHeight) $newHeight = $newWidth;

    $w = $this->width;  // $imagedata[0];
    $h = $this->height; // $imagedata[1];

    if ($keepAspect)
    {
      if (($h - $newHeight) > ($w - $newWidth) && $preferHeight) 
      {
        $new_w = ($newWidth / $h) * $w;
        $new_h = $newHeight;
      } else {
        $new_h = ($newHeight / $w) * $h;
        $new_w = $newWidth;
      }
      mjlog(DEBUG,__METHOD__,"keepAspect: new_w[$new_w] new_h[$new_h]",__LINE__);
    }
    else // force to new size, loose aspect
    {
      $new_h = $newHeight;
      $new_w = $newWidth;
      mjlog(DEBUG,__METHOD__,"force dimensions: new_w[$new_w] new_h[$new_h]",__LINE__);
    }

    if ($this->imagetmp !== NULL) 
    {
      imagedestroy($this->imagetmp);
      $this->imagetmp = NULL;
    }
    // if imagetmp exists then we have already done something with this image. so keep it and 
    $image = $this->imagetmp ? $this->imagetmp : $this->image;
    $this->imagetmp = ImageCreateTrueColor($new_w, $new_h);
    if (!imagecopyResampled($this->imagetmp, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h))
    {
      $this->_err = "image.resizeFailed";
      mjlog(ERROR,__METHOD__,"Error resizing image",__LINE__);
    }
  }

  /**
   *
   */
  function rotateImage($filename,$degrees)
  {
    if ($this->readImage($filename))
    {
      $this->image = imagerotate($this->image, $degrees, 0);
      file_put_contents($filename,$this->image);
      $this->writeImageSet();
    }
  }
  
  /**
   * 
   */
  function addWatermark()
  {
    mjlog(DEBUG,__METHOD__,"adding a watermark",__LINE__);

/*
function watermarkImage ($SourceFile, $WaterMarkText, $DestinationFile) { 
   list($width, $height) = getimagesize($SourceFile);
   $image_p = imagecreatetruecolor($width, $height);
   $image = imagecreatefromjpeg($SourceFile);
   imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height); 
   $black = imagecolorallocate($image_p, 0, 0, 0);
   $font = 'arial.ttf';
   $font_size = 10; 
   imagettftext($image_p, $font_size, 0, 10, 20, $black, $font, $WaterMarkText);
   if ($DestinationFile<>'') {
      imagejpeg ($image_p, $DestinationFile, 100); 
   } else {
      header('Content-Type: image/jpeg');
      imagejpeg($image_p, null, 100);
   };
   imagedestroy($image); 
   imagedestroy($image_p); 
};

$SourceFile = '/home/user/www/images/image1.jpg';
$DestinationFile = '/home/user/www/images/image1-watermark.jpg'; 
$WaterMarkText = 'Copyright phpJabbers.com';
watermarkImage ($SourceFile, $WaterMarkText, $DestinationFile);

-------------

$filename = 'life.jpg';
$watermark = 'copy.gif';
$DestinationFile = 'marked.jpg';
$dest = imagecreatefromjpeg($filename);
$src = imagecreatefromgif($watermark);
 
list($width, $height, $type, $attr)=getimagesize($filename);
 
list($markwidth, $markheight, $type1, $attr1)=getimagesize($watermark);
 
// Copy and merge
$opacity = 30;
imagecopymerge($dest, $src, ($width-$markwidth)>>1, ($height-$markheight)>>1, 0, 0, $markwidth, $markheight, $opacity);
 
if ($DestinationFile<>'') {
      imagejpeg ($dest, $DestinationFile, 100); 
   } 
      
// Output and free from memory
header('Content-Type: image/jpeg');
imagegif($dest);
 
imagedestroy($dest);
imagedestroy($src);

*/

  }

  /**
   * 
   */
  function writeImage($filename, $quality=75) // default image quality for GD jpeg
  {
    mjlog(DEBUG,__METHOD__,"filename[$filename] type[".$this->type."] quality[$quality] writeImageFuncs[". self::$writeImageFuncs[$this->type] ."]",__LINE__);

    if ($this->imagetmp) $image = &$this->imagetmp;
    else                 $image = &$this->image;

    $success = true;
    $func    = self::$writeImageFuncs[$this->type];
    if ($this->type == "jpg") $success = $func($image,$filename,$quality);
    else                      $success = $func($image,$filename);

    if (!$success)
    {
      $this->_err = 'image.writeFailed';
      mjlog(DEBUG,__METHOD__,"Failed to write image[$filename] mime[".$this->mime."]",__LINE__);
      return false;
    }
    return true;
  }

  /**
   * The basic flow is that:
   * 1. an image is loaded in (either directly or by upload)
   * 2. $image is directly cropped, rotated, whatever..  but not yet resized.
   * 3. metafile is written if necessary (currently, only on upload).
   * 4. new original is written at (conf max) full size w/ no watermark. basically, if the user wants to revert, he can't.
   * 5. after writing original image, non-watermarked sizes are then resized to $imagetmp and written to disk 
   * 6. after writing original image, non-watermarked sizes are then resized to $imagetmp and written to disk 
   * 7. $image is watermarked, then watermarked sizes are resized to $imagetmp and written to disk 
   */
  function writeImageSet($newFileName=NULL, $overwrite=false)
  {
    mjlog(DEBUG,__METHOD__,"newFileName[$newFileName] cfg: ". print_r($this->cfg,true),__LINE__);

    list($this->coreFileName,$this->coreFileExt) = mj_File2::splitNameAndExtension($newFileName);
    mjlog(DEBUG,__METHOD__,"coreFileName[".$this->coreFileName."] coreFileExt[".$this->coreFileExt."]",__LINE__);

    if ($this->imagetmp !== NULL) 
    {
      // maybe unnecessary here because resize does a check, but WTF, do it anyway to be sure.
      imagedestroy($this->imagetmp);
      $this->imagetmp = NULL;
    }
    if ($this->coreFileName === NULL) 
    {
      mjlog(ERROR,__METHOD__,"No output filename given",__LINE__);
      $this->_err = "image.invalidOutputName";
      return false;
    }
    
    // store original at a max size with no watermark and no exif data. This copy will be used for any future editing and cropping
    // maybe don't need this next section
    /*
    $fname  = $this->buildFileName($this->coreFileName); // $name, $sizeStr, $asURL=false
    $exists = file_exists($fname);
    if (!$exists || ($exists && $overwrite))
    {
      $side = self::$cfg['original']['maxSide'];
      if ($this->width > $side || $this->height > $side)
        $this->resize(self::$cfg['maxOrigSize']);
      $this->writeImage($fname,95); // if jpg then save original at high quality.
    }
    */
    
    // loop thru twice. First pass catches images with no watermark. Then base image is watermarked and the loop processed
    // again for the watermarked versions.
    //
    foreach ($this->cfg['image']['sizes'] as $szname => $szdata)
    {
      mjlog(DEBUG,__METHOD__,"processing name[$szname] szdata[".print_r($szdata,true)."]",__LINE__);
      //if ($szdata['watermark'] == 'false')
      //{
        //mjlog(DEBUG,__METHOD__,"watermark == false",__LINE__);
        $width  = $szdata['width'];
        $height = $szdata['height'];
        if ($this->width > $width || $this->height > $height)
          $this->resize($width, $height, $szdata['maintainAspect']);
        $fname = $this->coreFileName .'.rsz.'. $szname .'.'. $this->coreFileExt; // $name, $sizeStr, $asURL=false
        mjlog(DEBUG,__METHOD__,"calling this->writeImage: fname[$fname]",__LINE__);
        $this->writeImage($fname,$szdata['quality']); // if jpg then save original at high quality.
      //}
    }

    // now add a watermark and process those requiring it.
    //
/*
    if (self::$cfg['watermark'] != '')
      $this->addWatermark();
    foreach (self::$cfg['sizes'] as $szname => $szdata)
    {
      mjlog(DEBUG,__METHOD__,"processing name[$szname] szdata[".print_r($szdata,true)."]",__LINE__);
      if ($szdata['watermark'] != '' && $szdata['watermark'] != 'false')
      {
        mjlog(DEBUG,__METHOD__,"watermark != ''",__LINE__);
        $width  = $szdata['w'];
        $height = $szdata['h'];
        if ($this->width > $width || $this->height > $height)
          $this->resize($width, $height, $szdata['keepAspect']);
        $fname = $this->buildFileName($this->coreFileName,$szname); // ($name, $sizeStr, $asURL=false
        $this->writeImage($fname,self::$cfg['quality']); // if jpg then save original at high quality.
      }
    }
*/
    return true;
  }

  /**
   * 
   */
  static function isValidImage($fname)
  {
    mjlog(DEBUG,__METHOD__,"fname[$fname]",__LINE__);
    if (($info = getimagesize($fname)) === false) 
    {
      mjlog(NOTICE,__METHOD__,"Failed to get getimagesize data: filefname[$fname]",__LINE__);
      return false;
    }
    mjlog(DEBUG,__METHOD__,"getimagesize result: ". print_r($info,true),__LINE__);

    $width  = $info[0]; 
    $height = $info[1];
    $gdtype = $info[2];
    $attr   = $info[3];
    $mime   = $info['mime'];

    //list($width, $height, $gdtype, $attr, $fee, $foo, $mime) = getimagesize($fname);
    //list($width, $height, $type, $attr, ) = getimagesize($fname);

    if ($width > 0 && $height > 0)
    {
      if ($gdtype > 0 && $mime != "" && isset(self::$EXIF_TYPES[$gdtype]) && isset(mj_File2::$mimeToExtension[$mime])) 
      {
        mjlog(DEBUG,__METHOD__,"PASSED mime test: mime[$mime]",__LINE__);
        return true;
      }
      else mjlog(NOTICE,__METHOD__,"FAILED mime test: mime[$mime]",__LINE__);
    }
    else
      mjlog(NOTICE,__METHOD__,"failed w h test",__LINE__);
    return false;
  }

  /**
   * 
   */
  function getExifData($filename)
  {
    $exobj = new mj_File2Exif($filename);
    $exif = $exobj->getExifData();
    return $exif;
/*
    $this->exif = $exobj->getExifData();
    if ($this->exif === false)
    {
      if (isset($this->meta['exif'])) unset($this->meta['exif']);
    }
    else
      $this->meta['exif'] = $this->exif;
    unset($exobj);
    return $this->exif;
*/
  }

  /**
   * 
   */
  function wipeExifData()
  {
    // can't figure out what to do here.
  }

  /**
   * 
   */
  protected function fetchImageTypeFromMetafile($fname)
  {
    $filename = self::buildMetaFileName($fname);
    if ($fp = fopen($filename, "r")) 
    {
      if (($buf = fgets($fp, 4096)) !== false) 
      {
        list($name, $value) = explode('=',$buf); 
	$val = trim($value);
	return $val == '' ? false : $val;
      }
      fclose($fp);
    }
    return false;
  }

  /**
   * main function for processing an image upload
   * processing steps
   * - check name of original file for != ""
   * - check size > 0 and < max upload size
   * - check type is supported type
   * - check file extension for supportedd type
   * - get image with and height from image and validate > 0
   * - check size > 0 and < max upload size
   * - generate new filename
   * - load file in to IMa
   * - remove EXIF data
   * - write EXIF data to meta file
q
   * - foreach size.. generate sized files and save them
   * - delete upload file
   *
   * @params string $upname name of the uploaded images form field
   * @params mixed $xtra associative array of data to write to the meta file, things such as userID or groupID are appropriate
   *
   * @return mj_Image|boolean false if error, pointer to an mj_Image object on success
   */
  public function processImage($fname)
  {
    $LINE = 0;
    $log  = "";
    
    mjlog(DEBUG,__METHOD__,"\n\n**************************** start process image*\n\n\n",__LINE__);
    mjlog(DEBUG,__METHOD__,"fname[$fname] config: ".print_r($this->cfg,true),__LINE__);

    try 
    {
      //$fname = $this->mjfile->getFullPathAndFileName();
      mjlog(DEBUG,__METHOD__,"fname from getFullPathAndFileName()[$fname]",__LINE__);
      if (!file_exists($fname)) { $LINE = __LINE__; throw new Exception("Image file does not exist. filename[$fname]"); }
      if (!self::isValidImage($fname)) { $LINE = __LINE__; throw new Exception("Invalid Image Type"); }

      // we want to create the new name and lock it in as atomically as possible
 
      //$this = new mj_Image;
      $exif = $this->getExifData($fname);
      mjlog(DEBUG,__METHOD__,"exif data: ".print_r($exif,true),__LINE__);

      if (!$this->readImage($fname))
        { $LINE = __LINE__; $log = "file[$fname]"; throw new Exception("image.errorReadingUploadFile"); }

      if (!$this->writeImageSet($fname))
        { $LINE = __LINE__; throw new Exception("image.failedToWriteImage"); }
      
      $meta = $this->mjfile->getMeta();
      $meta['exif'] = $exif;
      $this->mjfile->setMeta($meta);
      //mjlog(DEBUG,__METHOD__,"about to write new metadata: [$fname] meta: ".print_r($meta,true),__LINE__);
      //if (($metaname = $this->mjfile->writeMetaData($meta)) === false) 
      //  { $LINE = __LINE__; throw new Exception("image.failedToCreateMetafile"); }
      //$this->meta = $meta;

      mjlog(DEBUG,__METHOD__,"Image processing complete, filename[$fname] type[".$this->type."] mime[".$this->mime."]",__LINE__);
      $rez = array($fname, $this->type);
      $this->destroyWorkingImageData();
      mjlog(DEBUG,__METHOD__,"\n\n**************************** SUCCESS: finished  process image*\n\n\n",__LINE__);
      return $rez;
    }
    catch (Exception $e)
    {
      $err = $e->getMessage();
      mjlog(NOTICE,__METHOD__,"Exception caught: ".$e->getMessage().' '.$log,$LINE);
      mjlog(DEBUG,__METHOD__,"\n\n**************************** Exception caught: end process image*\n\n\n",__LINE__);
      return false;
    }
    mjlog(ERROR,__METHOD__,"Should not have gotten here",__LINE__);
    return false; // should never get here!
  }

  /**
   * delete the image files related to the current object. 
   * if leaveOriginal == true the original file will NOT be deleted.
   */
  public function deleteImageFiles($leaveOriginal=true)
  {
    mjlog(DEBUG,__METHOD__,"upname[$upname] metaname[$metaname]",__LINE__);
    if (file_exists($upname)) unlink($upname);

    if ($metaname != '' && file_exists($metaname)) unlink($metaname);

    if (is_array($files))
    {
    }
    //unlink($path .".ths.". $type);
  }

}


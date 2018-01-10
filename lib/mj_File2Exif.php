<?php

/*
Latitude and longitude formats:  http://www.geomidpoint.com/latlon.html
*/

class mj_File2Exif
{
  protected $exif = NULL;

  /**
   *
   */
  function __construct($fname)
  {
    $this->exif = @exif_read_data($fname, $sections=NULL, $arrays = true);

    if ($this->exif === false) 
    {
      mjlog(DEBUG,__METHOD__,"No exif data available ---",__LINE__);
      return;
    }

    if (isset($this->exif["GPS"]) && isset($this->exif["GPS"]['GPSLatitude']) && isset($this->exif["GPS"]['GPSLongitude']))
    {
      $this->exif["GPS"]['Latitude']     = false;
      $this->exif["GPS"]['Longitude']    = false;
      $this->exif["GPS"]['LatLonString'] = false;

      // Precede South latitudes and West longitudes with a minus sign

      if (($lat = $this->_getGps($this->exif["GPS"]["GPSLatitude"])) !== false)
        $this->exif["GPS"]['Latitude'] = ($this->exif["GPS"]["GPSLatitudeRef"] == 'S' ? '-' : '') . $lat;
        
      if (($lon = $this->_getGps($this->exif["GPS"]["GPSLongitude"])) !== false)
        $this->exif["GPS"]['Longitude'] = ($this->exif["GPS"]["GPSLongitudeRef"] == 'W' ? '-' : '') . $lon;
        
      $this->exif["GPS"]['LatLonString'] = $this->exif["GPS"]['Latitude'] .', '. $this->exif["GPS"]['Longitude'];
    }
    else $this->exif['GPS'] = false;
  }
  
  public function getExifData()
  {
    return $this->exif;
  }

  public function getLatitude()  { return $this->exif['GPS'] === false ? false : $this->exif["GPS"]['Latitude']; }
  public function getLongitude() { return $this->exif['GPS'] === false ? false : $this->exif["GPS"]['Longitude']; }
  public function getLatLon()    { return $this->exif['GPS'] === false ? false : $this->exif["GPS"]['LatLonString']; }

  ////////////////////////////////////////////////////////////////////////////////
  
  private function _getGps($exifCoord)
  {
    $degrees = count($exifCoord) > 0 ? $this->_gps2Num($exifCoord[0]) : 0.0;
    $minutes = count($exifCoord) > 1 ? $this->_gps2Num($exifCoord[1]) : 0.0;
    $seconds = count($exifCoord) > 2 ? $this->_gps2Num($exifCoord[2]) : 0.0;
  
    if (($degrees > -0.0001 && $degrees < 0.0001) && ($minutes > -0.0001 && $minutes < 0.0001) && ($seconds > -0.0001 && $seconds < 0.0001)) return false;

    //so: GPSLatitude[0] + GPSLatitude[1]/60 + GPSLatitude[2]/3600
  
    $cminutes = $minutes > 0.0 ? ($minutes /   60) : 0.0;
    $cseconds = $seconds > 0.0 ? ($seconds / 3600) : 0.0;
   
    mjlog(DEBUG,__METHOD__,
        "getGps: degrees[$degrees] minutes[$cminutes] seconds[$cseconds] scalc[".($degrees + $cminutes + $cseconds)."] "
        ."calc[".($degrees + ($minutes / 60) + ($seconds / 3600))."]"
        ,__LINE__);

    return $degrees + ($minutes / 60) + ($seconds / 3600);
  }

  private function _gps2Num($coordPart)
  {
    $parts = explode('/', $coordPart);
  
    if(count($parts) <= 0) return 0;
    if(count($parts) == 1) return $parts[0];
  
    return floatval($parts[0]) / floatval($parts[1]);
  }

}
  
//////////////////////////////////////////////////////////////////////////////// TESTING
/*
# test from the commandline simply with the following command
# and changing the 0 to a 1 in the below if statement.

php mj_File2Exif.php

*/
if (0)
{
  $exif    = false;
//$exifObj = new mj_File2Exif('/Users/jmorgan/Sites/AppBackends/exif_test/crabs_dc.jpg');
  $exifObj = new mj_File2Exif('/Users/jmorgan/Pictures/tmp/DSC_1229.JPG');
  
  echo "\n\n============ EXIF DATA START ==============\n";
  if (($exif = $exifObj->getExifData()) === false)
  {
    echo "no exif data found\n";
    exit();
  }
  
  print_r($exif);
  echo "\n============ EXIF DATA END ==============\n\n";

  if (!isset($exif["GPS"]))
  {
    echo "no GPS data found\n\n";
    exit();
  }

  $lat    = $exifObj->getLatitude();
  $lon    = $exifObj->getLongitude();
  $latlon = $exifObj->getLatLon();

  echo "\nlat[$lat] lon[$lon] latlon[$latlon]\n\n";

  exit();
}
/* example, raw EXIF data
Array
(
    [FILE] => Array
        (
            [FileName] => crabs_dc.jpg
            [FileDateTime] => 1487164383
            [FileSize] => 2517266
            [FileType] => 2
            [MimeType] => image/jpeg
            [SectionsFound] => ANY_TAG, IFD0, THUMBNAIL, EXIF, GPS
        )

    [COMPUTED] => Array
        (
            [html] => width="3264" height="2448"
            [Height] => 2448
            [Width] => 3264
            [IsColor] => 1
            [ByteOrderMotorola] => 1
            [ApertureFNumber] => f/2.4
            [Thumbnail.FileType] => 2
            [Thumbnail.MimeType] => image/jpeg
        )

    [IFD0] => Array
        (
            [Make] => Apple
            [Model] => iPhone 5
            [Orientation] => 1
            [XResolution] => 72/1
            [YResolution] => 72/1
            [ResolutionUnit] => 2
            [Software] => 7.0.6
            [DateTime] => 2014:05:28 18:40:09
            [YCbCrPositioning] => 1
            [Exif_IFD_Pointer] => 204
            [GPS_IFD_Pointer] => 926
        )

    [THUMBNAIL] => Array
        (
            [Compression] => 6
            [XResolution] => 72/1
            [YResolution] => 72/1
            [ResolutionUnit] => 2
            [JPEGInterchangeFormat] => 1222
            [JPEGInterchangeFormatLength] => 13141
        )

    [EXIF] => Array
        (
            [ExposureTime] => 1/188
            [FNumber] => 12/5
            [ExposureProgram] => 2
            [ISOSpeedRatings] => 50
            [ExifVersion] => 0221
            [DateTimeOriginal] => 2014:05:28 18:40:09
            [DateTimeDigitized] => 2014:05:28 18:40:09
            [ComponentsConfiguration] => 
            [ShutterSpeedValue] => 5289/700
            [ApertureValue] => 4845/1918
            [BrightnessValue] => 3307/540
            [MeteringMode] => 3
            [Flash] => 24
            [FocalLength] => 103/25
            [MakerNote] => Apple iOS
            [SubSecTimeOriginal] => 379
            [SubSecTimeDigitized] => 379
            [FlashPixVersion] => 0100
            [ColorSpace] => 1
            [ExifImageWidth] => 3264
            [ExifImageLength] => 2448
            [SensingMethod] => 2
            [SceneType] => 
            [ExposureMode] => 0
            [WhiteBalance] => 0
            [FocalLengthIn35mmFilm] => 33
            [SceneCaptureType] => 0
            [UndefinedTag:0xA432] => Array
                (
                    [0] => 103/25
                    [1] => 103/25
                    [2] => 12/5
                    [3] => 12/5
                )

            [UndefinedTag:0xA433] => Apple
            [UndefinedTag:0xA434] => iPhone 5 back camera 4.12mm f/2.4
        )

    [GPS] => Array
        (
            [GPSLatitudeRef] => N
            [GPSLatitude] => Array
                (
                    [0] => 38/1
                    [1] => 52/1
                    [2] => 5222/100
                )

            [GPSLongitudeRef] => W
            [GPSLongitude] => Array
                (
                    [0] => 77/1
                    [1] => 1/1
                    [2] => 4176/100
                )

            [GPSAltitudeRef] => 
            [GPSAltitude] => 9153/1726
            [GPSTimeStamp] => Array
                (
                    [0] => 22/1
                    [1] => 40/1
                    [2] => 907/100
                )

            [GPSImgDirectionRef] => T
            [GPSImgDirection] => 19171/86
        )

)
*/

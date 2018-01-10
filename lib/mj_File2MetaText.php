<?php

/**
 * This is a helper class that writes metadata to disk files - NON FUNCTIONAL
 */
class mj_FileMetaText
{
  public function saveMeta($id,$data) {}
  public function fetchMeta($id) {}
  public function deleteMeta($id) {}


  /**
   * write the metafile on upload. This file will be used for filename checking 
   * It can be used to store additional information 
   *
   * @param string $basename unique name for the file set to be written
   * @param string $exifdata if there was exif data with the original image it can be stored in the metafile so it won't get lost
   * @param string $upname original upload filename
   * @param string $extrameta array of application information passed in to be stored with the image meta data 
   *
   * @return string|false false on failure, # of bytes written on success
   */
  function writeMetaFile($basename,$exifdata,$upname,$extrameta=NULL)
  {
    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"basename[$basename] upname[$upname]",__LINE__);
    if ($this->image === NULL || $basename == "" || $this->type === NULL)
    {
      mjlog(WARNING,__CLASS__."::".__FUNCTION__,"No image loaded or name of file to write",__LINE__);
      return false;
    }
    if (($filename = trim(self::buildMetaFileName($basename))) == "")
    {
      mjlog(WARNING,__CLASS__."::".__FUNCTION__,"unable to generate metafile name",__LINE__);
      return false;
    }

    $data = "imageType = ".$this->type."\n"
          . "baseName = $basename\n"
          . "uploadName = $upname\n"
          . "uploadDate = ". date("F j, Y, g:i a") ."\n";
    if ($extrameta && is_array($extrameta))
    {
      foreach ($extrameta as $key => $value)
        $data .= "extraMeta.$key = $value\n";
    }
    $data .= 'exif = '. serialize($exifdata) ."\n";

    mjlog(DEBUG,__CLASS__."::".__FUNCTION__,"filename[$filename]",__LINE__);
    return file_put_contents($filename, $data);
  }




}





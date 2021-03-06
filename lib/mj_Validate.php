<?php

/*
Greg TODO:

- change "config" and "v_arr" to "config" and "data"
- for each entry in the namedTestMappings array create a function to do the actual test
  - look at the function testInteger() below for an example..

- int and float should support range checking (I added some example config)
- text fields need to support min and max lengths
- date and time fields need to support format specification

Logic should be:

- for each config field
  - get data value to test ( basically, this should just be $data[$namedFieldFromConfig] )
  - test data value based on configuration

*/


/**
 * Main class for validating form data input meets a given configuration
 */
class mj_Validate
{
  static public $messages_s = array();

  static protected $namedTestMappings = array(
    'integer'           => 'testInteger',
    'float'             => 'testFloat',
    'email'             => 'testEmail',
    'length'            => 'testLength',
    'alphaOnly'         => 'testAlpha',
    'alphanumOnly'      => 'testAlphanum',
    'alphanumPunctOnly' => 'testAlphanumPunct',
    'time'              => 'testTime',
    'date'              => 'testDate',
    'dateTime'          => 'testDateTime',
  );

  /**
   * Function takes an associate array, $data, as a parameter, iterates through $data
   * and uses $config to validate it
   */
  static public function validate($data, $config)
  {
    $result = true;
    foreach ($config as $name => $field)
    {
      foreach ($field["Validation"] as $index)
      {
        // If $data is missing an field it should have, will return all error messages associated with that field
        if (!self::tests($data[$name], $index)) { $result = false; }
      }
    }
    return $result;
  }

  /**
   * Function that calls various validation tests
   */
  static public function tests($value_to_test, $field)
  {
    $testType = $field['TestType'];
    $test     = $field['Test'];

    if (!isset($testType) || $testType == 'Predefined')
    {
      if (array_key_exists($test, self::$namedTestMappings))
      {
        $func = self::$namedTestMappings[$test];
        return self::$func($value_to_test, $field);
      }
      else echo "[$test] is not a predefined test";   //mjlog(ERROR,__METHOD__,"[$test] is not a predefined test",__LINE__);
    }
    elseif ($testType == 'Function')
    {
      call_user_func($test, $value_to_test, $this);
    }
    elseif ($testType == 'Regex')
    {
      // Not sure if this is how you check regex or not
      preg_match($test, $value_to_test, $matches);
      return (array_search($value_to_test, $matches[0]));
    }
    elseif ($testType == 'SomethingElse')
    {
      //insert other validation methods
      return true;
    }
    else echo "Undefined TestType: [$testType]";     //mjlog(ERROR,__METHOD__,"Undefined TestType: [$testType]",__LINE__);

  }

  /**
   *  Accessor function returns array of error messages
   */
  static function getMessages() { return self::$messages_s; }


  ////////////////////////////////////////////////////////////////////////////////
  // indivudual validation methods
  ////////////////////////////////////////////////////////////////////////////////

  /**
   *
   */
  static public function isRequiredAndPresent($fieldValue,$fieldConfig)
  {
    if (  isset($fieldConfig['Required'])
       && filter_var($fieldConfig['Required'], FILTER_VALIDATE_BOOLEAN) === true
       && trim($fieldValue) != '') return true;
    return false;
  }

  /**
   * Tests if variable is a integer and if its value falls within a given range
   */
  static public function testInteger($fieldValue,$fieldConfig)
  {
    try
    {
      if (!self::isRequiredAndPresent($fieldValue,$fieldConfig)) throw new Exception("Required field");
      //if (!is_numeric($fieldValue))                              throw new Exception("Value not numeric");
      if (!is_int($fieldValue))                                  throw new Exception("Value not of type integer");

      if (isset($fieldConfig['Min']) && ((int)$fieldValue < (int)$fieldConfig['Min'])) throw new Exception("Value must be >= than ". (int)$fieldConfig['Min']);
      if (isset($fieldConfig["Max"]) && ((int)$fieldValue > (int)$fieldConfig["Max"])) throw new Exception("Value must be <= than ". (int)$fieldConfig['Max']);
    }
    catch (Exception $e)
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests if variable is a float and if its value falls within a given range
   */
  static public function testFloat($fieldValue,$fieldConfig)
  {
    try
    {
      if (!self::isRequiredAndPresent($fieldValue,$fieldConfig)) throw new Exception("Required field");
      //if (!is_numeric($fieldValue))                              throw new Exception("Value not numeric");
      if (!is_float($fieldValue))                                throw new Exception("Value not of type float");

      if (isset($fieldConfig['Min']) && ((float)$fieldValue < (float)$fieldConfig['Min'])) throw new Exception("Value must be >= than ". (float)$fieldConfig['Min']);
      if (isset($fieldConfig['Max']) && ((float)$fieldValue > (float)$fieldConfig['Max'])) throw new Exception("Value must be <= than ". (float)$fieldConfig['Max']);
    }
    catch (Exception $e)
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests if string lengths falls within given range
   */
  static public function testLength($fieldValue,$fieldConfig)
  {
    try
    {
      //if (!self::isRequiredAndPresent($fieldValue,$fieldConfig)) throw new Exception("Required field");
      if (!is_string($fieldValue))                               throw new Exception("Value not of type string");

      if (isset($fieldConfig["MinLen"]) && (strlen($fieldValue) < (int)$fieldConfig["MinLen"])) throw new Exception("Length must be >= than ". (int)$fieldConfig['MinLen']);
      if (isset($fieldConfig["MaxLen"]) && (strlen($fieldValue) > (int)$fieldConfig["MaxLen"])) throw new Exception("Length must be <= than ". (int)$fieldConfig['MaxLen']);
    }
    catch (Exception $e)
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   *  Tests that string meets valid email format
   */
  static public function testEmail($fieldValue,$fieldConfig)
  {
  	try
  	{
      if (!self::isRequiredAndPresent($fieldValue,$fieldConfig)) throw new Exception("Required field");
      if (!filter_var($fieldValue, FILTER_VALIDATE_EMAIL))       throw new Exception("Not valid email.");
  	}
	  catch (Exception $e)
	  {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests that string contains only alphabet characters
   */
  static public function testAlpha($fieldValue,$fieldConfig)
  {
    if (!ctype_alpha($fieldValue))
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests that string contains only alphabet or numeric characters
   */
  static public function testAlphanum($fieldValue,$fieldConfig)
  {
    if (!ctype_alnum($fieldValue))
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests that string contains only alphabet, numeric or punctuation characters
   */
  static public function testAlphanumPunct($fieldValue,$fieldConfig)
  {
    if (!ctype_print($fieldValue))
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests that time formated as hh/mm/ss where hours are 00-24
   */
  static public function testTime($fieldValue,$fieldConfig)
  {
    $time = @DateTime::createFromFormat('H:i:s', $fieldValue );
    if (!($time && $time->format('H:i:s') == $fieldValue))
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests that date is formated as mm/dd/yyyy
   */
  static public function testDate($fieldValue,$fieldConfig)
  {
    $date = @DateTime::createFromFormat('m/d/Y', $fieldValue );
    if (!($date && $date->format('m/d/Y') == $fieldValue))
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }

  /**
   * Tests that date and time are formated as mm/dd/yyyy hh/mm/ss where hours are 00-24
   */
  static public function testDateTime($fieldValue,$fieldConfig)
  {
    $dateTime = @DateTime::createFromFormat('m/d/Y H:i:s', $fieldValue );
    if (!($dateTime && ($dateTime->format('m/d/Y H:i:s') == $fieldValue)))
    {
      self::$messages_s[] = array($fieldValue => $fieldConfig["Msg"]);
      return false;
    }
    return true;
  }
}

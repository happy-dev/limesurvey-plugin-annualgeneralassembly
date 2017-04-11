<?php

class Utils {
  // Returns a boolean telling if the $haystack starts with the $needle
  public static function startsWith($needle, $haystack) {
   $length = strlen($needle);
   return (substr($haystack, 0, $length) === $needle);
  }


  // Returns a boolean telling if the $needle starts by a value of the $haystack
  public static function startsByOneOfThese($needle, $haystack) {
    foreach ($haystack as $string) {
      if (strpos($needle, $string) !== FALSE) {
        return true;
      }
    }
    return false;
  }


  // Returns a boolean telling if the string is null or empty
  public static function nullOrEmpty($x) {
    return (!isset($x) || trim($x) === '');
  }
}

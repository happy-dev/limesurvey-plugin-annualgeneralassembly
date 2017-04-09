<?php

class Utils {
  public static function startsWith($needle, $haystack) {
   $length = strlen($needle);
   return (substr($haystack, 0, $length) === $needle);
  }
}

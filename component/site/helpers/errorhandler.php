<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

class ErrorHandler {
   
   private static $errorArray = array();
   private static $warningArray = array();
   
   public static function registerError( $errorTransHandle ) {
      self::$errorArray[] = $errorTransHandle;
   }
   
   public static function clearErrors() {
      self::$errorArray = array();
   }
   
   public static function getErrors() {
      return self::$errorArray;
   }
   
   public static function hasErrors() {
      return count( self::$errorArray ) > 0;
   }
   
   public static function registerWarning( $warn ) {
       self::$warningArray[] = $warn;
   }
   
   public static function clearWarnings() {
       self::$warningArray = array();
   }
   
   public static function getWarnings() {
       return self::$warningArray;
   }
   
   public static function hasWarnings() {
       return count( self::$warningArray ) > 0;
   }
   
   public static function hasMessages() {
       return self::hasWarnings() || self::hasErrors();
   }
   
   public static function getMessages() {
       return array_merge( self::$warningArray, self::$errorArray );
   }
   
}

?>

<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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

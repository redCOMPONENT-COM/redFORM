<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

class Query {
     
    private $mString;
    private $mResult;
   
    private static $dbconn;  // holds the db connection
    private static $lastResult;
    private static $lastErrorCode;
    private static $lastErrorMsg;
   
    public static $echoQuery = false; // boolean for whether to echo or not
   
   private static $mOpenForBusiness;

   static public function isOpenForBusiness()
   {
      return self::$mOpenForBusiness;
   }

    static public function openDatabase()
    {
       
        // if already connected..
       if( self::IsOpenForBusiness() ) {
          return;
       }
      
       self::$dbconn = mysql_connect( DB_HOST, DB_USER, DB_PASS );
      
       if( self::$dbconn ) {
          
           if( mysql_select_db( DB_NAME ) ) {
              self::$mOpenForBusiness = true;
           }
           else {
               ErrorHandler::registerError( 'Unable to select db.' );
           }
          
       }
       else {
          self::$mOpenForBusiness = false;
       }
      
       return self::$mOpenForBusiness;
      
    }
   
    public function __construct( $query = "" )
    {
        $this->setString( $query );
    }
   
    public static function executeQuery( $queryStr ) {
       if( !self::$mOpenForBusiness && !self::OpenDatabase() ) {
           ErrorHandler::registerError( "Couldn't open database." );
           die( implode( "<br />\n", ErrorHandler::getErrors() ) . "<br />\n" );
       }
       
       if( self::$echoQuery ) echo "$queryStr<br />\n";
       
       self::$lastResult = @mysql_query( $queryStr );
       
       if( !self::$lastResult ) {
          self::$lastErrorMsg = mysql_error();
          self::$lastErrorCode = mysql_errno();
          ErrorHandler::registerError( self::$lastErrorCode . ': ' . self::$lastErrorMsg . " <br />Query: $queryStr" );
       }
       
       return self::$lastResult;
       
    }
   
    public static function freeResult() {
       
        mysql_free_result( self::$lastResult );
       
    }
   
    public static function queryToArray( $queryStr ) {
       if( self::executeQuery( $queryStr ) ) {
          $arr = array();
          while( $row = mysql_fetch_assoc( self::$lastResult ) ) $arr[] = $row;
          self::freeResult();
          return $arr;
       }
       return false;
    }
   
    // for queries where you are only concerned about the first record returned
    public static function queryToRecord( $queryStr ) {
       if( self::executeQuery( $queryStr ) ) {
          $record = mysql_fetch_assoc( self::$lastResult );
          self::freeResult();
          return $record;
       }
       return false;
    }
   
    public static function getLastInsertId() {
      if( $id = mysql_insert_id() ) {
         return $id;
      }
       // else...
      return false;
   }
   //*/
   
   /* pgsql
   public static function GetNextSequenceVal( $seqName ) {
      
      $sql = "SELECT nextval(" . self::makeDBStringSafe( $seqName ) . ") as key";
      if( $result = self::queryToRecord( $sql ) ) {
         return $result['key'];
      }
      return false;
   }
   //*/
      
   public static function totalRows() {
      return mysql_num_rows( self::$lastResult );
   }
   
   public static function getLastErrorCode() {
      return self::$lastErrorCode;
   }
   
   public static function getLastErrorMsg() {
      return self::$lastErrorMsg;
   }
   
    public static function makeDBStringSafe( $str, $omitQuotes=false ) {
       
       $str = mysql_real_escape_string( $str );
       //$str = str_replace( array('\\',"'"), array('\\\\',"\\'"), $str );
       
       if( $str == '' ) return 'NULL';
       if( $omitQuotes ) return $str;
        return "'$str'";
       
    }
   
    // this isn't really complete, be careful.
    public static function makeDBBinarySafe( $str ) {
       
        return mysql_real_escape_string( $str );
       
    }
   
    // Poorly named function.  Left in for compatibility's sake
    public static function makeDBNumberSafe( $str ) {
       
        return self::makeDBIntegerSafe( $str );
       
    }
   
    public static function makeDBIntegerSafe( $str ) {
       
       if( !preg_match( "/^\-?\d+$/", $str ) ) return 'NULL';
       else return $str;
       
    }
   
    // everything is true except (case-insensitive) 'false', 'f', 'no', 'n' or a value that evaluates to false.
    // '0' (String 0) is false.
    public static function makeDBBooleanSafe( $val ) {
       
      $val = strtolower( $val );
      $val = "'" . ( !$val || $val=='false' || $val=='f' || $val=='no' || $val=='n' ? 'FALSE' : 'TRUE' ) . "'";
      return $val;
      
    }
   
    public static function makeDBCurrencySafe( $str ) {
       
        return self::makeDBFloatSafe( $str );
       
    }
   
    public static function makeDBFloatSafe( $str ) {
       
       $num = preg_replace( "/[^\d\.]/", '', $str );
       if( $num == '' ) return 'NULL';
       else return $num;
       
    }
   
    public static function makeDBTimeStampSafe( $str ) {
       
        // eg. 2007-06-19 14:15:44
        if( preg_match( '/^\d{4}\-\d\d\-\d\d \d\d?:\d\d:\d\d$/', $str ) ) {
        //if( preg_match( '/^\d{4}\-?\d\d\-?\d\d$/', $str ) ) {
            return "'$str'";
        }
        // else
        ErrorHandler::registerError( "Invalid Timestamp encountered: $str" );
        return '';
       
    }
   
}

?>

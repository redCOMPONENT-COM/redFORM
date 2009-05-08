<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 

PhpListUser (derived from phplistSubscribe function)

Written by Ravis (http://www.ravis.org/)
March 29th, 2006

Modified by Josh Harle (http://www.neonascent.net) to replace PEAR DB with mysql database access
10th August, 2007

Modified by Rob Linton (http://www.yarwoodinteractive.com)
15th August, 2007
Refactored as a PHP5 class that uses my SimpleEmail, Query and ErrorHandler classes.
Much of the summary still applies although now you can add and remove lists - but not attributes, I
don't need if for this project so it's a to do item if needed down the road.  Attribute may only be
added and edited.  You no longer need PEAR DB but you'll need the class files I referenced above:
  require_once( 'SimpleEmail.php' );
  require_once( 'Query.php' );
  require_once( 'ErrorHandler.php' );

Enjoy.
 

SUMMARY:
This function provides a simple way to interact with phpList in your own programs (eg: adding a user
who just purchased something from your website to a mailing list).

Takes a list id, an email address, and optionally some attributes and subscribes the user to that list.
If the user already exists, the existing account will be subscribed. If it's a new user they'll be added
first. Users added in this way are pre-confirmed (they will not be sent a confirmation email).

DISCLAIMER:
All the standard rules apply. No responsibility for loss of data, corruption of data, alien abductions,
watching bad movies, unruly children, etc etc. Note that I'm pretty busy and I really don't have time
to support this code, so please don't contact me asking for help implementing it. I release it simply
because I thought it might be useful. I know absolutely nothing about phpList, really, I just installed
it today. The queries here are mostly ripped from the phpList import debug info, the rest of the code is
mine (although I wouldn't be surprised if it matches closely the phpList import code).

REQUIREMENTS:
This function makes use of the PEAR DB abstraction layer, which makes dealing with databases a lot
nicer. If you like there's nothing to stop you from modifying the $db-> commands to standard PHP
mysql functions, but since I use the PEAR DB layer for pretty much everything, I used it here too.
In fact, you could even use the DB layer included with phpList, I didn't because I wanted to keep
the code as independent as possible.

rl - The PEAR DB abstraction layer has been replaced with my Query class.

USAGE:

    PhpListUser::$PHPListPath = 'mypathto/phplist';
   
    $user = new PhpListUser( 'email@address.com' );
   
    $user->addListId( 2 );  // this won't add them until you save
    $user->save();          // now they're added
   
    $warns = ErrorHandler::getWarnings();
    $errs = ErrorHandler::getErrors();

Happy coding!

//-----------------------------------------------------------------------*/

class PhpListUser {
   
    // static
    public static $PHPListPath = '';
   
    private static $table_prefix = '';
    private static $usertable_prefix = '';
    private static $config_loaded = false;
   
    // instance
    private $userId;
    private $email;
    private $uniqid;
    private $attributes;    // user attributes.  array( id => array( name, value ), ... )
    private $lists;         // currently subscribed lists.  array( id=>array( list_name, entered date ), ... )
   
    private $removeLists;   // array of list ids to remove user from on a save
   
    private static function loadConfig() {
       
        require( self::$PHPListPath. '/config/config.php' );
       
        // We need $table_prefix and $usertable_prefix
        self::$table_prefix = $table_prefix;
        self::$usertable_prefix = $usertable_prefix;
       
        // If db constants are not defined, get them from the config for the Query class.
        if( !defined( 'DB_HOST' ) ) define( 'DB_HOST', $database_host );
        if( !defined( 'DB_USER' ) ) define ( 'DB_USER', $database_user );
        if( !defined( 'DB_PASS' ) ) define ( 'DB_PASS', $database_password );
        if( !defined( 'DB_NAME' ) ) define ( 'DB_NAME', $database_name );
       
        self::$config_loaded = true;
       
    }
   
    public function __construct( $email=null ) {
       
        if( !self::$config_loaded ) self::loadConfig();
       
        $this->userId = 0;
        $this->email = '';
        $this->uniqid = '';
        $this->attributes = array();
        $this->lists = array();
       
        $this->removeLists = array();
       
        if( $email ) {
            $this->set_email( $email );
            $this->load();
        }
       
    }
   
    public function clear() {
       
        $this->__construct();
       
    }
   
    public function load() {
       
        if( !$this->email ) {
            ErrorHandler::registerError( "Can't load PhpListUser without email address" );
            return false;
        }
       
        // else...
        // TO DO - actually load...
       
        $q = 'SELECT id,uniqid FROM ' . self::$usertable_prefix . "user WHERE email='$this->email'";
        $record = Query::queryToRecord( $q );
       
        if( $record ) {
           
            $this->userId = $record['id'];
            $this->uniqid = $record['uniqid'];
           
            $success = $this->loadAttributes() && $this->loadLists();
            return $success;
           
        }
       
        ErrorHandler::registerError( "Error loading user with email: $this->email" );
        return false;
       
    }
   
    public function save() {
       
        // Must be error free
        if( ErrorHandler::hasErrors() ) return false;
       
        // If there's no user id we assume it's an insert
        if( !$this->userId ) {
           
            // Make sure email is unique
            if( $this->emailIsUnique() ) {
                return $this->insertNewUser();
            }
            // If not unique... we load the user and update below
            else {
                if( !$this->load() ) {
                    return false;
                }
            }
        }
       
        // Here we've either returned already or have a user loaded up ready to be updated
        if( $this->updateUser() ) {
           
            // if success reload the attributes
            $email = $this->email;
            $this->clear();
            $this->email = $email;
            $this->load();
           
        }
       
    }
   
    // pass this any number of list ids
    public function addListId() {
       
        $ids = func_get_args();
        foreach( $ids as $id ) {
            $safeId = Query::makeDBIntegerSafe( $id );
            if( $safeId && $safeId != 'NULL' ) {
                $q = 'SELECT name,entered FROM ' . self::$table_prefix . "list WHERE id=$safeId";
                if( $record = Query::queryToRecord( $q ) ) {
                    $this->lists[$safeId] = $record;
                    return true;
                }
                else {
                    ErrorHandler::registerError( "Unable to find list with id: $id" );
                }
            }
           
            ErrorHandler::registerError( "Missing or invalid list id specified: $id" );
            return false;
           
        }
       
    }
   
   
    public function removeListId( $id ) {
        $this->removeLists[] = $id;
    }
   
    public function clearRemoveList() {
        $this->removeLists = array();
    }
   
    private function loadAttributes() {
       
        // 'id' is the attribute id
        $q = 'SELECT id,name,value FROM ' . self::$usertable_prefix . 'user_attribute AS ua' .
                ' JOIN ' . self::$usertable_prefix . 'attribute AS a ON( ua.attributeid = a.id )' .
                " WHERE ua.userid=$this->userId";
       
        $result = Query::queryToArray( $q );
        if( $result !== false ) {
            foreach( $result as $row ) {
                $this->attributes[ $row['id'] ] = array( 'name' => $row['name'], 'value' => $row['value'] );
            }
            return true;
        }
       
        ErrorHandler::registerError( 'Error retrieving attributes from database.' );
        return false;
       
    }
   
    private function loadLists() {
       
        $q = 'SELECT listid,name,lu.entered FROM ' . self::$table_prefix . 'listuser AS lu' .
                ' JOIN ' . self::$table_prefix . 'list AS l ON( lu.listid = l.id )' .
                " WHERE lu.userid=$this->userId";
       
        $result = Query::queryToArray( $q );
        if( $result !== false ) {
            foreach( $result as $row ) {
                $this->lists[ $row['listid'] ] = array( 'name' => $row['name'], 'entered' => $row['entered'] );
            }
            return true;
        }
       
        ErrorHandler::registerError( 'Error retrieving lists from database.' );
        return false;
       
    }
   
    private function insertNewUser() {
       
        if( !$this->createUniqueId() ) {
            return false;
        }
       
        $q = 'INSERT INTO ' . self::$usertable_prefix . 'user ( email, entered, confirmed, uniqid, htmlemail )' .
                ' VALUES ( ' .
                    "'$this->email', " .   // $this->email is verified in the setter
                    'NOW(), ' .
                    '1, ' .
                    "'$this->uniqid'," .     // $this->uniqid is generated internally by this class or retrieved from the db
					'1 ' .
                ' )';
        if( Query::executeQuery( $q ) ) {
            $this->userId = Query::getLastInsertId();
           
            // add a note saying we imported them manually
            $q = 'INSERT INTO ' . self::$usertable_prefix . 'user_history ( userid, ip, date, summary )' .
                    ' VALUES( ' .
                        $this->userId . ', ' .
                        "'{$_SERVER['REMOTE_ADDR']}', ".
                        'NOW(), ' .
                        "'Added via PhpListUser()'" .
                    ' )';
           
            // this warning won't stop anything from happening
            if( !Query::executeQuery( $q ) ) ErrorHandler::registerWarning( 'Failed to save user add note.' );
           
        }
        else {
            ErrorHandler::registerError( 'Unable to insert new user' );
            return false;
        }
       
        return $this->saveAttributes() && $this->saveLists();
       
    }
   
    private function updateUser() {
       
        // all we update is the email
        $q = 'UPDATE ' . self::$usertable_prefix . 'user' .
                " SET email='$this->email'" .   // $this->email is verified in the setter
                " WHERE id=$this->userId";
       
        if( Query::executeQuery( $q ) ) {
            return $this->saveAttributes() && $this->saveLists();
        }
       
        ErrorHandler::registerError( 'Error updating user.' );
        return false;
       
    }
   
    private function saveAttributes() {
       
        foreach( $this->attributes as $id => $attr ) {
            // REPLACE INTO is like INSERT but it will UPDATE if the primary key(s) already exist.  MySQL specific extension, kinda cool.
            // We can trust all of these values except $attr['value']
            $q = 'REPLACE INTO ' . self::$usertable_prefix . 'user_attribute (attributeid,userid,value)' .
                    ' VALUES ( ' .
                        $id . ', ' .
                        $this->userId . ', ' .
                        Query::makeDBStringSafe( $attr['value'] ) .
                    ' )';
           
            if( !Query::executeQuery( $q ) ) {
                // just a warning
                ErrorHandler::registerWarning( "Failed to save attribute {$attr['name']} = {$attr['value']}." );
            }
           
        }
       
        return true;
       
    }
   
    private function saveLists() {
       
        $removeIds = array_unique( $this->removeLists );
        foreach( $removeIds as $removeId ) {
            $q = 'DELETE FROM ' . self::$table_prefix . "listuser WHERE userid=$this->userId AND listid=" . Query::makeDBIntegerSafe( $removeId );
            if( Query::executeQuery( $q ) ) {
                if( isset( $this->attributes[$removeId] ) ) unset( $this->attributes[$removeId] );
            }
            else {
                // just a warning.
                ErrorHandler::registerWarning( "Failed to remove user from list id: $removeId" );
            }
        }
       
        // these are all either verified (by set_lists) or from the db
        $q = 'REPLACE INTO ' . self::$table_prefix . 'listuser ( userid, listid, entered ) VALUES';
        foreach( $this->lists as $id => $listInfo ) {
            $entered = $listInfo['entered'] ? "'{$listInfo['entered']}'" : 'NOW()';
            $q_array[] = " ( $this->userId , $id, $entered )";
        }
        $q .= implode(", ", $q_array);
        if( Query::executeQuery( $q ) ) {
            return true;
        }
		
        ErrorHandler::registerError( 'Error saving lists.' );
        return false;
       
    }
           
           
    private function emailIsUnique() {
       
        // We can trust $this->email because it's verified in the setter
        $q = 'SELECT id FROM ' . self::$usertable_prefix . "user WHERE email='$this->email'";
        $result = Query::queryToArray( $q );
        $isUnique = count( $result ) == 0;
       
        return $isUnique;
       
    }
   
    private function createUniqueId() {
        // create a unique id for the user (and make sure it's unique in the database)
        $safe = 0;
        do {
            $hash = md5( uniqid( mt_rand( 0, 1000 ) ) . $email );
            $sql = 'SELECT id FROM ' . self::$usertable_prefix . "user WHERE uniqid='$hash'";
            $result = Query::queryToArray( $sql );
           
            if( $safe++ > 10 ) {
               ErrorHandler::registerError( "Couldn't get unique id in $safe tries, aborting." );
               return false;
            }
        }
        while( count( $result ) > 0 );
       
        $this->uniqid = $hash;
        return true;
       
    }
   
    private function getAttributeIdFromName( $name ) {
       
        $sql = 'SELECT id FROM ' . self::$usertable_prefix . 'attribute WHERE name=' . Query::makeDBStringSafe($name);
       
        if( $result = Query::queryToArray( $sql ) ) {
           
            // I don't know about PHPList's database design, it might be possible to specify attributes with the same name
            // in which case we should be referencing attributes by their ids not their names.  I inherited this method...
            if( count( $result ) > 1 ) {
                ErrorHandler::registerError( "Ambiguous reference to attribute '$name'.  Multiple entries found in attribute table." );
                return false;
            }
            else {
                return $result[0]['id'];
            }
           
        }
       
        ErrorHandler::registerError( "Unable to find id for attribute '$name'." );
        return false;
       
    }
   
    /* Currently not using this... leaving it just in case.
    private function verifyLists() {
       
        // If any list names are missing, retrieve them from the db.  If the name is present then
        // it's already verified (it came from the db).
        foreach( $this->lists as $id => $listname ) {
            if( !$listname ) {
                $q = 'SELECT name FROM ' . self::$table_prefix . 'list WHERE id=' . Query::makeDBIntegerSafe( $id );
                if( $record = Query::queryToRecord( $q ) ) {
                    $this->lists[$id] = $record['name'];
                }
                else {
                    ErrorHandler::registerError( "Invalid list id specified: $id" );
                }
            }
        }
       
    }
    //*/
   
    private function addAttribute( $name, $value ) {
       
        if( $id = $this->getAttributeIdFromName( $name ) ) {
           
            // Special case for countries - get the country id instead of using the name.  This is from
            // the original code and I'm not sure what the implications are.  It populates the value
            // with the country id instead of the string value.  It might be specific to a particular
            // set up or it might be how PHPList works.  Keep an eye on this if things go wonky.
            if( strtolower( $name ) == 'country' ) {
                $countryName = $value;
                $q = 'SELECT id FROM ' . self::$table_prefix . 'listattr_countries WHERE name=' . Query::makeDBStringSafe( $countryName );
                if( $record = Query::queryToRecord( $q ) ) {
                    $value = $record['id'];
                }
                else {
                    ErrorHandler::registerError( "Failed to find match for special case country attribute: $countryName." );
                    return false;
                }
            }
            // end country exception
           
            $attrArray = array(
                            'name'  => $name,
                            'value' => $value
                         );
           
            // store the countryName for the get handler
            if( isset( $countryName ) ) $attrArray['countryName'] = $countryName;
           
            $this->attributes[$id] = $attrArray;
            return true;
           
        }
       
        ErrorHandler::registerError( 'Unable to find attribute by name: $name.' );
        return false;
       
    }
   
    private function attributeValue( &$attr ) {
        if( $attr['name'] == 'country' ) {
          return $attr['countryName'];
        }
        else {
         return $attr['value'];
        }
    }
   
    //
    // getter/setters
    //
   
    public function set_attribute( $name, $value ) {
       
        $this->addAttribute( $name, $value );
       
    }
   
    public function get_attribute( $name ) {
       
        foreach( $this->attributes as $attr ) {
            if( $attr['name'] == $name ) return $this->attributeValue( $attr );
        }
       
        ErrorHandler::registerWarning( "Unable to retrieve attribute '$name' from class attributes" );
        return false;
       
    }
   
    // Pass this an assoc array( $attr_name => $attr_value, ... )
    public function set_attributes( $val ) {
       
        foreach( $val as $n => $v ) {
            $this->addAttribute( $n, $v );
        }
       
    }
   
    public function get_attributes() {
       
        $return = array();
        foreach( $this->attributes as $attr ) {
            $return[ $attr['name'] ] = $this->attributeValue( $attr );
        }
        return $return;
       
    }
   
    public function get_lists() {
       
        return $this->lists;
       
    }
   
    public function set_email( $val ) {
       
        if( SimpleEmail::isValidEmail( $val ) ) {
            $this->email = $val;
        }
        else {
            ErrorHandler::registerError( 'Invalid Email supplied to PhpListUser' );
        }
       
    }
   
    public function get_email() {
        return $this->email;
    }
	
	public function isRegistedEmail( $val= 0 ) {
        if($val==0)
         $val=$this->email;
        if( SimpleEmail::isValidEmail( $val ) ) {
              $q = 'SELECT id FROM ' . self::$usertable_prefix . "user WHERE email='$val'";
            $result = Query::queryToArray( $q );
            return count( $result );
        }
        else {
            ErrorHandler::registerError( 'Invalid Email supplied to PhpListUser' );
         return -1;
        }
       
    }
   
	public function getListId($listname) {
		$q = 'SELECT id FROM ' . self::$table_prefix . "list WHERE name='$listname'";
		if( $record = Query::queryToRecord( $q ) ) {
			return $record['id'];
		}
		else {
			ErrorHandler::registerError( "Unable to find list with name: $listname" );
		}
	}
}
?>

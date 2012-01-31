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

class SimpleEmail {
   
    private $recipient;
    private $subject;
    private $message;
    private $headers;
   
    private $error;
   
    public static function isValidEmail( $email ) {
       
       return preg_match('/^[ a-z0-9-_]+(\.[a-z0-9-_]+)*@[a-z0-9-]+(\.[a-z0-9-_]+)*(\.[a-z]{2,4})$/i', $email);
       
    }
   
    function __construct() {
        $this->recipient = '';
        $this->subject = '';
        $this->message = '';
        $this->headers = '';
        $this->error = array();
    }
   
    private function doError( $msg ) {
        $this->error[] = $msg;
    }
   
    public function hasErrors() {
        return count( $this->error );
    }
   
    public function getErrors() {
        return $this->error;
    }
   
    public function addHeader( $header ) {
        $header = trim( trim( $header ), ';' );
        $this->headers .= "$header\r\n";
    }
   
    public function sendMail() {
       
        if( !($this->recipient && $this->subject && $this->message) ) {
            ErrorHandler::registerError( 'Missing fields required to send email' );
        }
       
        if( !ErrorHandler::hasErrors() ) {
            //*DEBUG*/ $success = true; echo "To: $this->recipient\nSubject:$this->subject\n\n$this->message\n";
            $success = mail( $this->recipient, $this->subject, $this->message, $this->headers );
            if( $success ) return true;
            else ErrorHandler::registerError( 'Error sending mail.' );
        }
       
        return false;
       
    }
   
    public function set_recipient( $val ) {
        if( $this->isValidEmail( $val ) ) {
        //if( self::isValidEmail( $val ) ) {
            $this->recipient = $val;
            return true;
        } else {
            ErrorHandler::registerError( 'Invalid email address for recipient' );
            return false;
        }
    }
   
    public function set_subject( $val ) {
        $this->subject = str_replace( array( '"', "\n", "\r" ), array( '\\\"', ''), $val );
    }
   
    public function set_message( $val ) {
        $this->message = str_replace( '"', '\\\"', $val );
    }
   
}

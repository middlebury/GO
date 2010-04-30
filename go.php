<?php

require_once "config.php";
require_once "user.php";
require_once "code.php";
require_once "alias.php";

global $connection;
$connection = new PDO(
  "mysql:dbname=" . GO_DATABASE_NAME . ";host=" . GO_DATABASE_HOST . ";",
  GO_DATABASE_USER, GO_DATABASE_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

/**
 * Handle Authentication and attribute lookups.
 *
 * @author Ian McBride <imcbride@middlebury.edu>
 * @category GO
 * @copyright 2009 The President and Fellows of Middlebury College
 * @license This code is not available under license.
 * @package GO
 * @version 06-08-2009
 * @link http://go.middlebury.edu/
 */
abstract class GoAuth {

  /**
   * Store the logged in user and their attributes.
   *
   * @access protected
   * @since 06-08-2009
   * @var The currently logged in user.
   */
  protected $user;
  
  /**
   * Get the internal ID of a user.
   *
   * If the username parameter is set, the returned ID will be that of the input
   * user, otherwise the ID of the currently logged in user will be returned.
   *
   * @access public
   * @param string $username A username to find the ID of.
   * @return string The ID of the requested user.
   * @since 06-08-2009
   */ 
  abstract public function getId($username = null);
  
  /**
   * Get the username of a user.
   *
   * If the ID parameter is set, the returned username will be that of the input
   * user, otherwise the username of the currently logged in user will be returned.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The username of the requested user.
   * @since 06-08-2009
   */
  abstract public function getName($id = null);
  
  /**
   * Get the email address of a user.
   *
   * If the ID parameter is set, the returned email will be that of the input
   * user, otherwise the email of the currently logged in user will be returned.
   *
   * @access public
   * @param string $id A user ID to find the email of.
   * @return string The email address of the requested user.
   * @since 06-08-2009
   */
  abstract public function getEmail($id = null);

}

class GoAuthLdap extends GoAuth {
  
  private $ldap;
    
  public function __construct($username, $password) {
    $this->connect();
    
    $filter = "(&(objectclass=user)(" . GO_ATTR_NAME . "=" . $username . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_PATH, $filter, array("distinguishedname", GO_ATTR_ID, GO_ATTR_NAME, GO_ATTR_EMAIL));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    if (ldap_bind($this->ldap, $entries[0]["distinguishedname"][0], $password) === false) throw new Exception("Cannot bind as user");
    
    $this->user = $entries[0];
  }
  
  private function connect() {
    $this->ldap = ldap_connect(GO_AUTH_HOST, GO_AUTH_PORT);
    
    if ($this->ldap === false) throw new Exception("Cannot establish connection to LDAP server");
    
    if (ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3) === false) throw new Exception("Cannot set LDAP_OPT_PROTOCOL_VERSION");
    if (ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0) === false) throw new Exception("Cannot set LDAP_OPT_REFERRALS");
    
    if (ldap_bind($this->ldap, GO_AUTH_USER, GO_AUTH_PASS) === false) throw new Exception("Cannot bind as LDAP user");
  }
  
  public function getId($username = null) {
    if (is_null($username)) {
      return $this->user[GO_ATTR_ID][0];
    }
    
    $this->connect();
    
    $filter = "(&(objectclass=user)(" . GO_ATTR_NAME . "=" . $username . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_PATH, $filter, array(GO_ATTR_ID));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    return $entries[0][GO_ATTR_ID][0];
  }
  
  public function getName($id = null) {
    if (is_null($id)) {
      return $this->user[GO_ATTR_NAME][0];
    }
    
    $this->connect();
    
    $filter = "(&(objectclass=user)(" . GO_ATTR_ID . "=" . $id . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_PATH, $filter, array(GO_ATTR_NAME));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    return $entries[0][GO_ATTR_NAME][0];
  }
  
  public function getEmail($id = null) {
    if (is_null($id)) {
      return $this->user[GO_ATTR_ID][0];
    }
    
    $this->connect();
    
    $filter = "(&(objectclass=user)(" . GO_ATTR_ID . "=" . $id . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_PATH, $filter, array(GO_ATTR_EMAIL));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    return $entries[0][GO_ATTR_EMAIL][0];
  }
  
}

class GoAuthCas extends GoAuth {
  
  public function __construct() {
    $name = preg_replace('/[^a-z0-9_-]/i', '', dirname($_SERVER['SCRIPT_NAME']));

    session_name($name);
    
    require_once('CAS.php');
    
    phpCAS::setDebug(GO_AUTH_LOG);
    
    phpCAS::proxy(CAS_VERSION_2_0, GO_AUTH_HOST, GO_AUTH_PORT, GO_AUTH_PATH);
    
    phpCAS::setFixedCallbackURL(GO_AUTH_PGT);
    
    phpCAS::setNoCasServerValidation();
    
    phpCAS::setPGTStorageFile('plain', GO_AUTH_PGTSTORE);
    
    phpCAS::forceAuthentication();
  }
  
  public function getId($username = null) {
    if(is_null($username)) {
      return $_SESSION["phpCAS"]["user"];
    }
    
    phpCAS::servericeWeb(
      "https://" . GO_AUTH_HOST . "/directory/?action=search_user_by_attributes&" . GO_ATTR_NAME . "=" . $username,
      $err_code, $output
    );
    
    $xml = simplexml_load_string($output);
    $id = $xml->xpath("/cas:results/cas:entry/cas:user");
    
    if (isset($err_code) && $err_code != 0) {
      throw new Exception($err_code);
    } else  if (!isset($id[0]) || $id[0] == "") {
      throw new Exception("User " . $_SESSION["phpCAS"]["user"] . " not found.");
    } else {
      return $id[0];
    }
  }
  
  public function getName($id = null) {
    if (is_null($id)) {
      $id = $_SESSION["phpCAS"]["user"];
    }
    
    phpCAS::serviceWeb(
      "https://" . GO_AUTH_HOST . "/directory/?action=get_user&id=" . $id,
      $err_code, $output
    );
    
    $xml = simplexml_load_string($output);
    $user = $xml->xpath("/cas:results/cas:entry/cas:attribute[@name='" . GO_ATTR_NAME . "']");
    
    if (isset($err_code) && $err_code != 0) {
      throw new Exception($err_code);
    } else {
      return $user[0]["value"];
    }
  }
  
  public function getEmail($id = null) {
    if (is_null($id)) {
      $id = $_SESSION["phpCAS"]["user"];
    }
    
    phpCAS::serviceWeb(
      "https://" . GO_AUTH_HOST . "/directory/?action=get_user&id=" . $id,
      $err_code, $output
    );
    
    $xml = simplexml_load_string($output);
    $email = $xml->xpath("/cas:results/cas:entry/cas:attribute[@name='" . GO_ATTR_EMAIL . "']");
    
    if (isset($err_code) && $err_code != 0) {
      throw new Exception($err_code);
    } else {
      return $email[0]["value"];
    }
  }

}

class Go {
  
  public static function httpquery($input_url, $input_cookies) {
    $response = "";
    
    $parsed_url = parse_url($input_url);
    
    // fsockopen doesn't properly handle the HTTPS protocol, so we need to translate it
    if(isset($parsed_url["scheme"]) && $parsed_url["scheme"] == "https") {
      if (!isset($parsed_url["port"])) {
        $parsed_url["port"] = "443";
      }
      
      $parsed_url["protocol"] = "ssl://";
    } else {
      if (!isset($parsed_url["port"])) {
        $parsed_url["port"] = "80";
      }
      
      $parsed_url["protocol"] = "";
    }
    
    if (!isset($parsed_url["path"])) {
      $parsed_url["path"] = "/";
    }
    
    // CMS 404 responses are served up as a custom error page with a 400 response
    if ($parsed_url["path"] == "/middcms/Html/Errors/Error.aspx") {
      return "404";
    }
    
    $errno = ""; $errstr = "";
    
    $fp = @fsockopen($parsed_url["protocol"] . $parsed_url["host"], $parsed_url["port"], $errno, $errstr);
    
    if ($fp) {
      $query = "";
      
      if (isset($parsed_url["query"])) {
        $query = "?" . $parsed_url["query"];
      }
      
      fputs($fp, "HEAD " . $parsed_url["path"] . $query . " HTTP/1.1\r\n");
      fputs($fp, "Host: " . $parsed_url["host"] . "\r\n");
      fputs($fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows XP)\r\n");
      
      foreach($input_cookies as $domain => $array) {
        if (strpos($parsed_url["host"], $domain) !== false) {
          $cookie = "Cookie: ";
          
          foreach($array as $name => $value) {
            $cookie .= $name . "=" . $value . " ";
          }
          
          fputs($fp, $cookie . "\r\n");
        }
      }
      
      fputs($fp, "Connection:close\r\n\r\n");
      
      while(!feof($fp)) {
        $line = @fgets($fp);
        
        $resp_matches = array();
        
        if (preg_match("/^HTTP\/\d\.\d\s(\d{3})\s.*$/i", $line, $resp_matches)) {
          
          if (substr_compare($resp_matches[1], "3", 0, 1) == 0) {
            $location = "";
            $cookies = array();
            
            while(!feof($fp)) {
              $tmp_matches = array();
              $line = @fgets($fp);
              
              if (preg_match("/Location:\s(.*)$/i", $line, $tmp_matches)) {
                $mat_arr = str_split(trim($tmp_matches[1]));
                
                if ($mat_arr[0] == "/") {
                  $location = $parsed_url["scheme"] . "://" . $parsed_url["host"] . $tmp_matches[1];
                } else {
                  $location = $tmp_matches[1];
                }
              }
              
              $matches = array();
              if (preg_match("/^Set-Cookie:\s(.*)$/i", $line, $matches)) {
                $domain = $parsed_url["host"];
                
                if (strpos($matches[1], "domain=") !== false) {
                  $start = strpos($matches[1], "domain=") + 7;
                  
                  if (strpos($matches[1], ";", $start) !== false) {
                    $domain = substr($matches[1], $start, strpos($matches[1], ";", $start) - $start);
                  } else {
                    $domain = trim(substr($matches[1], $start));
                  }
                }
              
                $first_sc = strpos($matches[1], ";");
                if ($first_sc !== false) {
                  $matches[1] = substr($matches[1], 0, $first_sc + 1);
                }
                
                $cookiename = substr($matches[1], 0, strpos($matches[1], "="));
                $cookieval = substr($matches[1], strpos($matches[1], "=") + 1);
                $input_cookies[$domain][$cookiename] = $cookieval;
              }
            }
          
            if ($location != "") {
              if (strpos($location, "http") !== 0) {
                $new_location = $parsed_url["scheme"] . "://" . $parsed_url["host"];
                
                if (strpos($location, "/") !== 0) {
                  $new_location .= substr($parsed_url["path"], 0, strrpos($parsed_url["path"], "/") + 1);
                }
                
                $location = $new_location . $location;
              }
              
              $response = Go::httpquery(trim($location), $input_cookies);
            }
          } else {
            $response = $resp_matches[1];
          }
        }
      }
    
      fclose($fp);
    }
    
    return $response;
  }
  
}

?>
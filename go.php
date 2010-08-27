<?php

require_once "config.php";
require_once "user.php";
require_once "code.php";
require_once "alias.php";

// Define admin pages and non-admin pages that need session
$admin_pages = array("admin.php", "create.php", "update.php", "notify.php", "functions.php");
$session_pages = $admin_pages;
$session_pages[] = 'info.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Initialize session on all but the redirect
if (in_array($current_page, $session_pages)) {
	session_name('GOSID');
	session_start();
}

// Force authentication on admin pages
if (in_array($current_page, $admin_pages)) {
	if (AUTH_METHOD == 'ldap') {
		if (!isset($_SESSION["AUTH"]) && $current_page != "login.php") {
  		header("Location: login.php?r=" . $_SERVER["PHP_SELF"]);
  		exit();
		}
	} else if (AUTH_METHOD == 'cas') {
		$_SESSION["AUTH"] = new GoAuthCas();
	} else {
		throw new Exception('Unknown Auth Method');
	}
}

// Initialize database
global $connection;
$connection = new PDO(
  "mysql:dbname=" . GO_DATABASE_NAME . ";host=" . GO_DATABASE_HOST . ";",
  GO_DATABASE_USER, GO_DATABASE_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

// Match the institution by URL.
global $institutions;
foreach ($institutions as $inst => $opts) {
	if (strpos('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $opts['base_uri']) !== FALSE) {
		$institution = $inst;
		break;
	}
}
// Set the first institution as the default if we haven't matched by URL.
if (!isset($institution)) {
	reset($institutions);
	$institution = key($institutions);
}

/**
 * Answer a URL equivalent to the current one, but for another institution.
 * 
 * @param string $institution
 * @return string
 * @since 6/18/10
 */
function equivalentUrl ($institution) {
	global $institutions;
	if (!isset($institutions[$institution]))
		throw new Exception ("$institution was not found in the configured list.");
	
	$url = $institutions[$institution]['base_uri'];
	$url .= basename($_SERVER['SCRIPT_NAME']);
	if (strlen($_SERVER['QUERY_STRING']))
		$url .= '?'.$_SERVER['QUERY_STRING'];
	return $url;
}

/**
 * Handle Authentication and attribute lookups.
 *
 * @author Ian McBride <imcbride@middlebury.edu>
 * @category GO
 * @copyright 2009 The President and Fellows of Middlebury College
 * @license GNU General Public License (GPL) version 3 or later
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
    
    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_NAME . "=" . $username . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array("distinguishedname", GO_AUTH_LDAP_ATTR_ID, GO_AUTH_LDAP_ATTR_NAME, GO_AUTH_LDAP_ATTR_EMAIL));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    if (ldap_bind($this->ldap, $entries[0]["distinguishedname"][0], $password) === false) throw new Exception("Cannot bind as user");
    
    $this->user = $entries[0];
  }
  
  private function connect() {
    $this->ldap = ldap_connect(GO_AUTH_LDAP_HOST, GO_AUTH_LDAP_PORT);
    
    if ($this->ldap === false) throw new Exception("Cannot establish connection to LDAP server");
    
    if (ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3) === false) throw new Exception("Cannot set LDAP_OPT_PROTOCOL_VERSION");
    if (ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0) === false) throw new Exception("Cannot set LDAP_OPT_REFERRALS");
    
    if (ldap_bind($this->ldap, GO_AUTH_LDAP_USER, GO_AUTH_LDAP_PASS) === false) throw new Exception("Cannot bind as LDAP user");
  }
  
  public function getId($username = null) {
    if (is_null($username)) {
      return $this->user[GO_AUTH_LDAP_ATTR_ID][0];
    }
    
    $this->connect();
    
    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_NAME . "=" . $username . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_ID));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    return $entries[0][GO_AUTH_LDAP_ATTR_ID][0];
  }
  
  public function getName($id = null) {
    if (is_null($id)) {
      return $this->user[GO_AUTH_LDAP_ATTR_NAME][0];
    }
    
    $this->connect();
    
    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_ID . "=" . $id . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_NAME));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    return $entries[0][GO_AUTH_LDAP_ATTR_NAME][0];
  }
  
  public function getEmail($id = null) {
    if (is_null($id)) {
      return $this->user[GO_AUTH_LDAP_ATTR_ID][0];
    }
    
    $this->connect();
    
    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_ID . "=" . $id . "))";
    
    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_EMAIL));
    
    if ($result === false) throw new Exception("Cannot find user");
    
    $entries = ldap_get_entries($this->ldap, $result);
    
    return $entries[0][GO_AUTH_LDAP_ATTR_EMAIL][0];
  }
  
}

class GoAuthCas extends GoAuth {
  /**
   * Configure phpCAS
   * 
   * @return void
   * @access public
   * @since 5/10/10
   * @static
   */
  public static function configurePhpCas () {
    
    require_once(dirname(__FILE__).'/phpcas/source/CAS.php');
    
    if (defined('GO_AUTH_CAS_LOG'))
	    phpCAS::setDebug(GO_AUTH_CAS_LOG);
    
    phpCAS::proxy(CAS_VERSION_2_0, GO_AUTH_CAS_HOST, GO_AUTH_CAS_PORT, GO_AUTH_CAS_PATH, false);
    
    phpCAS::setFixedCallbackURL(GO_AUTH_CAS_PGT);
    
    phpCAS::setNoCasServerValidation();
    
    phpCAS::setPGTStorageFile('plain', GO_AUTH_CAS_PGTSTORE);
  }
  
  public function __construct() {
    self::configurePhpCas();
    
    phpCAS::forceAuthentication();
  }
  
  public function getId($username = null) {
    if(is_null($username)) {
      return $_SESSION["phpCAS"]["user"];
    }
    
    if (!Go::cache_get('user_id-'.$username)) {
      
      @phpCAS::serviceWeb(
        "https://" . GO_AUTH_CAS_HOST . "/directory/?action=search_users_by_attributes&" . GO_AUTH_CAS_ATTR_NAME . "=" . $username,
        $err_code, $output
      );
      if ($err_code || !$output)
      	throw new Exception("A web-service error $err_code occured: $output", $err_code);
      $xml = simplexml_load_string($output);
      $id = $xml->xpath("/cas:results/cas:entry/cas:user");
      
      if (isset($err_code) && $err_code != 0) {
        throw new Exception($err_code);
      } else  if (!isset($id[0]) || $id[0] == "") {
        throw new Exception("User " . $_SESSION["phpCAS"]["user"] . " not found.");
      } else {
        Go::cache_set('user_id-'.$username, (string)$id[0]);
      }
    }
    
    return Go::cache_get('user_id-'.$username);
  }
  
  public function getName($id = null) {
    if (is_null($id)) {
      $id = $_SESSION["phpCAS"]["user"];
    }
    
    if (!Go::cache_get('user_name-'.$id)) {
      phpCAS::serviceWeb(
        "https://" . GO_AUTH_CAS_HOST . "/directory/?action=get_user&id=" . $id,
        $err_code, $output
      );
      if ($err_code || !$output)
      	throw new Exception("A web-service error $err_code occured: $output", $err_code);
      
      
      $xml = simplexml_load_string($output);
      $user = $xml->xpath("/cas:results/cas:entry/cas:attribute[@name='" . GO_AUTH_CAS_ATTR_NAME . "']");
      
      if (isset($err_code) && $err_code != 0) {
        throw new Exception($err_code);
      } else {
        Go::cache_set('user_name-'.$id, (string)$user[0]["value"]);
      }
    }
    
    return Go::cache_get('user_name-'.$id);
  }
  
  public function getEmail($id = null) {
    if (is_null($id)) {
      $id = $_SESSION["phpCAS"]["user"];
    }
    
    if (!Go::cache_get('user_email-'.$id)) {
      phpCAS::serviceWeb(
        "https://" . GO_AUTH_CAS_HOST . "/directory/?action=get_user&id=" . $id,
        $err_code, $output
      );
      
      $xml = simplexml_load_string($output);
      $email = $xml->xpath("/cas:results/cas:entry/cas:attribute[@name='" . GO_AUTH_CAS_ATTR_EMAIL . "']");
      
      if (isset($err_code) && $err_code != 0) {
        throw new Exception($err_code);
      } else {
        Go::cache_set('user_email-'.$id, (string)$email[0]["value"]);
      }
    }
    
    return Go::cache_get('user_email-'.$id);
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
  
 
  /**
   * Store a variable in cache.
   * 
   * @param string $key
   * @param mixed $value
   * @return boolean true on success, false on failure
   * @access public
   * @since 4/30/10
   */
  public static function cache_set ($key, $value) {
    // Use APC if available
    if (function_exists('apc_store')) {
      return apc_store('go_cache_'.$key, $value, 86400); // TTL = 1 day
    }
    // Fall back to caching in the session
    else {
      $_SESSION['go_cache_'.$key] = $value;
      return true;
    }
  }
  
  /**
   * Retrieve a variable from cache.
   * 
   * @param string $key
   * @return mixed The value or FALSE if not set
   * @access public
   * @since 4/30/10
   */
  public static function cache_get ($key) {
    // Use APC if available
    if (function_exists('apc_fetch')) {
      return apc_fetch('go_cache_'.$key);
    }
    // Fall back to caching in the session
    else {
      if (isset($_SESSION['go_cache_'.$key]))
        return $_SESSION['go_cache_'.$key];
      else
        return FALSE;
    }
  }
  
  /**
   * Clear a value from the cache.
   * 
   * @param string $key
   * @return boolean true on success, false on failure
   * @access public
   * @since 4/30/10
   */
  public static function cache_delete ($key) {
    // Use APC if available
    if (function_exists('apc_delete')) {
      return apc_delete('go_cache_'.$key);
    }
    // Fall back to caching in the session
    else {
      unset($_SESSION['go_cache_'.$key]);
      return TRUE;
    }
  }
  
  /**
   * Answer the fully-qualified GO URL for a code and institution
   * 
   * @param string $code
   * @param optional string $institution
   * @return string
   * @access public
   * @since 5/3/10
   */
  public static function getShortcutUrl ($code, $institution = 'middlebury.edu') {
  	global $institutions;
    return $institutions[$institution]['base_uri'].$code; 
  }
  
  /**
   * Log an event
   * 
   * @param string $description
   * @param string $code
   * @param optional string $institution
   * @param optional string $alias
   * @return void
   * @access public
   * @since 6/24/10
   */
  public static function log ($description, $code, $institution = 'middlebury.edu', $alias = '') {
	global $connection;
  	
  	if (isset($_SESSION["AUTH"])) {
  		$user_id = $_SESSION["AUTH"]->getId();
  		$user_display_name = $_SESSION["AUTH"]->getName();
  	} else {
  		$user_id = '0';
  		$user_display_name = 'anonymous';
  	}
  	
  	
	$insert = $connection->prepare("INSERT INTO log (code, alias, institution, description, user_id, user_display_name, request, referer) VALUES (:code, :alias, :institution, :description, :user_id, :user_display_name, :request, :referer)");
	$insert->bindValue(":code", $code);
	$insert->bindValue(":alias", $alias);
	$insert->bindValue(":institution", $institution);
	$insert->bindValue(":description", $description);
	$insert->bindValue(":user_id", $user_id);
	$insert->bindValue(":user_display_name", $user_display_name);
	$insert->bindValue(":request", $_SERVER["REQUEST_URI"]);
	if (isset($_SERVER["HTTP_REFERER"])) 
	 	$insert->bindValue(":referer", $_SERVER["HTTP_REFERER"]);
	else
		$insert->bindValue(":referer", '');
	$insert->execute();

  }
  
  /**
   * Answer a display-name that matches an id. Allows lookup even when not authenticated.
   * 
   * @param string $id
   * @return string
   * @access public
   * @since 6/28/10
   */
  public static function getUserDisplayName($id) {
    if (is_null($id)) {
      throw new Exception('No user id specified.');
    }
    
    if (!Go::cache_get('user_displayname-'.$id)) {
      if (!defined('GET_USER_DISPLAY_NAME_CALLBACK'))
        throw new Exception('You must configure GO with a GET_USER_DISPLAY_NAME_CALLBACK function to fetch user names for user-ids.');
        
      try {
	    $displayName = call_user_func(GET_USER_DISPLAY_NAME_CALLBACK, $id);
	  } catch (Exception $e) {
	    // Log the problem, but fall back to the id.
	  	error_log($e->getMessage());
	  	return $id;
	  }
      
      // Fall back to the id if we get no results, but don't cache it.
      if (!$displayName)
        return $id;
      
      Go::cache_set('user_displayname-'.$id, (string)$displayName);
    }
    
    return Go::cache_get('user_displayname-'.$id);
  }
  
}

?>
<?php

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
    if (function_exists('apcu_store')) {
      return apcu_store('go_cache_'.$key, $value, 86400); // TTL = 1 day
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
    if (function_exists('apcu_fetch')) {
      return apcu_fetch('go_cache_'.$key);
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
    if (function_exists('apcu_delete')) {
      return apcu_delete('go_cache_'.$key);
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
  		$user_id = $_SESSION["AUTH"]->getCurrentUserId();
  		$user_display_name = $_SESSION["AUTH"]->getCurrentUserName();
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
      $userLookupClass = GoAuth::userLookupClass();

      try {
        $displayName = $userLookupClass::getDisplayNameByUserId($id);
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

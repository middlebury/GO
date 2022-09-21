<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class GoAuthCas implements GoAuthAuthenticatedSessionInterface, GoAuthLookupInterface {
  /**
   * Configure phpCAS
   *
   * @return void
   * @access public
   * @since 5/10/10
   * @static
   */
  public static function configurePhpCas () {

    if (defined('GO_AUTH_CAS_LOG')) {
      $logger = new Logger('phpcas');
      $logger->pushHandler(new StreamHandler(GO_AUTH_CAS_LOG, Logger::DEBUG));
      phpCAS::setLogger($logger);
    }

    phpCAS::client(CAS_VERSION_2_0, GO_AUTH_CAS_HOST, GO_AUTH_CAS_PORT, GO_AUTH_CAS_PATH, false);

    phpCAS::setNoCasServerValidation();
  }

  public function __construct() {
    self::configurePhpCas();

    phpCAS::forceAuthentication();

    $groups = phpCAS::getAttribute('MemberOf');

    //use $allowedGroups from config
    global $allowedGroups;

    //check that user is member of a group
    if (empty($groups)) {
      throw new AuthorizationFailedException();
    //check groups against allowed groups if $allowedGroups is set
    } elseif (isset($allowedGroups) && !empty($allowedGroups)) {
      if (!is_array($groups) && !in_array($groups, $allowedGroups)) {
        throw new AuthorizationFailedException();
      } else {
        foreach ($groups as $group) {
          if (in_array($group, $allowedGroups))
            return;
        }
        throw new AuthorizationFailedException();
      }
    }
  }

  /**
   * Get the internal ID of the current user.
   *
   * @access public
   * @return string The ID of the requested user.
   * @since 06-08-2009
   */
  public function getCurrentUserId() {
    return $_SESSION["phpCAS"]["user"];
  }

  /**
   * Get the internal ID of a user.
   *
   * @access public
   * @param string $username A username to find the ID of.
   * @return string The ID of the requested user.
   */
  public static function getIdForUser($username) {
    if (!Go::cache_get('user_id-'.$username)) {
      $xml = self::directoryFetch('search_users_by_attributes', GO_AUTH_CAS_ATTR_NAME, $username);
      $elements = $xml->xpath("/cas:results/cas:entry/cas:user");
      if (count($elements)){
        $id = (string)$elements[0];
      } else {
        $id = NULL;
      }
      Go::cache_set('user_id-'.$username, $id);
    }

    return Go::cache_get('user_id-'.$username);
  }

  /**
   * Get the username of the current user.
   *
   * @access public
   * @return string The username of the requested user.
   */
  public function getCurrentUserName() {
    return self::getNameByUserId($_SESSION["phpCAS"]["user"]);
  }

  /**
   * Get the username of a user.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The username of the requested user.
   */
  public static function getNameByUserId($id) {
    if (!Go::cache_get('user_name-'.$id)) {
      $name = self::directoryLookupById($id, GO_AUTH_CAS_ATTR_NAME);
      Go::cache_set('user_name-'.$id, $name);
    }
    return Go::cache_get('user_name-'.$id);
  }

  /**
   * Get the email address of a user.
   *
   * @access public
   * @return string The email address of the requested user.
   * @since 06-08-2009
   */
  public function getCurrentUserEmail() {
    return self::getEmailByUserId($_SESSION["phpCAS"]["user"]);
  }

  /**
   * Get the email address of a user.
   *
   * @access public
   * @param string $id A user ID to find the email of.
   * @return string The email address of the requested user.
   */
  public static function getEmailByUserId($id) {
    if (!Go::cache_get('user_email-'.$id)) {
      $email = self::directoryLookupById($id, GO_AUTH_CAS_ATTR_EMAIL);
      Go::cache_set('user_email-'.$id, $email);
    }

    return Go::cache_get('user_email-'.$id);
  }

  /**
   * Lookup a value in a directory
   *
   * @param string $id The user's Id.
   * @param string $attributeToReturn Which attribute name to return from the XML document.
   * @return string
   */
  protected static function directoryLookupById ($id, $attributeToReturn) {
    $xml = self::directoryFetch('get_user', 'id', $id);
    $element = $xml->xpath("/cas:results/cas:entry/cas:attribute[@name='".$attributeToReturn."']");
    return (string)$element[0]["value"];
  }

  /**
   *
   *
   * @param string $action The directory action to take.
   * @param string $queryKey The parameter name to pass in the query
   * @param string $queryValue The parameter value to pass in the query
   * @return SimpleXMLElement
   */
  public static function directoryFetch ($action, $queryKey, $queryValue) {
    if (DIRECTORY_ADMIN_ACCESS_KEY) {
      $opts = array(
        'http' => array(
          'header' =>
            "Admin-Access: ".DIRECTORY_ADMIN_ACCESS_KEY."\r\n".
            "User-Agent: Drupal CAS-MM-Sync\r\n",
        )
      );
      $context = stream_context_create($opts);
    } else {
      $context = null;
    }

    $params = array(
      'action'    => $action,
      $queryKey    => $queryValue,
    );
    if (!defined('DIRECTORY_BASE_URL'))
      throw new Exception('DIRECTORY_BASE_URL is not defined');
    $base = DIRECTORY_BASE_URL;
    if (empty($base))
      throw new Exception('DIRECTORY_BASE_URL is empty');
    $url = $base . '?' . http_build_query($params, '', '&');
    $xmlString = @file_get_contents($url, false, $context);
    if (!$xmlString)
      throw new Exception("Couldn't fetch user for $queryKey '$queryValue'.");
    return simplexml_load_string($xmlString);
  }

}

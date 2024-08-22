<?php


class GoAuthLdap implements GoAuthAuthenticatedSessionInterface, GoAuthLookupInterface {

  private static $ldap;
  private $user;

  public function __construct($username, $password) {
    self::connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_NAME . "=" . $username . "))";

    $result = ldap_search(self::$ldap, GO_AUTH_LDAP_PATH, $filter, array("distinguishedname", GO_AUTH_LDAP_ATTR_ID, GO_AUTH_LDAP_ATTR_NAME, GO_AUTH_LDAP_ATTR_EMAIL));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries(self::$ldap, $result);

    if (ldap_bind(self::$ldap, $entries[0]["distinguishedname"][0], $password) === false) throw new Exception("Cannot bind as user");

    $this->user = $entries[0];
  }

  private static function connect() {
    self::$ldap = ldap_connect(GO_AUTH_LDAP_HOST, GO_AUTH_LDAP_PORT);

    if (self::$ldap === false) throw new Exception("Cannot establish connection to LDAP server");

    if (ldap_set_option(self::$ldap, LDAP_OPT_PROTOCOL_VERSION, 3) === false) throw new Exception("Cannot set LDAP_OPT_PROTOCOL_VERSION");
    if (ldap_set_option(self::$ldap, LDAP_OPT_REFERRALS, 0) === false) throw new Exception("Cannot set LDAP_OPT_REFERRALS");

    if (ldap_bind(self::$ldap, GO_AUTH_LDAP_USER, GO_AUTH_LDAP_PASS) === false) throw new Exception("Cannot bind as LDAP user");
  }

  /**
   * Answer true if a user is currently authenticated.
   *
   * @return boolean
   * @access public
   */
  public function isAuthenticated() {
    return true;
  }

  /**
   * Get the internal ID of the current user.
   *
   * @access public
   * @return string The ID of the requested user.
   * @since 06-08-2009
   */
  public function getCurrentUserId() {
    return $this->user[GO_AUTH_LDAP_ATTR_ID][0];
  }

  /**
   * Get the internal ID of a user.
   *
   * @access public
   * @param string $username A username to find the ID of.
   * @return string The ID of the requested user.
   */
  public static function getIdForUser($username) {
    self::connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_NAME . "=" . $username . "))";

    $result = ldap_search(self::$ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_ID));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries(self::$ldap, $result);

    return $entries[0][GO_AUTH_LDAP_ATTR_ID][0];
  }

  /**
   * Get the username of the current user.
   *
   * @access public
   * @return string The username of the requested user.
   */
  public function getCurrentUserName() {
    return $this->user[GO_AUTH_LDAP_ATTR_NAME][0];
  }

  /**
   * Get the username of a user.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The username of the requested user.
   */
  public static function getNameByUserId($id) {
    self::connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_ID . "=" . $id . "))";

    $result = ldap_search(self::$ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_NAME));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries(self::$ldap, $result);

    return $entries[0][GO_AUTH_LDAP_ATTR_NAME][0];
  }

  /**
   * Get the email address of a user.
   *
   * @access public
   * @return string The email address of the requested user.
   * @since 06-08-2009
   */
  public function getCurrentUserEmail() {
    return $this->user[GO_AUTH_LDAP_ATTR_ID][0];
  }

  /**
   * Get the email address of a user.
   *
   * @access public
   * @param string $id A user ID to find the email of.
   * @return string The email address of the requested user.
   */
  public static function getEmailByUserId($id) {
    self::connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_ID . "=" . $id . "))";

    $result = ldap_search(self::$ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_EMAIL));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries(self::$ldap, $result);

    return $entries[0][GO_AUTH_LDAP_ATTR_EMAIL][0];
  }


  /**
   * Get the display of a user.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The display name of the requested user.
   */
  public static function getDisplayNameByUserId($id) {
    return self::getEmailByUserId($id);
  }

}

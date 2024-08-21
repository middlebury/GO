<?php

/**
 * Access to the static methods of the current authentication implementation.
 */
abstract class GoAuth implements GoAuthLookupInterface {

  /**
   * Answer the currently configured implementation class.
   *
   */
  public static function authClass() {
    if (AUTH_METHOD == 'ldap') {
      return 'GoAuthLdap';
    } elseif (AUTH_METHOD == 'cas') {
      return 'GoAuthCas';
    } elseif (AUTH_METHOD == 'saml') {
      return 'GoAuthSaml';
    } else {
      throw new Exception('Unknown Auth Method');
    }
  }

  /**
   * Answer the currently configured lookup class.
   *
   */
  public static function userLookupClass() {
    if (USER_LOOKUP_METHOD == 'ldap') {
      return 'GoAuthLdap';
    } elseif (USER_LOOKUP_METHOD == 'cas') {
      return 'GoAuthCas';
    } elseif (USER_LOOKUP_METHOD == 'microsoft_graph') {
      return 'GoAuthLookupMicrosoftGraph';
    } else {
      throw new Exception('Unknown USER_LOOKUP_METHOD');
    }
  }

  /**
   * Get the internal ID of a user.
   *
   * @access public
   * @param string $username A username to find the ID of.
   * @return string The ID of the requested user.
   */
  public static function getIdForUser($username) {
    return self::userLookupClass()::getIdForUser($username);
  }

  /**
   * Get the username of a user.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The username of the requested user.
   */
  public static function getNameByUserId($id) {
    return self::userLookupClass()::getNameByUserId($id);
  }

  /**
   * Get the email address of a user.
   *
   * @access public
   * @param string $id A user ID to find the email of.
   * @return string The email address of the requested user.
   */
  public static function getEmailByUserId($id) {
    return self::userLookupClass()::getEmailByUserId($id);
  }

}

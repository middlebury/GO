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
   * Get the internal ID of a user.
   *
   * @access public
   * @param string $username A username to find the ID of.
   * @return string The ID of the requested user.
   */
  public static function getIdForUser($username) {
    return self::authClass()::getIdForUser($username);
  }

  /**
   * Get the username of a user.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The username of the requested user.
   */
  public static function getNameByUserId($id) {
    return self::authClass()::getNameByUserId($id);
  }

  /**
   * Get the email address of a user.
   *
   * @access public
   * @param string $id A user ID to find the email of.
   * @return string The email address of the requested user.
   */
  public static function getEmailByUserId($id) {
    return self::authClass()::getEmailByUserId($id);
  }

}

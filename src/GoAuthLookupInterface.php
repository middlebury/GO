<?php

/**
 * Interface for attribute lookups based on the configured authentication method.
 *
 * Static methods may be called in either an authenticated or anonymous context.
 *
 * @author Ian McBride <imcbride@middlebury.edu>
 * @category GO
 * @copyright 2022 The President and Fellows of Middlebury College
 * @license GNU General Public License (GPL) version 3 or later
 * @package GO
 * @link http://go.middlebury.edu/
 */
interface GoAuthLookupInterface {

  /**
   * Get the internal ID of a user.
   *
   * @access public
   * @param string $username A username to find the ID of.
   * @return string The ID of the requested user.
   */
  public static function getIdForUser($username);

  /**
   * Get the username of a user.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The username of the requested user.
   */
  public static function getNameByUserId($id);

  /**
   * Get the email address of a user.
   *
   * @access public
   * @param string $id A user ID to find the email of.
   * @return string The email address of the requested user.
   */
  public static function getEmailByUserId($id);

}

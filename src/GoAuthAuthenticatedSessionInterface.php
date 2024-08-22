<?php

/**
 * Interface for accessing information about the currently authenticed account.
 *
 * Instances are created to authenticate a user and only valid in an
 * authenticated context.
 *
 * @author Ian McBride <imcbride@middlebury.edu>
 * @category GO
 * @copyright 2022 The President and Fellows of Middlebury College
 * @license GNU General Public License (GPL) version 3 or later
 * @package GO
 * @link http://go.middlebury.edu/
 */
interface GoAuthAuthenticatedSessionInterface {

  /**
   * Answer true if a user is currently authenticated.
   *
   * @return boolean
   * @access public
   */
  public function isAuthenticated();

  /**
   * Get the internal ID of the current user.
   *
   * @access public
   * @return string The ID of the requested user.
   */
  public function getCurrentUserId();

  /**
   * Get the username of the current user.
   *
   * @access public
   * @return string The username of the requested user.
   */
  public function getCurrentUserName();

  /**
   * Get the email address of a user.
   *
   * @access public
   * @return string The email address of the requested user.
   */
  public function getCurrentUserEmail();

}

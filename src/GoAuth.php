<?php

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

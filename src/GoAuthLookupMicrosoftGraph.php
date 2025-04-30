<?php

use Microsoft\Graph\GraphServiceClient;
use Microsoft\Graph\Generated\Models\User;
use Microsoft\Graph\Generated\Users\UsersRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\UsersRequestBuilderGetRequestConfiguration;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;

/**
 * Perform attribute lookups based on Microsoft's Graph API.
 *
 * Static methods may be called in either an authenticated or anonymous context.
 *
 * @author Adam Franco <afranco@middlebury.edu>
 * @category GO
 * @copyright 2024 The President and Fellows of Middlebury College
 * @license GNU General Public License (GPL) version 3 or later
 * @package GO
 * @link http://go.middlebury.edu/
 */
class GoAuthLookupMicrosoftGraph implements GoAuthLookupInterface {

  /**
   * The O365 Graph Api client.
   *
   * @var \Microsoft\Graph\Graph|null
   */
  protected $graph;

  /**
   * Lookup singleton.
   *
   * @var \GoAuthLookupMicrosoftGraph
   */
  protected static $instance;

  /**
   * Get our class singleton.
   */
  protected static function getInstance() {
    if (!isset(self::$instance)) {
      self::$instance = new static();
    }
    return self::$instance;
  }

  /**
   * Get the internal ID of a user.
   *
   * @param string $username A username to find the ID of.
   * @return string The ID of the requested user.
   */
  public static function getIdForUser($username) {
    $lookup = self::getInstance();
    try {
      $user = $lookup->fetchUserByProperty('userPrincipalName', $username . '@middlebury.edu');
    } catch (\Exception $e) {
      if ($e->getCode() == 404) {
        $user = $lookup->fetchUserByProperty('mail', $username . '@middlebury.edu');
      } else {
        throw $e;
      }
    }
    return $lookup->getUserProperty($user, $lookup->getUniqueIdProperty());
  }

  /**
   * Get the username of a user.
   *
   * @param string $id A user ID to find the username of.
   * @return string The username of the requested user.
   */
  public static function getNameByUserId($id) {
    $lookup = self::getInstance();
    $user = $lookup->fetchUserForLogin($id);

    if (preg_match('/^(.+)@(.+)$/', $user->getUserPrincipalName(), $matches)) {
      return $matches[1];
    }
    else {
      return (string)$user->getUserPrincipalName();
    }
  }

  /**
   * Get the email address of a user.
   *
   * @param string $id A user ID to find the email of.
   * @return string The email address of the requested user.
   */
  public static function getEmailByUserId($id) {
    $lookup = self::getInstance();
    $user = $lookup->fetchUserForLogin($id);
    return (string)$user->getMail();
  }

  /**
   * Get the display of a user.
   *
   * @access public
   * @param string $id A user ID to find the username of.
   * @return string The display name of the requested user.
   */
  public static function getDisplayNameByUserId($id) {
    $lookup = self::getInstance();
    $user = $lookup->fetchUserForLogin($id);

    $displayName = trim($user->getGivenName()." ".$user->getSurname());
    if (empty($displayName)) {
      $displayName = trim($user->getDisplayName());
    }
    if (empty($displayName)) {
      $displayName = (string)$user->getUserPrincipalName();
    }
    return $displayName;
  }

  /**
   * Answer our already-configured O365 API.
   *
   * @return \Microsoft\Graph\Graph
   *   The Graph object.
   */
  protected function getGraph() {
    if (empty($this->graph)) {
      $this->graph = new GraphServiceClient(
        $this->getTokenRequestContext()
      );
    }
    return $this->graph;
  }

  /**
   * Get an O365 Access token context.
   */
  protected function getTokenRequestContext() {
    if (!defined('MICROSOFT_GRAPH_TENANT_ID') || empty(MICROSOFT_GRAPH_TENANT_ID)) {
      throw new \Exception("No MICROSOFT_GRAPH_TENANT_ID defined.");
    }
    if (!defined('MICROSOFT_GRAPH_APP_ID') || empty(MICROSOFT_GRAPH_APP_ID)) {
      throw new \Exception("No MICROSOFT_GRAPH_APP_ID defined.");
    }
    if (!defined('MICROSOFT_GRAPH_APP_SECRET') || empty(MICROSOFT_GRAPH_APP_SECRET)) {
      throw new \Exception("No MICROSOFT_GRAPH_APP_SECRET defined.");
    }

    return new ClientCredentialContext(
      MICROSOFT_GRAPH_TENANT_ID,
      MICROSOFT_GRAPH_APP_ID,
      MICROSOFT_GRAPH_APP_SECRET,
    );
  }

  /**
   * Answer an MS Graph User object matching a login string.
   *
   * @param string $login
   * @return Microsoft\Graph\Generated\Models\User
   */
  protected function fetchUserForLogin($login) {
    return $this->fetchUserByProperty($this->getUniqueIdProperty(), $login);
  }

  /**
   * Answer an MS Graph User object matching a login string.
   *
   * @param string $property
   *   The MSGraph property to match.
   * @param string $value
   *   The user-id value to match.
   * @return Microsoft\Graph\Generated\Models\User
   */
  protected function fetchUserByProperty($property, $value) {
    $requestConfig = new UsersRequestBuilderGetRequestConfiguration(
      queryParameters: UsersRequestBuilderGetRequestConfiguration::createQueryParameters(
        filter: $property . " eq '" . str_replace("'", "", $value) . "'",
        select: $this->getUserGraphProperties(),
        orderby: ["displayName"],
        top: 10,
        count: true
      ),
      headers: ['ConsistencyLevel' => 'eventual']
    );
    $result = $this->getGraph()->users()->get($requestConfig)->wait();
    $users = [];
    foreach ($result->getValue() as $user) {
      $users[] = $user;
    }
    if (count($users) < 1) {
      throw new \Exception('Could not get user. Expecting 1 entry, found '.count($users).' in AzureAD.', 404);
    } else if (count($users) === 1) {
      return $users[0];
    } else {
      return $this->getPrimaryAccountFromUserList($users);
    }
  }

  protected function getUserGraphProperties() {
    return [
      'id',
      'userPrincipalName',
      'displayName',
      'mail',
      'givenName',
      'surname',
      'userType',
      $this->getUniqueIdProperty(),
    ];
  }

  /**
   * Filter a list of MS Graph User objects to find a single "primary" one.
   *
   * @param array $users
   *   The MSGraph User list.
   * @return Microsoft\Graph\Generated\Models\User
   *   A single user if one can be determined to be "primary".
   */
  protected function getPrimaryAccountFromUserList(array $users) {
    // Give priority to users with the type "Member" over "Guest" or other
    // account types.
    $memberUsers = [];
    foreach ($users as $user) {
      if (strtolower($user->getUserType()) == "member") {
        $memberUsers[] = $user;
      }
    }
    // If we only have a single user with type "Member", then return that user.
    if (count($memberUsers) === 1) {
      return $memberUsers[0];
    }

    // Not sure what to do if we have multiple "Member" accounts with the same
    // ID or multiple "Guest" accounts with the same ID.
    // Perhaps we could do some email filtering or other logic, but hopefully
    // this case won't come up.
    ob_start();
    foreach ($users as $user) {
      $properties = $user->getProperties();
      print "\n\t<hr><dl>";
      print "\n\t\t<dt>Unique ID property (".$this->getUniqueIdProperty()."):</dt><dd>".(empty($properties[$this->getUniqueIdProperty()])?"":$properties[$this->getUniqueIdProperty()])."</dd>";
      print "\n\t\t<dt>User Type:</dt><dd>".$user->getUserType()."</dd>";
      print "\n\t\t<dt>Mapped username in WordPress:</dt><dd>".$this->getLoginForGraphUser($user)."</dd>";
      print "\n\t\t<dt>UserPrincipalName:</dt><dd>".$user->getUserPrincipalName()."</dd>";
      print "\n\t\t<dt>Display Name:</dt><dd>".$user->getDisplayName()."</dd>";
      print "\n\t\t<dt>Mail:</dt><dd>".$user->getMail()."</dd>";
      print "\n\t</dl>";
    }
    throw new \Exception('Could not get single user for ID. Expecting 1 entry, found '.count($users)." users in AzureAD that share an ID and User Type:\n".ob_get_clean());
  }

  /**
   * Answer the primary unique-id property key.
   *
   * @return string
   *   The property in MS Graph that holds the primary unique-id.
   */
  protected function getUniqueIdProperty() {
    static $userIdProperty;
    if (!isset($userIdProperty)) {
      if (!defined('MICROSOFT_GRAPH_UNIQUE_ID_PROPERTY') || empty(MICROSOFT_GRAPH_UNIQUE_ID_PROPERTY)) {
        throw new \Exception("No MICROSOFT_GRAPH_UNIQUE_ID_PROPERTY configured.");
      }
      $userIdProperty = MICROSOFT_GRAPH_UNIQUE_ID_PROPERTY;
    }
    return $userIdProperty;
  }

  /**
   * Answer the user login matching an MS Graph User object.
   *
   * @param \Microsoft\Graph\Generated\Models\User $user
   * @return string
   */
  protected function getLoginForGraphUser (User $user) {
    // Primary Unique ID.
    $id = $this->getUserProperty($user, $this->getUniqueIdProperty());
    if (!empty($id)) {
      return $id;
    }
    else {
      throw new \Exception('No id could be extracted for user ' . $user->getUserPrincipalName());
    }
  }

  /**
   * Answer a property from a User object.
   *
   * @param \Microsoft\Graph\Generated\Models\User $user
   *   The user object.
   * @param string $property
   *   The property name to fetch.
   * @return mixed
   *   The property or null.
   */
  protected function getUserProperty(User $user, $property) {
    $getterMethod = 'get'.ucfirst($property);
    if (method_exists($user, $getterMethod)) {
      return $user->$getterMethod();
    }
    else {
      $additionalData = $user->getAdditionalData();
      if (!isset($additionalData[$property])) {
        throw new \Exception("No '$property' could be found for user " . $user->getUserPrincipalName() . ' in ' . print_r($addtionalData, true));
      }
      return $additionalData[$property];
    }
  }

}

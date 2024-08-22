<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use OneLogin\Saml2\Auth as SamlAuth;

class GoAuthSaml implements GoAuthAuthenticatedSessionInterface {

  /**
   * Answer true if a user is currently authenticated.
   *
   * @return boolean
   * @access public
   */
  public function isAuthenticated() {
    if (!empty($_SESSION['SAML_AUTH_NAMEID'])) {
      return true;
    }

    return $this->getAuth()->isAuthenticated();
  }

  /**
   * Get the internal ID of the current user.
   *
   * @return string The ID of the requested user.
   */
  public function getCurrentUserId() {
    if (!empty($_SESSION['SAML_AUTH_NAMEID'])) {
      return $_SESSION['SAML_AUTH_NAMEID'];
    }

    if ($this->isAuthenticated()) {
      return $this->getAuth()->getNameId();
    }
    else {
      throw new Exception("No user is authenticated, cannot provide a user id.");
    }
  }

  /**
   * Get the username of the current user.
   *
   * @return string The username of the requested user.
   */
  public function getCurrentUserName() {
    return trim($this->getAttribute('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'));
  }

  /**
   * Get the email address of the current user.
   *
   * @return string The email address of the requested user.
   */
  public function getCurrentUserEmail() {
    return trim($this->getAttribute('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'));
  }

  /**
   * Get the groups for the current user.
   *
   * @return array
   *   The group identifiers of the current user.
   */
  public function getCurrentUserGroups() {
    return $this->getAttribute('http://schemas.microsoft.com/ws/2008/06/identity/claims/groups', false);
  }

  /**
   * Authenticate a user and set up session variables.
   */
  public function authenticate() {
    $auth = $this->getAuth();
    // Trigger SSO login.
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      if (!$this->isAuthenticated()) {
        // Pass our return URL if it is local to this application.
        if (!empty($_GET['r']) && strpos($_GET['r'], $this->getAbsoluteUrl()) === 0) {
          $auth->login($_GET['r']);
        } else {
          $auth->login();
        }
      }

      return true;
    }
    // Process SSO response.
    else {
      if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
        $requestID = $_SESSION['AuthNRequestID'];
      } else {
        $requestID = null;
      }

      $auth->processResponse($requestID);
      unset($_SESSION['AuthNRequestID']);

      $errors = $auth->getErrors();
      if (!empty($errors)) {
        throw new \Exception("Authentication failed with these errors: " . implode(', ', $errors));
      }

      if (!$auth->isAuthenticated()) {
        throw new \Exception("Authentication failed.");
      }

      $_SESSION['SAML_AUTH_NAMEID'] = $auth->getNameId();
      $_SESSION['SAML_AUTH_ATTRIBUTES'] = $auth->getAttributes();

      // Check $allowedGroups from config if set.
      global $allowedGroups;
      if (isset($allowedGroups) && !empty($allowedGroups)) {
        $groups = $this->getCurrentUserGroups();
        if (!is_array($groups)) {
          $groups = [$groups];
        }
        if (!$this->userGroupsInAllowedGroups($groups, $allowedGroups)) {
          throw new AuthorizationFailedException();
        }
      }

      // Redirect to the page we started the Login flow from.
      if (isset($_REQUEST['RelayState'])) {
        $auth->redirectTo();
      }
      return true;
    }
  }

  /**
   * Log out. Throw an exception if isAuthenticationEnabled is false.
   *
   * @param optional string $returnUrl A url to return to after successful logout.
   * @return void
   * @access public
   */
  public function logout($returnUrl = null)
  {
    unset($_SESSION['SAML_AUTH_NAMEID']);
    unset($_SESSION['SAML_AUTH_ATTRIBUTES']);
    $this->getAuth()->logout($returnUrl);
  }

  /**
   * Get the authentication provider.
   *
   * @return OneLogin\Saml2\Auth
   *   The authentication provider.
   */
  public function getAuth () {
    static $auth;
    if (empty($auth)) {
      require_once(dirname(dirname(__FILE__)) . '/vendor/onelogin/php-saml/_toolkit_loader.php');
      $auth = new SamlAuth($this->getSamlConfig());
    }
    return $auth;
  }

  /**
   * Answer the SAML configuration.
   *
   * @return array
   */
  protected function getSamlConfig() {
    if (!defined('SAML_IDP_ENTITY_ID')) {
      throw new \Exception('SAML_IDP_ENTITY_ID must be defined to use SAML authentication.');
    }
    if (!defined('SAML_IDP_SINGLE_SIGNON_SERVICE_URL')) {
      throw new \Exception('SAML_IDP_SINGLE_SIGNON_SERVICE_URL must be defined to use SAML authentication.');
    }
    if (!defined('SAML_IDP_SINGLE_LOGOUT_SERVICE_URL')) {
      throw new \Exception('SAML_IDP_SINGLE_LOGOUT_SERVICE_URL must be defined to use SAML authentication.');
    }
    if (!defined('SAML_IDP_X509_CERT')) {
      throw new \Exception('SAML_IDP_X509_CERT must be defined to use SAML authentication.');
    }
    return [
      // If 'strict' is True, then the PHP Toolkit will reject unsigned
      // or unencrypted messages if it expects them to be signed or encrypted.
      // Also it will reject the messages if the SAML standard is not strictly
      // followed: Destination, NameId, Conditions ... are validated too.
      'strict' => true,

      // Enable debug mode (to print errors).
      'debug' => true,

      // Set a BaseURL to be used instead of try to guess
      // the BaseURL of the view that process the SAML Message.
      // Ex http://sp.example.com/
      //  http://example.com/sp/
      // 'baseurl' => rtrim($this->getAbsoluteUrl(), '/'),

      // Service Provider Data that we are deploying.
      'sp' => [
        // Identifier of the SP entity  (must be a URI)
        'entityId' => $this->getAbsoluteUrl(),
        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => [
          // URL Location where the <Response> from the IdP will be returned
          'url' => $this->getAbsoluteUrl('/login2.php'),
          // SAML protocol binding to be used when returning the <Response>
          // message. SAML Toolkit supports this endpoint for the
          // HTTP-POST binding only.
          'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ],
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        'singleLogoutService' => [
          // URL Location where the <Response> from the IdP will be returned
          'url' => $this->getAbsoluteUrl('/logout.php'),
          // SAML protocol binding to be used when returning the <Response>
          // message. SAML Toolkit supports the HTTP-Redirect binding
          // only for this endpoint.
          'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        /*
         * Key rollover
         * If you plan to update the SP x509cert and privateKey
         * you can define here the new x509cert and it will be
         * published on the SP metadata so Identity Providers can
         * read them and get ready for rollover.
         */
        // 'x509certNew' => '',
      ],

      // Identity Provider Data that we want connected with our SP.
      'idp' => [
        // Identifier of the IdP entity  (must be a URI)
        'entityId' => SAML_IDP_ENTITY_ID,
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => [
          // URL Target of the IdP where the Authentication Request Message
          // will be sent.
          'url' => SAML_IDP_SINGLE_SIGNON_SERVICE_URL,
          // SAML protocol binding to be used when returning the <Response>
          // message. SAML Toolkit supports the HTTP-Redirect binding
          // only for this endpoint.
          'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        // SLO endpoint info of the IdP.
        'singleLogoutService' => [
          // URL Location of the IdP where SLO Request will be sent.
          'url' => SAML_IDP_SINGLE_LOGOUT_SERVICE_URL,
          // URL location of the IdP where the SP will send the SLO Response (ResponseLocation)
          // if not set, url for the SLO Request will be used
          'responseUrl' => '',
          // SAML protocol binding to be used when returning the <Response>
          // message. SAML Toolkit supports the HTTP-Redirect binding
          // only for this endpoint.
          'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        // Public x509 certificate of the IdP
        'x509cert' => SAML_IDP_X509_CERT,
        /*
         *  Instead of use the whole x509cert you can use a fingerprint in order to
         *  validate a SAMLResponse, but we don't recommend to use that
         *  method on production since is exploitable by a collision attack.
         *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it,
         *   or add for example the -sha256 , -sha384 or -sha512 parameter)
         *
         *  If a fingerprint is provided, then the certFingerprintAlgorithm is required in order to
         *  let the toolkit know which algorithm was used. Possible values: sha1, sha256, sha384 or sha512
         *  'sha1' is the default value.
         *
         *  Notice that if you want to validate any SAML Message sent by the HTTP-Redirect binding, you
         *  will need to provide the whole x509cert.
         */
        // 'certFingerprint' => '',
        // 'certFingerprintAlgorithm' => 'sha1',

        /* In some scenarios the IdP uses different certificates for
         * signing/encryption, or is under key rollover phase and
         * more than one certificate is published on IdP metadata.
         * In order to handle that the toolkit offers that parameter.
         * (when used, 'x509cert' and 'certFingerprint' values are
         * ignored).
         */
        // 'x509certMulti' => [
        //   'signing' => [
        //     0 => '<cert1-string>',
        //   ],
        //   'encryption' => [
        //     0 => '<cert2-string>',
        //   ],
      ],
    ];
  }

  protected function getAbsoluteUrl($path = '/') {
    return 'https://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/' . ltrim($path, '/');
  }

  /**
   * Answer an attribute from the SAML response.
   *
   * @param string $name
   *   The attribute name.
   * @param bool $singleValue
   *   Return a single value from the attribute array rather than multiple.
   * @return string|array|null
   *   The attribute value.
   */
  protected function getAttribute($name, $singleValue = true) {
    // Prefer attributes stored in the session already.
    if (!empty($_SESSION['SAML_AUTH_ATTRIBUTES'])) {
      if (empty($_SESSION['SAML_AUTH_ATTRIBUTES'][$name])) {
        return null;
      }
      else {
        if ($singleValue) {
          if (isset($_SESSION['SAML_AUTH_ATTRIBUTES'][$name][0])) {
            return $_SESSION['SAML_AUTH_ATTRIBUTES'][$name][0];
          }
          else {
            return null;
          }
        }
        else {
          return $_SESSION['SAML_AUTH_ATTRIBUTES'][$name];
        }
      }
    }
    // Get the attributes from the current authentication response if
    // available.
    else {
      if ($this->isAuthenticated()) {
        return $this->auth->getAttribute($name);
      }
      else {
        throw new Exception("No user is authenticated, cannot provide a $name attribute.");
      }
    }
  }

  /**
   * Verify that a user's groups are in the allowed groups.
   *
   * @var array $usersGroups
   *   The user's groups.
   * @var array $allowedGroups
   *   The allowed groups.
   * @return bool
   *   True if the user has a group in the allowed groups, false otherwise.
   */
  protected function userGroupsInAllowedGroups(array $usersGroups, array $allowedGroups) {
    foreach ($usersGroups as $group) {
      if (in_array($group, $allowedGroups)) {
        return true;
      }
    }
    return false;
  }

}

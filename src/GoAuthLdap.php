<?php


class GoAuthLdap extends GoAuth {

  private $ldap;

  public function __construct($username, $password) {
    $this->connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_NAME . "=" . $username . "))";

    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array("distinguishedname", GO_AUTH_LDAP_ATTR_ID, GO_AUTH_LDAP_ATTR_NAME, GO_AUTH_LDAP_ATTR_EMAIL));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries($this->ldap, $result);

    if (ldap_bind($this->ldap, $entries[0]["distinguishedname"][0], $password) === false) throw new Exception("Cannot bind as user");

    $this->user = $entries[0];
  }

  private function connect() {
    $this->ldap = ldap_connect(GO_AUTH_LDAP_HOST, GO_AUTH_LDAP_PORT);

    if ($this->ldap === false) throw new Exception("Cannot establish connection to LDAP server");

    if (ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3) === false) throw new Exception("Cannot set LDAP_OPT_PROTOCOL_VERSION");
    if (ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0) === false) throw new Exception("Cannot set LDAP_OPT_REFERRALS");

    if (ldap_bind($this->ldap, GO_AUTH_LDAP_USER, GO_AUTH_LDAP_PASS) === false) throw new Exception("Cannot bind as LDAP user");
  }

  public function getId($username = null) {
    if (is_null($username)) {
      return $this->user[GO_AUTH_LDAP_ATTR_ID][0];
    }

    $this->connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_NAME . "=" . $username . "))";

    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_ID));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries($this->ldap, $result);

    return $entries[0][GO_AUTH_LDAP_ATTR_ID][0];
  }

  public function getName($id = null) {
    if (is_null($id)) {
      return $this->user[GO_AUTH_LDAP_ATTR_NAME][0];
    }

    $this->connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_ID . "=" . $id . "))";

    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_NAME));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries($this->ldap, $result);

    return $entries[0][GO_AUTH_LDAP_ATTR_NAME][0];
  }

  public function getEmail($id = null) {
    if (is_null($id)) {
      return $this->user[GO_AUTH_LDAP_ATTR_ID][0];
    }

    $this->connect();

    $filter = "(&(objectclass=user)(" . GO_AUTH_LDAP_ATTR_ID . "=" . $id . "))";

    $result = ldap_search($this->ldap, GO_AUTH_LDAP_PATH, $filter, array(GO_AUTH_LDAP_ATTR_EMAIL));

    if ($result === false) throw new Exception("Cannot find user");

    $entries = ldap_get_entries($this->ldap, $result);

    return $entries[0][GO_AUTH_LDAP_ATTR_EMAIL][0];
  }

}

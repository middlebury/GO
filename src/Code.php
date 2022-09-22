<?php

/**
 * Code Class for GO Application.
 *
 * A "code" in go is defined as the part of the GO shortcut url following the
 * hostname. This is the "path" construct in a traditional URL. GO shortcut code
 * are more tightly structured than normal URL paths and may contain only
 * alphanumic characters and the two separator characters defined for the GO
 * service: the question mark (?) and the forward slash (/).
 *
 * @author Ian McBride <imcbride@middlebury.edu>
 * @category GO
 * @copyright 2009 The President and Fellows of Middlebury College
 * @license GNU General Public License (GPL) version 3 or later
 * @package GO
 * @version 02-25-2009
 * @link http://go.middlebury.edu/
 */
class Code {

	/**
	 * Answer true if the code exists, false otherwise.
	 *
	 * @access public
	 * @param string $name The full path string of the code.
	 * @param optional string $institution The institution for the code.
	 * @throws Exception from PDO functions.
	 */
	public static function exists($name, $institution = "middlebury.edu") {
		global $connection;

		$select = $connection->prepare("SELECT COUNT(*) AS num FROM code WHERE name = :name AND institution = :institution");
		$select->bindValue(":name", $name);
		$select->bindValue(":institution", $institution);
		$select->execute();
		if ($select->fetchColumn(0) >= 1)
			return true;
		else
			return false;
	}

	/**
	 * Answer a Code object for the name and institution if one exists, throw an
	 * Exception otherwise.
	 *
	 * @param string $name The full name of the Code
	 * @param optional string $institution The institution for the code.
	 * @return object Code
	 * @access public
	 * @since 6/29/10
	 */
	public static function get ($name, $institution = "middlebury.edu") {
		$name = str_replace(" ", "+", $_GET["code"]);

		// Try searching with the name as given.
		if (self::exists($name, $institution)) {
			$code = new Code($name, $institution);
		} else if (Alias::exists($name, $institution)) {
			$alias = new Alias($name, null, $institution);
			$code = new Code($alias->getCode(), $alias->getInstitution());
		} else {

			// Try searching with the trailing slash stripped.
			$name = trim($name, '/');
			if (Code::exists($name, $institution)) {
				$code = new Code($name, $institution);
			} else if (Alias::exists($name, $institution)) {
				$alias = new Alias($name, null, $institution);
				$code = new Code($alias->getCode(), $alias->getInstitution());
			} else {
				throw new Exception('Unknown Code "'.$name.'".');
			}
		}

		return $code;
	}

	/**
	 * Regular expression pattern to match allowed characters for codes.
	 *
	 * @since 02-25-2009
	 */
	const ALLOWED_CODES = "/^[A-Za-z0-9-_\?\/\.~\+%]+$/";

	/**
	 * Regular expression pattern to match allowed characters for urls.
	 *
	 * @since 10-07-2010
	 */
	const ALLOWED_URLS = "/^(http|ftp)s?:\/\/[A-Za-z0-9-_\?\/\.~\+\(\)\*\[\]%&=:;#@, !]+$/";

	/**
	 * Regular expression pattern to match allowed characters for descriptions.
	 *
	 * @since 10-08-2010
	 */
	const ALLOWED_DESC = "/^[A-Za-z0-9-_\?\/\.~\+%&=:;\s\(\)\[\]!@#\$\*'\",]*$/";

	/**
	 * Regular expression pattern to match allowed characters for descriptions.
	 *
	 * @since 10-08-2010
	 */
	const ALLOWED_ADMIN = "/^[A-Za-z0-9]+$/";

	/**
	 * Answer true if name validates, false if not.
	 *
	 * @param string $name The full name of the Code
	 * @access public
	 */
	public static function isValidCode ($name) {
		// Codes shouldn't start with "go/"
		if (preg_match('/^go\//', $name))
			return false;
		if (!preg_match(Code::ALLOWED_CODES, $name))
			return false;
		return true;
	}

	/**
	 * Answer true if a name is banned
	 *
	 * @param string $name
	 * @return boolean
	 */
	public static function isCodeBanned ($name) {
		global $connection;
		$select = $connection->prepare("SELECT COUNT(*) FROM banned_codes WHERE code = ?");
		$select->execute(array(strtolower($name)));
		$num = $select->fetchColumn();
		$select->closeCursor();
		return !empty($num);
	}

	/**
	 * Ban a name from being used in the future.
	 *
	 * @param string $name
	 * @return void
	 */
	public static function banCode ($name) {
		global $connection;
		$insert = $connection->prepare("INSERT INTO banned_codes (code, banned_by, banned_by_display_name) VALUES (?, ?, ?)");
		$insert->execute(array(strtolower($name), $_SESSION["AUTH"]->getCurrentUserId(), $_SESSION["AUTH"]->getCurrentUserName()));
	}

	/**
	 * Answer true if user is an admin of code, false if not.
	 *
	 * @param string $name The username
	 * @access public
	 */
	public function isAdmin ($name) {
		$users = $this->getUsers();
		if (array_key_exists($name, $users)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Answer true if name validates, false if not.
	 *
	 * @param string $name The full URL
	 * @access public
	 */
	public static function isValidUrl ($name) {
		return preg_match(Code::ALLOWED_URLS, $name);
	}

	/**
	 * Answer true if name validates, false if not.
	 *
	 * @param string $name The full description
	 * @access public
	 */
	public static function isValidDescription ($name) {
		return preg_match(Code::ALLOWED_DESC, $name);
	}

	/**
	 * Answer true if name validates, false if not.
	 *
	 * @param string $name The full name of the admin
	 * @access public
	 */
	public static function isValidAdmin ($name) {
		return preg_match(Code::ALLOWED_ADMIN, $name);
	}

	/**
	 * The "name" of the code is the full path string.
	 *
	 * @access protected
	 * @since 02-25-2009
	 * @var string The full path string of the code.
	 */
	protected $name;

	/**
	 * The institution or "host" related to this code.
	 *
	 * @access protected
	 * @since 04-03-2009
	 * @var string The institution or "host" related to this code.
	 */
	protected $institution;

	/**
	 * The URL of the code.
	 *
	 * @access protected
	 * @since 02-26-2009
	 * @var string The URL of the code.
	 */
	protected $url;

	/**
	 * A description of the code.
	 *
	 * @access protected
	 * @since 02-27-2009
	 * @var string A description of the code.
	 */
	protected $description;

	/**
	 * Whether or not the code appears on the GOtionary.
	 *
	 * @access protected
	 * @since 04-06-2009
	 * @var bool Whether or not the code appears on the GOtionary.
	 */
	protected $public;

	/**
	 * Whether or not the code is searchable on the main website.
	 *
	 * @access protected
	 * @since 04-06-2009
	 * @var bool Whether or not the code is searchable on the main website.
	 */
	protected $unsearchable;

	/**
	 * An array of {@link User}s that have access to this code.
	 *
	 * @access protected
	 * @since 02-26-2009
	 * @var array An array of {@link User}s that have access to this code.
	 */
	protected $users = array();

	/**
	 * An array of {@link Alias}es that are associated with this code.
	 *
	 * @access protected
	 * @since 04-03-2009
	 * @var array An array of {@link Alias}es that are associated with this code.
	 */
	protected $aliases = array();

	/**
	 * Class constructor for {@link Code}.
	 *
	 * @access public
	 * @param string $name The full path string of the code.
	 * @since 02-25-2009
	 * @throws Exception from {@link Code::setName()}
	 * @throws Exception from {@link Code::setInstitution()}
	 * @throws Exception from {@link Code::setUrl()}
	 * @throws Exception from {@link Code::setDescription()}
	 * @throws Exception from PDO functions.
	 */
	public function __construct($name, $institution = "middlebury.edu") {
		global $connection;

		try {
			$select = $connection->prepare("SELECT name, institution, url, description, public, unsearchable, updated FROM code WHERE name = :name AND institution = :institution");
			$select->bindValue(":name", $name);
			$select->bindValue(":institution", $institution);
			$select->execute();

			if ($select->rowCount() == 0) {
				$this->setName($name);
				$this->setInstitution($institution);

				try {
					$insert = $connection->prepare("INSERT INTO code (name, institution) VALUES (:name, :institution)");
					$insert->bindValue(":name", $name);
					$insert->bindValue(":institution", $institution);
					$insert->execute();

					Go::log("Created code $name via Code::__construct().", $name, $institution);
				} catch(Throwable $e) {
					throw $e;
				}
			} else {
				$row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT);
				$this->setName($row->name);
				$this->setInstitution($row->institution);
				$this->setUrl((!is_null($row->url) ? $row->url : ""));
				$this->setDescription((!is_null($row->description) ? $row->description : ""));
				$this->setPublic(($row->public == "1"));
				$this->setUnsearchable(($row->unsearchable == "1"));
				$this->setUpdated($row->updated);
			}
		} catch (Throwable $e) {
			throw $e;
		}
	}

	/**
	 * Get the full path string for this code.
	 *
	 * @access public
	 * @return string The full path string for this code.
	 * @since 02-25-2009
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the institution or "host" for this code.
	 *
	 * @access public
	 * @return string The institution or "host" for this code.
	 * @since 04-03-2009
	 */
	public function getInstitution() {
		return $this->institution;
	}

	/**
	 * Get the URL for this code.
	 *
	 * @access public
	 * @return string The URL for this code.
	 * @since 02-26-2009
	 */
	public function getUrl() {
		return self::filterUrl($this->url);
	}

	/**
	 * Get the description for this code.
	 *
	 * @access public
	 * @return string The description for this code.
	 * @since 02-27-2009
	 */
	public function getDescription() {
		if (empty($this->description)) {
			return '';
		}
		return htmlentities($this->description);
	}

	/**
	 * Get whether the code is displayed on the GOtionary.
	 *
	 * @access public
	 * @return bool Whether the code is displayed on the GOtionary
	 * @since 04-06-2009
	 */
	public function getPublic() {
		return $this->public;
	}

	/**
	 * Get whether the code is not searchable on the main website.
	 *
	 * @access public
	 * @return bool Whether the code is not searchable on the main website
	 */
	public function getUnsearchable() {
		return $this->unsearchable;
	}

	/**
	 * Get a single {@link User} which the user has access to.
	 *
	 * @access public
	 * @param string $user The name of the {@link User}
	 * @return {@link User} A {@link User} who has access to the code.
	 * @since 02-25-2009
	 * @throws Exception if parameter $user is not an int.
	 * @throws Exception if $user does not have access to this code.
	 * @throws Exception from PDO functions.
	 */
	public function getUser($user) {
		if (isset($this->users[$user])) {
			return $this->users[$user];
		}

		global $connection;

		try {
			$select = $connection->prepare("SELECT user FROM user_to_code WHERE user = :user AND code = :code AND institution = :institution");
			$select->bindValue(":user", $user);
			$select->bindValue(":code", $this->name);
			$select->bindValue(":institution", $this->institution);
			$select->execute();

			if ($select->rowCount() > 0) {
				$this->users[$user] = new User($user);
				return $this->users[$user];
			} else {
				throw new Exception("The user {$user} does not have access to code {$this->name}");
			}
		} catch(Throwable $e) {
			throw $e;
		}
	}

	/**
	 * Get the array of {@link User}s that have access to the code.
	 *
	 * @access public
	 * @return array The array of {@link User}s that have access to the code.
	 * @since 02-25-2009
	 * @throws Exception from PDO functions.
	 */
	public function getUsers() {
		$this->users = array();
		global $connection;

		try {
			$select = $connection->prepare("SELECT user FROM user_to_code WHERE code = :code AND institution = :institution");
			$select->bindValue(":code", $this->name);
			$select->bindValue(":institution", $this->institution);
			$select->execute();

			while($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
				$this->users[$row->user] = new User($row->user);
			}

			$select->closeCursor();
		} catch(Throwable $e) {
			throw $e;
		}

		return $this->users;
	}

	/**
	 * Set the full path string for this code.
	 *
	 * This function will throw an exception if the path is not a string type in PHP or if it contains characters other than the allowed character set for GO shortcuts.
	 *
	 * Allowed characters:
	 * <ul>
	 * 	<li>Alphabetical characters A-Z and a-z</li>
	 *  <li>Numeric characters 0-9</li>
	 * 	<li>The Question Mark ?</li>
	 *  <li>The Forward Slash /</li>
	 *  <li>The Hyphen -</li>
	 *  <li>The Underscore _</li>
	 * </ul>
	 *
	 * The question mark and forward slash are control characters used to separate sections of the GO shortcut.
	 *
	 * @access public
	 * @param string $name The full path string for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 02-25-2009
	 * @throws Exception if parameter $name is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception if parameter $name contains characters other than A-Z, a-z, 0-9, ?, /, -, and _.
	 * @throws Exception if parameter $name begins with a / or ? character or 'go/'.
	 * @throws Exception from PDO functions.
	 */
	public function setName($name, $save = false) {
		if (!is_string($name)) {
			throw new Exception(__METHOD__ . " expects parameter name to be a string; given " . $name);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if (!Code::isValidCode($name)) {
			throw new Exception(__METHOD__ . " expects parameter name to contain only A-Z, a-z, 0-9, ?, -, _, and / characters; given " . $name);
		}

		if (Code::isCodeBanned($name))
			throw new Exception($name." is banned from usage.");

		if ($name[0] == "/" || $name[0] == "?") {
			throw new Exception("Code names cannot begin with a / or ? character.");
		}

/*
		if (substr($name, strlen($name) - 1, 1) == "/") {
		    throw new Exception("Code names cannot end with a / character.");
		}
*/
		if (substr($name, 0, 3) == "go/") {
			throw new Exception("Code names cannot begin with 'go/'");
		}

		if($save && $name != $this->name) {
			global $connection;

			try {
				$update = $connection->prepare("UPDATE code SET name = :name WHERE name = :oldname AND institution = :institution");
				$update->bindValue(":name", $name);
				$update->bindValue(":oldname", $this->name);
				$update->bindValue(":institution", $this->institution);
				$update->execute();

				Go::log("Updated code name from '".$this->name."' to '$name' via Code::setName(). 1 of 2.", $this->name, $this->institution);
				Go::log("Updated code name from '".$this->name."' to '$name' via Code::setName(). 2 of 2.", $name, $this->institution);
			} catch(Throwable $e) {
				throw $e;
			}
		}

		$this->name = $name;
	}

	/**
	 * Set the institution or "host" for this code.
	 *
	 * @access public
	 * @param string $institution The institution or "host" for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 04-03-2009
	 * @throws Exception if parameter $institution is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setInstitution($institution, $save = false) {
		if (!is_string($institution)) {
			throw new Exception(__METHOD__ . " expects parameter institution to be a string; given " . $institution);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if ($this->institution != null && $institution != $this->institution) {
			global $connection;

			try {
				$select = $connection->prepare("SELECT name FROM code WHERE name = :name AND institution = :institution");
				$select->bindValue(":name", $this->name);
				$select->bindValue(":institution", $institution);
				$select->execute();

				if ($select->rowCount() > 0) {
					throw new Exception("The code " . $this->name . " already exists.");
				}
			} catch (Throwable $e) {
				throw $e;
			}
		}

		if($save && $institution != $this->institution) {
			global $connection;

			try {
				$update = $connection->prepare("UPDATE code SET institution = :institution WHERE name = :name AND institution = :oldinstitution");
				$update->bindValue(":institution", $institution);
				$update->bindValue(":name", $this->name);
				$update->bindValue(":oldinstitution", $this->institution);
				$update->execute();

				Go::log("Updated code institution from '".$this->institution."' to '$institution' via Code::setInstitution(). 1 of 2.", $this->name, $this->institution);
				Go::log("Updated code institution from '".$this->institution."' to '$institution' via Code::setName(). 2 of 2.", $this->name, $institution);
			} catch (Throwable $e) {
				throw $e;
			}
		}

		$this->institution = $institution;
	}

	/**
	 * Set the URL for this code.
	 *
	 * @access public
	 * @param string $url The URL for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 02-26-2009
	 * @throws Exception if parameter $url is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setUrl($url, $save = false) {
		if (!is_string($url)) {
			throw new Exception(__METHOD__ . " expects parameter url to be a string; given " . $url);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if($save && $url != $this->url) {
			global $connection;

			try {
				$update = $connection->prepare("UPDATE code SET url = :url WHERE name = :name AND institution = :institution");
				$update->bindValue(":url", $url);
				$update->bindValue(":name", $this->name);
				$update->bindValue(":institution", $this->institution);
				$update->execute();

				Go::log("Updated code url to '$url' via Code::setUrl().", $this->name, $this->institution);
			} catch(Throwable $e) {
				throw $e;
			}
		}

		$this->url = $url;
	}

	/**
	 * Verify a URL to ensure that it is valid and safe.
	 *
	 * @param string $url
	 * @return boolean
	 * @access public
	 * @since 6/28/10
	 */
	public static function isUrlValid ($url) {
		if (empty($url)) {
			return false;
		}
		$url = trim($url);
		// Encode any spaces;
		$url = str_replace(' ', '%20', $url);

		if (!filter_var($url, FILTER_VALIDATE_URL))
			return false;
		if (preg_match('/["\'<>]/i', $url))
			return false;
		if (!preg_match('#^(http|https|ftp|ftps)://.+#i', $url))
			return false;

		return true;
	}

	/**
	 * Filter a URL to ensure that it is valid and safe.
	 *
	 * @param string $url
	 * @return string
	 * @access public
	 * @since 6/28/10
	 */
	public static function filterUrl ($url) {
		if (empty($url)) {
			return '';
		}
		$url = trim($url);
		// Encode any spaces;
		$url = str_replace(' ', '%20', $url);

		$url = filter_var($url, FILTER_SANITIZE_URL);
		$url = preg_replace('/["\'<>]/i', '', $url);

		// if it still isn't valid, give up.
		if (!self::isUrlValid($url))
			return htmlentities($url);

		return $url;
	}

	/**
	 * Set the description for this code.
	 *
	 * @access public
	 * @param string $description The description for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @throws Exception if parameter $description is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setDescription($description, $save = false) {
		if (!is_string($description)) {
			throw new Exception(__METHOD__ . " expects parameter description to be a string; given " . $description);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if($save && $description != $this->description) {
			global $connection;

			try {
				$update = $connection->prepare("UPDATE code SET description = :description WHERE name = :name AND institution = :institution");
				$update->bindValue(":description", $description);
				$update->bindValue(":name", $this->name);
				$update->bindValue(":institution", $this->institution);
				$update->execute();

				Go::log("Updated code description to '$description' via Code::setDescription().", $this->name, $this->institution);

			} catch(Throwable $e) {
				throw $e;
			}
		}

		$this->description = $description;
	}

	/**
	 * Set whether this code is displayed on the GOtionary.
	 *
	 * @access public
	 * @param bool $public Whether this code is displayed on the GOtionary.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 04-06-2009
	 * @throws Exception if parameter $public is not a boolean.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setPublic($public, $save = false) {
		if (!is_bool($public)) {
			throw new Exception(__METHOD__ . " expects parameter public to be a bool; given " . $public);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if ($save && $public != $this->public) {
			global $connection;

			try {
				$update = $connection->prepare("UPDATE code SET public = :public WHERE name = :name AND institution = :institution");
				$update->bindValue(":public", ($public ? "1" : "0"));
				$update->bindValue(":name", $this->name);
				$update->bindValue(":institution", $this->institution);
				$update->execute();

				Go::log("Updated code publicity to '".($public ? "1" : "0")."' via Code::setPublic().", $this->name, $this->institution);
			} catch(Throwable $e) {
				throw $e;
			}
		}

		$this->public = $public;
	}

	/**
	 * Set whether this code is not searchable on the main website.
	 *
	 * @access public
	 * @param bool $unsearchable Whether this code is not searchable on the main website.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 04-06-2009
	 * @throws Exception if parameter $unsearchable is not a boolean.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setUnsearchable($unsearchable, $save = false) {
		if (!is_bool($unsearchable)) {
			throw new Exception(__METHOD__ . " expects parameter unsearchable to be a bool; given " . $unsearchable);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if ($save && $unsearchable != $this->unsearchable) {
			global $connection;

			try {
				$update = $connection->prepare("UPDATE code SET unsearchable = :unsearchable WHERE name = :name AND institution = :institution");
				$update->bindValue(":unsearchable", ($unsearchable ? "1" : "0"));
				$update->bindValue(":name", $this->name);
				$update->bindValue(":institution", $this->institution);
				$update->execute();

				Go::log("Updated code unsearchablity to '".($unsearchable ? "1" : "0")."' via Code::setUnsearchable().", $this->name, $this->institution);
			} catch(Throwable $e) {
				throw $e;
			}
		}

		$this->unsearchable = $unsearchable;
	}

	private $updated = 'Unknown';
	/**
	 * Set the last-updated date of the code.
	 *
	 * @param string $date
	 * @return void
	 */
	protected function setUpdated ($date) {
		if ($time = strtotime($date))
			$this->updated = date ('Y-m-d H:i', $time);
	}

	/**
	 * Answer the date the code was last updated.
	 *
	 * @return string
	 */
	public function getLastUpdateDate () {
		return $this->updated;
	}

	/**
	 * Delete this code from the database.
	 *
	 * @access public
	 * @since 04-03-2009
	 * @throws Exception from PDO functions.
	 */
	public function delete() {
		global $connection;

		try {
			$aliases = $this->getAliases();

			$users = $connection->prepare("DELETE FROM user_to_code WHERE code = :code AND institution = :institution");
			$users->bindValue(":code", $this->name);
			$users->bindValue(":institution", $this->institution);
			$users->execute();

			$alias = $connection->prepare("DELETE FROM alias WHERE code = :code AND institution = :institution");
			$alias->bindValue(":code", $this->name);
			$alias->bindValue(":institution", $this->institution);
			$alias->execute();

			foreach ($aliases as $alias) {
				Go::log("Deleted alias via Code::delete().", $alias->getCode(), $alias->getInstitution(), $alias->getName());
			}

			$code = $connection->prepare("DELETE FROM code WHERE name = :name AND institution = :institution");
			$code->bindValue(":name", $this->name);
			$code->bindValue(":institution", $this->institution);
			$code->execute();

			Go::log("Deleted code via Code::delete().", $this->name, $this->institution);
		} catch (Throwable $e) {
			throw $e;
		}
	}

	/**
	 * Grant a {$link User} access to this code's administration.
	 *
	 * @access public
	 * @param int $name The name of the {@link User}
	 * @since 04-03-2009
	 * @throws Exception if parameter $user is not an integer.
	 * @throws Exception if the user already has access to this code.
	 * @throws Exception from PDO functions.
	 */
	public function addUser($name) {
		global $connection;

		try {
			$user = new User($name);
			$code = $user->getCode($this->name, $this->institution);

			if (isset($code)) {
				throw new Exception("User {$name} already has access to {$this->name}");
			}
		} catch(Throwable $e) {
			throw $e;
		}

		try {
			$insert = $connection->prepare("INSERT INTO user_to_code (user, code, institution) VALUES (:user, :code, :institution)");
			$insert->bindValue(":user", $name);
			$insert->bindValue(":code", $this->name);
			$insert->bindValue(":institution", $this->institution);
			$insert->execute();

			Go::log("Added user '".$name."' to code via Code::addUser().", $this->name, $this->institution);
		} catch(Throwable $e) {
			throw $e;
		}
	}

	/**
	 * Revoke a {$link User} access to this code's administration.
	 *
	 * @access public
	 * @param int $name The name of the {@link User}
	 * @since 04-03-2009
	 * @throws Exception if parameter $user is not an integer.
	 * @throws Exception if the user does not have access to this code.
	 * @throws Exception from PDO functions.
	 */
	public function delUser($name) {
		if (!preg_match('/^[A-Z0-9]+$/i', $name)) {
			throw new Exception(__METHOD__ . " expected parameter name to be [A-Z0-9]+; given " . $name);
		}

		global $connection;

		try {
			$user = new User($name);
			$code = $user->getCode($this->name, $this->institution);

			if (!isset($code)) {
				throw new Exception("User {$name} does not have access to {$this->name}");
			}
		} catch(Throwable $e) {
			throw $e;
		}

		try {
			$delete = $connection->prepare("DELETE FROM user_to_code WHERE user = :user AND code = :code AND institution = :institution");
			$delete->bindValue(":user", $name);
			$delete->bindValue(":code", $this->name);
			$delete->bindValue(":institution", $this->institution);
			$delete->execute();

			Go::log("Removed user '".$name."' from code via Code::addUser().", $this->name, $this->institution);

		} catch(Throwable $e) {
			throw $e;
		}
	}

	/**
	 * Get an array of {@link Alias}es associated with this {$link Code}.
	 *
	 * @access public
	 * @return array An array of {@link Alias}es associated with this {@link Code}.
	 * @since 04-03-2009
	 * @throws Exception from PDO functions.
	 */
	public function getAliases() {
		$this->aliases = array();
		global $connection;

		try {
			$select = $connection->prepare("SELECT name, code, institution FROM alias WHERE code = :code AND institution = :institution");
			$select->bindValue(":code", $this->name);
			$select->bindValue(":institution", $this->institution);
			$select->execute();

			while($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
				$this->aliases[$row->name] = new Alias($row->name, $row->code, $row->institution);
			}

			$select->closeCursor();
		} catch(Throwable $e) {
			throw $e;
		}

		return $this->aliases;
	}
}

/**
 * A class to provide access to information about deleted codes.
 */
class DeletedCode extends Code {
	/**
	 * Class constructor for {@link Code}.
	 *
	 * @access public
	 * @param string $name The full path string of the code.
	 * @since 02-25-2009
	 * @throws Exception from {@link Code::setName()}
	 * @throws Exception from {@link Code::setInstitution()}
	 * @throws Exception from {@link Code::setUrl()}
	 * @throws Exception from {@link Code::setDescription()}
	 * @throws Exception from PDO functions.
	 */
	public function __construct($name, $institution = "middlebury.edu", $creator_id = null) {
		$this->name = $name;
		$this->institution = $institution;
		$this->creator_id = $creator_id;
	}

	/**
	 * Get a single {@link User} which the user has access to.
	 *
	 * @access public
	 * @param string $user The name of the {@link User}
	 * @return {@link User} A {@link User} who has access to the code.
	 * @since 02-25-2009
	 * @throws Exception if parameter $user is not an int.
	 * @throws Exception if $user does not have access to this code.
	 * @throws Exception from PDO functions.
	 */
	public function getUser($user) {
		throw new Exception("Method not supported: ".__CLASS__."::".__FUNCTION__."()");
	}

	/**
	 * Get the array of {@link User}s that have access to the code.
	 *
	 * @access public
	 * @return array The array of {@link User}s that have access to the code.
	 * @since 02-25-2009
	 * @throws Exception from PDO functions.
	 */
	public function getUsers() {
		$users = array();
		$users[] = new User($this->creator_id);
		global $connection;

		try {
			$select = $connection->prepare(
"SELECT description
FROM log
WHERE
	log.description REGEXP '^Added user'
	AND code = :code
	AND institution = :institution
ORDER BY tstamp ASC
");
			$select->execute(array(':code' => $this->name, ':institution' => $this->institution));
			// Get all users who had been added to the code at any point.
			foreach ($select->fetchAll(PDO::FETCH_OBJ) as $row) {
				if (preg_match("/^Added user '([a-zA-Z0-9]+)'/", $row->description, $m)) {
					$users[] = new User($m[2]);
				}
			}
		} catch(Throwable $e) {
			throw $e;
		}

		return $users;
	}

	/**
	 * Set the full path string for this code.
	 *
	 * This function will throw an exception if the path is not a string type in PHP or if it contains characters other than the allowed character set for GO shortcuts.
	 *
	 * Allowed characters:
	 * <ul>
	 * 	<li>Alphabetical characters A-Z and a-z</li>
	 *  <li>Numeric characters 0-9</li>
	 * 	<li>The Question Mark ?</li>
	 *  <li>The Forward Slash /</li>
	 *  <li>The Hyphen -</li>
	 *  <li>The Underscore _</li>
	 * </ul>
	 *
	 * The question mark and forward slash are control characters used to separate sections of the GO shortcut.
	 *
	 * @access public
	 * @param string $name The full path string for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 02-25-2009
	 * @throws Exception if parameter $name is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception if parameter $name contains characters other than A-Z, a-z, 0-9, ?, /, -, and _.
	 * @throws Exception if parameter $name begins with a / or ? character or 'go/'.
	 * @throws Exception from PDO functions.
	 */
	public function setName($name, $save = false) {
		if (!is_string($name)) {
			throw new Exception(__METHOD__ . " expects parameter name to be a string; given " . $name);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if (!Code::isValidCode($name)) {
			throw new Exception(__METHOD__ . " expects parameter name to contain only A-Z, a-z, 0-9, ?, -, _, and / characters; given " . $name);
		}

		if ($name[0] == "/" || $name[0] == "?") {
			throw new Exception("Code names cannot begin with a / or ? character.");
		}

/*
		if (substr($name, strlen($name) - 1, 1) == "/") {
		    throw new Exception("Code names cannot end with a / character.");
		}
*/
		if (substr($name, 0, 3) == "go/") {
			throw new Exception("Code names cannot begin with 'go/'");
		}

		if($save && $name != $this->name) {
			throw new Exception("Saving is not supported by ".__CLASS__."::".__FUNCTION__."()");
		}

		$this->name = $name;
	}

	/**
	 * Set the institution or "host" for this code.
	 *
	 * @access public
	 * @param string $institution The institution or "host" for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 04-03-2009
	 * @throws Exception if parameter $institution is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setInstitution($institution, $save = false) {
		if (!is_string($institution)) {
			throw new Exception(__METHOD__ . " expects parameter institution to be a string; given " . $institution);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if ($this->institution != null && $institution != $this->institution) {
			throw new Exception("Saving is not supported by ".__CLASS__."::".__FUNCTION__."()");
		}

		if($save && $institution != $this->institution) {
			throw new Exception("Saving is not supported by ".__CLASS__."::".__FUNCTION__."()");
		}

		$this->institution = $institution;
	}

	/**
	 * Set the URL for this code.
	 *
	 * @access public
	 * @param string $url The URL for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 02-26-2009
	 * @throws Exception if parameter $url is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setUrl($url, $save = false) {
		if (!is_string($url)) {
			throw new Exception(__METHOD__ . " expects parameter url to be a string; given " . $url);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if($save && $url != $this->url) {
			throw new Exception("Saving is not supported by ".__CLASS__."::".__FUNCTION__."()");
		}

		$this->url = $url;
	}

	/**
	 * Set the description for this code.
	 *
	 * @access public
	 * @param string $description The description for this code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @throws Exception if parameter $description is not a string.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setDescription($description, $save = false) {
		if (!is_string($description)) {
			throw new Exception(__METHOD__ . " expects parameter description to be a string; given " . $description);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if($save && $description != $this->description) {
			throw new Exception("Saving is not supported by ".__CLASS__."::".__FUNCTION__."()");
		}

		$this->description = $description;
	}

	/**
	 * Set whether this code is displayed on the GOtionary.
	 *
	 * @access public
	 * @param bool $public Whether this code is displayed on the GOtionary.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 04-06-2009
	 * @throws Exception if parameter $public is not a boolean.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setPublic($public, $save = false) {
		if (!is_bool($public)) {
			throw new Exception(__METHOD__ . " expects parameter public to be a bool; given " . $public);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if ($save && $public != $this->public) {
			throw new Exception("Saving is not supported by ".__CLASS__."::".__FUNCTION__."()");
		}

		$this->public = $public;
	}

	/**
	 * Set whether this code is not searchable on the main website.
	 *
	 * @access public
	 * @param bool $unsearchable Whether this code is not searchable on the main website.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 04-06-2009
	 * @throws Exception if parameter $unsearchable is not a boolean.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setUnsearchable($unsearchable, $save = false) {
		if (!is_bool($unsearchable)) {
			throw new Exception(__METHOD__ . " expects parameter unsearchable to be a bool; given " . $unsearchable);
		}

		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}

		if ($save && $unsearchable != $this->unsearchable) {
			throw new Exception("Saving is not supported by ".__CLASS__."::".__FUNCTION__."()");
		}

		$this->unsearchable = $unsearchable;
	}

	/**
	 * Delete this code from the database.
	 *
	 * @access public
	 * @since 04-03-2009
	 * @throws Exception from PDO functions.
	 */
	public function delete() {
		throw new Exception("Method not supported: ".__CLASS__."::".__FUNCTION__."()");
	}

	/**
	 * Grant a {$link User} access to this code's administration.
	 *
	 * @access public
	 * @param int $name The name of the {@link User}
	 * @since 04-03-2009
	 * @throws Exception if parameter $user is not an integer.
	 * @throws Exception if the user already has access to this code.
	 * @throws Exception from PDO functions.
	 */
	public function addUser($name) {
		throw new Exception("Method not supported: ".__CLASS__."::".__FUNCTION__."()");
	}

	/**
	 * Revoke a {$link User} access to this code's administration.
	 *
	 * @access public
	 * @param int $name The name of the {@link User}
	 * @since 04-03-2009
	 * @throws Exception if parameter $user is not an integer.
	 * @throws Exception if the user does not have access to this code.
	 * @throws Exception from PDO functions.
	 */
	public function delUser($name) {
		throw new Exception("Method not supported: ".__CLASS__."::".__FUNCTION__."()");
	}

	/**
	 * Get an array of {@link Alias}es associated with this {$link Code}.
	 *
	 * @access public
	 * @return array An array of {@link Alias}es associated with this {@link Code}.
	 * @since 04-03-2009
	 * @throws Exception from PDO functions.
	 */
	public function getAliases() {
		$aliases = array();
		global $connection;

		try {
			$select = $connection->prepare(
"SELECT alias
FROM log
WHERE
	code = :code
	AND institution = :institution
	AND alias != ''
GROUP BY alias
ORDER BY alias
	");
			$select->execute(array(':code' => $this->name, ':institution' => $this->institution));

			foreach ($select->fetchAll(PDO::FETCH_OBJ) as $row) {
				$aliases[$row->name] = new DeletedAlias($row->alias, $this->name, $this->institution);
			}
		} catch(Throwable $e) {
			throw $e;
		}

		return $aliases;
	}
}

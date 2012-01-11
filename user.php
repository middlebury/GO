<?php

require_once "go.php";

/**
 * User Class for GO Application.
 * 
 * @author Ian McBride <imcbride@middlebury.edu>
 * @category GO
 * @copyright 2009 The President and Fellows of Middlebury College
 * @license GNU General Public License (GPL) version 3 or later
 * @package GO
 * @version 02-25-2009
 * @link http://go.middlebury.edu/
 */
class User {
	
	/**
	 * The name of the user.
	 * 
	 * @access protected
	 * @since 02-25-2009
	 * @var string The name of the user.
	 */
	protected $name;
	
	/**
	 * Whether the user wishes to receive an email if any of their codes are broken.
	 * 
	 * @access protected
	 * @since 02-25-2009
	 * @var bool Whether the user wishes to receive an email if any of their codes are broken.
	 */
	protected $notify;
	
	/**
	 * An array of {@link Code}s that this user has access to.
	 * 
	 * @access protected
	 * @since 02-25-2009
	 * @var array An array of {@link Code}s that this user has access to.
	 */
	protected $codes = array();
	
	/**
	 * An array of {@link Alias}es that this user has access to.
	 * 
	 * @access protected
	 * @since 03-30-2009
	 * @var array An array of {@link Alias}es that this user has access to.
	 */
	protected $aliases = array();
	
	/**
	 * Class constructor for (@link User}
	 * 
	 * @access public
	 * @param string $name The name of the user.
	 * @since 02-25-2009
	 * @throws Exception from {@link User::setName()}
	 * @throws Exception from {@link User::setNotify()}
	 * @throws Exception from PDO functions.
	 */
	public function __construct($name) {
		global $connection;
		
		try {			
			$this->setName($name);

			$select = $connection->prepare("SELECT name, notify FROM user WHERE name = :name");
			$select->bindValue(":name", $name);
			$select->execute();
			
			if ($select->rowCount() == 0) {
				$this->setNotify(true);

				try {
					$insert = $connection->prepare("INSERT INTO user (name, notify) VALUES (:name, 1)");
					$insert->bindValue(":name", $name);
					$insert->execute();
				} catch (Exception $e) {
					throw $e;
				}
			} else {
				$row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT);
				$this->setNotify(($row->notify == "1"));
			}
		} catch(Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * Get the email address of the user.
	 * 
	 * @access public
	 * @return string The email address of the user.
	 * @since 02-25-2009
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get whether the user wishes to receive an email if any of their codes are broken.
	 * 
	 * @access public
	 * @return bool Whether the user wishes to receive an email if any of their codes are broken.
	 * @since 02-25-2009
	 */
	public function getNotify() {
		return $this->notify;
	}
	
	/**
	 * Get a single {@link Code} which the user has acccess to.
	 * 
	 * @access public
	 * @param string $code The name of the {@link Code}
	 * @return {@link Code} A {@link Code} which the user has access to.
	 * @since 02-25-2009
	 * @throws Exception if parameter $code is not a string.
	 * @throws Exception if parameter $institution is not a string.
	 * @throws Exception from PDO functions.
	 */
	public function getCode($code, $institution = "middlebury.edu") {
		if (!is_string($code)) {
			throw new Exception(__METHOD__ . " expects parameter code to be a string; given " . $code);
		}
		
		if (!is_string($institution)) {
			throw new Exception(__METHOD__ . " expects parameter institution to be a string; given " . $institution);
		}
		
		if (isset($this->codes[$code])) {
			return $this->codes[$code];
		}
		
		global $connection;
		
		try {
			$select = $connection->prepare("SELECT code FROM user_to_code WHERE code = :code AND user = :user AND institution = :institution");
			$select->bindValue(":code", $code);
			$select->bindValue(":user", $this->name);
			$select->bindValue(":institution", $institution);
			$select->execute();
			
			if ($select->rowCount() > 0) {
				$this->codes[$institution . "/" . $code] = new Code($code, $institution);
				return $this->codes[$institution . "/" . $code];
			}
		} catch(Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * Get the array of {@link Code}s the user has access to.
	 * 
	 * @access public
	 * @return array The array of {@link Code}s the user has access to.
	 * @since 02-25-2009
	 * @throws Exception from PDO functions.
	 */
	public function getCodes() {
		$this->codes = array();
		global $connection;
		
		try {
			$select = $connection->prepare("SELECT code, institution FROM user_to_code WHERE user = :name ORDER BY code ASC");
			$select->bindValue(":name", $this->name);
			$select->execute();
			
			while($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
				$this->codes[$row->institution . "/" . $row->code] = new Code($row->code, $row->institution);
			}
			
			$select->closeCursor();
		} catch(Exception $e) {
			throw $e;
		}
		
		return $this->codes;
	}
	
	/**
	 * Get the array of {@link Code}s that the user created, but are now deleted
	 * 
	 * @access public
	 * @return array The array of {@link Code}s
	 * @throws Exception from PDO functions.
	 */
	public function getDeletedCodes() {
		global $connection;
		
		try {
			$codes = array();
			$select = $connection->prepare(
"SELECT
	log.code, log.institution, log.user_id
FROM
	`log` 
	LEFT JOIN code ON (log.code = code.name AND log.institution = code.institution)
WHERE 
	log.description LIKE 'Created%' 
	AND code.name IS NULL
	AND log.user_id = :name
GROUP BY log.institution, log.code
ORDER BY log.code ASC, log.tstamp DESC");
			$select->execute(array(':name' => $this->name));
			foreach ($select->fetchAll(PDO::FETCH_OBJ) as $row) {
				$codes[$row->institution . "/" . $row->code] = new DeletedCode($row->code, $row->institution, $row->user_id);
			}
			
			$select->closeCursor();
		} catch(Exception $e) {
			throw $e;
		}
		
		return $codes;
	}
	
	/**
	 * Set the name of the {@link User}.
	 * 
	 * @access public
	 * @param int $name The name of the user.
	 * @param string $save Whether to commit changes to the database (default: false).
	 * @since 02-25-2009
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setName($name, $save = false) {		
		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $bool);
		}
		
		if($save) {
			global $connection;
			
			try {
				$update = $connection->prepare("UPDATE user SET name = :name WHERE name = :oldname");
				$update->bindValue(":name", $name);
				$update->bindValue(":oldname", $this->name);
				$update->execute();
			} catch(Exception $e) {
				throw $e;
			}
		}
		
		$this->name = $name;
	}
	
	/**
	 * Set whether the user wishes to receive an email if any of their codes are broken.
	 * 
	 * @access public 
	 * @param bool $notify Whether the user wishes to receive an email if any of their codes are broken.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 02-25-2009 
	 * @throws Exception if parameter $notify is not a boolean.
	 * @throws Exception if parameter $save is not a boolean.
	 * @throws Exception from PDO functions.
	 */
	public function setNotify($notify, $save = false) {
		if (!is_bool($notify)) {
			throw new Exception(__METHOD__ . " expects parameter notify to be a bool; given " . $notify);
		}
		
		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}
		
		if($save) {
			global $connection;

			try {
				$update = $connection->prepare("UPDATE user SET notify = :notify WHERE name = :name");
				$update->bindValue(":notify", ($notify ? "1" : "0"));
				$update->bindValue(":name", $this->name);
				$update->execute();
			} catch(Exception $e) {
				throw $e;
			}
		}
		
		$this->notify = $notify;			
	}
}
?>
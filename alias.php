<?php

require_once "go.php";
require_once "user.php";

/**
 * Alias Class for GO Application
 * 
 * An "alias" in go operates like a code, but doesn't have its own URL. Rather, you point the alias at a code, which is then used to store the URL. In this way, the URL can be updated without needing to update the alias.
 * 
 * @author imcbride
 * @category GO
 * @copyright 2009 The President and Fellows of Middlebury College
 * @license GNU General Public License (GPL) version 3 or later
 * @package GO
 * @version 03-26-2009
 * @link http://go.middlebury.edu
 */
class Alias {

	/**
	 * Answer true if the alias exists, false otherwise.
	 *
	 * @access public
	 * @param string $name The full path string of the code.
	 * @throws Exception from PDO functions.
	 */
	public static function exists($name, $institution = "middlebury.edu") {
		global $connection;
		
		$select = $connection->prepare("SELECT COUNT(*) AS num FROM alias WHERE name = :name AND institution = :institution");
		$select->bindValue(":name", $name);
		$select->bindValue(":institution", $institution);
		$select->execute();
		if ($select->fetchColumn(0) >= 1)
			return true;
		else
			return false;
	}
	
	/**
	 * The "name" of the alias is the full path string.
	 * 
	 * @access protected
	 * @since 03-26-2009
	 * @var string The full path string of the alias.
	 */
	protected $name;
	
	/**
	 * The code with which the alias is associated.
	 * 
	 * @access protected
	 * @since 03-26-2009
	 * @var string The code with which the alias is associated.
	 */
	protected $code;
	
	/**
	 * The institution or "host" of the code with which the alias is associated.
	 * 
	 * @access protected
	 * @since 04-03-2009
	 * @var string The institution or "host" of the code with which the alias is associated.
	 */
	protected $institution;
	
	/**
	 * Class constructor for {@link Alias}.
	 * 
	 * This function assumes that the currently logged in user has access to edit objects associated with the input code. This should be checked prior to calling this function.
	 * 
	 * @access public
	 * @param string $name The full path string of the alias.
	 * @param string $code The name of the code associated with this alias.
	 * @since 03-26-2009
	 * @throws Exception if parameter $name is the same as parameter $code.
	 * @throws Exception from {@link Alias::setName()}
	 * @throws Exception from {@link Alias::setCode()}
	 * @throws Exception from PDO functions.
	 */
	public function __construct($name, $code, $institution = "middlebury.edu") {
		if ($name == $code) {
			throw new Exception("Cannot create an alias with the same name as the shortcut.");
		}
		
		global $connection;
		
		try {
			$select = $connection->prepare("SELECT name, code, institution FROM alias WHERE name = :name AND institution = :institution");
			$select->bindValue(":name", $name);
			$select->bindValue(":institution", $institution);
			$select->execute();
			
			if ($select->rowCount() == 0) {
				$this->setName($name);
				$this->setInstitution($institution);
				
				try {
					$insert = $connection->prepare("INSERT INTO alias (name, code, institution) VALUES (:name, :code, :institution)");
					$insert->bindValue(":name", $name);
					$insert->bindValue(":code", $code);
					$insert->bindValue(":institution", $institution);
					$insert->execute();
					
					Go::log("Created alias via Alias::__construct().", $code, $institution, $name);
				} catch(Exception $e) {
					throw $e;
				}
			} else {
				$row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT);
				$this->setName($row->name);
				$this->setInstitution($row->institution);
				$this->setCode($row->code);
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * Get the full path string for this alias.
	 * 
	 * @access public
	 * @return string The full path string for this alias.
	 * @since 03-26-2009
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Get the name of the code for this alias.
	 * 
	 * @access public
	 * @return string The name of the code for this alias.
	 * @since 03-26-2009
	 */
	public function getCode() {
		return $this->code;
	}
	
	/**
	 * Get the institution or "host" for this alias.
	 * 
	 * @access public
	 * @return string The institution or "host" for this alias.
	 * @since 04-03-2009
	 */
	public function getInstitution() {
		return $this->institution;
	}
	
	/**
	 * Set the full path string for this alias.
	 * 
	 * The same naming convention which applies for {@link Code::setName()} applies here.
	 * 
	 * @access public
	 * @param string $name The full path string for this alias.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 03-26-2009
	 * @throws Exception if parameter $name is not a string.
	 * @throws Exception if parameter $name contains characters other than A-Z, a-z, 0-9, ?, /, -, and _.
	 * @throws Exception if parameter $name begins with the / or ? characters.
	 * @throws Exception if a code already exists with the same name as parameter $name
	 * @throws Exception from PDO functions.
	 */
	public function setName($name, $save = false) {
		if (!is_string($name)) {
			throw new Exception(__METHOD__ . " expects parameter name to be a string; given " . $name);
		}
		
		if (!Code::isValidCode($name)) {
			throw new Exception(__METHOD__ . " expects parameter name to contain only A-Z, a-z, 0-9, ?, -, _, and / characters; given " . $name);
		}
		
		if ($name[0] == "/" || $name[0] == "?") {
			throw new Exception("Alias names cannot begin with a / or ? character.");
		}
/*		
		if (substr($name, strlen($name) - 1, 1) == "/") {
		    throw new Exception("Alias names cannot end with a / character.");
		}
*/		
		if (substr($name, 0, 3) == "go/") {
			throw new Exception("Alias names cannot begin with 'go/'");
		}
		
		if ($save && $name != $this->name) {
			global $connection;
			
			try {
				$code = $connection->prepare("SELECT name FROM code WHERE name = :name AND institution = :institution");
				$code->bindValue(":name", $name);
				$code->bindValue(":institution", $this->institution);
				$code->execute();
				
				if ($code->rowCount() > 0) {
					throw new Exception("A code already exists with the name " . $name);
				}
				
				$update = $connection->prepare("UPDATE alias SET name = :name WHERE name = :name AND institution = :institution");
				$update->bindValue(":name", $name);
				$update->bindValue(":oldname", $this->name);
				$update->bindValue(":institution", $this->institution);
				$update->execute();
				
				Go::log("Updated alias name from '".$this->name."' to '$name' via Alias::setName(). 1 of 2.", $this->code, $this->institution, $this->name);
				Go::log("Updated alias name from '".$this->name."' to '$name' via Alias::setName(). 2 of 2.", $this->code, $this->institution, $name);
			} catch(Exception $e) {
				throw $e;
			}
		}
		
		$this->name = $name;
	}
	
	/**
	 * Set the code for this alias.
	 * 
	 * @access public
	 * @param string $code The name of the new code.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 03-26-2009
	 * @throws Exception if parameter $code is not a string.
	 * @throws Exception if parameter $code is not a code in the database.
	 * @throws Exception if parameter $save is not a bool.
	 * @throws Exception from PDO functions.
	 */
	public function setCode($code, $save = false) {
		if (!is_string($code)) {
			throw new Exception(__METHOD__ . " expects parameter code to be a string; given " . $code);
		}
		
		global $connection;
		
		try {
			$select = $connection->prepare("SELECT name FROM code WHERE name = :name AND institution = :institution");
			$select->bindValue(":name", $code);
			$select->bindValue(":institution", $this->institution);
			$select->execute();
			
			if ($select->rowCount() == 0) {
				throw new Exception("There is no code " . $code);
			}
		} catch (Exception $e) {
			throw $e;
		}
			
		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}
		
		if ($save && $code != $this->code) {			
			try {
				$update = $connection->prepare("UPDATE alias SET code = :code WHERE name = :name AND institution = :institution");
				$update->bindValue(":code", $code);
				$update->bindValue(":name", $this->name);
				$update->bindValue(":institution", $this->institution);
				$update->execute();
				
				Go::log("Updated alias code from '".$this->code."' to '$code' via Alias::setCode(). 1 of 2.", $this->code, $this->institution, $this->name);
				Go::log("Updated alias code from '".$this->code."' to '$code' via Alias::setCode(). 2 of 2.", $code, $this->institution, $this->name);
			} catch(Exception $e) {
				throw $e;
			}
		}
		
		$this->code = $code;
	}

	/**
	 * Set the institution of "host" for this alias.
	 * 
	 * @access public
	 * @param string $institution The name of the new institution.
	 * @param bool $save Whether to commit changes to the database (default: false).
	 * @since 03-26-2009
	 * @throws Exception if parameter $institution is not a string.
	 * @throws Exception if the code for this alias is not a code in the database.
	 * @throws Exception if parameter $save is not a bool.
	 * @throws Exception from PDO functions.
	 */
	public function setInstitution($institution, $save = false) {
		if (!is_string($institution)) {
			throw new Exception(__METHOD__ . " expects parameter institution to be a string; given " . $institution);
		}
		
		global $connection;
		
		if (!is_bool($save)) {
			throw new Exception(__METHOD__ . " expects parameter save to be a bool; given " . $save);
		}
		
		if ($save && $institution != $this->institution) {
			try {
				$update = $connection->prepare("UPDATE alias SET institution = :institution WHERE name = :name AND institution = :oldinstitution");
				$update->bindValue(":name", $this->name);
				$update->bindValue(":institution", $institution);
				$update->bindValue(":oldinstitution", $this->institution);
				$update->execute();
				
				Go::log("Updated alias institution from '".$this->institution."' to '$institution' via Alias::setInstitution(). 1 of 2.", $this->code, $this->institution, $this->name);
				Go::log("Updated alias institution from '".$this->institution."' to '$institution' via Alias::setInstitution(). 2 of 2.", $this->code, $institution, $this->name);
			} catch (Exception $e) {
				throw $e;
			}
		}
		
		$this->institution = $institution;
	}
	
	/**
	 * Delete the current alias from the database.
	 * 
	 * @access public
	 * @since 03-26-2009
	 * @throws Exception from PDO functions.
	 */
	public function delete() {
		try {
			global $connection;
			
			$alias = $connection->prepare("DELETE FROM alias WHERE name = :alias AND institution = :institution");
			$alias->bindValue(":alias", $this->name);
			$alias->bindValue(":institution", $this->institution);
			$alias->execute();
			
			Go::log("Deleted alias via Alias::delete().", $this->code, $this->institution, $this->name);
		} catch (Exception $e) {
			throw $e;
		}
	}
}
?>
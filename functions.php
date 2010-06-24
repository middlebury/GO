<?php

require_once "go.php";

header("Content-type:text/xml");

function doCreate($args) {
	try {
		$code = new Code(str_replace(" ", "+", $args["code"]), $args["institution"]);
		
		if ($code->getCreator() == $_SESSION["AUTH"]->getId()) {
			throw new Exception("You've already created this code. Did you want to update the URL?");
		}
		
		if ($code->getUrl() != "" && $code->getUrl() != $args["url"]) {
			throw new Exception("Someone has already created this code.");
		}
		
		$code->setCreator($_SESSION["AUTH"]->getId(), true);
		$code->setUrl(urldecode($args["url"]), true);
		$code->setDescription($args["description"], true);
		$code->setPublic(($args["public"] == "1"), true);
		$code->addUser($_SESSION["AUTH"]->getId());
		return "Added new shortcut " . $code->getName();
	} catch (Exception $e) {
		throw $e;
	}
}

function doAlias($args) {
	try {
		if (!Code::exists(str_replace(" ", "+", $args["code"]), $args["institution"]))
			throw new Exception('Code '.htmlentities(str_replace(" ", "+", $args["code"]))." doesn't exist.");
			
		$code = new Code(str_replace(" ", "+", $args["code"]), $args["institution"]);
		
/*	Allow anyone to create aliases.	
		if (in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers()))) {
			$alias = new Alias($args["name"], $args["code"], $args["institution"]);
		} else {
			throw new Exception("You do not have access to the shortcut " . $args["code"]);
		}
*/
		$alias = new Alias(str_replace(" ", "+", $args["name"]), str_replace(" ", "+", $args["code"]), $args["institution"]);
		return "Added new alias for " . $code->getName() . " called " . $alias->getName();
	} catch (Exception $e) {
		throw $e;
	}
}

function doDelete($args) {
	try {
		$codeString = str_replace(" ", "+", $args["code"]);
		if (!Code::exists($codeString, $args["institution"]))
			throw new Exception("Code ".htmlentities($codeString)." doesn't exist.");
		$code = new Code($codeString, $args["institution"]);
		
		if ($code->getCreator() != $_SESSION["AUTH"]->getId())
			throw new Exception("You are not the creator of the code. Cannot delete.");
			
		$code->delete();
		return "Deleted shortcut " . $codeString;
	} catch (Exception $e) {
		throw $e;
	}
}

function doDeleteAlias($args) {
	try {
		$codeString = str_replace(" ", "+", $args["code"]);
		if (!Code::exists($codeString, $args["institution"]))
			throw new Exception("Code ".htmlentities($codeString)." doesn't exist.");
		$code = new Code($codeString, $args["institution"]);
		
		if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())))
			throw new Exception("You do not have access to the shortcut " . $codeString);
		
		
		$alias = new Alias(str_replace(" ", "+", $args["alias"]), $codeString, $args["institution"]);
		$alias->delete();
		
		return "Deleted alias " . $args["alias"];
	} catch (Exception $e) {
		throw $e;
	}
}

function doAddUser($args) {
	try {
		$codeString = str_replace(" ", "+", $args["code"]);
		if (!Code::exists($codeString, $args["institution"]))
			throw new Exception("Code ".htmlentities($codeString)." doesn't exist.");
		$code = new Code($codeString, $args["institution"]);
		
		
		if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())))
			throw new Exception("You do not have access to the shortcut " . $codeString);
		
		$code->addUser($_SESSION["AUTH"]->getId($args["user"]));
		
		return "Added " . $args["user"] . " as a user for " . $codeString;
	} catch (Exception $e) {
		throw $e;
	}
}

function doDeleteUser($args) {
	try {
		$codeString = str_replace(" ", "+", $args["code"]);
		if (!Code::exists($codeString, $args["institution"]))
			throw new Exception("Code ".htmlentities($codeString)." doesn't exist.");
		$code = new Code($codeString, $args["institution"]);
		
		if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())))
			throw new Exception("You do not have access to the shortcut " . $codeString);
		
		$code->delUser($_SESSION["AUTH"]->getId($args["user"]));
		
		return "Removed " . $args["user"] . " from " . $code->getName();
	} catch (Exception $e) {
		throw $e;
	}
}

function doUpdate($args) {
	try {
		$codeString = str_replace(" ", "+", $args["code"]);
		if (!Code::exists($codeString, $args["oldinst"]))
			throw new Exception("Code ".htmlentities($codeString)." doesn't exist.");
		$code = new Code($codeString, $args["oldinst"]);
		
		if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())))
			throw new Exception("You do not have access to the shortcut " . $codeString);
				
		$code->setUrl(urldecode($args["url"]), true);
		$code->setInstitution($args["newinst"], true);
		$code->setDescription($args["description"], true);
		$code->setPublic(($args["public"] == "1"), true);
		
		return "Updated the settings for shortcut " . $code->getName();
	} catch (Exception $e) {
		throw $e;
	}
}

function doNotify($args) {
	try {
		$user = new User($_SESSION["AUTH"]->getId());
		$user->setNotify(($args["notify"] == "1"), true);
		return "Changed your notification preferences.";
	} catch (Exception $e) {
		throw $e;
	}
}

function parseArgs($args) {
	$array = split(";", $args);
	$parsed = array();
	
	if (count($array) == 0) {
		$tmp = split("=", $args);
		$parsed[$tmp[0]] = $tmp[1];		
	} else {
		foreach($array as $arg) {
			$tmp = split("=", $arg);
			$parsed[$tmp[0]] = $tmp[1];
		}
	}
	
	return $parsed;
}

if (isset($_GET["name"]) && isset($_GET["args"])) {
	$response = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><responses>";
	global $connection;
	$connection->beginTransaction();
	
	try {		
		$message = "<response id=\"response\" color=\"green\">ALRIGHT! B) ";
		$parsed = parseArgs($_GET["args"]);
		switch($_GET["name"]) {
			case "create":
				$message .= doCreate($parsed);
				break;
			case "alias":
				$message .= doAlias($parsed);
				break;
			case "delete":
				$message .= doDelete($parsed);
				break;
			case "delalias":
				$message .= doDeleteAlias($parsed);
				break;
			case "adduser":
				$message .= doAddUser($parsed);
				break;
			case "deluser":
				$message .= doDeleteUser($parsed);
				break;
			case "update":
				$message .= doUpdate($parsed);
				break;
			case "notify":
				$message .= doNotify($parsed);
				break;
			default:
				break;
		}
		$connection->commit();
		$response .= $message . "</response>";
	} catch(Exception $e) {
		$response .= "<response id=\"response\" color=\"red\">OH NO! :( " . $e->getMessage() . "</response>";
		$connection->rollBack();
	}
	
	$response .= "</responses>";
	print $response;
}

?>
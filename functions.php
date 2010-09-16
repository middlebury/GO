<?php

require_once "go.php";

//this caused problems when including in a file so commented out
//header("Content-type:text/xml");

function doCreate($args) {
	try {
		$codeString = str_replace(" ", "+", $args["code"]);
		
		$args['url'] = urldecode($args['url']);
		if (!Code::isUrlValid($args['url']))
			throw new Exception('URL is not valid.');
		
		if (Alias::exists($codeString, $args["institution"])) {
			$alias = new Alias($codeString, null, $args["institution"]);
			throw new Exception('An alias for "'.$alias->getCode().'" already exists with name "'.htmlentities($codeString).'". You can\'t create a shortcut with the same name.');
		}
		
		$code = new Code($codeString, $args["institution"]);
		
		if ($code->getUrl() != "" && $code->getUrl() != $args["url"]) {
			throw new Exception("Someone has already created this code. Did you want to <a href='update.php'>edit the code</a>? NOTE: You may only edit codes for which you are an admin.");
		}
		
		$code->setUrl($args['url'], true);
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
		$codeString = str_replace(" ", "+", $args["code"]);
		if (!Code::exists($codeString, $args["institution"]))
			throw new Exception('Code '.htmlentities($codeString)." doesn't exist.");
			
		$code = new Code($codeString, $args["institution"]);
		
/*	Allow anyone to create aliases.	
		if (in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers()))) {
			$alias = new Alias($args["name"], $args["code"], $args["institution"]);
		} else {
			throw new Exception("You do not have access to the shortcut " . $args["code"]);
		}
*/
		$aliasString = str_replace(" ", "+", $args["name"]);
		if (Alias::exists($aliasString, $args["institution"])) {
			$alias = new Alias($aliasString, null, $args["institution"]);
			throw new Exception('An alias for "'.$alias->getCode().'" already exists with name "'.htmlentities($aliasString).'". Can\'t create another alias with the same name.');
		}
		if (Code::exists($aliasString, $args["institution"]))
			throw new Exception('A code named "'.htmlentities($aliasString)."\" already exists, can't create an alias with the same name.");
		
		$alias = new Alias($aliasString, $codeString, $args["institution"]);
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
		
		if ($code->getUser() != $_SESSION["AUTH"]->getId() || !isSuperAdmin())
			throw new Exception("You are not an admin of the code. Cannot delete.");
			
		$code->delete();
		return "Deleted shortcut " . $codeString;
	} catch (Exception $e) {
		throw $e;
	}
}

function doDeleteAlias($args) {
	try {
		$aliasString = str_replace(" ", "+", $args["alias"]);
		if (!Alias::exists($aliasString, $args["institution"]))
			throw new Exception("Alias ".htmlentities($aliasString)." doesn't exist.");
			$alias = new Alias($aliasString, $args["institution"]);
			// Get the Code from the Alias rather than relying on the request data.
			$code = new Code($alias->getCode(), $alias->getInstitution());
			if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())) || !isSuperAdmin)
				throw new Exception("You do not have access to the shortcut " . $code->getName());

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
		
		
		if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())) || !isSuperAdmin)
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
		
		if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())) || !isSuperAdmin)
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
		
		if (!in_array($_SESSION["AUTH"]->getId(), array_keys($code->getUsers())) || !isSuperAdmin)
			throw new Exception("You do not have access to the shortcut " . $codeString);
		
		$url = urldecode($args['url']);
		if (!Code::isUrlValid($url))
			throw new Exception('URL is not valid.');
		
		$code->setUrl($url, true);
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
		
		if ((!isset($_GET['xsrfkey']) || $_GET['xsrfkey'] != $_SESSION['xsrfkey'])
			&& (!isset($_POST['xsrfkey']) || $_POST['xsrfkey'] != $_SESSION['xsrfkey']))
		{
			throw new Exception('Cannot complete the requested action. This request might have been forged.');
		}
		
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

//function to get IP address via client ip or x
//forwarded first before falling back on remote addr
function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  //to check ip is pass from proxy
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//function to check if a user is a
//super admin of the GO application
function isSuperAdmin() {
	//this var is not passed to this function, use the global
	global $goAdmin;
	//if the current user is logged in, check it they are in the admin array
	if(isset($_SESSION["AUTH"]) && in_array($_SESSION["AUTH"]->getId(), $goAdmin)) {
		return true;
	} else {
		return false;
	}
}

// This is copy pasted function to get URL from current page 
function curPageURL() {
	$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
	$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
	$port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
	$url = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
	return $url;
}
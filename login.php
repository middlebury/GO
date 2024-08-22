<?php

require_once "go.php";

if (!empty($_GET['r']) && strpos($_GET['r'], 'https://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/') === 0) {
  header("location: login2.php?r=".urlencode($_GET['r']));
} else {
  header("location: login2.php");
}

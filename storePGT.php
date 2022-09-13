<?php
require_once "go.php";

//
// phpCAS proxied proxy
//

GoAuthCAS::configurePhpCas();

// Run the isAuthenticated() method to store the PGT to a temporary file.
phpCAS::isAuthenticated();

echo "Success";

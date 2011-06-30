<?php
//require_once "config.php";
require_once "go.php";
require_once "header.php";
?>
			<div class="content">
				<div id="response"></div>
				
				<?php // If an update message was set prior to a redirect
				// to this page display it and clear the message.
				if (isset($_SESSION['update_message'])) {
					foreach ($_SESSION['update_message'] as $message) {
						print $message;
					}
					unset($_SESSION['update_message']);
				}?>
			
				<p>This page describes the details for a single GO shortcut and its aliases. To view a list of all GO shortcuts, please see the <a href="gotionary.php">GOtionary</a>.</p>
				<p>GO shortcuts are managed by the people who created them. If you are one of the administrators for this shortcut, please log into the <a href="admin.php">self-service admin</a> page to change or update this shortcut.</p>
				<p>If you are not an administrator of this shortcut, please contact one of the shortcut administrators listed below for any problems, changes, or updates related to this shortcut. Be sure to refer to the URL of this page when contacting them.</p>
<?php

try {
	$name = str_replace(" ", "+", $_GET["code"]);
	
	$code = Code::get($name, $institution);
?>
				<dl>
					<dt>Code</dt>
					<dd><?php print htmlentities($code->getName()); ?></dd>
					<dt>Destination</dt>
					<dd><?php
						if (strlen($code->getUrl())) {
							$host_url = parse_url($code->getUrl(), PHP_URL_HOST);
							$internal_host = false;  
							foreach ($internal_hosts as $host) {
								if (preg_match($host, $host_url)) {
									$internal_host = true;
								}
							}
							if (!$internal_host) {
								print '<a class="external" rel="nofollow" href="'.htmlspecialchars($code->getUrl()).'">'.htmlentities($code->getUrl()).'</a>';
							} else {
								print '<a href="'.htmlspecialchars($code->getUrl()).'">'.htmlentities($code->getUrl()).'</a>';
							}
							if (!Code::isUrlValid($code->getUrl()))
								print '<br/><span class="error">Error: This URL is not valid.</span>';
						} else 
							print '<span class="error">Error: No destination is set for this code.</span>';
					?></dd>
					<?php
					$aliases = $code->getAliases();
					if (count($aliases)) {
						print "<dt>Aliases</dt>";
						print "<dd>".implode(' <br/>', array_keys($aliases))."</dd>";
					}
					?>

					<dt>Administrators of this code</dt>
					<dd><?php
						if (count($code->getUsers())) {
							$userStrings = array();
							foreach ($code->getUsers() as $user) {
								$userStrings[] = Go::getUserDisplayName($user->getName());
							}
							print implode (' <br/>', $userStrings);
							print "<br/><br/>Please contact one of these people for changes to this shortcut. ";
						} else {
							print "None -- Contact <a href='mailto:go@middlebury.edu'>go@middlebury.edu</a> to claim this code.";
						}
					?></dd>
					<dt>Display In GOtionary?</dt>
					<dd><?php print ($code->getPublic()? "yes":"no"); ?></dd>

				</dl>

				<!-- form for submitting flag as inappropriate -->

				<form action="flag.php" method="post">

					<?php
					
					//we assume the current code has not been flagged
					$current_code_flagged = false;
					//check to see if the current code has been flagged
					//by this user this session 
					$current_code_flagged = in_array($code->getName(), $_SESSION['flagged']);
					//if not, check to see if the user is logged in and if
					//so, see if they've flagged this before and it's still in
					//the flag queue
					if (isset($_SESSION["AUTH"]) && $current_code_flagged == false) { 
						$result = array();
						//get a count of the times the current code appears
						//in the flag table for this user
  					$select = $connection->prepare("SELECT COUNT(*) FROM flag WHERE code = ? AND user = ?");
  					$select->bindValue(1, $code->getName());
  					$select->bindValue(2, $_SESSION["AUTH"]->getId());
  					$select->execute();
  					//place the results into count
  					if (intval($select->fetchColumn()) > 0) {
  						$current_code_flagged = true;
  					}
  				}
					
					//if the current code has been flagged this session or
					//the count is greater than 0 (has been flagged by this
					//user in a previous session) display this message
					if ($current_code_flagged == true) {
						print '<p id="already_flagged_message">You\'ve flagged this link as inappropriate. An administrator has been notified and will review the quality of this link at a later time. Thank you for your assistance in moderating our go links.</p>';
					//if anon flagging is turned of and the user is not authenticated	
					} elseif (ANON_FLAGGING == false && !isset($_SESSION["AUTH"])) {
						//don't display the flag as inappropriate button
					//otherwise, display the flag as inappropriate button
					} else {
						//pass the xsrfkey and code to the processor
						print '<div><input type="hidden" name="xsrfkey" value="'. $_SESSION['xsrfkey']. '" />';
						
						print '<input type="hidden" name="code" value="'. $code->getName() .'" />';
						
						print '<input type="hidden" name="institution" value="'. $code->getInstitution() .'" />';
						
						print '<input type="hidden" name="url" value="'. htmlentities($code->getUrl()) .'" />';
						
						print '<input type="submit" id="flag_inappropriate" value="Flag as Inappropriate" />';
						
						if (isset($_SESSION['comment_required'])) {
							//handle the "reason" field if it failed validation
							?>
							Reason: <input type="text" id="flag_comment" name="flag_comment" class="failed_validation" value="<?php if (isset($_SESSION['form_values'])) { print htmlentities($_SESSION['form_values']['flag_comment']); } ?>"/>
							<?php
							unset($_SESSION['comment_required']);
						} else {
							//handle the "reason" field normally
							?>
							Reason: <input type="text" id="flag_comment" name="flag_comment" value="<?php if (isset($_SESSION['form_values'])) { print htmlentities($_SESSION['form_values']['flag_comment']); } ?>"/>
							<?php
						}
						//include captcha
						if (!isset($_SESSION['AUTH'])) {
							require_once('recaptcha/recaptchalib.php');
          		$publickey = RECAPTCHA_PUBLIC; // you got this from the signup page
							echo recaptcha_get_html($publickey);
						}
					}
					
					//superadimin stuff
					if (isset($_SESSION['AUTH'])) {
						if (isSuperAdmin($_SESSION['AUTH']->getId())) {
					 		print "<p>Admin:<br /><a class='history_button' href='details.php?code=".$code->getName()."&amp;institution=".$code->getInstitution()."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='Show History' /></a>";
					
							print "<a class='info_edit_button' href='update.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "'><input onclick='window.location=\"update.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "\"' type='button' value='Edit this Code' /></a></p>";
						}
					}
					?>
					</div>
				</form>

				<?php } catch (Exception $e) { print "<div
				class='error'>Error: ".htmlentities($e->getMessage())."</div>"; } ?>
				</div> </div> </body> </html>

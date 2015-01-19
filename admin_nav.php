			<nav class="gonav">
				<ul>
					<li><a href="create.php">Create</a></li>
					<li><a href="my_codes.php">View / Update</a></li>
					<?php
						if (isSuperAdmin()) {
							print"\n<li>";
							print"\n<a href=\"flag_admin.php\">Flags</a>";
							print"\n</li>";
						}
					?>
					<li><a href="gotionary.php">GOtionary</a></li>
				</ul>
			</nav>
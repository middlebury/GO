			<div class="headerNavigation">
				<div class="CssMenu">
					<div class="AspNet-Menu-Horizontal">
						<ul class="AspNet-Menu">
							<li class="AspNet-Menu-Leaf">
								<a href="create.php">Create</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="my_codes.php">View / Update</a>
							</li>
							<?php
								if (isSuperAdmin()) {
									print"\n<li class=\"AspNet-Menu-Leaf\">";
									print"\n<a href=\"flag_admin.php\">Flags</a>";
									print"\n</li>";
								}
							?>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php">GOtionary</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="clear">&#160;</div>
			</div>
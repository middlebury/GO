=====================================
 About
=====================================
GO is a shortcut and redirection application that allows users to maintain the 
destination of links to resources.

For example, lets say that an important page lives at:
	http://www.example.edu/my/long/path/to/this/thing

Rather than making links to that URL, a user can create a go shortcut, 'thing',
for this resource. This allows people to link to the resource via:
	http://go.example.edu/thing 
which will redirect them to the full URL. 

If users are on the LAN and have their DNS search path set correctly, entering 
'go/thing' in their address bar will redirect them to the resource as well.

Now, lets say it is several years later and we want to move this resource to a 
new home:
	http://blogs.example.edu/this/thing

The user who moved the resource can go to the GO self-service admin screens and 
update the GO shortcut for 'thing' to redirect to the new URL. Users clicking on
other GO links in websites or email will be redirected to the new location of the
resource.

=====================================
 Authors
=====================================
   Ian McBride (imcbride@middlebury.edu)
   Adam Franco (afranco@middlebury.edu)
*  Matt La France (lafrance@middlebury.edu)

* Current Maintainer

=====================================
 History
=====================================
The first version of GO was proposed by Chris Norris (Middlebury College Webmaster
at the time) and written by Ian McBride in 2004. Ian rewrote GO in 2008 and added 
the self-service administration screens in 2009.

In April 2010 Adam Franco took over development of GO and has refactored portions
of the codebase.

GO was released under the GPL on June 23, 2010.

=====================================
 License
=====================================
The GO application is licensed under the GNU General Public License (GPL) version 3 or later.

The GO application includes the phpCAS library. Please see go/phpcas/docs/README for license details.

=====================================
 Installation
=====================================

From Git:

1. Clone the Git repository to a web-accessible directory:
	git clone git://github.com/middlebury/GO.git
	cd GO
	git submodule init
	git submodule update
	
2. Create a database for GO and import the database schema:
	mysql -u username -p -D go < database.sql

3. Copy the config.php.defaults to config.php and edit the values to match your 
   database username and password.

4. Edit config.php and configure 1 or more institution base URLs.

5. Edit config.php and configure an authentication scheme.
   
   Note, Middlebury College uses CAS authentication currently, so this method is
   the most tested. We used LDAP authentication in the past, so this should work,
   but hasn't received testing recently.

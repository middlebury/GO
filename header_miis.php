<?php
// Requiring go_functions.php give us acces to the curPageURL() function
require_once "go_functions.php";
require_once "go.php";

$name = "";

if (isset($_SESSION["AUTH"])) {
  try {
    $name = $_SESSION["AUTH"]->getName();
  } catch (Exception $e) {
    // We may have an expired proxy-ticket kept around. If so, regenerate the session
    // and log-in again.
    if ($e->getCode() == PHPCAS_SERVICE_PT_FAILURE) {
      session_destroy();
      header('Location: '.$_SERVER['REQUEST_URI']);
      exit;
    } else {
    	throw $e;
    }
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>MIIS</title>
    <link type="text/css" rel="stylesheet" media="all" href="//cdn.middlebury.edu/miis.edu/2010/css/miis.css" />
    <link rel="stylesheet" href="styles.css">
    <link type="image/x-icon" rel="shortcut icon" href="//cdn.middlebury.edu/miis.edu/2010/images/miis-favicon.gif" />
    <link rel="apple-touch-icon" href="//cdn.middlebury.edu/miis.edu/2010/images/apple-touch-icon/icon.png">
    <link rel="apple-touch-icon" sizes="76x76" href="//cdn.middlebury.edu/miis.edu/2010/images/apple-touch-icon/icon-ipad.png">
    <link rel="apple-touch-icon" sizes="120x120" href="//cdn.middlebury.edu/miis.edu/2010/images/apple-touch-icon/icon-retina.png">
    <link rel="apple-touch-icon" sizes="152x152" href="//cdn.middlebury.edu/miis.edu/2010/images/apple-touch-icon/icon-ipad-retina.png">
    <!--[if IE]><script type="text/javascript" src="//cdn.middlebury.edu/miis.edu/2010/js/html5.js"></script><![endif]-->
    <!--[if lt IE 7]><link rel="stylesheet" href="//cdn.middlebury.edu/miis.edu/2010/js/ie6.css" /><![endif]-->
    <!--[if lt IE 9]><link rel="stylesheet" href="//cdn.middlebury.edu/miis.edu/2010/js/ieHTML5.css" /><![endif]-->
  </head>
  <body>
    <a href="#miis_content" class="skiplink">Skip to content</a>
    <header class="header">
      <div class="container">
        <a class="wordmark" href="http://www.miis.edu">
          <img src="//cdn.middlebury.edu/miis.edu/2010/images/logo.png" width="430" height="90" alt="Middlebury Institute of International Studies at Monterey. Formerly the Monterey Institute of International Studies." />
        </a>
        <ul class="languages">
          <li class="left"><a href="http://www.miis.edu/languages/arabic">&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;</a></li>
          <li class="right"><a href="http://www.miis.edu/languages/espanol">Espa&#241;ol</a></li>
          <li class="left"><a href="http://www.miis.edu/languages/chinese">&#20013;&#25991;</a></li>
          <li class="right"><a href="http://www.miis.edu/languages/francais">Fran&#231;ais</a></li>
          <li class="left"><a href="http://www.miis.edu/languages/deutsch">Deutsch</a></li>
          <li class="right"><a href="http://www.miis.edu/languages/japanese">&#26085;&#26412;&#35486;</a></li>
          <li class="left"><a href="http://www.miis.edu/languages/korean">&#54620;&#44397;&#50612;</a></li>
          <li class="right"><a href="http://www.miis.edu/languages/russian">&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;</a></li>
          <li class="left"><a href="http://www.miis.edu/languages/portuguese">Portugu&#234;s</a></li>
        </ul>
        <form class="search" action="http://www.miis.edu/search" method="get">
          <input type="text" name="q2" />
          <button type="submit">Go</button>
        </form>
        <ul class="gateways">
          <li class="left"><a href="http://www.miis.edu/students">Students</a></li>
          <li class="right"><a href="http://www.miis.edu/alumni">Alumni</a></li>
          <li class="left"><a href="http://www.miis.edu/facstaff">Faculty &amp; Staff</a></li>
          <li class="right"><a href="http://www.miis.edu/employers">Employers</a></li>
          <li class="left"><a href="http://www.miis.edu/offices">Offices &amp; Services</a></li>
          <li class="right"><a href="http://www.miis.edu/events">Events</a></li>
        </ul>
      </div>
    </header>
    <nav class="navigation">
      <ul id="miis_navigation" class="container has_dropdowns">
        <li class="navigation_why"><a href="http://www.miis.edu/why" class="tab">Why MIIS<span></span></a>
          <div class="nav_dropdown">
            <div class="nav_feature"></div>
            <ul>
              <li class="main left"><a href="http://www.miis.edu/why">Why MIIS</a></li>
              <li class="right"><a href="http://www.miis.edu/why/careers">Why MIIS: Careers</a></li>
              <li class="left"><a href="http://www.miis.edu/why/faculty">Why MIIS: Faculty</a></li>
              <li class="right"><a href="http://www.miis.edu/why/immersive">Why MIIS: Opportunities</a></li>
              <li class="left"><a href="http://www.miis.edu/why/monterey">Why MIIS: Student Life</a></li>
            </ul>
          </div>
        </li>
        <li class="navigation_about"><a href="http://www.miis.edu/about" class="tab">About<span></span></a>
          <div class="nav_dropdown">
            <div class="nav_feature"></div>
            <ul>
              <li class="main left"><a href="http://www.miis.edu/about">About MIIS</a></li>
              <li class="right"><a href="http://www.miis.edu/about/facts">Fast Facts</a></li>
              <li class="left"><a href="http://www.miis.edu/about/governance">Governance</a></li>
              <li class="right"><a href="http://www.miis.edu/about/groups">Affinity Groups</a></li>
              <li class="left"><a href="http://www.miis.edu/about/middlebury">Middlebury Connection</a></li>
              <li class="right"><a href="http://www.miis.edu/about/partnerships">Strategic Partnerships</a></li>
              <li class="left"><a href="http://www.miis.edu/about/sustainability">Sustainable Campus</a></li>
              <li class="right"><a href="http://www.miis.edu/about/monterey">Location</a></li>
              <li class="left"><a href="http://www.miis.edu/about/newsroom">Newsroom</a></li>
              <li class="right"><a href="http://www.miis.edu/about/contact">Contact Us</a></li>
            </ul>
          </div>
        </li>
        <li class="navigation_admissions"><a href="http://www.miis.edu/admissions" class="tab">Admissions<span></span></a>
          <div class="nav_dropdown">
            <div class="nav_feature"></div>
            <ul>
              <li class="main left"><a href="http://www.miis.edu/admissions">Admissions</a></li>
              <li class="right"><a href="http://www.miis.edu/admissions/apply">Application Process</a></li>
              <li class="left"><a href="http://www.miis.edu/admissions/requirements">Requirements</a></li>
              <li class="right"><a href="http://www.miis.edu/admissions/visit">Visit Campus</a></li>
              <li class="left"><a href="http://www.miis.edu/admissions/tuition">Tuition &amp; Fees</a></li>
              <li class="right"><a href="http://www.miis.edu/admissions/financialaid">Finance Your Education</a></li>
              <li class="left"><a href="http://www.miis.edu/admissions/faqs">FAQs</a></li>
              <li class="right"><a href="http://www.miis.edu/admissions/contact">Contact Us</a></li>
            </ul>
          </div>
        </li>
        <li class="navigation_academics"><a href="http://www.miis.edu/academics" class="tab">Academics<span></span></a>
          <div class="nav_dropdown">
            <div class="nav_feature"></div>
            <ul>
              <li class="main left"><a href="http://www.miis.edu/academics">Academic Programs</a></li>
              <li class="right"><a href="http://www.miis.edu/academics/calendar">Academic Calendar</a></li>
              <li class="left"><a href="http://www.miis.edu/academics/courses">Courses</a></li>
              <li class="right"><a href="http://www.miis.edu/academics/faculty">Faculty</a></li>
              <li class="left"><a href="http://www.miis.edu/academics/language">Language Learning</a></li>
              <li class="right"><a href="http://www.miis.edu/academics/library">Library</a></li>
              <li class="left"><a href="http://www.miis.edu/academics/short">Professional Development</a></li>
              <li class="right"><a href="http://www.miis.edu/academics/researchcenters">Research</a></li>
              <li class="left"><a href="http://www.miis.edu/academics/monterey-abroad">Study Abroad</a></li>
              <li class="right"><a href="http://www.miis.edu/academics/resources">Academic Resources</a></li>
            </ul>
          </div>
        </li>
        <li class="navigation_studentlife"><a href="http://www.miis.edu/student-life" class="tab">Student Life<span></span></a>
          <div class="nav_dropdown">
            <div class="nav_feature"></div>
            <ul>
              <li class="main left"><a href="http://www.miis.edu/student-life">Student Life</a></li>
              <li class="right"><a href="http://www.miis.edu/student-life/housing">Housing</a></li>
              <li class="left"><a href="http://www.miis.edu/student-life/international">International Students</a></li>
              <li class="right"><a href="http://www.miis.edu/student-life/monterey">Life in Monterey</a></li>
              <li class="left"><a href="http://www.miis.edu/student-life/health-wellness">Health and Wellness</a></li>
              <li class="right"><a href="http://www.miis.edu/student-life/newstudent">New Student Info</a></li>
              <li class="left"><a href="http://www.miis.edu/student-life/commencement">Commencement</a></li>
              <li class="right"><a href="http://www.miis.edu/student-life/council">Student Council</a></li>
              <li class="left"><a href="http://www.miis.edu/student-life/veterans">MIIS Veterans</a></li>
              <li class="right"><a href="http://www.miis.edu/student-life/clubs">Student Clubs</a></li>
              <li class="left"><a href="http://www.miis.edu/student-life/policies">Student Life Policies</a></li>
              <li class="right"><a href="http://www.miis.edu/student-life/world">MIIS Around the World</a></li>
              <li class="left"><a href="http://www.miis.edu/student-life/contact">Contact Us</a></li>
            </ul>
          </div>
        </li>
        <li class="navigation_careers"><a href="http://www.miis.edu/careers" class="tab">Careers<span></span></a>
          <div class="nav_dropdown">
            <div class="nav_feature"></div>
            <ul>
              <li class="main full"><a href="http://www.miis.edu/careers">Launch Your Career</a></li>
              <li class="left"><a href="http://www.miis.edu/careers/contact">Meet Your Advisor</a></li>
              <li class="right"><a href="http://www.miis.edu/careers/our-services">Check Out Our Services</a></li>
              <li class="left"><a href="http://www.miis.edu/careers/fields">Explore Careers by Field</a></li>
              <li class="right"><a href="http://www.miis.edu/careers/events">Attend Career Events</a></li>
              <li class="left"><a href="http://www.miis.edu/careers/internships">Find Internships</a></li>
              <li class="right"><a href="http://www.miis.edu/careers/fellowships">Apply for Fellowships</a></li>
              <li class="left"><a href="http://www.miis.edu/careers/jobs">Search for Jobs</a></li>
              <li class="right"><a href="http://www.miis.edu/careers/connected">Stay Connected</a></li>
            </ul>
          </div>
        </li>
        <li class="navigation_giving"><a href="http://www.miis.edu/giving" class="tab">Giving<span></span></a>
          <div class="nav_dropdown">
            <div class="nav_feature"></div>
            <ul>
              <li class="main left"><a href="http://www.miis.edu/giving">Giving to MIIS</a></li>
              <li class="right"><a href="http://www.miis.edu/giving/annualfund">Annual Fund</a></li>
              <li class="left"><a href="http://www.miis.edu/giving/donor-recognition">Donor Recognition</a></li>
              <li class="right"><a href="http://www.miis.edu/giving/ways">Ways to Give</a></li>
              <li class="left"><a href="http://www.miis.edu/giving/legacy">Plan Your Legacy</a></li>
              <li class="right"><a href="http://www.miis.edu/giving/roundtable">Career Fair + MIIS Roundtable</a></li>
              <li class="left"><a href="http://www.miis.edu/giving/news">News &amp; Events</a></li>
              <li class="right"><a href="http://www.miis.edu/giving/contact">Contact Us</a></li>
            </ul>
          </div>
        </li>
      </ul>
    </nav>
    <section class="page container">
      <div class="body fullwidth">
        <nav class="breadcrumb">
          <?php
            if ($name) {
              print "Welcome ".htmlentities($name)." &#160; | &#160;";
            }
            foreach(array_keys($institutions) as $inst) {
              if ($inst == $institution)
                print "<strong>";
              print "<a href=\"".htmlentities(equivalentUrl($inst))."\">" . $inst . "</a> &#160; | &#160;";
              if ($inst == $institution)
                print "</strong>";
            }
            //show a login link if a user is not logged in
            //this is duplicated in gotionary.php
            if (!isset($_SESSION["AUTH"])) {
              if (AUTH_METHOD == 'cas') {
                print "<a href='login2.php?&amp;url=".urlencode(curPageURL()."&amp;destination=".curPageURL())."'>Log in</a>";
              } else {
                print "<a href='login.php?r=".urlencode(curPageURL())."'>Log in</a>";
              }
            } else {
              print "<a href='admin.php'>Manage GO</a>";
              print " | <a href='logout.php'>Log Out</a>";
            }
          ?>
        </nav>
        <div id="miis_content" class="content">
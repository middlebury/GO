<?php
// Requiring go_functions.php give us acces to the curPageURL() function
require_once "go_functions.php";
require_once "go.php";

$name = "";

if (isset($_SESSION["AUTH"]) && $_SESSION["AUTH"]->isAuthenticated()) {
  $name = $_SESSION["AUTH"]->getCurrentUserName();
}
?>
<!DOCTYPE html>
<html lang="en" xmlns:org="http://opengraphprotocol.org/schema/" xmns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Middlebury</title>
    <meta charset="utf-8">
    <script async defer data-domain="middlebury.edu" src="https://plausible.io/js/script.file-downloads.hash.outbound-links.pageview-props.tagged-events.js"></script>
    <script>window.plausible = window.plausible || function() { (window.plausible.q = window.plausible.q || []).push(arguments) }</script>
    <link rel="stylesheet" href="//cdn.middlebury.edu/middlebury.edu/2010/css/midd.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="//cdn.middlebury.edu/middlebury.edu/2010/images/midd_favicon.ico">
    <link rel="apple-touch-icon" href="//cdn.middlebury.edu/middlebury.edu/2010/images/apple-touch-icon/icon.png">
    <link rel="apple-touch-icon" sizes="76x76" href="//cdn.middlebury.edu/middlebury.edu/2010/images/apple-touch-icon/icon-ipad.png">
    <link rel="apple-touch-icon" sizes="120x120" href="//cdn.middlebury.edu/middlebury.edu/2010/images/apple-touch-icon/icon-retina.png">
    <link rel="apple-touch-icon" sizes="152x152" href="//cdn.middlebury.edu/middlebury.edu/2010/images/apple-touch-icon/icon-ipad-retina.png">
    <!--[if lt IE 8]><script src="https://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js"></script><![endif]-->
    <!--[if lt IE 9]><script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
  </head>
  <body class="fullwidth">
    <!-- Google Tag Manager -->
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NB55WH"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NB55WH');</script>
    <!-- End Google Tag Manager -->
    <a href="#midd_content" class="skiplink">Skip to content</a>
    <header class="container">
      <nav class="leftlinks">
        <a href="http://go.middlebury.edu/mail">WebMail</a>&nbsp;|
        <a href="http://go.middlebury.edu/bw">BannerWeb</a>&nbsp;|
        <a href="http://go.middlebury.edu/portal">Portal</a>
      </nav>
      <form class="search" action="http://www.middlebury.edu/search" method="get" target="_top">
        <label for="midd_search_query">Search Midd</label>
        <input type="search" id="midd_search_query" class="search_query x-webkit-speech" name="q2" placeholder="Search Midd" x-webkit-speech required>
        <input type="submit" id="midd_search_submit" class="search_submit" value="Go">
        <input type="hidden" id="midd_ajax_search_url" value="http://www.middlebury.edu/go/search">
      </form>
      <h1 id="midd_wordmark" class="wordmark"><a href="http://www.middlebury.edu"><img src="//cdn.middlebury.edu/middlebury.edu/2010/images/logo.png" width="380" height="110" alt="Middlebury"></a></h1>
    </header>
    <nav id="midd_navigation" class="navigation container">
      <ul>
        <li class="nav_admissions top"><a href="http://www.middlebury.edu/admissions">Admissions<span></span></a></li>
        <li class="nav_academics top"><a href="http://www.middlebury.edu/academics">Academics<span></span></a></li>
        <li class="nav_studentlife top"><a href="http://www.middlebury.edu/studentlife">Student Life<span></span></a></li>
        <li class="nav_athletics top"><a href="http://www.middlebury.edu/athletics">Athletics<span></span></a></li>
        <li class="nav_arts top"><a href="http://www.middlebury.edu/arts">Arts<span></span></a></li>
        <li class="nav_international top"><a href="http://www.middlebury.edu/international">Middlebury International<span></span></a></li>
        <li class="nav_middlab top"><a href="http://middlab.middlebury.edu/">MiddLab<span></span></a></li>
        <li class="nav_about bottom"><a href="http://www.middlebury.edu/about">About<span></span></a></li>
        <li class="nav_sustainability bottom"><a href="http://www.middlebury.edu/sustainability">Sustainability<span></span></a></li>
        <li class="nav_giving bottom"><a href="http://www.middlebury.edu/giving">Giving<span></span></a></li>
        <li class="nav_news bottom"><a href="http://www.middlebury.edu/newsroom">News Room<span></span></a></li>
        <li class="nav_events bottom"><a href="http://www.middlebury.edu/events">Calendar of Events<span></span></a></li>
        <li class="nav_offices bottom"><a href="http://www.middlebury.edu/offices">Office &amp; Services<span></span></a></li>
      </ul>
    </nav>
    <article id="midd_content" class="pagecontent container">
      <nav id="midd_taskbar" class="taskbar">
        <a class="taskbar_back" href="/offices/">Return to all <strong>Offices &amp; Services</strong></a>
        <section class="taskbar_dropdowns">
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
        </section>
      </nav>
      <section class="banner">
        <a class="noborder" href="#"><img src="//cdn.middlebury.edu/middlebury.edu/2010/images/headers/go.png" alt="GO" /></a>
      </section>
      <section class="page">
        <section class="body">

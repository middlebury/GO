<?php
	if (in_array($_SERVER["HTTP_HOST"], array("go.middlebury.edu", "go.miis.edu"))) {
?>
    <script type="text/javascript">
    var _gaq = _gaq || [];
  	_gaq.push(['_setAccount', 'UA-993303-1']);
  	_gaq.push(['_setDomainName', 'middlebury.edu']);
  	_gaq.push(['_trackPageview']);

  	(function() {
    	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  	})();

    </script>
<?php	
	}
?>
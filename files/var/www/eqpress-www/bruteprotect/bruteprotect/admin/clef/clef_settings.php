<?php
	define( 'CLEF_BASE', 'https://clef.io' );
    $site_name = urlencode(get_option('blogname'));
    $site_domain = urlencode(get_option('siteurl'));
	
?>

<div class="wrap">
    
    <h2 style="clear: both; margin-bottom: 15px;">
        <img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>images/BruteProtect-CLEF-Logo-Text-Only-40.png" alt="BruteProtect" width="447" height="40" style="margin-bottom: -2px;" /> 
    </h2>
	
	<p>Clef is a free WordPress plugin that allows you to use your phone to log in to your website.  It only takes about 30 seconds to set up, and it is a great compliment to BruteProtect in securing your website.</p>
	
    <p>Something not working? Email <a href="mailto:support@getclef.com">support@getclef.com.</a>
    </p>
    <!-- <a href="<?php echo $url ?>" class="button button-primary button-hero">Install and Activate Clef</a> -->
	<p>To install Clef, search for it in the Plugin Directory and follow instructions</p>
	<div style="clear:both; height: 20px;"> &nbsp; </div>

<iframe src="<?php echo CLEF_BASE ?>/iframes/wordpress?domain=<?php echo $site_domain ?>&amp;name=<?php echo $site_name ?>&amp;source=bruteprotect" width="525" height="350"></iframe>

</div>
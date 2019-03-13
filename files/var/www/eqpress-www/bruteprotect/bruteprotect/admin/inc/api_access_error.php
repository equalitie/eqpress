<?php
$reporting_data = unserialize(base64_decode($this->error_reporting_data));
$error_rows = @$reporting_data['error']->errors;
$privacy_key = $this->get_privacy_key();
?>
<div class="error below-h2" id="message" style="padding-bottom: 20px;">
	<h2>API access error :(</h2>
	<h3>In order for BruteProtect to work, your site needs to be able to contact our servers.  This isn't working right now, but we're going to help you figure out the problem. Please follow the steps below:</h3>
	<ol style="max-width: 600px;">
		<li>Click <a href="http://api.bruteprotect.com/up.php" target="_blank">this link</a> (<a href="http://api.bruteprotect.com/up.php" target="_blank">http://api.bruteprotect.com/up.php</a>). <br />If this link <strong>DOES NOT</strong> work, our servers are currently offline, and you can disregard this message.  We'll be back online soon!  <br />If this link <strong>DOES</strong> work, then please continue to the next step</li>
		<li>Copy all of the following text to your cliboard and paste it into a new support request to your hosting company:<br />
			<textarea style="width: 100%; height: 250px; font-size: 12px; line-height: 14px;">Hello!  I am submitting this support ticket because I am having trouble with your server and the domain <?php echo $this->brute_get_local_host(); ?>

				
I have copied and pasted this message from a WordPress security plugin called BruteProtect (http://bruteprotect.com/).  If you're not familiar with BruteProtect, it is the most effective tool available to keep WordPress sites from being infiltrated by distributed brute force attacks.  It has also been shown to reduce server load by up to 70% and decrease bandwidth usage by up to 85%, so it's good for everyone!

The problem we are experiencing is an inability to access the BruteProtect API (https://api.bruteprotect.com/) from within the plugin. When we attempt to reach the API, the following error(s) are returned:

<?php if(is_array($error_rows)) :  foreach($error_rows as $key => $msg) : ?>
<?php echo $key ?>: <?php echo $msg[0] ?>
<?php endforeach; endif;  ?>


For more information, or to test to see if this issue is fixed, you can visit:
http://<?php echo $this->brute_get_local_host(); ?>/?bpc=<?php echo $privacy_key ?>


If you have any questions, feel free to reach out to the team at BruteProtect:
help@bruteprotect.com</textarea>
		</li>
	</ol>
	
	If you have an exceptionally good (or an exceptionally bad) experience with your host, let us know by sending an email to <a href="mailto:help@bruteprotect.com">help@bruteprotect.com</a>.  Thank you!
	
</div>
</div>
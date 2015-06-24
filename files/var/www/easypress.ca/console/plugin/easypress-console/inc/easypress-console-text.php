<?php
function ep_console_docs_website_stats() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>Website Stats</span></h3>
						<div class="inside">
							<p>Website stats will show you exactly the number of times your site has been accessed. This includes robots and spiders.</p>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
						<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
<?php
					$on_the_overview_page = strpos( $_SERVER['QUERY_STRING'], "page=ep_dashboard" );
					if ( false !== $on_the_overview_page ) {
?>
						<div class="postbox">
							<h3><span>About The Console</span></h3>
							<div class="inside">
								<p>The Console allows you to perform some administrative tasks that otherwise would require shell access.</p>
							</div>
						</div> <!-- .postbox -->
<?php
					}
?>
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}

function ep_console_docs_manage_cache() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>Manage Cache</span></h3>
						<div class="inside">
							<p>Manage Cache provides a way to delete the server cache. If you are making changes to your content and you need to see the changes immediately then you can use this feature to purge the web server's cache. It might take up to a minute for the cache to be removed depending on the its size.</p>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}

function ep_console_docs_view_logs() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>View Logs</span></h3>
						<div class="inside">
							<p>View Logs allows you to view the following log files:</p>
							<ol>
								<li><strong>PHP error log</strong> - contains a record of all PHP errors produced by plugins and themes.</li>
								<li><strong>Web server access log</strong> - contains a record of every file transfered from you site.</li>
								<li><strong>Web server error log</strong> - contains a record of every error encountered by the web server.</li>
							</ol>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}

function ep_console_docs_file_permissions() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>File Permissions</span></h3>
						<div class="inside">
							<p>The File Permissions feature allows you to reset the permissions and ownership on your files back to the default settings. The reason you might need to do this is if you upload or install a plugin manually and then that plugin requires write access to functions properly. The issue is that your SFTP username is not the same as the web server's user name so when you upload files into your document root you do so as your SFTP username. So, when the plugin tries to write to a file or directory that's owned by your SFTP username and not the web server's username it will fail because of insufficient ownership and permission of those files. The button on the File Permissions page will reset all directories and files under your document root to be owned by the web server user.</p>
							<p>This default setting is very convenient for installing and updating plugins and themes but it's not the most secure way to configure a WordPress environment. This is why we also provide the Security Lockdown feature.
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}

function ep_console_docs_security_lockdown() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>Security Lockdown</span></h3>
						<div class="inside">
							<p>The Security Lockdown feature allows you to change the ownership and permissions on all the files and directories under your document root to be owned by your SFTP username. When you choose to lock down your site none of your files will be owned by the web server's user which effectively prevents it from writing to any of your files or directories. This creates a very secure environment since access to your public site is via the web server and by disallowing the web server from having write access then you are protecting yourself against any potential hacks that work by creating or downloading new files within your document root.</p>
							<p>When the site is locked down you will see the text "Site Locked Down" in your admin bar at the top of the page which is also a link to the Security Lockdown page.</p>
							<p><strong>Important Note:</strong> When the site is locked down you will not be able to install new plugins or themes. You will not be able to update plugins, themes or WordPress itself. This is not a bug but a feature since the web server does not have write access to your files. This is exactly what the lockdown is supposed to do. If you need to update or install a plugin (or theme), simply unlock your site, peform the update or installation and then lock the site down once again.</p>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}

function ep_console_docs_sftp_stuff() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>Reset Password</span></h3>
						<div class="inside">
							<p>This screen presents your SFTP info as well as allowing you to reset your SFTP password. Just click the button and a new password will be emailed to you as well as shown on the screen.</p>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}

function ep_console_docs_wpadmin_ssl() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>Administration Over SSL</span></h3>
						<div class="inside">
							<p>SSL provides confidentiality between your browser and the web server. By encrypting the communication between you and the server you are making it very difficult for malicious hackers to steal your private information. Credentials such as usernames and passwords will be undecipherable if they were to be intercepted while in transit if you use SSL. Same with your authentication tokens such as cookies which are sent every time you view or make changes via the admin screens.</p>
							<p>There are 3 choices when configuring WordPress to use SSL:
							<ol>
								<li>Enable SSL for logins and all admin screens.</li>	
								<li>Enable SSL for logging in only.</li>	
								<li>Disable SSL.</li>	
							</ol></p>
							<p>Please note that if you enable SSL you will be using the our SSL certificates therefore you will encounter SSL warnings the first time you visit your admin screens. You can read more about the warnings you will encounter on the help page titled <a href="http://eqpress.equalit.ie/security-warning-for-wp-admin-and-ssl-certificates/" target="_blank" title="Security warning for wp-admin and SSL certificates">Security warning for wp-admin and SSL certificates.</a> If you have your own SSL certificates please get in touch with <a href="mailto:{{ org_support_email }}">Support</a> and we will install them for you.</p>
							<p>You can read more about <a href="http://codex.wordpress.org/Administration_Over_SSL" target="_blank" title="Administration Over SSL for WordPress">Administration Over SSL</a> on the WordPress Codex</p>	
					</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}

function ep_console_docs_editor() {
?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span>Plugin and Theme Editor</span></h3>
						<div class="inside">
							<p>Occasionally you may wish to disable the plugin or theme editor to prevent overzealous users from being able to edit sensitive files and potentially crash the site. Disabling these also provides an additional layer of security if a hacker gains access to a well-privileged user account.</p>
							<p>There are 2 choices:
							<ol>
								<li>Enable plugin and theme editing via the admin screens.</li>	
								<li>Disable plugin and theme editing via the admin screens.</li>	
							</ol></p>
							<p>You can read more about <a href="http://codex.wordpress.org/Editing_wp-config.php#Disable_the_Plugin_and_Theme_Editor" target="_blank" title=" Disable the Plugin and Theme Editor"> Disabling the Plugin and Theme Editor</a> on the WordPress Codex</p>	
					</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 .postbox-container -->
		</div> <!-- #poststuff -->
	</div>
<div class="clear"></div>
<?php
}
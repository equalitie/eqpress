=== BruteProtect ===
Contributors: hotchkissconsulting, roccotripaldi, sdquirk
Tags: security, brute force, brute force attack, harden wp, login lockdown, multisite
Requires at least: 3.0
Tested up to: 3.8
Stable tag: trunk

BruteProtect is a cloud-powered Brute Force attack prevention plugin.  We leverage the millions of WordPress sites to identify and block malicious IPs.  Once you install the plugin, you will need to get a free BruteProtect API key, which you can do directly from your WordPress dashboard.

== Description ==

BruteProtect tracks failed login attempts across all installed users of the plugin.  If any single IP has too many failed attempts in a short period of time, they are blocked from logging in to any site with this plugin installed.  Once you install the plugin, you will need to get a free BruteProtect API key, which you can do directly from your WordPress dashboard.

This allows you to protect yourself against traditional brute force attacks AND distributed brute force attacks that use many servers and many IPs

BruteProtect FULLY SUPPORTS multisite networks

== Installation ==

1.  Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation
2.  Activate the Plugin from Plugins page
3.  Open the BruteProtect settings under the \"Plugin\" section of the WordPress dashboard
4.  Follow the simple instructions to obtain and enter a free API key

== Screenshots ==
1. Simply create an API key directly from your WordPress Admin panel...
2. Enter your API key, it will be verified instantly so that you know you're protected
3. On your WordPress dashboard, you can see just how many attackers BruteProtect has blocked
4. If a blocked user shows up on your login page, they will see this message

== Changelog ==

= 1.0.0.2b =
* Remove 1-click clef until we figure out the bug.

= 1.0.0.1b =
* File got corrupted when uploading to the plugin repository.  All better now

= 1.0b =
* Bite the bullet and say 1.0
* Code stabilization and optimization
* Performance improvements

= 0.9.10 =
* More backwards compatibility

= 0.9.9.9d =
* Squash a bug which caused an error in older versions of PHP

= 0.9.9.9c =
* Integrate Clef install
* Add debug information for hosts, improve copy for sites with broken install

= 0.9.9.9b =
* Remove left over debug code

= 0.9.9.9 =
* Fix error with server identification and errors in older versions of PHP
* Version Codename: I really don't want to say 1.0

= 0.9.9.8 =
* Fix error with cached blocks

= 0.9.9.7 =
* page-now fallback fix

= 0.9.9.6 =
* Fix bug on local environments

= 0.9.9.5 =
* Major code rewrite!  Every line of code was reviewed, optimized, and made prettier.  It can be prettier, though, and we're going to keep working on that
* Blocked users from obtaining a key on a local environment
* Laid groundwork for Clef Integration

= 0.9.9 =
* Add in the ability to whitelist IPs or IP blocks
* Improve wp-login.php performance via $pagenow -- thanks Mark Barnes!

= 0.9.8.6.2 =
* Don't ever block localhost

= 0.9.8.6.1 =
* Fixed typo

= 0.9.8.6 =
* Expired transients now get cleaned up-- thanks KirkM, Tevya, David Anderson, and Seebz!

= 0.9.8.4 =
* Fixed a few PHP parsing notices, thanks Till and clwill!

= 0.9.8.3 =
* Added hooks: brute_log_failed_attempt and brute_kill_login -- both are passed the offending IP address

= 0.9.8.2 =
* Remove unused code from upcoming functionality.

= 0.9.8.1 =
* Admin can now prevent other users from seeing BruteProtect statistics
* Fixed a typo in the admin panel

= 0.9.8 =
* Added a fallback for failed multisite blog count reporting
* Added the ability to hide BruteProtect stats from network blog dashboards

= 0.9.7.2 =
* Fixed a minor display issue in 0.9.7.1

= 0.9.7.1 =
* Fixed a minor display issue in 0.9.7

= 0.9.7 =
* BruteProtect now supports multisite networks!  One key will protect every site in your network, and will always be free for small networks!
* Fixed API URI logic so that we fall back to non-https if your server doesn't support SSL
* Fixed admin config page image (thanks, flick!)
* Added index.php to prevent directory contents from being displayed (thanks, flick!)

= 0.9.6 =
* Admin-side updates for better compatibility and readability -- Thanks again, Michael Cain!

= 0.9.5 =
* Changed API server to HTTPS for increased security
* Improved domain check method even further
* Added a "Settings" link to the Plugins page
* Made things prettier

= 0.9.4 =
* Changed domain check method to reduce API key errors

= 0.9.3 =
* Added hooks in for upcoming remote security and uptime scans

= 0.9.2 =
* Fixed error if Login Lockdown was installed
* Improve admin styling (thanks Michael Cain!)
* Added statistics to your dashboard
* If the API server goes down, we fall back to a math-based human verification
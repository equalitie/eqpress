<?php
/**
 * class-ep-provision-view.php
 *
 * Methods to satisfy the viewing of the provision process.
 *
 */
class EP_Provision_View {
	/**
 	* Display the HTML header.
 	*
 	*/
	public function display_header() {
		header( 'Content-Type: text/html; charset=utf-8' );	?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="utf-8" />
			<title>WordPress Provisioning</title>
			<link rel='stylesheet' id='install-css'  href='install.css' type='text/css' media='all' />
		</head>
		<body>
			<h1 id="logo"><img alt="WordPress" src="https://s.w.org/about/images/logos/wordpress-logo-hoz-rgb.png" /></h1>
	<?php
	}
	
	/**
	 * Display the form.
	 *
	 */
	public function display_setup_form( $error = null ) {
		global $params;
	?>
	<form id="setup" method="post" action="?step=2">
		<table class="form-table">
		<!---	<tr>
				<th align="right" scope="row"><label>Username</label></th>
				<td><input name="username" type="text" id="username" size="64"></td>
			</tr>
			<tr>
				<th align="right" scope="row"><label>Password</label></th>
				<td><input name="password" type="password" id="password" size="64"></td>
			</tr> --->
			<tr>
				<th align="right" scope="row"><label>Key</label></th>
				<td><input name="api_key" type="password" id="api_key" size="64"></td>
			</tr>
			<tr>
				<th align="right" scope="row"><label>Node</label></th>
				<td><select name="location" id="location">
				<?php
				if (isset($params['nodes'])) {
					foreach($params['nodes'] as $key => $value) {
						print "<option value=\"".$key."\">".$key."</option>";
					}
				}
				?></select></td>
			<tr>
				<th align="right" scope="row"><label>Domain Name</label></th>
				<td><input name="domain" type="text" id="domain" size="64"></td>
			</tr>
			<tr>
				<th align="right" scope="row"><label>Admin's First Name</label></th>
				<td><input name="first_name" type="text" id="fname" size="64"></td>
			</tr>
			<tr>
				<th align="right" scope="row"><label>Admin's Last Name</label></th>
				<td><input name="last_name" type="text" id="lname" size="64"></td>
			</tr>
			<tr>
				<th align="right" scope="row"><label>Admin's Email</label></th>
				<td><input name="email" type="text" id="email" size="64"></td>
			</tr>
			<tr>
				<th align="right" scope="row"><label>Multisite</label></th>
				<td>
				No<input name="multisite" type="radio" id="multisite" value="no" checked>&nbsp;&nbsp;&nbsp;
				Subdomain<input name="multisite" type="radio" id="multisite" value="subdomain">&nbsp;&nbsp;&nbsp;
				Subdirectory<input name="multisite" type="radio" id="multisite" value="subdirectory">
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2">
				<!-- <input name="api_key" type="hidden" value="web_form"> -->
				<p class="step"><input type="submit" value="Install WordPress" class="button" /></p>
				</td>		
			</tr>
		</table>
	</form>
	<?php
	}
	
	
	/**
	 * Display the footer and any errors.
	 *
	 */
	public function display_footer() {
		global $all_errors;
		if ( isset( $all_errors[0] ) && $all_errors[0] != '' ) {
			echo '<h2>Provisioning Errors</h2>';
			echo '<pre>';
			var_dump( $all_errors );
			echo '</pre>';
		}
		echo '</body></html>';
	}

}

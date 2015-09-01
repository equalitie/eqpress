# WordPress Auto-Provision System

## Initialzing the Auto-Provision Sever

Run the following playbooks

	ansible-playbook -i hosts play-init-env.yml -v
	ansible-playbook -i hosts play-init-auto-provision.yml -v

The following settings can be specified:

* **Auto-provision base directory** - The base directory where all auto-provision related directories and files exist.
* **Auto-provision log file name** - The file where to log provision requests and errors.
* **Auto-provision database host** - Default database host for a newly installed WordPress instance. Typically "localhost".
* **Auto-provision default database character set** - Default database character set for a newly installed WordPress instance.
* **Auto-provision default database collation** - Default database collation for a newly installed WordPress instance.
* **Auto-provison random number device** - Random number generation is required for passwords, keys and salts and this setting points to a local or remote device.
* **Auto-provision Console API secret key** - The secret key used for generating the API key for each site used by the console proxy for authenticating console requests.
* **Email or keyID of GPG account for encrypting logs** - A GPG key is required for encrypting the incoming provision request and logs.
* **Version of Ansible to checkout** - The version number of Ansible to checkout after cloning the repository.

Optional settings when using a API for automatic DNS updates. These should be specified when sites will be installed in development mode using a unique domain name. e.g. example.com.wp.equalit.ie

* **Auto-provision DNS API hostname** - Just the hostname of the DNS API serrver.
* **Auto-provision DNS API URL** - The full URL to add a new record.
* **Auto-provision DNS API username** - DNS API username.
* **Auto-provision DNS API password** - DNS API password.


## Edit the Inventory File
Add the server acting as the auto-provision server to the ansible hosts file under a group named "auto-provision"

	[auto-provision]
	provision.equalit.ie

## Building the Auto-Provision Server

	ansible-playbook -i hosts play-auto-provision.yml -u root -l auto-provision

## PHP Config
Variables are set using the PHP environment via the superglobal $_SERVER. Environment variables are set in the file:

	template: files/etc/php5/fpm/pool.d/auto-provision.conf.j2
	production: /etc/php5/fpm/pool.d/auto-provision.conf


## Nginx Config
A specific nginx configuration is required to use the auto-provision backend.

## Testing Auto-Provision Code Updates
Run the following command to deploy changes to the auto-provision code to the testing environment:

	ansible-playbook -i hosts play-deploy-provision-testing.yml -u root -v


## Deploy Auto-Provision Code Updates to Production
Run the following command to deploy changes to the auto-provision code to production:

	ansible-playbook -i hosts play-deploy-provision.yml -u root -v




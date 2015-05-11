### Setting Up the Ansible Environment

	source ~/Development/ansible/hacking/env-setup

### Full Replicated Deployment

#### Minimum Requirements
##### Managed Nodes
* The servers that will be used for creating the replicated pair must be running Debian 7.
* The debian packages python and python-simplejson must be installed for ansible to work.

##### Management Node
The easypress-ssl role requires a root key and cert to be located in the following directory

	files/etc/ssl/easypress

They should be named root\_CA.key and root\_CA.pem. The following script and accompanying configuration file can be used to generate the self signed root certificate:

	files/etc/ssl/easypress/genRootCA.sh
	files/etc/ssl/easypress/genRootCA.conf

#### Host and Group Configuration
Create an alias in the ansible hosts file with the names of the new server pairs below:

	[eqpress-test]
	eqpress-test1.boreal321.com  
	eqpress-test1.boreal321.com

Create a group YAML file in the group_vars directory (copy an existing one). Name the group file the same as the alias entered in the ansible hosts file:

	group_vars/eqpress-test.yml

Edit this new group file and minmally change the following variables:

	easypress_server_id
	mysql_root_db_pass
	mysql_repl_creds: password
	mysql_admin_user: password
	mysql_webstats: password
	
Create a host YAML file in the host_vars directory (copy an existing primary and replica). Name the host files the same as what was entered in the ansible hosts file:

	host_vars/eqpress-test1.boreal321.com.yml
	host_vars/eqpress-test2.boreal321.com.yml

Edit these new host files and minmally change the following variables:

	mysql_server_id
	mysql_repl_slave
	mysql_repl_master

#### Ansible Plays

*   ansible-playbook -i hosts play-fullstack.yml -u root -l eqpress-test

When the servers are ready for production then it's time to deploy the cron jobs

*   ansible-playbook -i hosts play-go-live.yml -u root -l eqpress-test


#### Common Failures and Remedies

* SSL certificate fails to be created with the error:  
`openssl Serial number xx has already been issued check the database/serial_file for corruption`  
    1. Edit the files/etc/ssl/easypress/root_CA.srl file, delete the serial number and save the file
    1. Re-enter the same serial number and save the file
    1. Re-run the playbook

* MySQL fails to start
    1. Check if mysql is running on the host
    1. Re-run the play using the mysql or slaveon tag  
`ansible-playbook -i hosts play-fullstack.yml -u root -l eqpress-test --tags slaveon`  
`ansible-playbook -i hosts play-fullstack.yml -u root -l eqpress-test --tags mysql`  

### Role based updates

Update nginx and php-fpm config

*   ansible-playbook -i hosts play-fullstack.yml -u root -l eph --tags="nginx,php"

### Console

Deploy changes to the easyPress Console must-use plugin and proxy code

*   ansible-playbook -i hosts play-fullstack.yml -u root -v -l production --tags console

Deploy easyPress console must-use plugin to all sites

*   ansible -i hosts masters -m command -a "/usr/local/sbin/ep_install_console.sh all" -u root

#### Testing New Console Code

ansible-playbook -i hosts play-fullstack.yml -u root -l jester.easypress.ca --tags console && ansible -i hosts jester.easypress.ca -m command -a "/usr/local/sbin/ep_install_console.sh wtj.boreal321.com" -u root


### Add or Update System Users

*   ansible-playbook -i hosts play-fullstack.yml -u root -l production --tags users
*  ansible-playbook -i hosts play-add-user.yml -u root -l eqpress-test1.boreal321.com

### Update nginx configs

All configs and reload nginx

*   ansible-playbook -i hosts play-fullstack.yml -u root -l masters --tags nginx_config

### WordPress and plugin updates

Update a specific plugin on all master servers

*   ansible -i hosts masters -m command -a "/usr/local/sbin/wp_update_plugins.sh wordpress-seo" -u root -v


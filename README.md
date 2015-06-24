### Installing
A host with a working Ansible installation is required. Clone this repo:

	git clone https://github.com/equalitie/eqpress.git

#### Environment Initialization
Initializing the ansible environment is required before any other playbooks can be executed. Run the following commands in the directory where the repo was cloned:

	cd eqpress
	ansible-playbook -i hosts play-init-env.yml -v

The following settings can be specified. Accepting the defaults are enough to configure the environment so the other playbooks will work but making them unique to your environment is best.

SSL/TLS certificate attributes are required to generate the self-signed certificates used for MySQL replication.

* **Enter organization name**
* **Root certificate country**
* **Root certificate state/province**
* **Root certificate city**
* **Root certificate orginazational unit**
* **Root certificate common name**
* **Root certificate email address**

Mandrill and Sendgrid are email delivery services offering free accounts for moderate levels of email traffic. These are not required but recommended for reliable email delivery.

* **Mandrill username** - Sign up for an account [here](https://mandrill.com/signup/). Free plan allows 12,000 sent emails per month.
* **Mandrill password**
* **Sendgrid username** - Sign up for a free account [here](https://sendgrid.com/user/signup). Free plan allows 400 sent emails per day.
* **Sendgrid password**
* **Default email service**
* **Monitoring email address** - Where all alerts are sent to.
* **Timezone**

#### Server Configuration Initialization
To build a redundant pair of servers there are some ansible variables that need to be set for the playbooks to work. Run the initialization playbook to create the group and host variables:

	ansible-playbook -i hosts play-init-servers.yml -v

The following settings must be specified:

* **Nginx worker processes** - Should equal c - 2 where c is number of CPU cores. If c is < 4 then worker procs should equal 2.
* **PHP-FPM max children** - default is typically fine
* **PHP-FPM start servers** - default is typically fine
* **PHP-FPM min spare** - default is typically fine
* **PHP-FPM max spare** - default is typically fine
* **PHP-FPM max requests** - default prevents processes from eating too much RAM. Increase to 64 if server is very busy.
* **PHP-FPM opcache memory size** - increase default if more than 20 sites are hosted on the same server
* **MySQL root user password** - [click here for long random strings](https://www.random.org/passwords/?num=5&len=23&format=html&rnd=new) 
* **MySQL InnoDB buffer pool size** - default good for servers with RAM <= 1GB. Set to 1536M for servers with 4GB RAM. Don't forget the K, M or G after the number
* **MySQL InnoDB log file size** - default is fine for servers < 4GB RAM
* **MySQL replication user password - [click here for long random strings](https://www.random.org/passwords/?num=5&len=23&format=html&rnd=new)
* **MySQL Admin user password** - mysqladmin user has process rights for monitoring replication status. [click here for long random strings](https://www.random.org/passwords/?num=5&len=23&format=html&rnd=new)
* **MySQL webstats user password** - webstats user writes to webstats DB to store HTTP access log data. [click here for long random strings](https://www.random.org/passwords/?num=5&len=23&format=html&rnd=new)
* **MySQL Server ID for master** - must be unique, don't accept the default
* **MySQL Server ID for slave** - must be unique, don't accept the default
* **Master server hostname** - using a fully qualified domain name is best.
* **Slave server hostname** - using a fully qualified domain name is best.
* **Ansible group name** - the group that these hosts will be uniquely identified by within the hosts file and variables stored in a file in the group_vars directory



### Building a Replicated Pair of Servers

#### Minimum Requirements
##### Managed Nodes
* The servers that will be used for creating the replicated pair must be running Debian 7 (Wheezy).
* The debian packages python and python-simplejson must be installed for ansible to work.


#### Manual Host and Group Configuration
You can build the host and group files manually instead of running the play-init-servers.yml playbook. Create an alias in the ansible hosts file with the names of the new server pairs below:

	[eqpress-test]
	eqpress-test1.boreal321.com  
	eqpress-test2.boreal321.com

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

###[Auto-Provision Documentation](https://github.com/equalitie/eqpress/tree/master/roles/init-auto-provision#wordpress-auto-provision-system)

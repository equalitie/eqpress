eqpress
=========

Ansible to build press servers.
wordpress server infrastructure

Requirements
------------

3 - Servers (2 press, 1 provision)
    Tested on Ubuntu 24.04 LTS

1 - System (controller 'local') used to install to servers. 
    This will also be place to push out updates.
    Tested on Ubuntu 24.04 LTS and Debian 12

Dependencies
------------

Ansible v2.15+

Ubuntu 24.04 LTS +

Note: Tested and developed for Ubuntu 24.04 LTS

Install
-------

First: Setup 4 Systems 
       2 PRESS servers (recommend at least 2CPU, 16G RAM, 20G DISK)
       1 PROVISION server (recommend at least 2CPU, 4G RAM, 10G DISK)
       1 CONTROLLER system (recommend at least 1CPU, 2G RAM 10G DISK)

Note: Best if all are same Linux Distribution

Second: Pick a admin user, non-root, and create that user on all the systems.
Third: Setup /etc/sudoers "user ALL=(ALL:ALL) NOPASSWD:ALL" for this user on all systems.
Forth: setup key login from CONTROLLER to all systems
Fifth: Install ansible on CONTROLLER. 

At this point you should be able to get started

  sudo apt install git-core

  git clone this repo on your CONTROLLER
  in user account you created above.

  sudo apt install ansible python3-passlib

  # If using docker [default], make sure you have community.docker version 3.8.1 or above.
  # Check by running "ansible-galaxy collection list | grep community.docker"
  # You can install by "ansible-galaxy collection install community.docker"

  Change to your repository directory

  run:
    ansible-playbook playbooks/init-local.yml -i localhost.init
    ansible-playbook playbooks/init-press.yml
    vi config/inventory/hosts and add your [group] you named in the init-press

      example: If you chose testservers1 as your group then
               you would write the servers in like this.
               Replace <user> and <ip addr> with correct values.

       [testservers1]
       server1.name.com ansible_ssh_user=<user> ansible_ssh_host=<ip addr>
       server2.name.com ansible_ssh_user=<user> ansible_ssh_host=<ip addr>

    # If the host names are not added to DNS, add to your local /etc/hosts file or some
    # tasks will fail

    ansible-playbook playbooks/press.yml -l testservers1

You should now have 2 press servers running

Next: The provision server
   run:
     ansible-playbook playbooks/init-provision.yml
     Add the provision server to group [provision] in hosts like;

       [provision]
       myprovision.name.com

     ansible-playbook playbooks/provision.yml

   On provision server, chdir 'cd /home/wordpress' and install wordpress source 'latest.tar.gz'.
   ie; wget https://wordpress.org/latest.tar.gz


License
-------


Author Information
------------------

Thank you to Victor and his work on easypress that this was created largely after
with his permission.

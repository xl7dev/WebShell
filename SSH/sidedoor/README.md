# sidedoor

sidedoor maintains a reverse tunnel to provide a backdoor.
sidedoor can be used to remotely control a device behind a NAT.

sidedoor is packaged for Debian-based systems with systemd or upstart.
It has been used on Debian 8 (jessie) and Ubuntu 14.04 LTS (trusty).

The sidedoor user has full root access configured in /etc/sudoers.d.

## Installation

If sidedoor is in your package repositories, simply install it, e.g.,
`sudo apt-get install sidedoor`.

Otherwise, you will need to build a Debian package and install it.
First, install build dependencies.

    sudo apt-get install debhelper dh-systemd

Then, from the directory containing this README file, build and install
a package.

    rm -f ../sidedoor*.deb # remove old package builds
    dpkg-buildpackage -us -uc -b
    sudo dpkg -i ../sidedoor*.deb

## Configuration

The remote server and tunnel port are configured in `/etc/default/sidedoor`.
SSH configuration files are located in the `/etc/sidedoor` directory.
`~sidedoor/.ssh` is a symlink to `/etc/sidedoor`.

 * Configure `REMOTE_SERVER` and `TUNNEL_PORT` in `/etc/default/sidedoor`.
 * Create SSH configuration files under `/etc/sidedoor`.
   - `authorized_keys`:  SSH public key(s) to control access to the local
     sidedoor user.
   - `id_rsa`: SSH private key to access the remote server.
     Can be generated with `sudo ssh-keygen -t rsa -f /etc/sidedoor/id_rsa`
     (press enter when prompted for passphrase to leave empty).
     Needs read permission by the sidedoor user or group, e.g.,
     `sudo chown root:sidedoor /etc/sidedoor/id_rsa` and
     `sudo chmod 640 /etc/sidedoor/id_rsa`.
     The corresponding public key `id_rsa.pub` will need to be included in
     the remote user's `~/.ssh/authorized_keys` file.
   - `known_hosts`: SSH host key of the remote server.
   - `config` (optional): Additional SSH config, see `man ssh_config`.

Restart the sidedoor service to apply changes.

    sudo service sidedoor restart

## Recommendations

 * Lock down the local SSH server by editing `/etc/ssh/sshd_config`.
   - Disable password authentication
     (`ChallengeResponseAuthentication no` and `PasswordAuthentication no`).
   - Limit daemon to only listen on localhost.
     (`ListenAddress ::1` and `ListenAddress 127.0.0.1`).
   - To apply changes, restart or reload sshd, e.g.,
     `sudo service ssh reload`.
 * Modify the `ssh_client_config_example` file and include it in a client's
   `~/.ssh/config` file to easily access the tunneled backdoor
   with `ssh`, `scp`, `rsync`, etc.

## License

Copyright 2015 Dara Adib.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

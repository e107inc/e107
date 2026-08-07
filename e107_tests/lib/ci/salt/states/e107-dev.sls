Install Python module build dependencies:
  pkg.installed:
    - pkgs:
      - build-essential
      - libmariadb-dev
      - pkg-config

Python modules for SaltStack:
  pip.installed:
    - pkgs:
      - mysqlclient
      - passlib
      - saltext.mysql
    - bin_env: /usr/bin/salt-pip
    - require:
      - pkg: "Install Python module build dependencies"

Install LAMP stack:
  pkg.installed:
    - pkgs:
      - mariadb-server
      - php
      - composer
      - libapache2-mod-php
      - php-mysql
      - php-xml
      - php-curl
      - php-gd
      - php-mbstring
      - curl

Install SSH:
  pkg.installed:
    - pkgs:
      - openssh-server
      - sshpass

Start and enable OpenSSH server:
  service.running:
    - name: ssh
    - enable: True
    - require:
      - pkg: Install SSH

Allow user logins:
  service.running:
    - name: systemd-user-sessions

Allow user logins on every boot:
  file.symlink:
    - name: /etc/systemd/system/multi-user.target.wants/systemd-user-sessions.service
    - target: /usr/lib/systemd/system/systemd-user-sessions.service
    - makedirs: True

Install Composer:
  cmd.run:
    - name: 'curl -fsSL https://getcomposer.org/installer | php -- --install-dir="/usr/local/bin" --filename="composer"'
    - unless: test -f /usr/local/bin/composer

MariaDB server configuration file:
  file.managed:
    - name: /etc/mysql/mariadb.conf.d/99-overrides.cnf
    - source: salt://files/etc/mysql/mariadb.conf.d/99-overrides.cnf
    - user: root
    - group: root
    - mode: 0644
    - template: jinja

Start and enable MariaDB server daemon:
  service.running:
    - name: mariadb
    - enable: True
    - watch:
      - file: /etc/mysql/mariadb.conf.d/99-overrides.cnf

Create MariaDB remote user:
  mysql_user.present:
    - name: {{ salt['pillar.get']('db:user') | yaml_encode }}
    - host: '%'
    - password: {{ salt['pillar.get']('db:password') | yaml_encode }}
    - allow_passwordless: True
    - unix_socket: False

Create MariaDB local user:
  mysql_user.present:
    - name: {{ salt['pillar.get']('db:user') | yaml_encode }}
    - host: 'localhost'
    - password: {{ salt['pillar.get']('db:password') | yaml_encode }}
    - allow_passwordless: True
    - unix_socket: False

Create MariaDB database:
  mysql_database.present:
    - name: {{ salt['pillar.get']('db:dbname') | yaml_encode }}

Create MariaDB grants for the remote user:
  mysql_grants.present:
    - grant: ALL PRIVILEGES
    - database: {{ (salt['pillar.get']('db:dbname') ~ '.*') | yaml_encode }}
    - user: {{ salt['pillar.get']('db:user') | yaml_encode }}
    - host: '%'
    - require:
      - mysql_user: Create MariaDB remote user

Create MariaDB grants for the local user:
  mysql_grants.present:
    - grant: ALL PRIVILEGES
    - database: {{ (salt['pillar.get']('db:dbname') ~ '.*') | yaml_encode }}
    - user: {{ salt['pillar.get']('db:user') | yaml_encode }}
    - host: 'localhost'
    - require:
      - mysql_user: Create MariaDB local user

# Ubuntu ships `AllowOverride None` for /var/www/, which makes every .htaccess
# in the tree an inert text file. e107 is not installable on such a host: it
# ships e107.htaccess for its own rewriting, and e_file::protectDirectory()
# keeps private uploads private with a deny rule Apache has to be allowed to
# read. Granting the override classes e107 actually asks for (FileInfo,
# Options, Indexes, Limit) is what makes this box resemble a real e107 host,
# and without it the attachment and media suites test nothing.
Allow e107 its .htaccess directives:
  file.managed:
    - name: /etc/apache2/conf-available/e107-override.conf
    - makedirs: True
    - contents: |
        <Directory {{ salt['pillar.get']('fs:path') }}>
            AllowOverride All
            Require all granted
        </Directory>
    - require:
      - pkg: Install LAMP stack

Enable the e107 override configuration:
  cmd.run:
    - name: a2enconf e107-override
    - unless: test -L /etc/apache2/conf-enabled/e107-override.conf
    - require:
      - file: Allow e107 its .htaccess directives

Start and enable the web server:
  service.running:
    - name: apache2
    - enable: True
    - watch:
      - pkg: Install LAMP stack
      - file: Allow e107 its .htaccess directives
      - cmd: Enable the e107 override configuration

Configure Apache user:
  user.present:
    - name: {{ salt['pillar.get']('fs:user') | yaml_encode }}
    - password: {{ salt['pillar.get']('fs:password') | yaml_encode }}
    - hash_password: True
    - shell: /bin/bash

Ensure docroot has the correct permissions:
  file.directory:
    - name: {{ salt['pillar.get']('fs:path') | yaml_encode }}
    - user: {{ salt['pillar.get']('fs:user') | yaml_encode }}
    - group: {{ salt['pillar.get']('fs:user') | yaml_encode }}
    - recurse:
      - user
      - group
    - makedirs: True

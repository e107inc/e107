Install Python module build dependencies:
  pkg.installed:
    - pkgs:
      - build-essential
      - libmariadb-dev
      - pkg-config

MySQLdb Python module for SaltStack:
  pip.installed:
    - name: mysqlclient
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

Allow user logins (alternate):
  file.absent:
    - name: /run/nologin
    - onfail:
      - service: Allow user logins

Install Composer:
  cmd.run:
    - name: 'wget https://getcomposer.org/installer -O - | php -- --install-dir="/usr/local/bin" --filename="composer"'
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

Start and enable the web server:
  service.running:
    - name: apache2
    - enable: True
    - watch:
      - pkg: Install LAMP stack

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

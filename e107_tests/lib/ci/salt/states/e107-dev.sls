Install LAMP stack:
  pkg.installed:
    - pkgs:
      - mariadb-server
      - python3-mysqldb
      - php
      - libapache2-mod-php
      - php-mysql
      - php-xml
      - php-curl
      - php-gd
      - php-mbstring

MySQL server configuration file:
  file.managed:
    - name: /etc/mysql/mariadb.conf.d/99-overrides.cnf
    - source: salt://files/etc/mysql/mariadb.conf.d/99-overrides.cnf
    - user: root
    - group: root
    - mode: 0644
    - template: jinja

Start and enable MySQL server daemon:
  service.running:
    - name: mysql
    - enable: True
    - watch:
      - file: /etc/mysql/mariadb.conf.d/99-overrides.cnf

Create MySQL user:
  mysql_user.present:
    - name: {{ salt['pillar.get']('db:user') }}
    - host: '%'
    - password: {{ salt['pillar.get']('db:password') }}
    - allow_passwordless: True
    - unix_socket: False

Create MySQL database:
  mysql_database.present:
    - name: {{ salt['pillar.get']('db:dbname') }}

Create MySQL grants:
  mysql_grants.present:
    - grant: ALL PRIVILEGES
    - database: {{ salt['pillar.get']('db:dbname') }}.*
    - user: {{ salt['pillar.get']('db:user') }}
    - host: '%'

Start and enable the web server:
  service.running:
    - name: apache2
    - enable: True
    - watch:
      - pkg: Install LAMP stack

Configure Apache user:
  user.present:
    - name: {{ salt['pillar.get']('fs:user') }}
    - password: {{ salt['pillar.get']('fs:password') }}
    - hash_password: True
    - shell: /bin/bash

Ensure docroot has the correct permissions:
  file.directory:
    - name: {{ salt['pillar.get']('fs:path') }}
    - user: {{ salt['pillar.get']('fs:user') }}
    - group: {{ salt['pillar.get']('fs:user') }}
    - recurse:
      - user
      - group
    - makedirs: True

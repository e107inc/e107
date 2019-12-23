Install MySQL server:
  pkg.installed:
    - pkgs:
      - mariadb-server
      - python3-mysqldb

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

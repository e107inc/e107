e107 Test Suites
===

## Quickstart (Docker — recommended)

The fastest path is the Docker harness in [`docker/`](docker/). It builds a per-worktree PHP + Apache + MariaDB stack so several developers (or AI agents) can run tests in parallel without sharing a dev VM.

```sh
git clone https://github.com/e107inc/e107.git
cd e107
e107_tests/bin/e107-tests up                # PHP 8.3 + MariaDB 10.11 by default
e107_tests/bin/e107-tests run unit          # run the unit suite
e107_tests/bin/e107-tests run acceptance    # run the acceptance suite
e107_tests/bin/e107-tests urls              # see where the app is reachable
e107_tests/bin/e107-tests down              # tear it all down
```

Pick a different matrix combo with `--php` and `--db`, e.g. `e107-tests up --php 8.4 --db mysql:8.0`. See [`docker/README.md`](docker/README.md) for the full command reference.

## Quickstart (Manual / legacy deployers)

If you prefer to wire tests into your own LAMP stack — local, SFTP, or anything else — keep reading. This is the original flow described below.

1. Check out this repository:
   ```sh
   # SSH option
   git clone git@github.com:e107inc/e107.git
   
   # HTTPS option
   git clone https://github.com/e107inc/e107.git
   ```
2. Change your current working directory into your copy of the tests:
   ```sh
   cd e107/e107_tests
   ```
3. Configure the testing environment by copying [`config.sample.yml`](config.sample.yml) into `config.yml` at the root level of this repository and then editing `config.yml`.  The `db` section needs to be configured for all tests while acceptance tests can be configured with one of the following deployment options:

   * [**Local**](#local)
   
     *Use if:* You develop the app locally and have a LAMP/WAMP stack that is configured to serve the app at a local path
   
   * [**SFTP**](#sftp)
   
     *Use if:* You regularly upload the app over SFTP (perhaps in some setup with Vagrant) and have a remote LAMP stack that serves the app
     
   * [**Manual**](#manual)
   
     *Use if:* You are not able to set up any of the other options

4. On PHP 5.6 or newer, install dependencies with [Composer](https://getcomposer.org/):
   ```sh
   php -d allow_url_fopen=On $(which composer) update
   ```
5. Run tests:

   * **All tests:**
     ```sh
     ./vendor/bin/codecept run
     ```
   * **All tests and details:**
     ```sh
     php -d allow_url_fopen=On -d display_errors=On ./vendor/bin/codecept run --step --debug
     ```
   * **All tests with code coverage report:**
     ```sh
     php -d zend_extension=xdebug.so -d xdebug.mode=coverage -d allow_url_fopen=On ./vendor/bin/codecept run --coverage --coverage-xml --coverage-html
     ```
     > **Note:** This needs Xdebug.  See the "Code Coverage" section below for details.
   * **Unit tests:**
     ```sh
     ./vendor/bin/codecept run unit
     ```
   * **Acceptance tests:**
     ```sh
     ./vendor/bin/codecept run acceptance
     ```

## Deployment

With varying levels of configuration, this test harness can run reproducible tests on different environments.

The default configuration is in [`config.sample.yml`](config.sample.yml).  Each configuration item can be overridden in a non-version-controlled `config.yml` that you create or copy from [`config.sample.yml`](config.sample.yml).  (It is optionally possible to override both configuration files with a `config.local.yml`.)

In the config file, there are some base settings:

* `app_path` – The path, relative or absolute, to the app intended to be tested.  The deployers use the app at this path to set up the tests.
* `deployer` – Which deployer is to be used to set up tests.  See the sections below for configuration instructions for the respective deployers.

Each deployer needs one or more of the following sections to be configured:

* `url` – The URL that acceptance tests will access
* `db` – Database credentials and populator settings
* `fs` – File transfer credentials if the app is hosted at a remote location

Details on how to configure these sections can be found in [`config.sample.yml`](config.sample.yml) or further down this README.

Here is a table of which sections need to be configured for which deployers:

| Deployer (`deployer`) | URL (`url`) required? | Database (`db`) required? | Files (`fs`) required? |
| --- |:---:|:---:|:---:|
| Local (`local`)   | yes | yes | no  |
| SFTP (`sftp`)     | yes | yes | yes |
| Manual (`none`)   | no  | yes | no  |

### Local

#### Requirements

* **Local testing environment** – The app's files must be served from the same system as this test harness.
* **Local web server** – A web server with PHP must serve the app from the same local path as the app's files.
* **MySQL/MariaDB database** – The web server and test harness must be able to access a MySQL or MariaDB database, not necessarily a local one.

#### Configuration

To set up locally deployed tests, copy the file called [`config.sample.yml`](config.sample.yml) in the root folder of this repository to a new file called `config.yml` (or create a new file called `config.yml`), open `config.yml`, and input the following configuration information:

```yaml
# Path (absolute or relative) to the app intended to be tested
# Absolute path begins with "/"; relative path does not begin with "/"
app_path: '../'

# Which deployer to use to set up tests
deployer: 'local'

# URL (with trailing slash) at which the app can be reached for acceptance tests
url: 'http://set-this-to-your-acceptance-test-url.local/'

# Only MySQL/MariaDB is supported
db:

  # Hostname or IP address; use 'localhost' for a local server
  host: 'set-this-to-your-test-database-hostname.local'

  # Port number of the server
  port: '3306'

  # Database name; must exist already
  dbname: 'e107'

  # Username; must exist already, with ALL PRIVILEGES on the database and nothing global
  user: 'e107'

  # Password; set to blank string for no password
  password: ''
```

### SFTP

#### Requirements

* **Remote SSH server** – This is where the app's files would be sent.  The SSH account needs shell access and the `rsync` command.
* **`rsync`** – Both the client and server need the `rsync` command.
* **`sshpass`** – The client needs the `sshpass` command only if password authentication is being used (the `fs.password` field in `config.yml`).
* **Private key file to authenticate** – Only needed if the SSH account is authenticated by private key (`fs.privkey_path` in `config.yml` is set to the path of the private key).  `fs.privkey_path` can be left blank if the SSH client configuration already has an identity file set for the remote SSH account.
* **Web server** – A web server with PHP must serve the app from the uploaded destination.
* **MySQL/MariaDB database** – The web server and the test harness must be able to access a MySQL or MariaDB database.

#### Configuration

To set up SFTP-deployed tests, copy the file called [`config.sample.yml`](config.sample.yml) in the root folder of this repository to a new file called `config.yml` (or create a new file called `config.yml`), open `config.yml`, and input the following configuration information:

```yaml
# Path (absolute or relative) to the app intended to be tested
# Absolute path begins with "/"; relative path does not begin with "/"
app_path: '../'

# Which deployer to use to set up tests
deployer: 'sftp'

# URL (with trailing slash) at which the app can be reached for acceptance tests
url: 'http://set-this-to-your-acceptance-test-url.local/'

# Only MySQL/MariaDB is supported
db:

  # Hostname or IP address; use 'localhost' for a local server
  host: 'set-this-to-your-test-database-hostname.local'

  # Port number of the server
  port: '3306'

  # Database name; must exist already
  dbname: 'e107'

  # Username; must exist already, with ALL PRIVILEGES on the database and nothing global
  user: 'e107'

  # Password; set to blank string for no password
  password: ''

# Configure this section for deployers that need file upload configuration
fs:

  # Hostname or IP address to the remote destination
  host: ''

  # Port number of the file transfer server
  port: '22'

  # Username used for the file transfer
  user: ''

  # Path to the private key of the user. Takes precedence over "fs.password"
  privkey_path: ''

  # Password of the file transfer user. Ignored if "fs.privkey_path" is specified
  password: ''

  # Absolute path to where the remote web server serves "url"
  path: ''
```

### Manual

#### Requirements

* **MySQL/MariaDB database** – The test harness must be able to access a MySQL or MariaDB database because the database currently cannot be abstracted away in test code.
* **Web server** – To run acceptance tests, a web server with PHP is needed.

#### Limitations

* **Acceptance tests cannot be deployed automatically** – In this manual testing mode, the app deployed for acceptance tests must be reset manually before each test.  Running the entire suite at once is likely to cause failures that would not occur with an automated test deployer.

#### Configuration

To turn off automated acceptance test deployments, copy the file called [`config.sample.yml`](config.sample.yml) in the root folder of this repository to a new file called `config.yml` (or create a new file called `config.yml`), open `config.yml`, and input the following configuration information:

```yaml
# Path (absolute or relative) to the app intended to be tested
# Absolute path begins with "/"; relative path does not begin with "/"
app_path: '../'

# Which deployer to use to set up tests
deployer: 'none'

# URL (with trailing slash) at which the app can be reached for acceptance tests
url: 'http://set-this-to-your-acceptance-test-url.local/'

# Only MySQL/MariaDB is supported
db:

  # Hostname or IP address; use 'localhost' for a local server
  host: 'set-this-to-your-test-database-hostname.local'

  # Port number of the server
  port: '3306'

  # Database name; must exist already
  dbname: 'e107'

  # Username; must exist already, with ALL PRIVILEGES on the database and nothing global
  user: 'e107'

  # Password; set to blank string for no password
  password: ''
  
  # If set to true, the database populator will populate the database with the dump specified in the "dump_path" key
  # If set to false, the test database needs to be set up separately
  # Affects all tests and modes of deployment
  populate: true

  # Path (absolute or relative) to the database dump of a testable installation of the app
  # Absolute path begins with "/"; relative path does not begin with "/"
  dump_path: 'tests/_data/e107_v2.1.9.sample.sql'
```

## Code Coverage

You can generate code coverage reports for all PHP files in the app.  Code coverage is enabled for the unit suite but disabled for the browser suites (acceptance and webdriver) by default.

The reports may take minutes to be generated.

### Requirements

* **[Xdebug](https://xdebug.org/)** – You'll have to figure out the best way to [install Xdebug](https://xdebug.org/docs/install) in your environment.

### Sample Command

This command runs all tests and generates a code coverage report in HTML format and [Clover](https://bitbucket.org/atlassian/clover) XML format, using the Xdebug module that you installed with PECL:

```sh
php -d zend_extension=/usr/local/php/modules/xdebug.so -d xdebug.mode=coverage -d allow_url_fopen=On ./vendor/bin/codecept run --coverage --coverage-xml --coverage-html
```

### Output

The generated coverage reports are stored in `./tests/_output/` relative to the tests root folder.
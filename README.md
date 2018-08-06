e107 Test Suites
===

## Quickstart

1. Check out this repository:
   ```sh
   git clone git@github.com:e107inc/e107-test.git e107-test
   ```
2. Change your current working directory into your copy of the repository:
   ```sh
   cd e107-test
   ```
3. Configure the testing environment.

   * **Automatic deployments:** Copy `config.sample.yml` into a file called `config.yml` and edit `config.yml` enable deploying to a cPanel account.  See the "Automatic Test Deployments » Configuration" section below for details.
   * **Manual deployments:** See the "Manual Test Deployment » Configuration" section below for instructions.

4. On PHP 5.6 or newer, install dependencies with [Composer](https://getcomposer.org/):
   ```sh
   php -d allow_url_fopen=On $(which composer) update
   ```
5. Update all submodules, which also obtains the latest development code of e107:
   ```sh
   git submodule update --init --recursive --remote
   ```
6. Run tests:

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
     /opt/cpanel/ea-php72/root/usr/bin/php -d zend_extension=/opt/alt/php72/usr/lib64/php/modules/xdebug.so -d allow_url_fopen=On ./vendor/bin/codecept run --coverage --coverage-xml --coverage-html
     ```
     > **Note:** This command is specific to cPanel EasyApache PHP 7.2 and CloudLinux PHP Selector.  See the "Code Coverage" section below for details.
   * **Unit tests:**
     ```sh
     ./vendor/bin/codecept run unit
     ```
   * **Functional tests:**
     ```sh
     ./vendor/bin/codecept run functional
     ```
   * **Acceptance tests:**
     ```sh
     ./vendor/bin/codecept run acceptance
     ```

## Automatic Test Deployment

The test suites can deploy themselves onto a cPanel account automatically.

### Requirements

* **cPanel user account** – It is recommended to use a cPanel account dedicated to testing for isolation, but the test suite runs on most typical accounts and tries not to interfere with existing data.
* **Resolvable main domain** – The cPanel account's main domain must be resolvable to the machine running the test suite.  This usually means that the domain must resolve on the Internet.
* **MariaDB database quota** – Each run of the test suite creates one new MariaDB database and deletes it after executing the suite.
* **64MiB free disk space** – The test suite archives a copy of the app and uploads it to the cPanel account for cPanel to extract.  The app, its archive form, and test resources may grow in the future, so the more free disk space, the better.
* **4096 free inodes** – The app and test resources will take up at least a few thousand inodes and may need more in the future, so the more free inodes, the better.

### Limitations

* **PHP version cannot be set** – The test suite currently does not have the ability to set custom versions of PHP for the target app directory.  If the cPanel host supports multiple versions of PHP (e.g. EasyApache 4 MultiPHP, CloudLinux alt-php), they will have to be configured manually to test different PHP versions.
* **MariaDB username character limit** – cPanel MariaDB usernames are limited to 47 characters in length, and test runs are expected to use 18 plus the length of your cPanel username plus 1.
* **MariaDB database character limit** – cPanel MariaDB databases are limited to 64 characters in length, and test runs are expected to use 18 plus the length of your cPanel username plus 4.  (cPanel double-counts underscores (`_`) and the deployer uses 2 underscores, so the visible character count is 2 less than what cPanel counts.)
* **MariaDB remote access host `%` is preserved on crash** – The deployer adds a cPanel Remote MySQL® access host, `%`, but will forget to remove it if the test run is uncleanly aborted. Subsequent runs will not touch the `%` remote access host because the deployer would not be sure if it added `%`.
* **cPanel max POST size** – The cPanel PHP maximum POST request size can be as low as 55MiB on some hosts.  If the app's archive form exceeds this size, the upload will fail.  This limit can be adjusted in the hosting provider's server-wide WHM settings.

### Configuration

To set up automatically deployed tests, copy the file called `config.sample.yml` in the root folder of this repository to a new file called `config.yml` (or create a new file called `config.yml`), open `config.yml`, and input the following configuration information:

```yaml
# Configure this section for automated test deployments to cPanel
cpanel:

  # If set to true, this section takes precedence over the "manual" section.
  enabled: true

  # cPanel domain without the port number
  hostname: 'SHARED-HOSTNAME.YOUR-HOSTING-PROVIDER.EXAMPLE'

  # cPanel account username
  username: 'TEST-ACCOUNT-USER'

  # cPanel account password
  password: 'TEST-ACCOUNT-PASS'
```

## Manual Test Deployment

If you do not have a cPanel account that meets the requirements or if you would prefer not to use a cPanel account, you can deploy tests manually.

### Configuration

1. Set up a web server wherever you can access.  A local web server is fine.
2. Create a MySQL or MariaDB database.
3. Create a MySQL or MariaDB user.
4. Grant the MySQL/MariaDB user `ALL PRIVILEGES` on the MySQL/MariaDB database.
5. Put the app on the web server and note its URL.
6. Copy the file called `config.sample.yml` in the root folder of this repository to a new file called `config.yml` (or create a new file called `config.yml`), open `config.yml`, and input the following configuration information:
   ```yaml
   # Configure this section for manual test deployments
   manual:
   
     # URL to the app that you deployed manually; needed for acceptance tests
     url: 'http://set-this-if-running-acceptance-tests-manually.local'
   
     # Only MySQL/MariaDB is supported
     db:
       # Hostname or IP address; use 'localhost' for a local server
       host: 'set-this-if-running-tests-manually.local'
   
       # Port number of the server
       port: '3306'
   
       # Database name; must exist already
       dbname: 'e107'
   
       # Username; must exist already
       user: 'root'
   
       # Password; set to blank string for no password
       password: ''
   ```

## Code Coverage

You can generate code coverage reports for all PHP files in the app.  Code coverage is enabled for local tests (unit and functional tests) but disabled for remote tests (acceptance tests) by default.

The reports may take minutes to be generated.

### Requirements

* **[Xdebug](https://xdebug.org/)** – You'll have to figure out the best way to [install Xdebug](https://xdebug.org/docs/install) in your environment.

### Sample Commands

These commands run all tests and generate a code coverage report in HTML format and [Clover](https://bitbucket.org/atlassian/clover) XML format:

* Using [cPanel EasyApache 4](https://documentation.cpanel.net/display/EA4/PHP+Home) with PHP 7.2 and Xdebug from [CloudLinux PHP Selector](https://docs.cloudlinux.com/php_selector.html):
  ```sh
  /opt/cpanel/ea-php72/root/usr/bin/php -d zend_extension=/opt/alt/php72/usr/lib64/php/modules/xdebug.so -d allow_url_fopen=On ./vendor/bin/codecept run --coverage --coverage-xml --coverage-html
  ```
* Using the Xdebug module that you installed with PECL:
  ```sh
  php zend_extension=/usr/local/php/modules/xdebug.so -d allow_url_fopen=On ./vendor/bin/codecept run --coverage --coverage-xml --coverage-html
  ```

### Output

The generated coverage reports are stored in `./tests/_output/` relative to the root of your copy of this repository.

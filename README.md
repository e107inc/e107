e107 Test Suites
===
For developmental use with e107 v2.

- Place all these files inside a 'phpunit' directory in e107's root directory. ie. the same directory as class2.php
- Set the Custom Working Directory for PHPUNit Test to e107's root. eg. In PHPStorm under "Run/Debug Configurations"

## Automatic Acceptance Tests

The acceptance test suite can deploy itself onto a cPanel account automatically.

### Requirements

* **cPanel user account** – It is recommended to use a cPanel account dedicated to testing for isolation, but the test suite runs on most typical accounts and tries not to interfere with existing data.
* **Resolvable main domain** – The cPanel account's main domain must be resolvable to the machine running the test suite.  This usually means that the domain must resolve on the Internet.
* **MariaDB database quota** – Each run of the test suite creates one new MariaDB database and deletes it after executing the suite.
* **64MiB free disk space** – The test suite archives a copy of the app and uploads it to the cPanel account for cPanel to extract.  The app, its archive form, and test resources may grow in the future, so the more free disk space, the better.
* **4096 free inodes** – The app and test resources will take up at least a few thousand inodes and may need more in the future, so the more free inodes, the better.

### Limitations

* **PHP version cannot be set** – The test suite currently does not have the ability to set custom versions of PHP for the target app directory.  If the cPanel host supports multiple versions of PHP (e.g. EasyApache 4 MultiPHP, CloudLinux alt-php), they will have to be configured manually to test different PHP versions.
* **MariaDB username character limit** – cPanel MariaDB usernames are limited to 47 characters in length, and test runs are expected to use 18 plus the length of your cPanel username plus 1.
* **MariaDB database character limit** – cPanel MariaDB databases are limited to 64 characters in length, and test runs are expected to use 18 plus the length of your cPanel username plus 2.  (cPanel double-counts underscores (`_`), so the visible character count is 1 less than what cPanel counts.)
* **cPanel max POST size** – The cPanel PHP maximum POST request size can be as low as 55MiB on some hosts.  If the app's archive form exceeds this size, the upload will fail.  This limit can be adjusted in the hosting provider's server-wide WHM settings.

### Configuration

To run automatic acceptance tests, edit `secrets.yml` in the root folder of this repository and input the following configuration information:

```
cpanel:
  enabled: true
  hostname: 'SHARED-HOSTNAME.YOUR-HOSTING-PROVIDER.EXAMPLE'
  username: 'TEST-ACCOUNT-USER'
  password: 'TEST-ACCOUNT-PASS'
```

## Manual Acceptance Tests

If you do not have a cPanel account that meets the requirements, you can run acceptance tests manually.

### Configuration

1. Set up a web server wherever you can access.  A local web server is fine.
2. Create a MySQL or MariaDB database.
3. Create a MySQL or MariaDB user.
4. Grant the MySQL/MariaDB user `ALL PRIVILEGES` on the MySQL/MariaDB database.
5. Put the app on the web server and note its URL.
6. Write the URL to `tests/acceptance.suite.yml` in your copy of this repository where the `url` setting for the `PhpBrowser` module is.
7. **TODO:** Manual database credentials are not supported yet.

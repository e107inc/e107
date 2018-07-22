## e107 [Content Management System][1] (CMS) - v2

[![Join the chat at https://gitter.im/e107inc/e107](https://badges.gitter.im/e107inc/e107.svg)](https://gitter.im/e107inc/e107?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

### What is e107?
e107 is a free (open-source) content management system which allows you to easily manage and publish your content online. Developers can save time in building websites and powerful online applications. Users can avoid programming completely! Blogs, Websites, Intranets - e107 does it all. 

### Requirements

   #### Minimum

   * A Web-Server (Apache or Microsoft IIS) running PHP v5.4 or higher and MySQL 4.x or 5.x.
   * FTP access to your webserver and an FTP program such as Filezilla
   * Username/Password to your MySQL Database

   #### Recommended

   * A Linux based Webserver running Apache 2.x, PHP 7.x and MySQL 5.x (LAMP)
   * A registered Domain Name
   * Access to a Server Control Panel (such as cPanel)


### Standard Installation 

* Unzip/Extract the compressed file onto your server. 
* Point your browser to the *http://localhost/YOUR FOLDER/install.php* (depending on your webserver setup)
* Follow the installation wizard



### Git Installation (developer version)

* Run the following commands ( where 'youraccount' is the folder above your public_html folder and xxx:xxx is the default owner for your files - this can be found using FileZilla and FTP)
```
     cd youraccount   
     git clone https://github.com/e107inc/e107.git public_html	
     chown -R xxx:xxx public_html 
```    
* Point your browser to the *http://localhost/YOUR FOLDER/install.php* (depending on your webserver setup)
* Follow the installation wizard



### Reporting Bugs

Be sure you are using the most recent version prior to reporting an issue. 
You may report any bugs or feature requests on GitHub (https://github.com/e107inc/e107/issues)



### Pull-Requests

* Please submit 1 pull-request for each Github #issue you may work on. 
* Make sure that only the lines you have changed actually show up in a file-comparison (diff) ie. some text-editors alter every line so this should be avoided. 
* Make sure you are using rebase on your local .git/config file. 
ie. [branch "master"]
	rebase = true`
* Here's a small tutorial to give you a start on [CONTRIBUTING](CONTRIBUTING.md)

### Donations
If you like e107 and wish to help it to improve - please consider making a small donation.

* Bitcoin address: 18C7W2YvkzSjvPoW1y46PjkTdCr9UzC3F7
* Paypal: donate (at) e107.org



### Support
* http://e107help.org 



### License

* e107 is released under the terms and conditions of the GNU General Public License (http://www.gnu.org/licenses/gpl.txt)

  [1]: http://e107.org
  [2]: http://www.e107.org

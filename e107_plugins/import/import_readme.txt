E107 IMPORT FACILITY FOR 0.8
============================

This facility imports data from an existing non-E107 CMS or other sources into E107.


**************** CAUTION!!! EXISTING DATA WILL BE DELETED FROM YOUR E107 INSTALLATION *****************


**************** THIS IS BETA CODE AT PRESENT - it may (probably will) contain bugs ! *********************


If you use these routines, please help to make them better and report success or any problems to steved by PM.


If you encounter problems, it may help solve them if you are able to provide a sample database which demonstrates the problem. It doesn't have to be massive - often 5-10 records is plenty. A zipped export from PHPMyAdmin is often simplest.



Prerequisites
-------------
You require an existing E107 V0.8 installation; create one if you are not working with an existing installation.

This installation must include all plugins and features for which you want to import data - for example:
	- if you want to import forum threads, the E107 forum plugin must be installed. 
	- If you want to import certain predefined extended user fields, they must be set up

Other than configuration, you will usually want to import into a 'clean' installation - one which hasn't been used.

The import will in most cases delete and/or overwrite existing data, so BACK UP YOUR E107 INSTALLATION BEFORE PROCEEDING.


Running the Import
------------------
Log in to your E107 site as the main admin (this must be the very first user you created, which has an ID of 1).

Point your browser at:  yoursite/e107_files/import/import_to_e107.php

You should see the front screen of the import routines.

Select the appropriate options and click on 'continue'.

Note that the import can take a long time if you have a large amount of 'source' data; you may need to temporarily adjust settings in php.ini to ensure that PHP doesn't time out before completion. (It is often simplest to run the import on a local set of databases using xampp or similar, so that you have complete control over the setup).


CSV Data import
---------------
This is the simplest way to import a list of users; a suitable file can be created as an export from many databases and spreadsheets, and is quite simple to create in a basic text editor such as Notepad.

Each line of the input text file corresponds to a single user, with the various values separated by commas, thus:

fred,password
jim,jimspassword

Make sure there are no spaces at the beginning or end of values (although there may be spaces in the middle).

E107 requires passwords to be MD5-encoded; if yours are in 'plain text' just tick the box 'Password in CSV file is not already encrypted' and the import routine will encode them during processing.


Custom CSV Import
-----------------
If more than basic username/password information is available, you'll want to use a custom CSV import. These are specified in lines of the file csv_import.txt. Some examples are already in the file; add your special entries at the end.

First step is to determine what data values you have available for import, and then tell the import routine the format of your CSV file. Must likely values you'll have are:

user_loginname - the 'login name' for the user
user_name - the 'display name' for the user - may be the same as the login name
user_password - the user's password; either in 'clear text' or MD5 encoded
user_image - path to the user's avatar
user_session - path to the user's photo

(for other values, just identify the field name in E107's 'user' table. You can also specify the predefined fields of E107's extended user data table).


Within file csv_import.txt, each line is itself CSV-formatted data, defining an import format:
	Field 1 - an internal 'name' for the format - something fairly short
	Field 2 - a description for the format - this is displayed in selection dropdowns
	Field 3 - a format specifier code (see the list later)
	Field 4 on are the names of data fields, in the order they will appear in the data (CSV) file

There are some example formats already in this file; it may be edited to add more.

Format
Name		Delimiter	Enclosure
--------------------------------------------
simple		','		none
simple_sq	','		single quote (')
simple_dq	','		double quote (")
simple_semi	','		semi-colon (;)
simple_bar	','		'pipe' (|)



During the actual import some lines of data may be rejected; an error message is given, together with the line number of the data file.



Database Import
---------------
Converters have been written for most popular Content Management systems and BBS, and appear in the drop-down.

At present only user data is imported, to an extent which depends on the source data and our knowledge of the data format.

Extended user fields are imported if present in the E107 database.

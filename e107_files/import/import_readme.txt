PHPBB2 to E107 Conversion Routine
=================================

Issue dated: 13.06.2006

Any updates, bugfixes or comments please send pm to steved at e107.org


Conversion routines from other BBS
==================================
phpbb2.php - modified from earlier script, with ability to process and re-map bbcodes, plus more options.

Note: Only the underlying read/translate routine has been modified, plus some detail changes in the configuration table. The key routines which read and write the databases should be the same as those originally shipped.

Credits:  Based on the script originally shipped with E107 (jalist)
	  Modified to mostly work by aidee
	  Enhanced by steved
	  Reflects mods made by McFly in the "official" script between V1.4 and V1.5
	  Thanks to Rashan for a lot of testing and feedback.

********* This is still to some extent a work in progress - please report any problems to steved by PM ***********

Installation
============
1. Create a fresh install of E107. (Not essential, but it will overwrite rather a lot!)
2. Copy the files into the e107_files/convert subdirectory.


To Run
======
1) If there's anything in the e107 user table, you must delete ALL entries, EXCEPT user ID:1 - This is the admin login. (or whatever you named the #1 when you installed e107)
2) Make sure the forum plugin is installed (don't define any categories etc)
3) login to the e107 as admin (or whatever #1 is)
4) browse to e107_files/import/new_phpbb2.php
5) fill in the required info for login/password, etc. 
      db to import FROM is your phpbb2 database, with whatever prefix is set - often _phpbb_
6) once the script runs sucessfully, return to the e107 admin, head on over to the Administrators page and set the proper people up with their admin access, delete unnessesary admins
7) go to the Forum plugin page, select Tools from the right hand side and tell it to recalculate posts and such for all the forums, and sub-forums and posts.
8) double-check forum mods to make sure the proper userclasses are setup as mods for the proper forums
9) Head on over to the UserClasses page and doublecheck that the proper users are in the proper classes (Forum Mods, etc...)



Known Issues
============
1. Forum polls may not be transferred. At the current time there are various issues with this in the E107 core, and this may be the reason. So this has been put on hold until the core is definitely fixed.


BBCodes
=======
BBCode conversion may not always appear to work completely, especially if they were originally not been entered correctly. And there may be bbcodes which are not supported in E107.
In some cases it is possible to do a direct translation within the conversion routine (see later).
Otherwise download the add_bbcodes plugin from e107coders.org, and create bbcodes to match the originals (its often possible to use an existing bbcode as a template).


===========================================================================
		MODIFYING THE BEHAVIOUR
===========================================================================

File import routine (import_mapper.php)
===================

The import is based on a processing routine whose behaviour is defined in an array. The rest of these notes deal with that routine. Using the tables in the accompanying file as an example, it should be possible to modify the behavior of the conversion, and adapt to other CMS.


Using the conversion routine
============================
Read one line from your database; pass to:

function createQuery($convertArray, $dataArray, $table, $maptable default null)

where:
$convertArray is an array defining the translations and lookups to be done
$dataArray is an array of input data, keyed by source field name.
$table is the table name
$maptable if specified is used to translate bbcodes.

The function returns a line of text which is a mySQL 'INSERT' query.


$convertArray format
====================
This is a 2-dimensional array which relates fields in the source and destination databases.
Each row has a number of definition fields which relate to a single data item. The first three are 
mandatory. The keys must be as specified:

Field 1	"srcdata"	Source field name

Field 2	"e107"		Destination field name

Field 3	"type"		Type of destination field:
				INT	- Integer
				STRING	- Text/string of any sort
				STRTOTIME - time/date as text string (not yet supported)
				
Field 4	"value"		(Not always present - should only be if source field is null)
				Sets a value (sometimes from a function parameter). Overrides all other options.
					
Field 5 "default"	Sets default if the source field is undefined

Field 6	"sproc"		Various processing options (see below)


Processing Options
------------------
Comma separated string of processing options; options are invoked if present.

The following are applied to string fields:
usebb	Allows bbcode, and enables other bbcode processing options.
			If the $maptable parameter is non-null, translates bbcodes as well
phpbb	Causes numerics after colons within bbcodes to be stripped
bblower All bbcodes are converted to lower case. (Applied before other processing)
stripbb	Strips all bbcode in a string (i.e. anything between [])
					
The following are applied to integer fields:
zeronull If the integer value is zero, writes a null string.


BBCode Mapping Table
====================
A mapping table may be passed to the conversion routine, which translates bbcode between the two systems.
The table is an array of key/data pairs, for example:

$mapdata = array("b" => "bold","u" => "ul");

The above translates the 'b' bbcode to 'bold', and the 'u' bbcode to 'ul'.
It is also possible to delete a specific bbcode (while retaining the text between the start and end of the code) by setting the translated value to an empty string.
The above method can also be used to translate bbcodes from upper to lower case.
Parameters after the bbcode (identified as following an '=') are retained;


Base routines read one record at a time from the source database, and 
pass it to createQuery, which uses the above tables to generate an 'insert' 
line with the appropriate data.


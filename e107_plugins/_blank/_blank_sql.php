/**
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * INFORMATION
 *
 * BEFORE DOING ANY DATABASE CHANGES OR UPDATES, CREATE A FRESH BACKUP!!!
 *
 * Add the SQL statements for your table here.
 * Make sure that you do not add the e107 table prefix (by default e107) to the table name!!
 * This file will be analyzed on plugin install and missing tables will be installed automatically.
 * To check if the table structure is still valid, run the "Tools -> Database -> Check for Updates" command.
 * Any differences between the defined structure here and the table structure on the server will than be detected.
 * In another step, you are able to update the table structure to the latest version from this file!
 *
 *
 * For the moment, the following operations are supported:
 * -------------------------------------------------------
 * - Create table
 * - Change field type, field size, field null or not, field default value
 * - Add index
 *
 *
 * What is currently NOT supported:
 * --------------------------------
 * - Rename table (by renaming the tablename, e.g. "blank" > "blank2"). The renamed table will be considered as new!
 * - Drop a table (e.g. if you remove the "blank" table definition from this file, the table will NOT be deleted from the database!)
 * - Rename or drop a field (a renamed field will be considered new, a missing field definition will NOT be recognized at all!)
 * - Change an index/key (e.g. the change is recognized, but leads to an error message and the change is not applied)
 * - Rename or drop an index/key (Rename is recognized as a new index and the missing index is not recognized at all!)
 * - A field definition containing "NULL DEFAULT NULL". The "Check for updates" method will always detect a change,
 *   but fails silently when trying to update. In that case remove the first "NULL" and run the the "Check for updates" again.
 *
 *
 * How to rename or drop tables, fields and indexes or modify indexes:
 * -------------------------------------------------------------------
 * There are methods that can be used to detect tables, fields and indexes. Some examples of how to use them
 * can be found in the "_blank_setup.php". There are also examples on how to drop a field or index or to check for specific properties.
 * Other examples can be found also in the "forum_setup.php"
 */


CREATE TABLE blank (
  `blank_id` int(10) NOT NULL AUTO_INCREMENT,
  `blank_icon` varchar(255) NOT NULL,
  `blank_type` varchar(10) NOT NULL,
  `blank_name` varchar(50) NOT NULL,
  `blank_folder` varchar(50) NOT NULL,
  `blank_version` varchar(5) NOT NULL,
  `blank_author` varchar(50) NOT NULL,
  `blank_authorURL` varchar(255) NOT NULL,
  `blank_date` int(10) NOT NULL,
  `blank_compatibility` varchar(5) NOT NULL,
  `blank_url` varchar(255) NOT NULL,
  `blank_media` json DEFAULT NULL,
  `blank_class` int(10) NOT NULL,
  PRIMARY KEY (`blank_id`)
) ENGINE=MyISAM;

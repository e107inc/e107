<?php
// Using an original code from the marvellous PhpMyAdmin
// Code to read sql files
/*
*
* Code used by news.php for multilanguage
*
*/
// $Id: read_dump.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 

function PMA_splitSqlFile(&$ret, $sql, $release)
{
    $sql          = trim($sql);
    $sql_len      = strlen($sql);
    $char         = '';
    $string_start = '';
    $in_string    = FALSE;
    $time0        = time();

    for ($i = 0; $i < $sql_len; ++$i) {
        $char = $sql[$i];

        // We are in a string, check for not escaped end of strings except for
        // backquotes that can't be escaped
        if ($in_string) {
            for (;;) {
                $i = strpos($sql, $string_start, $i);
                // No end of string found -> add the current substring to the
                // returned array
                if (!$i) {
                    $ret[] = $sql;
                    return TRUE;
                }
                // Backquotes or no backslashes before quotes: it's indeed the
                // end of the string -> exit the loop
                else if ($string_start == '`' || $sql[$i-1] != '\\') {
                    $string_start      = '';
                    $in_string         = FALSE;
                    break;
                }
                // one or more Backslashes before the presumed end of string...
                else {
                    // ... first checks for escaped backslashes
                    $j                     = 2;
                    $escaped_backslash     = FALSE;
                    while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                        $escaped_backslash = !$escaped_backslash;
                        $j++;
                    }
                    // ... if escaped backslashes: it's really the end of the
                    // string -> exit the loop
                    if ($escaped_backslash) {
                        $string_start  = '';
                        $in_string     = FALSE;
                        break;
                    }
                    // ... else loop
                    else {
                        $i++;
                    }
                } // end if...elseif...else
            } // end for
        } // end if (in string)

        // We are not in a string, first check for delimiter...
        else if ($char == ';') {
            // if delimiter found, add the parsed part to the returned array
            $ret[]      = substr($sql, 0, $i);
            $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
            $sql_len    = strlen($sql);
            if ($sql_len) {
                $i      = -1;
            } else {
                // The submited statement(s) end(s) here
                return TRUE;
            }
        } // end else if (is delimiter)

        // ... then check for start of a string,...
        else if (($char == '"') || ($char == '\'') || ($char == '`')) {
            $in_string    = TRUE;
            $string_start = $char;
        } // end else if (is start of string)

        // ... for start of a comment (and remove this comment if found)...
        else if ($char == '#'
                 || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')) {
            // starting position of the comment depends on the comment type
            $start_of_comment = (($sql[$i] == '#') ? $i : $i-2);
            // if no "\n" exits in the remaining string, checks for "\r"
            // (Mac eol style)
            $end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
                              ? strpos(' ' . $sql, "\012", $i+2)
                              : strpos(' ' . $sql, "\015", $i+2);
            if (!$end_of_comment) {
                // no eol found after '#', add the parsed part to the returned
                // array if required and exit
                if ($start_of_comment > 0) {
                    $ret[]    = trim(substr($sql, 0, $start_of_comment));
                }
                return TRUE;
            } else {
                $sql          = substr($sql, 0, $start_of_comment)
                              . ltrim(substr($sql, $end_of_comment));
                $sql_len      = strlen($sql);
                $i--;
            } // end if...else
        } // end else if (is comment)

        // ... and finally disactivate the "/*!...*/" syntax if MySQL < 3.22.07
        else if ($release < 32270
                 && ($char == '!' && $i > 1  && $sql[$i-2] . $sql[$i-1] == '/*')) {
            $sql[$i] = ' ';
        } // end else if

        // loic1: send a fake header each 30 sec. to bypass browser timeout
        $time1     = time();
        if ($time1 >= $time0 + 30) {
            $time0 = $time1;
            header('X-pmaPing: Pong');
        } // end if
    } // end for

    // add any rest to the returned array
    if (!empty($sql) && ereg('[^[:space:]]+', $sql)) {
        $ret[] = $sql;
    }

    return TRUE;
} // end of the 'PMA_splitSqlFile()' function


if (!function_exists('is_uploaded_file')) {
    /**
     * Emulates the 'is_uploaded_file()' function for old php versions.
     * Grabbed at the php manual:
     *     http://www.php.net/manual/en/features.file-upload.php
     *
     * @param   string    the name of the file to check
     *
     * @return  boolean   wether the file has been uploaded or not
     *
     * @access  public
     */
    function is_uploaded_file($filename) {
        if (!$tmp_file = @get_cfg_var('upload_tmp_dir')) {
            $tmp_file = tempnam('','');
            $deleted  = @unlink($tmp_file);
            $tmp_file = dirname($tmp_file);
        }
        $tmp_file     .= '/' . basename($filename);

        // User might have trailing slash in php.ini...
        return (ereg_replace('/+', '/', $tmp_file) == $filename);
    } // end of the 'is_uploaded_file()' emulated function
} // end if


/**
 * Set up default values for some variables
 */
$sql_query     = isset($sql_query)    ? $sql_query    : '';
$sql_file = !empty($tmp_sql_file)    ? $tmp_sql_file     : 'none';




/**
 * Prepares the sql query
 */
// Gets the query from a file if required
$tmp_lg = array();
if ($sql_file != 'none') {
    for ($i=0;$i<$yy;$i++){
		if(!in_array($lang_to_create[$i],$tmp_lg)){
		$tmp_lg[] = $lang_to_create[$i];
		$sql_file = e_PLUGIN."custom/".md5($lang_to_create[$i])."_".$tmp_sql_file;
		if (file_exists($sql_file)) {
	        $open_basedir     = '';
	        if (PMA_PHP_INT_VERSION >= 40000 ) {
	            $open_basedir = @ini_get('open_basedir');
	        }
	        if (empty($open_basedir)) {
	            $open_basedir = @get_cfg_var('open_basedir');
	        }
	
	        // If we are on a server with open_basedir, we must move the file
	        // before opening it. The doc explains how to create the "./tmp"
	        // directory
	
	        if (!empty($open_basedir)) {
	            // check if '.' is in open_basedir
	            $pos = strpos(' ' . $open_basedir, '.');
	
	            // from the PHP annotated manual
	            if (!$pos) {
	                // if no '.' in openbasedir, do not move the file, force the
	                // error and let PHP report it
	                error_reporting(E_ALL);
	                $sql_query .= fread(fopen($sql_file, 'r'), filesize($sql_file));
	            }
	            else {
	                $sql_file_new = './tmp/' . basename($sql_file);
	                if (PMA_PHP_INT_VERSION < 40003) {
	                    copy($sql_file, $sql_file_new);
	                } else {
	                    move_uploaded_file($sql_file, $sql_file_new);
	                }
	                $sql_query .= fread(fopen($sql_file_new, 'r'), filesize($sql_file_new));
	                unlink($sql_file_new);
	            }
	        }
	        else {
	            // read from the normal upload dir
	            $sql_query .= fread(fopen($sql_file, 'r'), filesize(e_ADMIN.$sql_file));
	        }
	
	        if (get_magic_quotes_runtime() == 1) {
	            $sql_query = stripslashes($sql_query);
	        }
			if(DEBUG_ML){echo "<br /><hr /><br /><br />File:".$sql_file;}
			if(DEBUG_ML){echo "<br />Query: ".$sql_query;}
	    }
	}
	}
}
/*
else if (get_magic_quotes_gpc() == 1) {
    $sql_query = stripslashes($sql_query);
}
*/
// Kanji convert SQL textfile 2002/1/4 by Y.Kawada
if (@function_exists('PMA_kanji_str_conv')) {
    $sql_tmp   = trim($sql_query);
    PMA_change_enc_order();
    $sql_query = PMA_kanji_str_conv($sql_tmp, $knjenc, isset($xkana) ? $xkana : '');
    PMA_change_enc_order();
} else {
    $sql_query = trim($sql_query);
}

// $sql_query come from the query textarea, if it's a reposted query gets its
// 'true' value
if (!empty($prev_sql_query)) {
    $prev_sql_query = urldecode($prev_sql_query);
    if ($sql_query == trim(htmlspecialchars($prev_sql_query))) {
        $sql_query  = $prev_sql_query;
    }
}

// Drop database is not allowed -> ensure the query can be run
if (!$cfgAllowUserDropDatabase
    && eregi('DROP[[:space:]]+(IF EXISTS[[:space:]]+)?DATABASE ', $sql_query)) {
    // Checks if the user is a Superuser
    // TODO: set a global variable with this information
    // loic1: optimized query
    $result = @mysql_query('USE mysql');
    if (mysql_error()) {
        include('./header.inc.php');
        PMA_mysqlDie($strNoDropDatabases, '', '', $err_url);
    }
}
define('PMA_CHK_DROP', 1);

/**
 * Executes the query
 */
// echo "<br> query to execute: ".$sql_query;
if ($sql_query != '') {
    $pieces = array();
    PMA_splitSqlFile($pieces, $sql_query, PMA_MYSQL_INT_VERSION);
    $pieces_count = count($pieces);

    $sql_query_cpy = implode(";\n", $pieces) . ';';

    // Only one query to run
	/*
    if ($pieces_count == 1 && !empty($pieces[0])) {
        // sql.php will stripslash the query if get_magic_quotes_gpc
        if (get_magic_quotes_gpc() == 1) {
            $sql_query = addslashes($pieces[0]);
        } else {
            $sql_query = $pieces[0];
        }
        if (eregi('^(DROP|CREATE)[[:space:]]+(IF EXISTS[[:space:]]+)?(TABLE|DATABASE)[[:space:]]+(.+)', $sql_query)) {
            $reload = 1;
        }
        include('./sql.php');
        exit();
    }
	*/

    // Runs multiple queries
    //else 
	if (mysql_select_db($mySQLdefaultdb)) {
		
        for ($i = 0; $i < $pieces_count; $i++) {
            $a_sql_query = $pieces[$i];
			// Comment next line for tests
            $result = mysql_query($a_sql_query);

            if ($result == FALSE) { // readdump failed
                //echo "XX.".$a_sql_query;
			          $my_die = $a_sql_query;
                break;
            }
            if (!isset($reload) && eregi('^(DROP|CREATE)[[:space:]]+(IF EXISTS[[:space:]]+)?(TABLE|DATABASE)[[:space:]]+(.+)', $a_sql_query)) {
                $reload = 1;
            }
        } // end for
    } // end else if
    unset($pieces);
} // end if


/**
 * MySQL error
*/
$ml_install_error = "";
if (isset($my_die)) {
    $ml_install_error = "<br />".MLAD_LAN_49."<br />".MLAD_LAN_50."<br />";
}


/**
 * Go back to the calling script
 */
// Checks for a valid target script
if (isset($table) && $table == '') {
    unset($table);
}
if (isset($mySQLdefaultdb) && $mySQLdefaultdb == '') {
    unset($mySQLdefaultdb);
}

?>

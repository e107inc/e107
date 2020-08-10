<?php

ini_set("display_errors", 1); 
//22/10/2009 9.58.12
//general list of implementaion and 
// TODO admin log implementation to log out all the traffic
// TODO error handling (not really sure how can be done, at least in log)
// TODO better ACL configuration (getperms) and admin panel to configure ACL (example this user has rw access to news?) wraps ACL configuration
// TODO custom content plugin support
// TODO sintax to write in custom field of custom (ie sort of: {field_name:value}) this one need WLW customization OR use the wordpress plugin see http://windowslivewire.spaces.live.com/blog/cns!2F7EB29B42641D59!41603.entry AND http://codex.wordpress.org/Custom_Fields
// TODO better admin preferences panel (for example to choise to use content plugin instead of pages)
// TODO full manual/instructions to explain configuration of plugin and WLW


//check e107 instance
if (!defined('e107_INIT'))
{ 
	require_once("../../class2.php");
}

//check if plugin is installed
if (!e107::isInstalled('metaweblog'))
{
	e107::redirect();
}


if((e_QUERY == 'rsd') || isset($_GET['rsd'])) // http://archipelago.phrasewise.com/rsd
{ 
	header('Content-Type: text/xml; charset=UTF-8', true);
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	<rsd version=\"1.0\" xmlns=\"http://archipelago.phrasewise.com/rsd\">
	  <service>
	    <engineName>e107</engineName>
	    <engineLink>http://www.e107.org/</engineLink>
	    <homePageLink>".SITEURL."</homePageLink>
	    <apis>
	      <api name=\"WordPress\" blogID=\"1\" preferred=\"true\" apiLink=\"".SITEURLBASE.e_PLUGIN_ABS."metaweblog/metaweblog.php\" />
	      <api name=\"Movable Type\" blogID=\"1\" preferred=\"false\" apiLink=\"". SITEURLBASE.e_PLUGIN_ABS."metaweblog/metaweblog.php\" />
	      <api name=\"MetaWeblog\" blogID=\"1\" preferred=\"false\" apiLink=\"". SITEURLBASE.e_PLUGIN_ABS."metaweblog/metaweblog.php\" />
	      <api name=\"Blogger\" blogID=\"1\" preferred=\"false\" apiLink=\"". SITEURLBASE.e_PLUGIN_ABS."metaweblog/metaweblog.php\"  />
	    </apis>
	  </service>
	</rsd>";
	
	exit;
}




// These three files are from the PHP-XMLRPC library.
include (e_HANDLER.'xmlrpc/xmlrpc.inc.php');
include (e_HANDLER.'xmlrpc/xmlrpcs.inc.php');
include (e_HANDLER.'xmlrpc/xmlrpc_wrappers.inc.php');




//general note: XMLRPC method functions parameters
//have this rule: 1st parameter is the type of the OUT data (result: array,struct,etc), from 2nd are the IN parameters

//default caracter encoding !IMPORTANT
$xmlrpc_internalencoding = 'UTF-8';


// VARIABLES FOR SOME REASONS $pref seems to not work in code? so we define local variables
define('eXMLRPC_FILES_UPLOAD_PATH', e_NEWSIMAGE);
define('eXMLRPC_FILES_SITEBASE_URL', e_NEWSIMAGE_ABS);
define('eXMLRPC_BLOG_XMLRPC', e_PLUGIN_ABS.'metaweblog/metaweblog.php');
define('eXMLRPC_NEWS_RENDER_TYPE_LOC', $pref['eXMLRPC_NEWS_RENDER_TYPE']);
define('eXMLRPC_BLOG_ID_LOC', $pref['eXMLRPC_BLOG_ID']);
define('eXMLRPC_BLOG_NAME_LOC', SITENAME ); // $pref['eXMLRPC_BLOG_NAME']

/*
 * Used to test usage of object methods in dispatch maps
 */
class xmlrpc_server_methods_container
{
}
//13/08/2009 17.08.11
//to add function to check users priledges for $area...
function userLogin($username, $password, $area)
{
	$sql = e107::getDb();
	
	$query = 'SELECT user_perms FROM #user WHERE user_loginname = \''.$username.'\' AND user_password = \''.md5($password).'\'';

	$sql->db_Select_gen($query);
	$row = $sql->db_Fetch();
	//variable to store user permissions
	$perms = explode('.', $row['user_perms']);
	$haspe = in_array($area, $perms);
	//if neededs permissions are founds or user is the main site admin
	if (($haspe != false) || ($row['user_perms'] == 0))
	{
		return true;
	}
	else
	{
		return false;
	}
}

$getUsersBlogs_sig = array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString));
$getUsersBlogs_doc = 'Returns a list of weblogs to which an author has posting privileges.';
function getUsersBlogs($xmlrpcmsg)
{
	$structArray = array();
	$structArray[] = new xmlrpcval(array(
		'isAdmin'=> new xmlrpcval(true, 'boolean'),
		'url'=> new xmlrpcval(SITEURL, 'string'),
		'blogid'=> new xmlrpcval(eXMLRPC_BLOG_ID_LOC, 'string'),
		'blogName'=> new xmlrpcval(eXMLRPC_BLOG_NAME_LOC, 'string'),
		'xmlrpc'=> new xmlrpcval(eXMLRPC_BLOG_XMLRPC, 'string')
		),'struct');
	
	return new xmlrpcresp(new xmlrpcval($structArray, 'array'));
}

/*
 *************************
 ***** NEW POST  *********
 *************************
 */

$newPost_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcStruct, $xmlrpcBoolean));
$newPost_doc = 'Post a new item to the blog.';
function newPost($xmlrpcmsg)
{
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	
	if (userLogin($username, $password, 'H') == true)
	{
		$content = $xmlrpcmsg->getParam(3);
		$title = $content->structMem('title')->scalarval();
		
		$description = '[html]'.htmlspecialchars_decode($content->structMem('description')->scalarval()).'[/html]';
		
		//22/10/2009 14.48.04 added mt_text_more ie news_extended
		//check if we have something...
		$tempTextMore = checkXmlElementS($content->serialize(), 'mt_text_more');
		if($tempTextMore == 1){
				$mt_text_more = '[html]'.$content->structMem('mt_text_more')->scalarval().'[/html]';
		}
		
		//if date is null will be replaced with current datetime (wordpress like)
		//check with simplexml for the parameter dateCreated? XMLRPC-PHP seems to not have such functions??
		$tempDate = checkXmlElementS($content->serialize(), 'dateCreated');
		if ($tempDate == 1)
		{
			$dateCreated = $content->structMem('dateCreated')->serialize(); // Not all clients send dateCreated info. So add if statement here if you want to use it.
			$timestamp = iso8601_decode($dateCreated); // To convert to unix timestamp
		}
		else
		{
			$timestamp = time();
		}
		
		//21/10/2009 17.17.46 added $mt_excerpt
		//add in the news summary
		//check if we have something...
		$tempExcerpt = checkXmlElementS($content->serialize(), 'mt_excerpt');
		if($tempExcerpt == 1){
				$mt_excerpt = $content->structMem('mt_excerpt')->scalarval();
		}
		
		//22/10/2009 11.51.54 added $mt_allow_comments
		//add the news_allow_comments flag
		//check if we have something...
		$tempAllowComments = checkXmlElementS($content->serialize(), 'mt_allow_comments');
		if($tempAllowComments == 1){
				$mt_allow_comments = $content->structMem('mt_allow_comments')->scalarval();
		}
		
		//26/10/2009 14.30.41 added mt_keywords ie tags
		//check if we have something...
		$tempKeywords = checkXmlElementS($content->serialize(), 'mt_keywords');
		if($tempKeywords == 1){
				$mt_keywords = $content->structMem('mt_keywords')->scalarval();
		}
		
		//author from e107
		$query = 'SELECT u.user_id FROM `#user` AS u WHERE u.user_loginname = \''.$username.'\' AND u.user_password = \''.md5($password).'\'';
		$sql = e107::getDb();
		$sql->db_Select_gen($query);
		$row = $sql->db_Fetch();
		$author = $row['user_id'];
		if ($content->structMem('categories')->arraySize() > 0)
		{
			$categories = $content->structMem('categories')->arrayMem(0)->scalarval();
			//try to read out the id of the category
			if ($categories != '')
			{
				$query = 'SELECT c.category_id FROM `#news_category` AS c WHERE c.category_name = \''.$categories.'\'';
				$sql->db_Select_gen($query);
				$row = $sql->db_Fetch();
				$categories = $row['category_id'];
			}
		}
		
		$published = $xmlrpcmsg->getParam(4)->scalarval();
		
		
		// TODO use:
		// $ix = new news;
		// $ret = $ix->submit_item($arrayvalues);
		
		//post data with new fuctions
		$data = array();
		$data['data']['news_title'] = $title;
		$data['_FIELD_TYPES']['news_title'] = 'todb';
		$data['data']['news_body'] = $description;
		$data['_FIELD_TYPES']['news_body'] = 'todb';
		$data['data']['news_extended'] = $mt_text_more;
		$data['_FIELD_TYPES']['news_extended'] = 'todb';
		$data['data']['news_datestamp'] = $timestamp;
		$data['_FIELD_TYPES']['news_datestamp'] = 'int';
		$data['data']['news_author'] = $author;
		$data['_FIELD_TYPES']['news_author'] = 'int';
		$data['data']['news_category'] = $categories; //category id is taken by a query against news categories
		$data['_FIELD_TYPES']['news_category'] = 'int';
		$data['data']['news_allow_comments'] = $mt_allow_comments;
		$data['_FIELD_TYPES']['news_allow_comments'] = 'int';
		$data['data']['news_start'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_start'] = 'int';
		$data['data']['news_end'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_end'] = 'int';
		//$data['data']['news_class'] = $news['news_class'];
		//$data['_FIELD_TYPES']['news_class'] = 'todb';
		$data['data']['news_render_type'] = eXMLRPC_NEWS_RENDER_TYPE_LOC; //from preferences
		$data['_FIELD_TYPES']['news_render_type'] = 'int';
		//news_comment_total
		$data['data']['news_summary'] = $mt_excerpt; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_summary'] = 'todb';
		$data['data']['news_thumbnail'] = ''; //NOT APPLICABLE?
		$data['_FIELD_TYPES']['news_thumbnail'] = 'todb';
		$data['data']['news_sticky'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_sticky'] = 'int';
		$data['data']['news_meta_keywords'] = $mt_keywords;
		$data['_FIELD_TYPES']['news_meta_keywords'] = 'todb';
		$data['data']['news_meta_description'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_meta_description'] = 'todb';
		
		$postid = $sql->db_Insert('news', $data);
		
		return new xmlrpcresp( new xmlrpcval($postid, 'string')); // Return the id of the post just inserted into the DB. See mysql_insert_id() in the PHP manual.
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}

/*
 *************************
 ***** EDIT POST *********
 *************************
 */

$editPost_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcStruct, $xmlrpcBoolean));
$editPost_doc = 'Edit item on the blog.';
function editPost($xmlrpcmsg)
{
	$postid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	
	if (userLogin($username, $password, 'H') == true)
	{
		$content = $xmlrpcmsg->getParam(3);
		$title = $content->structMem('title')->scalarval();
		
		$description = '[html]'.$content->structMem('description')->scalarval().'[/html]';
		
		//22/10/2009 14.48.04 added mt_text_more ie news_extended
		//check if we have something...
		$tempTextMore = checkXmlElementS($content->serialize(), 'mt_text_more');
		if($tempTextMore == 1){
				$mt_text_more = '[html]'.$content->structMem('mt_text_more')->scalarval().'[/html]';
		}
		
		//if date is null will be replaced with current datetime (wordpress like)
		//check with simplexml for the parameter dateCreated? XMLRPC-PHP seems to not have such functions??
		$tempDate = checkXmlElementS($content->serialize(), 'dateCreated');
		if ($tempDate == 1)
		{
			$dateCreated = $content->structMem('dateCreated')->serialize(); // Not all clients send dateCreated info. So add if statement here if you want to use it.
			$timestamp = iso8601_decode($dateCreated); // To convert to unix timestamp
		}
		else
		{
			$timestamp = time();
		}
		
		//22/10/2009 11.51.54 added $mt_excerpt
		//add the news summary
		//check if we have something...
		$tempExcerpt = checkXmlElementS($content->serialize(), 'mt_excerpt');
		if($tempExcerpt == 1){
				$mt_excerpt = $content->structMem('mt_excerpt')->scalarval();
		}
		
		//22/10/2009 11.51.54 added $mt_allow_comments
		//add the news_allow_comments flag
		//check if we have something...
		$tempAllowComments = checkXmlElementS($content->serialize(), 'mt_allow_comments');
		if($tempAllowComments == 1){
				$mt_allow_comments = $content->structMem('mt_allow_comments')->scalarval();
		}
		
		//26/10/2009 14.30.41 added mt_keywords ie tags
		//check if we have something...
		$tempKeywords = checkXmlElementS($content->serialize(), 'mt_keywords');
		if($tempKeywords == 1){
				$mt_keywords = $content->structMem('mt_keywords')->scalarval();
		}
		
		//author from e107
		$query = 'SELECT u.user_id FROM `#user` AS u WHERE u.user_loginname = \''.$username.'\' AND u.user_password = \''.md5($password).'\'';
		$sql = new db();
		$sql->db_Select_gen($query);
		$row = $sql->db_Fetch();
		$author = $row['user_id'];
		if ($content->structMem('categories')->arraySize() > 0)
		{
			$categories = $content->structMem('categories')->arrayMem(0)->scalarval();
			//try to read out the id of the category
			if ($categories != '')
			{
				$query = 'SELECT c.category_id FROM `#news_category` AS c WHERE c.category_name = \''.$categories.'\'';
				$sql->db_Select_gen($query);
				$row = $sql->db_Fetch();
				$categories = $row['category_id'];
			}
		}
		$published = $xmlrpcmsg->getParam(4)->scalarval();
		
		// TODO use:
		// $ix = new news;
		// $ret = $ix->submit_item($arrayvalues);
		
		//edit data with new fuctions
		$data = array();
		//to update we need to set news id...
		$data['data']['news_id'] = $postid;
		$data['_FIELD_TYPES']['news_id'] = 'int';
		$data['data']['news_title'] = $title;
		$data['_FIELD_TYPES']['news_title'] = 'todb';
		$data['data']['news_body'] = $description;
		$data['_FIELD_TYPES']['news_body'] = 'todb';
		$data['data']['news_extended'] = $mt_text_more;
		$data['_FIELD_TYPES']['news_extended'] = 'todb';
		$data['data']['news_datestamp'] = $timestamp;
		$data['_FIELD_TYPES']['news_datestamp'] = 'int';
		$data['data']['news_author'] = $author;
		$data['_FIELD_TYPES']['news_author'] = 'int';
		$data['data']['news_category'] = $categories; //category id is taken by a query against news categories
		$data['_FIELD_TYPES']['news_category'] = 'int';
		$data['data']['news_allow_comments'] = $mt_allow_comments;
		$data['_FIELD_TYPES']['news_allow_comments'] = 'int';
		$data['data']['news_start'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_start'] = 'int';
		$data['data']['news_end'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_end'] = 'int';
		//$data['data']['news_class'] = $news['news_class'];
		//$data['_FIELD_TYPES']['news_class'] = 'todb';
		$data['data']['news_render_type'] = eXMLRPC_NEWS_RENDER_TYPE_LOC; //from preferences
		$data['_FIELD_TYPES']['news_render_type'] = 'int';
		//news_comment_total
		$data['data']['news_summary'] = $mt_excerpt; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_summary'] = 'todb';
		$data['data']['news_thumbnail'] = ''; //NOT APPLICABLE?
		$data['_FIELD_TYPES']['news_thumbnail'] = 'todb';
		$data['data']['news_sticky'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_sticky'] = 'int';
		$data['data']['news_meta_keywords'] = $mt_keywords;
		$data['_FIELD_TYPES']['news_meta_keywords'] = 'todb';
		$data['data']['news_meta_description'] = ''; //NOT AVAIBLE MAKE A CUSTOM FIELD?
		$data['_FIELD_TYPES']['news_meta_description'] = 'todb';
		
		$postid = $sql->db_Update('news', $data);
		
		return new xmlrpcresp( new xmlrpcval(true, 'boolean'));
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}

/*
 *************************
 ***** GET POST *********
 *************************
 */

$getPost_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
$getPost_doc = 'Get an item on the blog.';
function getPost($xmlrpcmsg)
{
	$postid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	
	if (userLogin($username, $password, 'H') == true)
	{
		$query = 'SELECT n.*, c.category_name FROM `#news` AS n 
              LEFT JOIN `#news_category` AS c ON c.category_id = n.news_category 
              WHERE n.news_id=\''.$postid.'\' LIMIT 1';
		
		//link back to the page important!
		$link = SITEURL.'news.php?'.$postid;
		
		$sql = e107::getDb();
		$sql->db_Select_gen($query);
		
		while ($row = $sql->db_Fetch())
		{
			return new xmlrpcresp( new xmlrpcval(array(
																									'postid'=> new xmlrpcval($row['news_id'], 'string'), 
																									'dateCreated'=> new xmlrpcval(iso8601_encode($row['news_datestamp']),'dateTime.iso8601'),
																									'title'=> new xmlrpcval($row['news_title'], 'string'),
																									'mt_excerpt'=> new xmlrpcval($row['news_summary'], 'string'),
																									'mt_keywords'=> new xmlrpcval($row['news_meta_keywords'], 'string'),
																									'mt_allow_comments'=> new xmlrpcval($row['news_allow_comments'], 'string'),
																									'description'=> new xmlrpcval(str_replace('[html]', '', str_replace('[/html]', '', $row['news_body'])), 'string'),
																									'mt_text_more'=> new xmlrpcval(str_replace('[html]', '', str_replace('[/html]', '', $row['news_extended'])), 'string'),
																									'categories'=> new xmlrpcval(array( new xmlrpcval($row['category_name'], 'string')), 'array'),
																									'publish'=> new xmlrpcval(1, 'boolean'), //e107 does not have this flag? 
																									'link'=> new xmlrpcval($link, 'string'), 'permaLink'=> new xmlrpcval($link, 'string')), 'struct'));
		}
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
			
/*
 *************************
 ***** DELETE POST *******
 *************************
 */
function deletePost($xmlrpcmsg)
{
	$postid = $xmlrpcmsg->getParam(1)->scalarval();
	$username = $xmlrpcmsg->getParam(2)->scalarval();
	$password = $xmlrpcmsg->getParam(3)->scalarval();
	if (userLogin($username, $password, 'H') == true)
	{
		//TODO use: $sql->db_Delete();
		$sql = e107::getDb();
		//22/10/2009 15.06.16 delete news with new methods
		$sql->db_Delete('news', 'news_id='.$postid);
		
		return new xmlrpcresp( new xmlrpcval(true, 'boolean'));
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}


/*
 *************************
 ***** GET RECENT POSTS **
 *************************
 */
$getRecentPosts_sig = array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcInt));
$getRecentPosts_doc = 'Get the recent posts on the blog.';
function getRecentPosts($xmlrpcmsg)
{
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, 'H') == true)
	{
		$numposts = $xmlrpcmsg->getParam(3)->scalarval();
		$structArray = array();
		$query = 'SELECT 
                 n.*,
                 c.category_name
              FROM `#news` AS n 
              LEFT JOIN `#news_category` AS c ON c.category_id = n.news_category 
              ORDER BY n.news_datestamp DESC 
              LIMIT '.$numposts;
			  
		$link = SITEURL.'news.php?'.$postid; //link back to the page important!
			  
		$sql = e107::getDb();
		$sql->db_Select_gen($query);		
		
		while ($row = $sql->db_Fetch())
		{
			$structArray[] = new xmlrpcval(array(
			
				'postid'=> new xmlrpcval($row['news_id'], 'string'), 
				'dateCreated'=> new xmlrpcval(iso8601_encode($row['news_datestamp']),'dateTime.iso8601'),
				'title'=> new xmlrpcval($row['news_title'], 'string'),
				'mt_excerpt'=> new xmlrpcval($row['news_summary'], 'string'),
				'mt_keywords'=> new xmlrpcval($row['news_meta_keywords'], 'string'),
				'mt_allow_comments'=> new xmlrpcval($row['news_allow_comments'], 'string'),
				'description'=> new xmlrpcval(str_replace('[html]', '', str_replace('[/html]', '', $row['news_body'])), 'string'),
				'mt_text_more'=> new xmlrpcval(str_replace('[html]', '', str_replace('[/html]', '', $row['news_extended'])), 'string'),
				'categories'=> new xmlrpcval(array( new xmlrpcval($row['category_name'], 'string')), 'array'),
				'publish'=> new xmlrpcval(1, 'boolean'), //e107 does not have this flag? 
				'link'=> new xmlrpcval($link, 'string'), 'permaLink'=> new xmlrpcval($link, 'string')
			
			), 'struct'
			);
		}
		
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}


/*
 *************************
 ***** GET CATEGORIES  ***
 *************************
 */
 
 
$getCategories_sig = array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString));
$getCategories_doc = 'Get the categories on the blog.';
function getCategories($xmlrpcmsg)
{
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	
	if (userLogin($username, $password, 'H') == 1)
	{
		$structArray = array();
		$query = 'SELECT n.* FROM `#news_category` AS n';

		$sql = e107::getDb();
		$sql->db_Select_gen($query);
		while ($row = $sql->db_Fetch())
		{	
			$structArray[] = new xmlrpcval(array(
			'categoryId'			=> new xmlrpcval($row['category_id'], 'string'),
			'parentId'				=> new xmlrpcval('', 'string'),
			'description'			=> new xmlrpcval($row['category_name'], 'string'),
			'categoryDescription'	=> new xmlrpcval('', 'string'),
			'categoryName'			=> new xmlrpcval($row['category_name'], 'string'),
			'htmlUrl'				=> new xmlrpcval('', 'string'),
			'rssUrl'				=> new xmlrpcval('', 'string')), 'struct');
		}
		
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}


/*
 *************************
 ***** NEW MEDIA OBJECT **
 *************************
 */
$newMediaObject_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcStruct)
		);
$newMediaObject_doc = 'Upload media files onto the blog server.';
function newMediaObject($xmlrpcmsg)
{
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, 'H') == true || userLogin($username, $password, '5') == true)
	{
		$file = $xmlrpcmsg->getParam(3);
		$filename = $file->structMem('name')->scalarval();
		$filename =	substr($filename, (strrpos($filename, '/') + 1));
		$type = $file->structMem('type')->scalarval(); // The type of the file
		$bits = $file->structMem('bits')->serialize();
		$bits = str_replace('<value><base64>', '', $bits);
		$bits = str_replace('</base64></value>', '', $bits);
		
		$uploaddir = eXMLRPC_FILES_UPLOAD_PATH; // Make sure this folder has been chmoded to 777.
		if (fwrite(fopen($uploaddir.$filename, 'xb'), base64_decode($bits)) == false)
		{
			return new xmlrpcresp(0, $xmlrpcerruser + 1, 'File Failed to Write');
		}
		else
		{
			return new xmlrpcresp( new xmlrpcval(array('url'=>	new xmlrpcval(eXMLRPC_FILES_SITEBASE_URL.$filename, 'string')), 'struct'));
		}
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}



//21/08/2009 10.07.36
//WORDPRESS APIs
/*
 *************************
 ***** GET PAGE  *********
 *************************
 */
$getPage_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
$getPage_doc = 'Get a page on the blog.';
function getPage($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	$blog_id = $xmlrpcmsg->getParam(0)->scalarval();
	$pageid = $xmlrpcmsg->getParam(1)->scalarval();
	$username = $xmlrpcmsg->getParam(2)->scalarval();
	$password = $xmlrpcmsg->getParam(3)->scalarval();
	
	if (userLogin($username, $password, '5') == true)
	{
		$query = 'SELECT p.*, u.user_id, u.user_name FROM `#page` AS p LEFT JOIN `#user` AS u ON u.user_id = p.page_author  WHERE p.page_id=\''.$pageid.'\' LIMIT 1';
		
		$link = SITEURL.'page.php?'.$pageid; //link back to the page important!

		$sql->db_Select_gen($query);
		while ($row = $sql->db_Fetch())
		{
			return new xmlrpcresp( new xmlrpcval(array(
				'dateCreated'			=> new xmlrpcval(iso8601_encode($row['page_datestamp']), 'dateTime.iso8601'),
				'userid'				=> new xmlrpcval($row['user_id'], 'string'),
				'page_id'				=> new xmlrpcval($row['page_id'], 'string'),
				'page_status'			=> new xmlrpcval('', 'string'),
				'description'			=> new xmlrpcval(str_replace('[html]', '', str_replace('[/html]', '', $row['page_text'])), 'string'),
				'title'					=> new xmlrpcval($row['page_title'], 'string'),
				'link'					=> new xmlrpcval($link, 'string'),
				'permaLink'				=> new xmlrpcval($link, 'string'),
				'categories'			=> new xmlrpcval('', 'string'),
				'excerpt'				=> new xmlrpcval('', 'string'),
				'text_more'				=> new xmlrpcval('', 'string'),
				'mt_allow_comments'		=> new xmlrpcval($row['page_comment_flag'], 'boolean'),
				'mt_allow_pings'		=> new xmlrpcval('', 'string'),
				'wp_slug'				=> new xmlrpcval('', 'string'),
				'wp_password'			=> new xmlrpcval($row['page_password'], 'string'),
				'wp_author'				=> new xmlrpcval($row['user_name'], 'string'),
				'wp_page_parent_id'		=> new xmlrpcval('', 'string'),
				'wp_page_parent_title'	=> new xmlrpcval('', 'string'),
				'wp_page_order'			=> new xmlrpcval('', 'string'),
				'wp_author_id'			=> new xmlrpcval($row['user_id'], 'string'),
				'wp_author_display_name'=> new xmlrpcval($row['user_name'], 'string'),
				'date_created_gmt'		=> new xmlrpcval('', 'string'),
				'custom_fields'			=> new xmlrpcval('', 'string'),
				'wp_page_template'		=> new xmlrpcval('', 'string')),
				'struct')
			);
		}
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}


/*
 *************************
 ***** GET PAGES  ********
 *************************
 */
$getPages_sig = array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcInt));
$getPages_doc = 'Get pages on the blog.';
function getPages($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, '5') == true)
	{
		$numpages = $xmlrpcmsg->getParam(3)->scalarval();
		$structArray = array();
		$query = 'SELECT 
                 p.*,
                 u.user_id,
                 u.user_name
              FROM `#page` AS p 
              LEFT JOIN `#user` AS u ON u.user_id = p.page_author
              ORDER BY p.page_datestamp DESC 
              LIMIT '.$numpages;
		//link back to the page important!
		$link = SITEURL.'page.php?'.$pageid;

		$sql->db_Select_gen($query);
		while ($row = $sql->db_Fetch())
		{
			$structArray[] = new xmlrpcval(array(
					'dateCreated'=> new xmlrpcval(iso8601_encode($row['page_datestamp']), 'dateTime.iso8601'),
					'userid'=> new xmlrpcval($row['user_id'], 'string'),
					'page_id'=> new xmlrpcval($row['page_id'], 'string'),
					'page_status'=> new xmlrpcval('', 'string'),
					'description'=> new xmlrpcval(str_replace('[html]','', str_replace('[/html]', '', $row['page_text'])),'string'),
					'title'=> new xmlrpcval($row['page_title'], 'string'),
					'link'=> new xmlrpcval($link, 'string'),
					'permaLink'=> new xmlrpcval($link, 'string'),
					'categories'=> new xmlrpcval('', 'string'),
					'excerpt'=> new xmlrpcval('', 'string'),
					'text_more'=> new xmlrpcval('', 'string'),
					'mt_allow_comments'=> new xmlrpcval($row['page_comment_flag'], 'boolean'),
					'mt_allow_pings'=> new xmlrpcval('', 'string'),
					'wp_slug'=> new xmlrpcval('', 'string'),
					'wp_password'=> new xmlrpcval($row['page_password'],'string'),
					'wp_author'=> new xmlrpcval($row['user_name'], 'string'),
					'wp_page_parent_id'=> new xmlrpcval('', 'string'),
					'wp_page_parent_title'=> new xmlrpcval('', 'string'),
					'wp_page_order'=> new xmlrpcval('', 'string'),
					'wp_author_id'=> new xmlrpcval($row['user_id'], 'string'),
					'wp_author_display_name'=> new xmlrpcval($row['user_name'], 'string'),
					'date_created_gmt'=> new xmlrpcval('', 'string'),
					'custom_fields'=> new xmlrpcval('', 'string'),
					'wp_page_template'=> new xmlrpcval('', 'string')),
					'struct');
		}
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}



/*
 *************************
 ***** NEW PAGE  *********
 *************************
 */
$newPage_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcStruct, $xmlrpcBoolean));
$newPage_doc = 'Post a new item to the blog.';
function newPage($xmlrpcmsg)
{
	$sql = e107::getDb();
		
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, '5') == true)
	{
		$content = $xmlrpcmsg->getParam(3);
		$title = $content->structMem('title')->scalarval();
		$description = '[html]'.htmlspecialchars_decode($content->structMem('description')->scalarval()).'[/html]';
		//if date is null will be replaced with current datetime (wordpress like)
		//check with simplexml for the parameter dateCreated? XMLRPC-PHP seems to not have such functions??
		$tempDate = checkXmlElementS($content->serialize(), 'dateCreated');
		if ($tempDate == 1)
		{
			$dateCreated = $content->structMem('dateCreated')->serialize(); // Not all clients send dateCreated info. So add if statement here if you want to use it.
			$timestamp = iso8601_decode($dateCreated); // To convert to unix timestamp
		}
		else
		{
			$timestamp = time();
		}
		
		//21/10/2009 17.17.46 added $wp_password
		//add password page
		//check if we have something...
		$tempPassword = checkXmlElementS($content->serialize(), 'wp_password');
		if($tempPassword == 1){
				$wp_password = $content->structMem('wp_password')->scalarval();
		}
		
		//author from e107
		$query = 'SELECT u.user_id FROM `#user` AS u WHERE u.user_loginname = \''.$username.'\' AND u.user_password = \''.md5($password).'\'';

		$sql->db_Select_gen($query);
		$row = $sql->db_Fetch();
		$author = $row['user_id'];
		
		//21/08/2009 14.37.49 allow comments
		//add comments flag
		//check if we have something...
		$tempAllowComments = checkXmlElementS($content->serialize(), 'mt_allow_comments');
		if($tempAllowComments == 1){
				$comments = $content->structMem('mt_allow_comments')->scalarval();
		}
		
		$published = $xmlrpcmsg->getParam(4)->scalarval();
		
		//post data with new fuctions
		$data['data']['page_title'] = $title;
		$data['_FIELD_TYPES']['page_title'] = 'todb';
		$data['data']['page_text'] = $description;
		$data['_FIELD_TYPES']['page_text'] = 'todb';
		$data['data']['page_datestamp'] = $timestamp;
		$data['_FIELD_TYPES']['page_datestamp'] = 'int';
		$data['data']['page_author'] = $author;
		$data['_FIELD_TYPES']['page_author'] = 'int';
		$data['data']['page_comment_flag'] = $comments;
		$data['_FIELD_TYPES']['page_comment_flag'] = 'int';
		$data['data']['page_password'] = $wp_password;
		$data['_FIELD_TYPES']['page_password'] = 'todb';
		$data['data']['page_class'] = 0;
		$data['_FIELD_TYPES']['page_class'] = 'int';
		
		$postid = $sql->db_Insert('page', $data);
		
		return new xmlrpcresp( new xmlrpcval($postid, 'string')
		); // Return the id of the post just inserted into the DB. See mysql_insert_id() in the PHP manual.
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** DELETE PAGE *******
 *************************
 */
$deletePage_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString)
		);
$deletePage_doc = "Delete a page from blog";
function deletePage($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	$pageid = $xmlrpcmsg->getParam(3)->scalarval();
	if (userLogin($username, $password, 'H') == true)
	{
		//TODO Use db_Delete();
		
		//23/10/2009 16.15.15 delete page with new methods
		$sql->db_Delete('page', 'page_id='.$pageid);
		
		return new xmlrpcresp( new xmlrpcval(true, 'boolean'));
	
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** EDIT PAGE *********
 *************************
 */
$editPage_sig = array
		(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcStruct, $xmlrpcBoolean));
$editPage_doc = 'Edit a page on the blog.';
function editPage($xmlrpcmsg)
{
	$sql = e107::getDb();
		
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$pageid = $xmlrpcmsg->getParam(1)->scalarval();
	$username = $xmlrpcmsg->getParam(2)->scalarval();
	$password = $xmlrpcmsg->getParam(3)->scalarval();
	if (userLogin($username, $password, 'H') == true)
	{
		$content = $xmlrpcmsg->getParam(4);
		$title = $content->structMem('title')->scalarval();
		$description = '[html]'.$content->structMem('description')->scalarval().'[/html]';
		//if date is null will be replaced with current datetime (wordpress like)
		//check with simplexml for the parameter dateCreated? XMLRPC-PHP seems to not have such functions??
		$tempDate = checkXmlElementS($content->serialize(), 'dateCreated');
		if ($tempDate == 1)
		{
			$dateCreated = $content->structMem('dateCreated')->serialize(); // Not all clients send dateCreated info. So add if statement here if you want to use it.
			$timestamp = iso8601_decode($dateCreated); // To convert to unix timestamp
		}
		else
		{
			$timestamp = time();
		}
		
		//author from e107
		$query = 'SELECT u.user_id FROM `#user` AS u WHERE u.user_loginname = \''.$username.'\' AND u.user_password = \''.md5($password).'\'';
		$sql->db_Select_gen($query);
		$row = $sql->db_Fetch();
		
		$author = $row['user_id'];
		
		//21/08/2009 14.37.49 allow comments
		//add comments flag
		//check if we have something...
		$tempAllowComments = checkXmlElementS($content->serialize(), 'mt_allow_comments');
		if($tempAllowComments == 1){
				$comments = $content->structMem('mt_allow_comments')->scalarval();
		}
		
		$published = $xmlrpcmsg->getParam(5)->scalarval();
		
		//edit data with new fuctions
		$data['data']['page_id'] = $pageid;
		$data['_FIELD_TYPES']['page_id'] = 'int';
		$data['data']['page_title'] = $title;
		$data['_FIELD_TYPES']['page_title'] = 'todb';
		$data['data']['page_text'] = $description;
		$data['_FIELD_TYPES']['page_text'] = 'todb';
		$data['data']['page_datestamp'] = $timestamp;
		$data['_FIELD_TYPES']['page_datestamp'] = 'int';
		$data['data']['page_author'] = $author;
		$data['_FIELD_TYPES']['page_author'] = 'int';
		$data['data']['page_comment_flag'] = $comments;
		$data['_FIELD_TYPES']['page_comment_flag'] = 'int';
		$data['data']['page_password'] = $wp_password;
		$data['_FIELD_TYPES']['page_password'] = 'todb';
		$data['data']['page_class'] = 0;
		$data['_FIELD_TYPES']['page_class'] = 'int';
		
		$pageid = $sql->db_Update('page', $data);
		
		return new	xmlrpcresp( new xmlrpcval(true, 'boolean'));
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** GET PAGE LIST *****
 *************************
 */
//21/08/2009 15.45.15 uhm... DON'T SURE ABOUT THIS :D
$getPageList_sig = array(array($xmlrpcArray, $xmlrpcString
		, $xmlrpcString, $xmlrpcString));
		
$getPageList_doc = 'Get pages list on the blog.';
function getPageList($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, '5') == true)
	{
		$structArray = array();
		$query = 'SELECT 
                 p.*,
                 u.user_id,
                 u.user_name
              FROM `#page` AS p 
              LEFT JOIN `#user` AS u ON u.user_id = p.page_author
              ORDER BY p.page_datestamp DESC 
              LIMIT '.$numpages;

		$sql->db_Select_gen($query);
		while ($row = $sql->db_Fetch())
		{
			$structArray[] = new xmlrpcval(array(
						'page_id'=> new xmlrpcval($row['page_id'], 'string'),
						'page_title'=> new xmlrpcval($row['page_title'], 'string'),
						'page_parent_title'=> new xmlrpcval('', 'string'),
						'date_created_gmt'=> new xmlrpcval('', 'string'),
						'dateCreated'=> new xmlrpcval(iso8601_encode($row['page_datestamp']),
						'dateTime.iso8601')),
						'struct');
		}
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** NEW CATEGORY ******
 *************************
 */
$newCategory_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcStruct));
$newCategory_doc = 'Create a new category on the blog.';
function newCategory($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, 'H') == true || userLogin($username, $password, '5') == true)
	{
		$content = $xmlrpcmsg->getParam(3);
		$name = $content->structMem('name')->scalarval();
		//21/08/2009 16.20.02 unused at this stage
		$slug = (checkXmlElementS($content->serialize(), 'slug') == true) ? $content->structMem('slug')->scalarval() : '';
		//21/08/2009 16.20.02 unused at this stage
		$parentid = (checkXmlElementS($content->serialize(), 'parent_id') == true) ? $content->structMem('parent_id')->scalarval() : '';
		//21/08/2009 16.20.02 unused at this stage
		$description = (checkXmlElementS($content->serialize(), 'description') == true) ? $content->structMem('description')->scalarval() : '';
		
		//post data with new fuctions
		$data['data']['category_name'] = $name;
		$data['_FIELD_TYPES']['category_name'] = 'todb';
		
		$catid = $sql->db_Insert('news_category', $data);
		
		return new xmlrpcresp( new xmlrpcval($postid, 'string')); // Return the id of the post just inserted into the DB. See mysql_insert_id() in the PHP manual.
	}
	else
	{
		return new xmlrpcresp
		(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** DELETE CATEGORY ***
 *************************
 */
$deleteCategory_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString)
		);
$deleteCategory_doc = "Delete a page from blog";
function deleteCategory($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	
	$blogid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	$cateid = $xmlrpcmsg->getParam(3)->scalarval();
	if (userLogin($username, $password, 'H') == true || userLogin($username, $password, '5') == true)
	{
		
		//23/10/2009 16.15.15 delete category with new methods
		$sql->db_Delete('news_category', 'category_id='.$cateid);
		
		return new	xmlrpcresp( new xmlrpcval(true, 'boolean'));
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** GET AUTHORS *******
 *************************
 */
$getAuthors_sig = array
		(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString));
$getAuthors_doc
		= 'Get the categories on the blog.';
function getAuthors($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	
	$blogid = $xmlrpcmsg->getParam(1)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, '5') == 1)
	{
		$structArray = array();
		$query = 'SELECT 
                      p.page_id,
                      u.user_id,
                      u.user_loginname,
                      u.user_name
               FROM `#page` AS p 
               LEFT JOIN `#user` AS u ON u.user_id = p.page_author 
               GROUP BY u.user_name';

		$sql->db_Select_gen($query);
		while ($row = $sql->db_Fetch())
		{
	
			$structArray[] = new xmlrpcval(array('user_id'=> new xmlrpcval($row['user_id'], 'string'), 'user_login'=>
		new xmlrpcval($row['user_loginname'], 'string'), 'display_name'=> new
		xmlrpcval($row['user_name'], 'string')), 'struct');
		}
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** GET CATEGORY LIST *
 *************************
 */
$getCategoryList_sig = array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString, $xmlrpcString));
		
$getCategoryList_doc = 'Get the categories on the blog.';
function getCategoryList($xmlrpcmsg)
{
    $sql = e107::getDb();
	
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, 'H') == 1)
	{
		$structArray = array();
		$query = 'SELECT n.* FROM `#news_category` AS n';

		$sql->db_Select_gen($query);
		while ($row = $sql->db_Fetch())
		{	
			$structArray[] = new xmlrpcval(array('categoryId'=> new xmlrpcval($row['category_id'], 'string'), 'categoryName'=> new xmlrpcval($row['category_name'], 'string')), 'struct');
		}
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** POST CATEGORIES ***
 *************************
 */
$setPostCategories_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcArray)
		);
$setPostCategories_doc = 'Set the categories on blog.';
function setPostCategories($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	$postid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	if (userLogin($username, $password, 'H') == true)
	{
		$content = $xmlrpcmsg->getParam(3);
		if ($content->arrayMem(0)->arraySize() > 0)
		{
			$content->arrayMem(0)->structMem('categoryId')->scalarval();
		}
		
		$data['data']['news_id'] = $postid;
		$data['_FIELD_TYPES']['news_id'] = 'int';
		$data['data']['news_category'] = $categories;
		$data['_FIELD_TYPES']['news_category'] = 'todb';
		
		$postid = $sql->db_Update('news', $data);
		
		$sql->db_Select_gen($query);
		
		return new xmlrpcresp( new xmlrpcval(true, 'boolean'));
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser +1, 'Login Failed');
	}
}
/*
 *************************
 ***** GET P CATEGORIES **
 *************************
 */
$getPostCategories_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString));
$getPostCategories_doc
		= 'Set the categories on blog.';
function getPostCategories($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	$postid = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	
	if (userLogin($username, $password, 'H') == true)
	{
		// get set post categories
		$query = 'SELECT 
                 n.*,
                 c.category_name,
                 c.category_id
              FROM `#news` AS n 
              LEFT JOIN `#news_category` AS c ON c.category_id = n.news_category 
              WHERE news_id ='.$postid.'
              LIMIT 1';
	
		$sql->db_Select_gen($query);
		while ($row = $sql->db_Fetch())
		{
			$structArray[] = new xmlrpcval(array('categoryName'=> new xmlrpcval($row['category_name'], 'string'), 'categoryId'=>
			new xmlrpcval($row['category_id'], 'string'), 'isPrimary'=> new	xmlrpcval('1', 'string')), 'struct');
		}
		
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}
/*
 *************************
 ***** GET TAGS **********
 *************************
 */
$getTags_sig = array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString));
$getTags_doc = 'Set the categories on blog.';
function getTags($xmlrpcmsg)
{
	$sql = e107::getDb();
	
	$blogid   = $xmlrpcmsg->getParam(0)->scalarval();
	$username = $xmlrpcmsg->getParam(1)->scalarval();
	$password = $xmlrpcmsg->getParam(2)->scalarval();
	
	if (userLogin($username, $password, 'H') == true)
	{
		// get set post categories
		$query = "SELECT 
                 GROUP_CONCAT( n.news_meta_keywords SEPARATOR ',') AS meta_keys
              FROM `#news` AS n 
              WHERE news_meta_keywords != '' ;";
		
		$sql->db_Select_gen($query);
		$row = $sql->db_Fetch();
		
		//explode data in array and remove duplicates
		$meta_tags = array();
		$meta_tags = explode(',', $row['meta_keys']);
		$meta_tags = array_unique( $meta_tags );
		
		foreach ($meta_tags as $key => $value)
		{
			$structArray[] = new xmlrpcval(array(
				'tag_id'   => new xmlrpcval($key, 'string'),
				'name'     => new xmlrpcval($value, 'string'),
				'count'    => new xmlrpcval('1', 'string'), //NOT SENSE IN e107 for now??
				'slug'     => new xmlrpcval('1', 'string'), //NOT SENSE IN e107 for now??
				'html_url' => new xmlrpcval('1', 'string'), //NOT SENSE IN e107 for now??
				'rss_url'  => new xmlrpcval('1', 'string') //NOT SENSE IN e107 for now??
				
				), 'struct');
		}
		
		return new xmlrpcresp( new xmlrpcval($structArray, 'array')); // Return type is struct[] (array of struct)
	}
	else
	{
		return new xmlrpcresp(0, $xmlrpcerruser + 1, 'Login Failed');
	}
}

//
//METHODS DECLARATION
//
$o = new xmlrpc_server_methods_container;
$a = array(
	'blogger.getUsersBlogs'		=> array('function'=>'getUsersBlogs','docstring'=> $getUsersBlogs_doc,'signature'=> $getUsersBlogs_sig),
	'metaWeblog.newPost'		=> array('function'=>'newPost', 'signature'=>$newPost_sig, 'docstring'=>$newPost_doc),
	'metaWeblog.editPost'		=> array('function'=>'editPost', 'signature'=>$editPost_sig, 'docstring'=>$editPost_doc),
	'metaWeblog.getPost'		=> array('function'=>'getPost', 'signature'=>$getPost_sig, 'docstring'=>$getPost_doc),
	'metaWeblog.getRecentPosts'	=> array('function'=>'getRecentPosts', 'signature'=>$getRecentPosts_sig, 'docstring'=>$getRecentPosts_doc),
	'metaWeblog.getCategories'	=> array('function'=>'getCategories', 'signature'=>$getCategories_sig, 'docstring'=>$getCategories_doc),
	'metaWeblog.newMediaObject'	=> array('function'=>'newMediaObject', 'signature'=>$newMediaObject_sig, 'docstring'=>$newMediaObject_doc),
	
// 	 'blogger.getUserInfo' => array('function' => 'getUserInfo', 'docstring' => 'Returns information about an author in the system.', 'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString)))*/
	
	'blogger.deletePost'		=> array('function'=>'deletePost', 'docstring'=>'Deletes a post.', 'signature'=>array(array($xmlrpcBoolean, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean))),
	'wp.getPage'				=> array('function'=>'getPage', 'signature'=>$getPage_sig, 'docstring'=>$getPage_doc),
	'wp.getPages'				=> array('function'=>'getPages', 'signature'=>$getPages_sig, 'docstring'=>$getPages_doc),
	'wp.newPage'				=> array('function'=>'newPage', 'signature'=>$newPage_sig, 'docstring'=>$newPage_doc),
	'wp.deletePage'				=> array('function'=>'deletePage', 'signature'=>$deletePage_sig, 'docstring'=>$deletePage_doc),
	'wp.editPage'				=> array('function'=>'editPage', 'signature'=>$editPage_sig, 'docstring'=>$editPage_doc),
	'wp.getPageList'			=> array('function'=>'getPageList', 'signature'=>$getPageList_sig, 'docstring'=>$getPageList_doc),
	'wp.getAuthors'				=> array('function'=>'getAuthors', 'signature'=>$getAuthors_sig, 'docstring'=>$getAuthors_doc),
	'wp.getCategories'			=>	array('function'=>'getCategories', 'signature'=>$getCategories_sig, 'docstring'=>$getCategories_doc),
	
	
		
	'wp.getTags' => array(
	'function' => 'getTags',
	'signature' => $getTags_sig,
	'docstring' => $getTags_doc
	),
	'wp.newCategory'=>array(
	'function'=>'newCategory',
	'signature'=>$newCategory_sig,
	'docstring'=>$newCategory_doc
	),
	'wp.deleteCategory'=>array('function'=>'deleteCategory',
	'signature'=>$deleteCategory_sig,
	'docstring'=>$deleteCategory_doc
	),
	'wp.uploadFile'=>array(
	'function'=>'newMediaObject',
	'signature'=>$newMediaObject_sig,
	'docstring'=>$newMediaObject_doc
	),
	/* TO BE IMPLEMENTED
	 'wp.suggestCategories' => array(
	 'function' => 'suggestCategories',
	 'signature' => $suggestCategories_sig,
	 'docstring' => $suggestCategories_doc
	 ),
	 'wp.getCommentCount' => array(
	 'function' => 'getCommentCount',
	 'signature' => $getCommentCount_sig,
	 'docstring' => $getCommentCount_doc
	 ),
	 'wp.getPostStatusList' => array(
	 'function' => 'getPostStatusList',
	 'signature' => $getPostStatusList_sig,
	 'docstring' => $getPostStatusList_doc
	 ),
	 'wp.getPageStatusList' => array(
	 'function' => 'getPageStatusList',
	 'signature' => $getPageStatusList_sig,
	 'docstring' => $getPageStatusList_doc
	 ),
	 'wp.getPageTemplates' => array(
	 'function' => 'getPageTemplates',
	 'signature' => $getPageTemplates_sig,
	 'docstring' => $getPageTemplates_doc
	 ),
	 'wp.getOptions' => array(
	 'function' => 'getOptions',
	 'signature' => $getOptions_sig,
	 'docstring' => $getOptions_doc
	 ),
	 'wp.setOptions' => array(
	 'function' => 'setOptions',
	 'signature' => $setOptions_sig,
	 'docstring' => $setOptions_doc
	 ),
	 'wp.getComment' => array(
	 'function' => 'getComment',
	 'signature' => $getComment_sig,
	 'docstring' => $getComment_doc
	 ),
	 'wp.getComments' => array(
	 'function' => 'getComments',
	 'signature' => $getComments_sig,
	 'docstring' => $getComments_doc
	 ),
	 'wp.deleteComment' => array(
	 'function' => 'deleteComment',
	 'signature' => $deleteComment_sig,
	 'docstring' => $deleteComment_doc
	 ),
	 'wp.editComment' => array(
	 'function' => 'editComment',
	 'signature' => $editComment_sig,
	 'docstring' => $editComment_doc
	 ),
	 'wp.newComment' => array(
	 'function' => 'newComment',
	 'signature' => $newComment_sig,
	 'docstring' => $newComment_doc
	 ),
	 'wp.getCommentStatusList' => array(
	 'function' => 'getCommentStatusList',
	 'signature' => $getCommentStatusList_sig,
	 'docstring' => $getCommentStatusList_doc
	 )*/
	'mt.getCategoryList'=>array(
	'function'=>'getCategoryList',
	'signature'=>$getCategoryList_sig,
	'docstring'=>$getCategoryList_doc
	),
	'mt.setPostCategories'=>array(
	'function'=>'setPostCategories',
	'signature'=>$setPostCategories_sig,
	'docstring'=>$setPostCategories_doc
	),
	'mt.getPostCategories'=>array(
	'function'=>'getPostCategories',
	'signature'=>$getPostCategories_sig,
	'docstring'=>$getPostCategories_doc
	)
	);
$s = new xmlrpc_server($a, false);
$s->setdebug(1);
$s->service();
// that should do all we need!
/*
 *********************************************
 ********** XML FUNCTIONS  *******************
 *********************************************
 */
//19/08/2009 13.36.16 unused!
function checkXmlElement($xml, $element)
{
	// DOMElement->getElementsByTagName() -- Gets elements by tagname
	// nodeValue : The value of this node, depending on its type.
	// Load XML File. You can use loadXML if you wish to load XML data from a string
	$objDOM = new DOMDocument();
	$objDOM->loadXML($xml);
	$objDOM->normalizeDocument();
	echo $objDOM;
	$found = 0;
	//elements member contains all data...
	$member = $objDOM->getElementsByTagName('struct')
;
	// for each note tag, parse the document and get values for
	// tasks and details tag.
	foreach ($member as $value)
	{
		if ($member->name == $element)
		{
			$found	= 1;
		}
	}
	if ($found == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
}
//check for element exitence with simplexml
function checkXmlElementS($string, $element)
{
	
	$found = 0;
	$xml = new SimpleXMLElement($string);
	//search with xpath the $element name
	$result = $xml->xpath('/value/struct/member/name');
	while (list(, $node) = each($result))
	{
		if ($node == $element)
		{
			$found = 1;
		}
	}
	if ($found == 1)
	{
		return true;
	}
	else	
	{
		return false;
	}
}
/*
 *********************************************
 ********** FROM WORDPRESS SOURCE ! **********
 *********************************************
 */
function get_date_from_gmt($string)
{
	// note: this only adds $time_difference to the given date
	preg_match('#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches);
	$string_time = gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
	$string_localtime = gmdate('Y-m-d H:i:s', $string_time + get_settings('gmt_offset') * 3600);
	return $string_localtime;
}
// computes an offset in seconds from an iso8601 timezone
function iso8601_timezone_to_offset($timezone)
{
	// $timezone is either 'Z' or '[+|-]hhmm'
	if ($timezone == 'Z')
	{
		$offset = 0;
	}
	else
	{
		$sign = (substr($timezone, 0, 1) == '+') ? 1 : - 1;
		$hours = intval(substr($timezone, 1, 2));
		$minutes = intval(substr($timezone, 3, 4)) / 60;
		$offset = $sign * 3600 * ($hours + $minutes);
	}
	return $offset;
}
function iso8601_to_datetime($date_string, $timezone = USER )
{
	if ($timezone == GMT)
	{
		preg_match('#([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(Z|[\+|\-][0-9]{2,4}){0,1}#', $date_string, $date_bits);
		if (! empty($date_bits[7]))
		{ // we have a timezone, so let's compute an offset
			$offset = iso8601_timezone_to_offset($date_bits[7]);

		}
		else
		{ // we don't have a timezone, so we assume user local timezone (not server's!)
			$offset = 3600 * get_settings('gmt_offset');
		}
		$timestamp = gmmktime($date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1]);
		$timestamp -= $offset;
		return gmdate('Y-m-d H:i:s', $timestamp);
	}
	elseif ($timezone == USER)
	{
		return preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(Z|[\+|\-][0-9]{2,4}){0,1}#', '$1-$2-$3 $4:$5:$6', $date_string);
	}
}




<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Alternate login
 *
 * $URL$
 * $Id$
 * 
 */

use e107\Database\QueryBuilder;

/**
 *	e107 Alternate authorisation plugin
 *
 *	@package	e107_plugins
 *	@subpackage	alt_auth
 *	@version 	$Id$;
 */

define('AA_DEBUG',FALSE);
define('AA_DEBUG1',FALSE);


//TODO convert to class constants (but may be more useful as globals, perhaps within a general login manager scheme)
$authConst = array(
	'AUTH_SUCCESS'       => -1,
	'AUTH_NOUSER'        => 1,
	'AUTH_BADPASSWORD'   => 2,
	'AUTH_NOCONNECT'     => 3,
	'AUTH_UNKNOWN'       => 4,
	'AUTH_NOT_AVAILABLE' => 5,
	'AUTH_NORESOURCE'    => 6,		// Used to indicate => for example => that a required PHP module isn't loaded
);

foreach($authConst as $def => $val)
{
	if(!defined($def))
	{
		define($def, $val);
	}
}

/**
 *	Methods used by a number of alt_auth classes.
 *	The login authorisation classes are descendants of this one.
 *	Admin functions also use it - a little extra overhead by including this file, but less of a problem for admin
 */
class alt_auth_base
{
	public function __construct()
	{
	}


	/**
	 *	Get configuration parameters for an authentication method
	 *
	 *	@param string $prefix - the method
	 *
	 *	@return array
	 */
	public function altAuthGetParams($prefix)
	{
		$sql = e107::getDb();

		$sql->createQueryBuilder()->select('*')->from('alt_auth')->where('auth_type', $prefix)->execute();
		$parm = array();
		while($row = $sql->fetch())
		{
			$parm[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
		}
		return $parm;
	}
}


class alt_login
{
	protected $e107;
	public $loginResult = false;

	public function __construct($method, &$username, &$userpass)
	{
		$this->e107 = e107::getInstance();
		$newvals=array();

		if ($method == 'none')
		{
			$this->loginResult = AUTH_NOCONNECT;
			return;
		}

		require_once(e_PLUGIN.'alt_auth/'.$method.'_auth.php');
		$_login = new auth_login;

		if(isset($_login->Available) && ($_login->Available === FALSE))
		{	// Relevant auth method not available (e.g. PHP extension not loaded)
			$this->loginResult = AUTH_NOT_AVAILABLE;
			return;
		}

		$login_result = $_login->login($username, $userpass, $newvals, FALSE);

		if($login_result === AUTH_SUCCESS )
		{
			require_once (e_HANDLER.'user_handler.php');
			require_once(e_HANDLER.'validator_class.php');

			if (MAGIC_QUOTES_GPC == FALSE)
			{
				$username = e107::getParser()->toDB($username);
			}
			$username = preg_replace("/\sOR\s|\=|\#/", "", $username);
			$username = substr($username, 0, e107::getPref('loginname_maxlength'));

			$aa_sql = e107::getDb('aa');
			$userMethods = new UserHandler;
			$db_vals = array('user_password' => $userMethods->HashPassword($userpass,$username));
			$xFields = array();					// Possible extended user fields
			
			// See if any of the fields need processing before save
			if (isset($_login->copyMethods) && count($_login->copyMethods))
			{
				foreach ($newvals as $k => $v)
				{
					if (isset($_login->copyMethods[$k]))
					{
						$newvals[$k] = $this->translate($_login->copyMethods[$k], $v);
						if (AA_DEBUG1) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth convert",$k.': '.$v.'=>'.$newvals[$k],FALSE,LOG_TO_ROLLING);
					}
				}
			}
			foreach ($newvals as $k => $v)
			{
				if (strpos($k,'x_') === 0)
				{	// Extended field
					$k = substr($k,2);
					$xFields['user_'.$k] = $v;
				}
				else
				{	// Normal user table
					if (strpos($k,'user_' !== 0)) $k = 'user_'.$k;			// translate the field names (but latest handlers don't need translation)
					$db_vals[$k] = $v;
				}
			}
			if (count($xFields))
			{	// We're going to have to do something with extended fields as well - make sure there's an object
				require_once (e_HANDLER.'user_extended_class.php');
				$ue = new e107_user_extended;

				// Column lists are built from data-derived field names. select()
				// keeps unknown expressions verbatim (it is fail-OPEN), so each
				// data-derived identifier is validated fail-closed here via
				// quoteColumn() (throws InvalidArgumentException on anything
				// outside the identifier grammar) before it reaches the query.
				$columns = array('u.user_id');
				foreach(array_keys($db_vals) as $col)
				{
					$columns[] = 'u.'.$col;
				}
				$columns[] = 'ue.user_extended_id';
				foreach(array_keys($xFields) as $col)
				{
					$columns[] = 'ue.'.$col;
				}

				$qb = $aa_sql->createQueryBuilder();
				foreach($columns as $col)
				{
					$qb->quoteColumn($col);		// fail-closed identifier validation
				}
				$qb->select($columns)
					->from('user', 'u')
					->leftJoin('user_extended', 'ue', $qb->expr()->compareColumns('ue.user_extended_id', 'u.user_id'));
				$this->applyLookupCriteria($qb, $username, FALSE, 'u.');
				if (AA_DEBUG) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth login","Query: {$qb->getSQL()}[!br!]".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
			}
			else
			{
				$qb = $aa_sql->createQueryBuilder();
				$qb->select('*')->from('user');
				$this->applyLookupCriteria($qb, $username, FALSE);
			}
			$row = $qb->fetchRow();
			if($row)
			{ // Existing user - get current data, see if any changes
				foreach ($db_vals as $k => $v)
				{
					if ($row[$k] == $v) unset($db_vals[$k]);
				}
				if (count($db_vals)) 
				{
					$newUser = array();
					$newUser['data'] = $db_vals;
					validatorClass::addFieldTypes($userMethods->userVettingInfo,$newUser);
					$newUser['WHERE'] = '`user_id`='.$row['user_id'];
					$qb = $aa_sql->createQueryBuilder()->update('user');
					$this->applyTypedEnvelope($qb, $aa_sql, 'user', $newUser, false);
					$qb->where('user_id', (int) $row['user_id'])->execute();
					if (AA_DEBUG1) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth login","User data update: ".print_r($newUser,TRUE),FALSE,LOG_TO_ROLLING);
				}
				foreach ($xFields as $k => $v)
				{
					if ($row[$k] == $v) unset($xFields[$k]);
				}
				if (AA_DEBUG1) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth login","User data read: ".print_r($row,TRUE)."[!br!]".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
				if (AA_DEBUG) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth login","User xtnd read: ".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
				if (count($xFields))
				{
					$xArray = array();
					$xArray['data'] = $xFields;
					if ($row['user_extended_id'])
					{
						$ue->addFieldTypes($xArray);		// Add in the data types for storage
						$xArray['WHERE'] = '`user_extended_id`='.intval($row['user_id']);
						if (AA_DEBUG) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth login","User xtnd update: ".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
						$qb = $aa_sql->createQueryBuilder()->update('user_extended');
						$this->applyTypedEnvelope($qb, $aa_sql, 'user_extended', $xArray, false);
						$qb->where('user_extended_id', (int) $row['user_id'])->execute();
					}
					else
					{	// Never been an extended user fields record for this user
						$xArray['data']['user_extended_id'] = $row['user_id'];
						$ue->addDefaultFields($xArray);		// Add in the data types for storage, plus any default values
						if (AA_DEBUG) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth login","Write new extended record".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
						$qb = $aa_sql->createQueryBuilder()->insert('user_extended');
						$this->applyTypedEnvelope($qb, $aa_sql, 'user_extended', $xArray, true);
						$qb->execute();
					}
				}
			}
			else
			{  // Just add a new user
				
				if (AA_DEBUG) $this->e107->admin_log->addEvent(10,debug_backtrace(),"DEBUG","Alt auth login","Add new user: ".print_r($db_vals,TRUE)."[!br!]".print_r($xFields,TRUE),FALSE,LOG_TO_ROLLING);
				if (!isset($db_vals['user_name'])) $db_vals['user_name'] = $username;
				if (!isset($db_vals['user_loginname'])) $db_vals['user_loginname'] = $username;
				if (!isset($db_vals['user_join'])) $db_vals['user_join'] = time();
				$db_vals['user_class'] = e107::getPref('initial_user_classes');
				if (!isset($db_vals['user_signature'])) $db_vals['user_signature'] = '';
				if (!isset($db_vals['user_prefs'])) $db_vals['user_prefs'] = '';
				if (!isset($db_vals['user_perms'])) $db_vals['user_perms'] = '';
				$userMethods->userClassUpdate($db_vals, 'userall');
				$newUser = array();
				$newUser['data'] = $db_vals;
				$userMethods->addNonDefaulted($newUser['data']); 
				validatorClass::addFieldTypes($userMethods->userVettingInfo,$newUser);
				
				$qb = $aa_sql->createQueryBuilder()->insert('user');
				$this->applyTypedEnvelope($qb, $aa_sql, 'user', $newUser, true);
				$insertOk = $qb->execute();
				$newID = ($insertOk !== FALSE) ? $aa_sql->lastInsertId() : FALSE;
				
				if ($newID !== FALSE)
				{
					if (count($xFields))
					{
						$xFields['user_extended_id'] = $newID;
						$xArray = array();
						$xArray['data'] = $xFields;

						e107::getUserExt()->addDefaultFields($xArray);		// Add in the data types for storage, plus any default values
						$qb = $aa_sql->createQueryBuilder()->insert('user_extended');
						$this->applyTypedEnvelope($qb, $aa_sql, 'user_extended', $xArray, true);
						$result = $qb->execute();
						if (AA_DEBUG) e107::getLog()->addEvent(10,debug_backtrace(),'DEBUG','Alt auth login',"Add extended: UID={$newID}  result={$result}",FALSE,LOG_TO_ROLLING);
					}
				}
				else
				{	// Error adding user to database - possibly a conflict on unique fields
					$this->e107->admin_log->addEvent(10,__FILE__.'|'.__FUNCTION__.'@'.__LINE__,'ALT_AUTH','Alt auth login','Add user fail: DB Error '.$aa_sql->getLastErrorText()."[!br!]".print_r($db_vals,TRUE),FALSE,LOG_TO_ROLLING);
					$this->loginResult = LOGIN_DB_ERROR;
					return;
				}
			}
			$this->loginResult = LOGIN_CONTINUE;
			return;
		}
		else
		{	// Failure modes
			switch($login_result)
			{
				case AUTH_NOCONNECT:
					$noconn = e107::getPref('auth_noconn');
					if(varset($noconn, TRUE))
					{
						$this->loginResult = LOGIN_TRY_OTHER;
						return;
					}
					$username=md5('xx_noconn_xx');
					$this->loginResult = LOGIN_ABORT;
					return;
				case AUTH_BADPASSWORD:
				case AUTH_NOUSER:
					$badpass = e107::getPref('auth_badpassword');
					if(varset($badpass, TRUE))
					{
						$this->loginResult = LOGIN_TRY_OTHER;
						return;
					}
					$userpass=md5('xx_badpassword_xx');
					$this->loginResult = LOGIN_ABORT;					// Not going to magically be able to log in!
					return;
			}
		}
		$this->loginResult = LOGIN_ABORT;			// catch-all just in case
		return;
	}


	/**
	 *	Apply the user-lookup WHERE predicates to a query builder with bound
	 *	values, mirroring the column-selection logic of
	 *	{@see userlogin::getLookupQuery()} (which builds the same predicates as an
	 *	inlined SQL string). The username is a bound parameter, not concatenated.
	 *
	 *	@param QueryBuilder $qb
	 *	@param string $username - as entered (already toDB()/preg cleaned upstream)
	 *	@param boolean|string $forceLogin - 'provider', or TRUE/FALSE
	 *	@param string $dbAlias - optional table-alias prefix (e.g. 'u.') for joins
	 *	@return void
	 */
	protected function applyLookupCriteria($qb, $username, $forceLogin, $dbAlias = '')
	{
		$pref = e107::getPref();

		$username = preg_replace("/\sOR\s|\=|\#/", "", $username);

		if($forceLogin === 'provider')
		{
			$qb->where($dbAlias.'user_xup', $username);
			return;
		}

		// 0: username only (default), 1: email only, 2: username or email
		$mode = (!$forceLogin && varset($pref['allowEmailLogin'], 0)) ? (int) $pref['allowEmailLogin'] : 0;

		if($mode === 1)
		{
			$qb->where($dbAlias.'user_email', $username);
		}
		elseif($mode === 2 && strpos($username, '@') !== false)
		{
			$qb->where($dbAlias.'user_loginname', $username)
				->orWhere($qb->expr()->eq($dbAlias.'user_email', $username));
		}
		else
		{
			$qb->where($dbAlias.'user_loginname', $username);
		}
	}


	/**
	 *	Apply an array-CRUD envelope's row to a query builder's SET list via the
	 *	field-typed writer, so the stored bytes stay byte-identical to the
	 *	deprecated array-form {@see e_db::insert()}/{@see e_db::update()} this
	 *	replaces. Mirrors the legacy per-column field-type resolution
	 *	({@see \e107\Database\ConnectionTrait::insert()} / {@see \e107\Database\ConnectionTrait::_prepareUpdateArg()}):
	 *	the envelope's own '_FIELD_TYPES' is authoritative when present; otherwise
	 *	the table's getFieldDefs() supply the types - and, for inserts, its
	 *	'_NOTNULL' columns backfill any value the row omits.
	 *
	 *	@param QueryBuilder $qb - builder already in insert()/update() state
	 *	@param e_db $db - connection handle (for getFieldDefs())
	 *	@param string $table - logical table name
	 *	@param array $env - array('data' => row[, '_FIELD_TYPES'][, '_NOTNULL'])
	 *	@param boolean $applyNotNull - backfill '_NOTNULL' defaults (insert only)
	 *	@return void
	 */
	private function applyTypedEnvelope($qb, $db, $table, array $env, $applyNotNull)
	{
		$data = isset($env['data']) ? $env['data'] : array();

		if(isset($env['_FIELD_TYPES']))
		{
			$fieldTypes = $env['_FIELD_TYPES'];
			$notNull    = isset($env['_NOTNULL']) ? $env['_NOTNULL'] : array();
		}
		else
		{	// Legacy path merges the table defs when the envelope carries no types
			$defs       = $db->getFieldDefs($table);
			$fieldTypes = (is_array($defs) && isset($defs['_FIELD_TYPES'])) ? $defs['_FIELD_TYPES'] : array();
			$notNull    = (is_array($defs) && isset($defs['_NOTNULL']))     ? $defs['_NOTNULL']     : array();
		}

		if($applyNotNull)
		{
			foreach($notNull as $f => $v)
			{
				if(!isset($data[$f])) { $data[$f] = $v; }
			}
		}

		foreach($data as $col => $val)
		{	// Absent column binds as the legacy default ('string' == 'str' == the
			// insert null-fallback: identity transform, PARAM_STR).
			$type = isset($fieldTypes[$col]) ? $fieldTypes[$col] : 'string';
			$qb->setTyped($col, $val, $type);
		}
	}


	// Function to implement copy methods
	public function translate($method, $word)
	{
		$tp = e107::getParser();
		switch ($method)
		{
			case 'bool1' :
				switch ($tp->ustrtoupper($word))
				{
					case 'TRUE' : return TRUE;
					case 'FALSE' : return FALSE;
				}
				return $word;
			case 'ucase' :
				return $tp->ustrtoupper($word);
			case 'lcase' :
				return $tp->ustrtolower($word);
			case 'ucfirst' :
				return ucfirst($word);						// TODO: Needs changing to utf-8 function
			case 'ucwords' :
				return ucwords($word);						// TODO: Needs changing to utf-8 function
			case 'none' :
				return $word;
		}
	}

}


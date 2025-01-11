<?php
	/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)

* Extended user field shortcode
*/

	/**
	 * @param $parm
	 * @usage {USER_EXTENDED=<field_name>.[text|value|icon|text_value].<user_id>}
	 * @example {USER_EXTENDED=gender.value.5}  will show the value of the extended field user_gender for user #5
	 * @return bool|string
	 */
	function user_extended_shortcode($parm)
	{

	//	$currentUser = e107::user();
		$tp = e107::getParser();
		$ue = e107::getUserExt();

		global $loop_uid, $e107, $sc_style;

		if(empty($parm))
		{
			trigger_error('{USER_EXTENDED} was sent an empty $parm',E_USER_NOTICE);
			return null;
		}

		$tmp = explode('.', $parm);

		$fieldname = trim($tmp[0]);
		$type = trim($tmp[1]);
		$user = varset($tmp[2], 0);

		if(isset($loop_uid) && $loop_uid === 0)
		{
			return '';
		}

		$key = $fieldname. '.' .$type;

		$sc_style['USER_EXTENDED']['pre'] = (isset($sc_style['USER_EXTENDED'][$key]['pre']) ? $sc_style['USER_EXTENDED'][$key]['pre'] : '');
		$sc_style['USER_EXTENDED']['post'] = (isset($sc_style['USER_EXTENDED'][$key]['post']) ? $sc_style['USER_EXTENDED'][$key]['post'] : '');

		$uid = (int) $user;

		if($uid === 0)
		{
			if(isset($loop_uid) && $loop_uid > 0)
			{
				$uid = $loop_uid;
			}
			else
			{
				$uid = USERID;
			}
		}

		$udata = e107::user($uid);

		$udata['user_class'] .= ($udata['user_class'] == '' ? '' : ',');
		$udata['user_class'] .= e_UC_PUBLIC. ',' .e_UC_MEMBER;

		if(!empty($udata['user_admin']))
		{
			$udata['user_class'] .= ','.e_UC_ADMIN;
		}


		// Need to pick up the 'miscellaneous' category - anything which isn't in a named category. Have to control visibility on a field by field basis
		// And I don't think this should apply to icons
		/**
		 *	@todo - must be a better way of picking up the 'Miscellaneous' category
		 */

	//	e107::coreLan('user');

		if(($type !== 'icon') && ($ue->getCategoryAttribute($fieldname, 'read') === false))
		{
			$fkeyApplic = $ue->getFieldAttribute('user_' . $fieldname, 'applicable');
			$fkeyRead   = $ue->getFieldAttribute('user_' . $fieldname, 'read');
			$fkeyStruct = $ue->getFieldAttribute('user_' . $fieldname, 'parms');

			$ret_cause = 0;

			if(!check_class($fkeyApplic, $udata['user_class']))
			{
				$ret_cause = 1;
			}

			if(!check_class($fkeyRead, $udata['user_class']))
			{
				$ret_cause = 2;
			}

			if(($fkeyRead == e_UC_READONLY && (!ADMIN && $udata['user_id'] != USERID)))
			{
				$ret_cause = 3;
			}

			if((!ADMIN && substr($fkeyStruct, -1) == 1	&& strpos($udata['user_hidden_fields'], '^user_' . $fieldname . '^') !== false && $uid != USERID))
			{
				$ret_cause = 4;
			}
			
			if(!empty($ret_cause))
			{
				return false;
			}
		}



		$ret = false;

		switch($type)
		{
			case 'text_value':
				$_value = user_extended_shortcode($fieldname. '.value.' .$user);

				if($_value)
				{
					$__pre = (isset($sc_style['USER_EXTENDED'][$key]['pre']) ? $sc_style['USER_EXTENDED'][$key]['pre'] : '');
					$__post = (isset($sc_style['USER_EXTENDED'][$key]['post']) ? $sc_style['USER_EXTENDED'][$key]['post'] : '');
					$_text = $ue->getFieldLabel($fieldname); // user_extended_shortcode($fieldname.".text");
					$_mid = (isset($sc_style['USER_EXTENDED'][$key]['mid']) ? $sc_style['USER_EXTENDED'][$key]['mid'] : ': ');
					$ret = $__pre.$_text.$_mid.$_value.$__post;
				}

				break;

			case 'text':
				if(isset($fieldname))
				{
					$ret = $ue->getFieldLabel($fieldname);
				}
				break;


			case 'icon':
				if(defined(strtoupper($fieldname).'_ICON'))
				{
					$ret = constant(strtoupper($fieldname).'_ICON');
				}
				elseif(is_readable(e_IMAGE."user_icons/user_{$fieldname}.png"))
				{
					$ret = "<img src='".e_IMAGE_ABS."user_icons/user_{$fieldname}.png' style='width:16px; height:16px' alt='' />";
				}
				elseif(is_readable(e_IMAGE."user_icons/{$fieldname}.png"))
				{
					$ret = "<img src='".e_IMAGE_ABS."user_icons/{$fieldname}.png' style='width:16px; height:16px' alt='' />";
				}
			break;


			case 'value':
				$fullField = 'user_'.$fieldname;
				$uVal = isset($fieldname, $udata[$fullField]) ?  str_replace(chr(1), '', $udata[$fullField]) : '';

				if(!empty($uVal))
				{
					$ret = $ue->renderValue($uVal, $fullField);

					if(!empty($ret))
					{
						$ret = $tp->toHTML($ret, TRUE, 'no_make_clickable', "class:{$udata['user_class']}");
					}
				}
				elseif(!isset($udata[$fullField]))
				{
				//	trigger_error($fullField. ' is not defined: '.print_r($udata, true), E_USER_NOTICE);
				}

			break;

				// code to be executed if n is different from all labels;
		}




		return $ret;

	}
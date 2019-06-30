<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once('class2.php');
e107::coreLan('search');
e107_require(e_HANDLER.'search_class.php');

if (!check_class($pref['search_restrict'])) 
{
	require_once(HEADERF);
	$ns->tablerender(LAN_SEARCH_20, "<div style='text-align: center'>".LAN_SEARCH_21."</div>");
	require_once(FOOTERF);
	exit;
}

if (isset($_GET['t']) && is_numeric($_GET['t']))
{
  switch ($_GET['t'])
  {
	case '0' : 
	  $_GET['t'] = 'news';
	  break;
	case 1 :
	  $_GET['t'] = 'comments';
	  break;
	case  2 :
	  $_GET['t'] = 'users';
	  break;
	case 3 :
	  $_GET['t'] = 'downloads';
	  break;
	case 4 :
	  $_GET['t'] = 'pages';
	  break;
  }
}


class search extends e_shortcode
{
	
	private $search_prefs = array();
	private $search_info = array();
	private $auto_order = 1000;
	private $enhanced = false;
	private $query = null;
	private $doSearch = false;
	private $result_flag = 0; // same as 'from'
	private $message = '';
	public 	$template = array();
	private $enhancedTypes = array(
								'in' => LAN_SEARCH_24,
								'ex' => LAN_SEARCH_25,
								'ep' => LAN_SEARCH_26,
								'be' => LAN_SEARCH_27
							);
	
	function __construct()
	{
		$this->search_prefs = e107::getConfig('search')->getPref();		
		$this->search_info 	= $this->searchConfig();

		
		if(deftrue('BOOTSTRAP'))
		{
			$tmp 				= e107::getCoreTemplate('search','form');
			$SEARCH_TOP_TABLE 	= $tmp['start'];
			$SEARCH_BOT_TABLE 	= $tmp['end'];
			$SEARCH_CATS		= $tmp['category'];
			$SEARCH_TYPE		= $tmp['type'];
			$SEARCH_ADV			= $tmp['advanced'];
			$SEARCH_ENHANCED	= $tmp['enhanced'];
			$SEARCH_ADV_COMBO	= $tmp['advanced-combo'];
			
			$this->template = $tmp;
		
			
			
			unset($tmp);
		}
		else
		{
			$SEARCH_TOP_TABLE = '';
			$SEARCH_BOT_TABLE = '';
			$SEARCH_CATS = '';
			$SEARCH_TYPE = '';
			$SEARCH_ADV = '';
			$SEARCH_ENHANCED = '';
			$SEARCH_ADV_COMBO = '';

			if (file_exists(THEME."templates/search_template.php"))
			{
				require(THEME."templates/search_template.php");
			}
			elseif (file_exists(THEME."search_template.php"))
			{
				require(THEME."search_template.php");
			} 
			else 
			{
				require(e_CORE."templates/search_template.php");
			}
			
			$SEARCH_TOP_TABLE .= "{SEARCH_ENHANCED}";
			
			$tmp = array();
			
			$tmp['start']  			= $SEARCH_TOP_TABLE ;
			$tmp['end'] 			= $SEARCH_BOT_TABLE ;
			$tmp['category'] 		= $SEARCH_CATS;
			$tmp['type'] 			= $SEARCH_TYPE;
			$tmp['advanced'] 		= $SEARCH_ADV;
			$tmp['enhanced'] 		= $SEARCH_ENHANCED;
			$tmp['advanced-combo'] 	= $SEARCH_ADV_COMBO;
			
			$this->template = $tmp;
		}





		if(e_AJAX_REQUEST)
		{
			if(vartrue($_POST['t']))
			{
				echo $this->sc_search_advanced_block($_POST['t']);
			}
			
			exit;
		}

	}

	function getPrefs()
	{
		return $this->search_prefs;	
	}


	function getConfig()
	{
		return $this->search_info;	
	}
	
	// Shortcodes  -----------------------
	
	function sc_search_main($parm = '')
	{
		$tp = e107::getParser();
		$value = isset($_GET['q']) ? $tp->post_toForm($_GET['q']) : "";


		
		$text = "<div class='input-group'>
		<input class='tbox form-control m_search' type='text' id='q' name='q' size='35' value='".$value."' maxlength='50' />
		<div class='input-group-btn'>
		<button class='btn btn-primary' type='submit' name='s' value='1' >".$tp->toGlyph('fa-search',false)."</button>
		<button class='btn btn-primary dropdown-toggle' tabindex='-1' data-toggle='dropdown' type='button'>
		";

		if(BOOTSTRAP !== 4)
		{
			$text .= "<span class='caret'></span></button>";
		}
		
		$text .= '<ul class="dropdown-menu pull-right">
          <li><a class="e-expandit" href="#" data-target="search-advanced,search-enhanced"><small>'.LAN_SEARCH_202.'</small></a></li>
        </ul>';
		
		$text .= "
		</div>
		
		</div>
		<input type='hidden' name='r' value='0' />";
		
		return $text;		
		
	}

	function sc_search_main_searchfield($parm='')
	{
		$tp = e107::getParser();
		$value = isset($_GET['q']) ? $tp->post_toForm($_GET['q']) : "";
		return "<input class='tbox form-control m_search' type='text' id='q' name='q' size='35' value='".$value."' maxlength='50' />";	
	}	
	
	function sc_search_main_submit($parm='')
	{
		return "<input class='btn btn-primary button' type='submit' name='s' value='".LAN_SEARCH."' />
		<input type='hidden' name='r' value='0' />";
	}
	
	function sc_enhanced_icon($parm='')
	{
		return  "<img src='".e_IMAGE_ABS."generic/search_basic.png' style='width: 16px; height: 16px; vertical-align: top' alt='".LAN_SEARCH_23."' title='".LAN_SEARCH_23."' onclick=\"expandit('en_in'); expandit('en_ex'); expandit('en_ep'); expandit('en_be')\"/>";
	}
	
	function sc_search_main_checkall($parm='')
	{
		 if($this->search_prefs['selector'] == 1) 
		 {
		 	return "<input class='btn btn-default btn-secondary button' type='button' name='CheckAll' value='".LAN_SEARCH_1."' onclick='checkAll(this);' />";
		 }
	}

	function sc_search_main_uncheckall($parm='')
	{
		 if($this->search_prefs['selector'] == 1) 
		 {
		 	return "<input class='btn btn-default btn-secondary button' type='button' name='UnCheckAll' value='".LAN_SEARCH_2."' onclick='uncheckAll(this); uncheckG();' />";
		 }	
	}
	
	function sc_search_type_sel($parm='')
	{
		return e107::getForm()->radio_switch('adv', vartrue($_GET['adv']), LAN_SEARCH_30, LAN_SEARCH_29, array('class'=>'e-expandit','reverse'=>1, 'data-target'=>'search-advanced'));
		
		
		
	//	return "<input type='radio' name='adv' value='0' ".(vartrue($_GET['adv']) ? "" : "checked='checked'")." /> ".LAN_SEARCH_29."&nbsp;
	//	<input type='radio' name='adv' value='1' ".(vartrue($_GET['adv']) ? "checked='checked'" : "" )." /> ".LAN_SEARCH_30;
	}
	
	function sc_search_dropdown($parm = '')
	{
		return $this->selectElement('dropdown');
	}
	
	function sc_search_main_checkboxes($parm = '')
	{
		return $this->selectElement('checkboxes');
	}
	
	function sc_search_message($parm = '')
	{
		return $this->message;	
	}

	function sc_search_form_url($parm='')
	{
		return e107::getUrl()->create('search');
	}



	// -----------------------



	private function selectElement($parm)
	{
		// standard search config
		$dropdown = '';
		$PRE_CHECKBOXES = '';
		$POST_CHECKBOXES = '';

		$search_count = count($this->search_info);
		$google_id = $search_count + 1;


		if ($this->search_prefs['selector'] == 2) 
		{
			$dropdown = "<select name='t' id='t' class='tbox form-control e-ajax' data-src='".e_SELF."' data-target='search-advanced' >";
			
			if ($this->search_prefs['multisearch']) 
			{
				$dropdown .= "<option value='all'>".LAN_SEARCH_22."</option>";
			}
		} 
		else 
		{
		  $checkboxes = '';
		}
		
		foreach($this->search_info as $key => $value) 
		{
			if ($this->search_prefs['selector'] == 2) 
			{
				$sel = (isset($this->searchtype[$key]) && $this->searchtype[$key]) ? " selected='selected'" : "";
			} 
			else 
			{
				$sel = (isset($this->searchtype[$key]) && $this->searchtype[$key]) ? " checked='checked'" : "";
			}
			
			$google_js = check_class($this->search_prefs['google']) ? "onclick=\"uncheckG();\" " : "";
			
			if ($this->search_prefs['selector'] == 2) 
			{
				$dropdown .= "<option value='".$key."' ".$sel.">".$value['qtype']."</option>";
			} 
			else if ($this->search_prefs['selector'] == 1) 
			{
				$checkboxes .= $PRE_CHECKBOXES."<input ".$google_js." type='checkbox' name='t[".$key."]' ".$sel." />".$value['qtype'].$POST_CHECKBOXES;
			} 
			else 
			{
				$checkboxes .= $PRE_CHECKBOXES."<input type='radio' name='t' value='".$key."' ".$sel." />".$value['qtype'].$POST_CHECKBOXES;
			}
		}
		
		if (check_class($this->search_prefs['google'])) 
		{
			if ($this->search_prefs['selector'] == 2) 
			{
				$dropdown .= "<option value='".$google_id."'>Google</option>";
			} 
			else if 
			($this->search_prefs['selector'] == 1)  //FIXME PRE_CHECKBOXES and POST_CHECKBOXES
			{
				$checkboxes .= $PRE_CHECKBOXES."<input id='google' type='checkbox' name='t[".$google_id."]' onclick='uncheckAll(this)' />Google".$POST_CHECKBOXES;
			} 
			else 
			{
				$checkboxes .= $PRE_CHECKBOXES."<input id='google' type='radio' name='t' value='".$google_id."' />Google".$POST_CHECKBOXES;
			}
		}
		
		if ($this->search_prefs['selector'] == 2) 
		{
			$dropdown .= "</select>";
		}
		
		if($parm == 'dropdown')
		{
			return $dropdown;	
		}
		else 
		{
			return $checkboxes;
		}
	
		
	}

	function sc_search_enhanced()
	{
		
		$tp = e107::getParser();
		
		$text = '';
		$var = array();
		
		foreach ($this->enhancedTypes as $en_id => $ENHANCED_TEXT) 
		{
			$var['ENHANCED_TEXT'] 		= $ENHANCED_TEXT;
			$var['ENHANCED_DISPLAY_ID'] = "en_".$en_id;
			$var['ENHANCED_FIELD'] 		= "<input class='tbox form-control' type='text' id='".$en_id."' name='".$en_id."' size='35' value='".$tp->post_toForm($_GET[$en_id])."' maxlength='50' />";
		
			$text .= $tp->simpleParse($this->template['enhanced'], $var);
		}
		
		return $text;
	}


	function sc_enhanced_display()
	{
		return ($this->enhanced !== true) ?  "style='display: none'" : "" ;
	}

	function sc_search_advanced($parm='')
	{
		$hiddenBlock = (!empty($_GET['t'])) ? "" : "class='e-hideme'";
		$text = "<div {$hiddenBlock} id='search-advanced' >";
		$text .= $this->sc_search_advanced_block(vartrue($_GET['t']));
		$text .= "</div>";
		return $text;

	}
		
	private function sc_search_advanced_block($parm='')
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$sql2 = e107::getDb('search');
		
			
		if(!$parm)
		{
			return '';
		}	

		$text = '';


		if (isset($this->search_info[$parm]['advanced'])) 
		{

			if(is_array($this->search_info[$parm]['advanced']))
			{
				$advanced  = ($this->search_info[$parm]['advanced']);
			}
			elseif(isset($this->search_info[$parm]['advanced']))
			{
				require($this->search_info[$parm]['advanced']); 
				
			}
			
			$vars = array();
			
			
			
			foreach ($advanced as $adv_key => $adv_value) 
			{
				if ($adv_value['type'] == 'single') 
				{
					$vars['SEARCH_ADV_TEXT'] = $adv_value['text'];
					$text .= $tp->simpleParse($this->template['advanced-combo'], $vars);
				} 
				else 
				{
					if ($adv_value['type'] == 'dropdown') 
					{
						$vars['SEARCH_ADV_A'] = $adv_value['text'];
						$vars['SEARCH_ADV_B'] = "<select name='".$adv_key."' class='tbox form-control'>";
						
						foreach ($adv_value['list'] as $list_item) 
						{
							$vars['SEARCH_ADV_B'] .= "<option value='".$list_item['id']."' ".($_GET[$adv_key] == $list_item['id'] ? "selected='selected'" : "").">".$list_item['title']."</option>";
						}
						$vars['SEARCH_ADV_B'] .= "</select>";
					} 
					else if ($adv_value['type'] == 'date') 
					{
						$vars['SEARCH_ADV_A'] = $adv_value['text'];
						$vars['SEARCH_ADV_B'] = "
						
						<div class='form-inline'>
						<select id='on' name='on' class='tbox form-control '>
						<option value='new' ".($_GET['on'] == 'new' ? "selected='selected'" : "").">".LAN_SEARCH_34."</option>
						<option value='old' ".($_GET['on'] == 'old' ? "selected='selected'" : "").">".LAN_SEARCH_35."</option>
						</select>&nbsp;
					
						<select id='time' name='time' class='tbox form-control '>";
						
						$time = array(LAN_SEARCH_36 => 'any', LAN_SEARCH_37 => 86400, LAN_SEARCH_38 => 172800, LAN_SEARCH_39 => 259200, LAN_SEARCH_40 => 604800, LAN_SEARCH_41 => 1209600, LAN_SEARCH_42 => 1814400, LAN_SEARCH_43 => 2628000, LAN_SEARCH_44 => 5256000, LAN_SEARCH_45 => 7884000, LAN_SEARCH_46 => 15768000, LAN_SEARCH_47 => 31536000, LAN_SEARCH_48 => 63072000, LAN_SEARCH_49 => 94608000);
						
						foreach ($time as $time_title => $time_secs) 
						{
							$vars['SEARCH_ADV_B'] .= "<option value='".$time_secs."' ".($_GET['time'] == $time_secs ? "selected='selected'" : "").">".$time_title."</option>";
						}
						
						$vars['SEARCH_ADV_B'] .= "</select>
						</div>";
					} 
					else if ($adv_value['type'] == 'author') 
					{
						$vars['SEARCH_ADV_A'] = $adv_value['text'];
						$vars['SEARCH_ADV_B'] = e107::getForm()->userpicker($adv_key."_name",$adv_key,$_GET[$adv_key]);
					} 
					else if ($adv_value['type'] == 'dual') 
					{
						$vars['SEARCH_ADV_A'] = $adv_value['adv_a'];
						$vars['SEARCH_ADV_B'] = $adv_value['adv_b'];
					}
			
					$text .= $tp->simpleParse($this->template['advanced'], $vars);
				}
			}
	
			
		} 
		else 
		{
			$_GET['adv'] = 0;
		}
		
		
		return $text;
	}
				
				
			
			
		

	// -------------
	
	
	
	function searchPrefs()
	{
		return $this->search_prefs;	
	}


	
	function search_info($id, $type, $plug_require=null, $info='') 
	{
		$tp = e107::getParser();
		
		if (check_class($this->search_prefs[$type.'_handlers'][$id]['class'])) 
		{
	//		echo "<br />type = ".$this->search_prefs[$type.'_handlers'][$id]['class'];
			
	//		print_a($this->search_prefs);
			
			if ($plug_require) 
			{
				$search_info = array();
				require_once($plug_require);
				$ret = $search_info[0];
			} 
			else 
			{
				$ret = $info;
			}
			
			if($obj = e107::getAddon($id,'e_search'))
			{
				$obj->setParams($_GET);

				if(!$ret = $obj->config())
				{
					return false;
				}	


				$ret['qtype'] = $ret['name'];
				
				if(!isset($ret['id']))
				{
					$ret['id'] = $ret['name'];	
				}
				
				$ret['weights'] = array_values($ret['search_fields']);
				$ret['search_fields'] = array_keys($ret['search_fields']);
	
			}	
			
			
			$ret['chars'] 			= $this->search_prefs[$type.'_handlers'][$id]['chars'];
			$ret['results'] 		= $this->search_prefs[$type.'_handlers'][$id]['results'];
			$ret['pre_title'] 		= $this->search_prefs[$type.'_handlers'][$id]['pre_title'];
			$ret['pre_title_alt'] 	= $tp -> toHTML($this->search_prefs[$type.'_handlers'][$id]['pre_title_alt']);
		//	$ret['order'] 			= (isset($this->search_prefs[$type.'_handlers'][$id]['order']) && $this->search_prefs[$type.'_handlers'][$id]['order']) ? $this->search_prefs[$type.'_handlers'][$id]['order'] : $this->auto_order;
			
			$this->auto_order++;
			
			return $ret;
		} 
		else 
		{
			return false;
		}
	}
	


	
	// Get Core and Plugin search configurations
	function searchConfig()
	{
		
		
		//core search routines
		
		$search_info = array();
		
		/*
		if ($search_info['news'] = $this->search_info('news', 'core', false, array('sfile' => e_HANDLER.'search/search_news.php', 'qtype' => LAN_SEARCH_98, 'refpage' => 'news.php', 'advanced' => e_HANDLER.'search/advanced_news.php', 'id' => 'news'))) {
		   //	$search_id++;
		} else {
			unset($search_info['news']);
		}
		*/
		if(e107::getConfig('core')->get('comments_disabled')!=1)  // Only when comments are enabled.
		{
			if ($search_info['comments'] = $this->search_info('comments', 'core', false, array('sfile' => e_HANDLER.'search/search_comment.php', 'qtype' => LAN_COMMENTS, 'refpage' => 'comment.php', 'advanced' => e_HANDLER.'search/advanced_comment.php', 'id' => 'comment'))) {
			   //	$search_id++;
			   $search_info['comments']['listorder'] = $this->search_prefs['core_handlers']['comments']['order'];
			} else {
				unset($search_info['comments']);
			}
		}
		
		
		if(e107::getConfig('core')->get('user_reg')==1) // Only when user-registration is enabled.
		{
			if ($search_info['users'] = $this->search_info('users', 'core', false, array('sfile' => e_HANDLER.'search/search_user.php', 'qtype' => LAN_140, 'refpage' => 'user.php', 'advanced' => e_HANDLER.'search/advanced_user.php', 'id' => 'user'))) {
				//	$search_id++;
				$search_info['users']['listorder']  = $this->search_prefs['core_handlers']['users']['order'];

			} else {
				unset($search_info['users']);
			}
		}
		
	/*
		if ($search_info['pages'] = $this->search_info('pages', 'core', false, array('sfile' => e_HANDLER.'search/search_pages.php', 'qtype' => LAN_418, 'refpage' => 'page.php', 'advanced' => e_HANDLER.'search/advanced_pages.php', 'id' => 'pages'))) {
		   //	$search_id++;
		} else {
			unset($search_info['pages']);
		}
	*/
		 $e_searchList = e107::getConfig('core')->get('e_search_list');
		
		
		//plugin search routines    // plugin folder is used as the search key. ie. $_GET['t'] = 'chatbox';
		foreach ($this->search_prefs['plug_handlers'] as $plug_dir => $active) 
		{
			if(isset($search_info[$plug_dir]))
			{
				continue;
			}
			
			if (in_array($plug_dir,$e_searchList) && is_readable(e_PLUGIN.$plug_dir."/e_search.php"))
			{
				if ($search_info[$plug_dir] = $this->search_info($plug_dir, 'plug', e_PLUGIN.$plug_dir."/e_search.php"))
				{
					$search_info[$plug_dir]['listorder'] = $active['order'];
				  //	$search_id++;
				}
				else
				{
					unset($search_info[$plug_dir]);
				}


			}
			
			
		}
		
		// order search routines


		 $search_info = $this->array_sort($search_info, 'listorder', SORT_ASC);
		 $this->search_info = $search_info;

		if(e_DEBUG)
		{
	//		echo e107::getMessage()->addDebug(print_a($this->search_info,true))->render();
		}

		 return $search_info;
	}	




	// determine areas being searched
	public function searchType()
	{
		$searchtype = array();	
			
		if (!$this->search_prefs['user_select'] && $_GET['r'] < 1) 
		{
			foreach($this->search_info as $key => $value) 
			{
				$searchtype[$key] = true;
			}
		}
		 else 
		 {
			if (isset($_GET['t'])) 
			{
				if (is_array($_GET['t'])) 
				{
					$searchtype = $_GET['t'];
				} 
				else 
				{
					$searchtype[$_GET['t']] = true;
				}
			} 
			else 
			{
				if (isset($_GET['ref'])) 
				{
					foreach($this->search_info as $key => $value) 
					{
						if ($value['id'] == $_GET['ref']) 
						{
							$searchtype[$key] = true;
							$_GET['t'] = $key;
						}
					}
				} 
				else if (e_QUERY) 
				{
					if (isset($_SERVER['HTTP_REFERER'])) 
					{
						if (!$refpage = substr($_SERVER['HTTP_REFERER'], (strrpos($_SERVER['HTTP_REFERER'], "/")+1))) 
						{
							$refpage = "index.php";
						}
					} 
					else 
					{
						$refpage = "";
					}
		
					foreach($this->search_info as $key=>$value) 
					{
						if ($value['refpage']) 
						{
							if (strpos($refpage, $value['refpage']) !== FALSE) 
							{
								$searchtype[$key] = true;
								$_GET['t'] = $key;
							}
						}
					}
				}
		
				if (!isset($this->searchtype) && isset($this->query)) 
				{
					if ($this->search_prefs['multisearch']) 
					{
						$searchtype['all'] = true;
					} 
					else 
					{
						$searchtype[0] = true;
					}
				}
			}
		}	
		
		$this->searchtype = $searchtype;
		
		return $searchtype;
	}





	function array_sort($array, $column, $order = SORT_DESC) 
	{
		$i = 0;
		foreach($array as $info) {
			$sortarr[] = $info[$column];
			$i++;
		}
	 	array_multisort($sortarr, $order, $array, $order);
		return($array);
	}	
	
	
	
	
	function renderResults()
	{

		global $query, $search_prefs, $pre_title, $search_chars, $search_res, $result_flag, $advanced_caption;
		
		$ns = e107::getRender();

		$tp = e107::getParser();
	
		$con = e107::getDateConvert(); // BC Fix

        $sch = new e_search; // BC Fix

		$query = $this->query;

		
		$_GET['q'] = rawurlencode($_GET['q']);
		$_GET['t'] = preg_replace('/[^\w\-]/i', '', $_GET['t']);
		
		$search_prefs	= $this->search_prefs;
		$result_flag	= $this->result_flag;
		
		foreach ($this->search_info as $key => $a) 
		{
			if (isset($this->searchtype[$key]) || isset($this->searchtype['all'])) 
			{
				
				$text = "";

				//if (file_exists($this->search_info[$key]['sfile'])) 
				{
					$pre_title 		= ($this->search_info[$key]['pre_title'] == 2) ? $this->search_info[$key]['pre_title_alt'] : $this->search_info[$key]['pre_title'];
					$search_chars 	= $this->search_info[$key]['chars'];
					$search_res 	= $this->search_info[$key]['results'];
			
					if(!empty($this->search_info[$key]['sfile']) && file_exists($this->search_info[$key]['sfile'])) // Legacy
					{
						$text .= '<div class="search-block">';
						require_once($this->search_info[$key]['sfile']);
						$text .= '</div>';
					}
					else// New v2 standard. @see chatbox_menu/e_search.php
					{
						
						$className = $key."_search";
						
						if(!class_exists($className))
						{
							continue;
						}
						
						$obj = new $className($this->query);
						
						$where = (method_exists($obj,'where')) ? $obj->where($_GET) : "";
						
						$ps = $obj->parsesearch($this->search_info[$key]['table'], $this->search_info[$key]['return_fields'], $this->search_info[$key]['search_fields'], $this->search_info[$key]['weights'], 'self', varset($this->search_info[$key]['no_results'],"<div class='alert alert-danger'>".LAN_198."</div>"), $where , $this->search_info[$key]['order']);

						if(e_DEBUG)
						{
						//	echo e107::getMessage()->addDebug(print_a($this->search_info,true))->render();// "DEBUG: Order is missing";

						}

					//	print_a($ps);

						$text .= '<ul id="search-results" class="list-unstyled search-block">';
						$text .= $ps['text'];
						$text .= '</ul>';
						$results = $ps['results'];	
						
					}
					
					
				//	$parms = $results.",".$search_res.",".$_GET['r'].",".e_REQUEST_SELF."?q=".$_GET['q']."&t=".$key."&r=[FROM]";
					
					$nextprev = array(
								'total'			=> $results,
								'amount'		=> intval($search_res),
								'current'		=> intval($_GET['r']),
								'url'			=> urldecode(e_REQUEST_SELF."?q=".$_GET['q']."&t=".$key."&r=[FROM]"),
							//	'caption'		=> false,
								'tmpl_prefix'	=>'default'
					);
					
					$npParms = http_build_query($nextprev,false,'&');
					
					$core_parms = array('r' => '', 'q' => '', 't' => '', 's' => '');
					foreach ($_GET as $pparm_key => $pparm_value) 
					{
						$temp = preg_replace('/[^\w_]/i','',$pparm_key);
						$temp1 = preg_replace('/[^\w_ +]/i','',$pparm_value);		// Filter 'non-word' charcters in search term
						if (($temp == $pparm_key) && !isset($core_parms[$pparm_key])) 
						{
						//	$parms .= "&".$pparm_key."=".$temp1; //FIXME Unused
						}
					}
					if ($results > $search_res) 
					{
						$nextprev = ($results > $search_res) ? $tp -> parseTemplate("{NEXTPREV={$npParms}}") : "";
						$text .= "<div class='nextprev search form-inline'>".$nextprev."</div>";
					}
					if ($results > 0) 
					{
						$res_from = $_GET['r'] + 1;
						$res_to = ($_GET['r'] + $search_res) > $results ? $results : ($_GET['r'] + $search_res);
						$res_display = $res_from." - ".$res_to." ".LAN_SEARCH_12." ".$results;
					} 
					else 
					{
						$res_display = "";
					}
					
					$ns->tablerender(LAN_SEARCH_11." ".$res_display." ".LAN_SEARCH_13." ".(isset($_GET[$advanced_caption['id']]) ? $advanced_caption['title'][$_GET[$advanced_caption['id']]] : $this->search_info[$key]['qtype']), $text, 'search_result');
				}

			}

		}

	}
		
		
		
	function magic_search($data) 
	{
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = $this->magic_search($value);
			} else {
				$data[$key] = stripslashes($value);
			}
		}
		return $data;
	}
	
		
	function searchQuery()
	{
		global $perform_search;
		$tp = e107::getParser();
		$sql = e107::getDb();

		
		if (isset($_GET['q']) || isset($_GET['in']) || isset($_GET['ex']) || isset($_GET['ep']) || isset($_GET['beg'])) 
		{
			if (MAGIC_QUOTES_GPC == true) 
			{
				$_GET = $this->magic_search($_GET);
			}
			
			$full_query = $tp->filter($_GET['q']);
			
			if ($_GET['in']) 
			{
				$en_in = explode(' ', $_GET['in']);
				foreach ($en_in as $en_in_key) 
				{
					$full_query .= " +".$tp->filter($en_in_key);
				}
				$this->enhanced = true;
			}
			if ($_GET['ex']) 
			{
				$en_ex = explode(' ', $_GET['ex']);
				foreach ($en_ex as $en_ex_key) 
				{
					$full_query .= " -".$tp->filter($en_ex_key);
				}
				$this->enhanced = true;
			}
			if ($_GET['ep']) 
			{
				$full_query .= " \"".$tp->filter($_GET['ep'])."\"";
				$this->enhanced = true;
			}
			if ($_GET['be']) 
			{
				$en_be = explode(' ', $_GET['be']);
				foreach ($en_be as $en_be_key) 
				{
					$full_query .= " ".$tp->filter($en_be_key)."*";
				}
				$this->enhanced = true;
			}
		
			if (isset($_GET['r']) && !is_numeric($_GET['r'])) 
			{
				$perform_search = false;
				$this->message = LAN_SEARCH_201;
				$this->result_flag = false;
			} 
			else if (strlen($full_query) == 0) 
			{
				$perform_search = false;
				$this->message = LAN_SEARCH_201;
			} 
			elseif (strlen($full_query) < ($char_count = ($this->search_prefs['mysql_sort'] ? 4 : 3))) 
			{
				$perform_search = false;
				$this->message = str_replace('[x]', $char_count, LAN_417);
			} 
			elseif ($this->search_prefs['time_restrict']) 
			{
				$time = time() - $this->search_prefs['time_secs'];
				$query_check = $tp->toDB($full_query);
				$ip = e107::getIPHandler()->getIP(FALSE);
				
				if ($sql->select("tmp", "tmp_ip, tmp_time, tmp_info", "tmp_info LIKE 'type_search%' AND tmp_ip='".$ip."'")) 
				{
					$row = $sql->fetch();
					if (($row['tmp_time'] > $time) && ($row['tmp_info'] != 'type_search '.$query_check)) 
					{
						$perform_search = false;
						$this->message = LAN_SEARCH_17.$this->search_prefs['time_secs'].LAN_SEARCH_18;
					} 
					else 
					{
						$sql->update("tmp", "tmp_time='".time()."', tmp_info='type_search ".$query_check."' WHERE tmp_info LIKE 'type_search%' AND tmp_ip='".$ip."'");
					}
				} 
				else 
				{
					$sql->insert("tmp", "'".$ip."', '".time()."', 'type_search ".$query_check."'");
				}
			}
			

			
			$this->query = trim($full_query);

			if ($this->query)
			{
				$this->result_flag = intval($_GET['r']);
				$this->doSearch = true;
			}


			return $this->query;
		}	
		
		
		
	}	
	
	public function doSearch()
	{
		return $this->doSearch;

	}
	
}


$srchObj 		= new search;
$search_info 	= $srchObj->getConfig();
$search_prefs	= $srchObj->getPrefs();

$SEARCH_VARS = new e_vars();

$query =  $srchObj->searchQuery();
$perform_search = $srchObj->doSearch();
$perform_search = true; 
// forward user if searching in google
$search_count = count($search_info);
$google_id = $search_count + 1;




if ($perform_search)
{
  if ($search_prefs['selector'] == 1) 
  {  // Care needed - with alpha strings on search of single area $_GET['t'][$google_id] returns a character on page > 1
	if (isset($_GET['t'][$google_id]) && ($_GET['t']==$google_id) && $_GET['t'][$google_id]) 
	{
//	echo "We think google should be used using checkboxes<br />";
		header("location:http://www.google.com/search?q=".stripslashes(str_replace(" ", "+", $query)));
		exit;
	}
  } 
  else 
  { 
	if (isset($_GET['t']) && $_GET['t'] == $google_id) 
	{
		header("location:http://www.google.com/search?q=".stripslashes(str_replace(" ", "+", $query)));
		exit;
	}
  }
}

$searchtype = $srchObj->searchType();

$enhanced_types['in'] = LAN_SEARCH_24.':';
$enhanced_types['ex'] = LAN_SEARCH_25.':';
$enhanced_types['ep'] = LAN_SEARCH_26.':';
$enhanced_types['be'] = LAN_SEARCH_27.':';

$SEARCH_VARS->ENHANCED_DISPLAY = $enhanced ? "" : "style='display: none'";

// advanced search config
if (!vartrue($_GET['adv']) || $_GET['t'] == 'all')
{
  foreach ($_GET as $gk => $gv) 
  {
	if ($gk != 't' && $gk != 'q' && $gk != 'r' && $gk != 'in' && $gk != 'ex' && $gk != 'ep' && $gk != 'be' && $gk != 'adv') 
	{
//	  unset($_GET[$gk]);
	}
  }
}

//$SEARCH_VARS->SEARCH_TYPE_SEL = "<input type='radio' name='adv' value='0' ".(varsettrue($_GET['adv']) ? "" : "checked='checked'")." /> ".LAN_SEARCH_29."&nbsp;
//<input type='radio' name='adv' value='1' ".(varsettrue($_GET['adv']) ? "checked='checked'" : "" )." /> ".LAN_SEARCH_30;

$js_adv = '';
foreach ($search_info as $key => $value) 
{
  if (!isset($search_info[$key]['advanced'])) 
  {
	$js_adv .= " && abid != '".$key."'";
  }
}

if (isset($_GET['t']) && isset($search_info[$_GET['t']]['advanced'])) 
{
  $SEARCH_VARS->SEARCH_TYPE_DISPLAY = "";
} 
else 
{
  $SEARCH_VARS->SEARCH_TYPE_DISPLAY = "style='display: none'";
}

if (check_class($search_prefs['google'])) {
	$js_adv .= " && abid != '".$google_id."'";
}


if ($perform_search) 
{
  $con = e107::getDate();
 
  $sch = new e_search;

  // omitted words message
  $stop_count = count($sch -> stop_keys);
  
  if ($stop_count) 
  {
	if ($stop_count > 1) 
	{
	  $SEARCH_VARS->SEARCH_MESSAGE = LAN_SEARCH_32.": ";
	} 
	else 
	{
	  $SEARCH_VARS->SEARCH_MESSAGE = LAN_SEARCH_33.": ";
	}
	
	$i = 1;
	foreach ($sch -> stop_keys as $stop_key) 
	{
	  $SEARCH_VARS->SEARCH_MESSAGE .= $stop_key;
	  if ($i != $stop_count) 
	  {
		$SEARCH_VARS->SEARCH_MESSAGE .= ', ';
	  }
	  $i++;
	}
  }
}

require_once(HEADERF);

// render search config

if(deftrue('BOOTSTRAP'))
{
	$tmp 				= e107::getCoreTemplate('search','form');
	$SEARCH_TOP_TABLE 	= $tmp['start'];
	$SEARCH_BOT_TABLE 	= $tmp['end'];
	$SEARCH_CATS		= $tmp['category'];
	$SEARCH_TYPE		= $tmp['type'];
	$SEARCH_ADV			= $tmp['advanced'];
	$SEARCH_ENHANCED	= $tmp['enhanced'];
	$SEARCH_ADV_COMBO	= $tmp['advanced-combo'];
	
	$srchObj->template = $tmp;
	unset($tmp);
}
else
{
	if (file_exists(THEME."templates/search_template.php"))
	{
		require(THEME."templates/search_template.php");
	}
	elseif (file_exists(THEME."search_template.php"))
	{
		require(THEME."search_template.php");
	} 
	else 
	{
		require(e_CORE."templates/search_template.php");
	}

	$SEARCH_TOP_TABLE .= "{SEARCH_ENHANCED}";
}


$tp = e107::getParser();
$text =  $tp->parseTemplate($SEARCH_TOP_TABLE,true,$srchObj);

if ($search_prefs['user_select']) 
{
	$text .= $tp->parseTemplate($SEARCH_CATS,true, $srchObj);
}

// $text .= $tp->parseTemplate($SEARCH_TYPE,true, $srchObj);
/*
$hiddenBlock = (!empty($_GET['t'])) ? "" : "class='e-hideme'";
$text .= "<div {$hiddenBlock} id='search-advanced' >";
$text .= $tp->parseTemplate("{SEARCH_ADVANCED_BLOCK=".vartrue($_GET['t'])."}",true, $srchObj);
$text .= "</div>";*/

	//print_a($search_prefs);
//$

// $text .= $SEARCH_MESSAGE ? preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_TABLE_MSG) : "";
$text .= $SEARCH_VARS->SEARCH_MESSAGE ? $tp->simpleParse($SEARCH_TABLE_MSG, $SEARCH_VARS) : "";
//$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_BOT_TABLE);
$text .= $tp->simpleParse($SEARCH_BOT_TABLE, $SEARCH_VARS);

if(!isset($_GET['nf'])) // no form flag.
{
	e107::getRender()->tablerender(PAGE_NAME." ".SITENAME, $text, 'search_head');
}

// parse search
$SEARCH_VARS = new e_vars();

if ($perform_search) 
{
	$srchObj->renderResults();
}





// old 6xx search parser for reverse compatability
function parsesearch($text, $match) 
{
	$tp = e107::getParser();
	$text = strip_tags($text);
	$temp = $tp->ustristr($text, $match);
	$pos = $tp->ustrlen($text) - $tp->ustrlen($temp);
	$matchedText = $tp->usubstr($text,$pos,$tp->ustrlen($match));
	if ($pos < 70) {
		$text = "...".$tp->usubstr($text, 0, 100)."...";
	} else {
		$text = "...".$tp->usubstr($text, ($pos-50), $pos+30)."...";
	}
	$text = preg_replace("/".$match."/i", "<span class='searchhighlight'>".$matchedText."</span>", $text);
	return($text);
}


function headerjs() {
	global $search_count, $google_id, $search_prefs, $js_adv, $search_info;
	if ($search_prefs['selector'] == 1) {
		
		$types = array_keys($search_info);
		$types = implode("', '", $types);
		
		$script = "<script type='text/javascript'>
		<!--
		var i;
		var stypes = new Array('".$types."');
		
		function checkAll(allbox) {
			for (var i in stypes)
			document.getElementById('searchform')[\"t[\" + stypes[i] + \"]\"].checked = true ;
			uncheckG();
		}

		function uncheckAll(allbox) {
			for (var i in stypes)
			document.getElementById('searchform')[\"t[\" + stypes[i] + \"]\"].checked = false ;
		}\n";

		if (check_class($search_prefs['google'])) {
		$script .= "
		function uncheckG() {
			document.getElementById('searchform')[\"t[".$google_id."]\"].checked = false ;
		}\n";
		}

		$script .= "// -->
		</script>";

	}



	return $script;
}

require_once(FOOTERF);

?>
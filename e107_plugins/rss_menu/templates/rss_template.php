<?php



$RSS_TEMPLATE = [];

//$RSS_WRAPPER['page']['RSS_TEXT'] = '(pre){---}(post)';


$RSS_TEMPLATE['page']['start'] = "<!-- RSS Template -->
							<table class='table table-striped table-bordered fborder'>
							<tr>
								<th class='fcaption' style='width:70%'> </th>
								<th class='fcaption' style='text-align:right'>{LAN=RSS_PLUGIN_LAN_6}</th>
							</tr>";

$RSS_TEMPLATE['page']['item'] = "
							<tr>
								<td class='forumheader3'>{RSS_FEED}<br />
								<span class='smalltext' ><small>{RSS_TEXT}</small></span>
								</td>
							<td class='forumheader3' style='text-align:right'>
						        {RSS_TYPES}
							</td>
							</tr>";


$RSS_TEMPLATE['page']['end'] = "</table>";





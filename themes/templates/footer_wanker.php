<?php
echo "<br />
<div style=\"text-align:center\">".
SITEDISCLAIMER.
"</div>";
?>
</td>
<td style="width:20%; vertical-align:top">
<?

$sql5 = new dbFunc;
$sql5 -> dbQuery("SELECT * FROM ".MUSER."menus WHERE menu_location='2' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql5-> dbFetch()){
	if(!eregi("menu", $menu_name)){
		if($links_display != 1){
			$menu_name();
		}
	}else{
		require_once("menus/".$menu_name.".php");
	}
}

?>
</td>		
		</tr></table>
			<tr>
					<td align="right" class="small" bgcolor="#5A6F5A" width=\"100%\">.:&#183; <? if(file_exists("menus/qotd_menu.php")) require_once("menus/qotd_menu.php"); ?></td>
			</tr>	
		</table>

	
	</td>
</tr>
<tr>
		<td align="right" class="smalldark">design: <a href="http://www.oswd.org/userinfo.phtml?user=deadbeat" target="_blank">wanker</a> by <a href="mailto:jeff@hype52.com" target="_blank">deadbeat</a> // based on <a href="http://www.oswd.org/userinfo.phtml?user=whompy" target="_blank">libra</a> by <a href="http://www.oswd.org/userinfo.phtml?user=whompy" target="_blank">whompy</a> // <a href="http://www.oswd.org" target="_blank">open source web design</a></td>
</tr>
</table>
</td></tr>
</table>
</td></tr></table>							
</td></tr></table>
</center>

<br>
</body>

<?
$sql -> db_Close();
?>
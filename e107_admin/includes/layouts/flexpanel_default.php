<?php
/*
+ ----------------------------------------------------------------------------+
| 
|     e107 website system
|     Copyright (C) 2008-2016 e107 Inc (e107.org)
|     Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
| 
|     Default layout for "flexpanel" admin dashboard style.
+ ----------------------------------------------------------------------------+
*/

$FLEXPANEL_LAYOUT = '
<div class="row">
	<div class="col-md-3 col-lg-2 admin-left-panel">
		<div class="draggable-panels" id="menu-area-01">
			{MENU_AREA_01}
		</div>
	</div>
	<div class="col-md-9 col-lg-10 admin-right-panel">
		<div class="sidebar-toggle">
			<a href="#" title="'.ADLAN_185.'" data-toggle-sidebar="true">&nbsp;</a>
		</div>
		
		<div>
			<div class="row">
				<div class="col-sm-12">
					{MESSAGES}
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12">
					<div class="draggable-panels" id="menu-area-02">
						{MENU_AREA_02}
					</div>
				</div>
			</div>
			
			<div class="row row-flex">
				<div class="col-sm-4">
					<div class="draggable-panels" id="menu-area-03">
						{MENU_AREA_03}
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="draggable-panels" id="menu-area-04">
						{MENU_AREA_04}
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="draggable-panels" id="menu-area-05">
						{MENU_AREA_05}
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12">
					<div class="draggable-panels" id="menu-area-06">
						{MENU_AREA_06}
					</div>
				</div>
			</div>
			
			<div class="row row-flex">
				<div class="col-sm-6">
					<div class="draggable-panels" id="menu-area-07">
						{MENU_AREA_07}
					</div>
				</div>
				
				<div class="col-sm-6">
					<div class="draggable-panels" id="menu-area-08">
						{MENU_AREA_08}
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12">
					<div class="draggable-panels" id="menu-area-09">
						{MENU_AREA_09}
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-4">
					<div class="draggable-panels" id="menu-area-10">
						{MENU_AREA_10}
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="draggable-panels" id="menu-area-11">
						{MENU_AREA_11}
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="draggable-panels" id="menu-area-12">
						{MENU_AREA_12}
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12">
					<div class="draggable-panels" id="menu-area-13">
						{MENU_AREA_13}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';

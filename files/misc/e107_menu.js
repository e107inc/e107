/***********************************************************************************
*	(c) Ger Versluis 2000 version 5.411 24 December 2001 (updated Jan 31st, 2003 by Dynamic Drive for Opera7)
*	For info write to menus@burmees.nl		          *
*	You may remove all comments for faster loading	          *		
***********************************************************************************/

	var NoOffFirstLineMenus=9;			// Number of first level items
	var LowBgColor='white';			// Background color when mouse is not over
	var LowSubBgColor='white';			// Background color when mouse is not over on subs
	var HighBgColor='black';			// Background color when mouse is over
	var HighSubBgColor='black';			// Background color when mouse is over on subs
	var FontLowColor='black';			// Font color when mouse is not over
	var FontSubLowColor='black';			// Font color subs when mouse is not over
	var FontHighColor='#767564';			// Font color when mouse is over
	var FontSubHighColor='#767564';			// Font color subs when mouse is over
	var BorderColor='black';			// Border color
	var BorderSubColor='black';			// Border color for subs
	var BorderWidth=1;				// Border width
	var BorderBtwnElmnts=1;			// Border between elements 1 or 0
	var FontFamily="verdana,comic sans ms,technical"	// Font family menu items
	var FontSize=7;				// Font size menu items
	var FontBold=0;				// Bold menu items 1 or 0
	var FontItalic=0;				// Italic menu items 1 or 0
	var MenuTextCentered='left';			// Item text position 'left', 'center' or 'right'
	var MenuCentered='left';			// Menu horizontal position 'left', 'center' or 'right'
	var MenuVerticalCentered='top';		// Menu vertical position 'top', 'middle','bottom' or static
	var ChildOverlap=.2;				// horizontal overlap child/ parent
	var ChildVerticalOverlap=.2;			// vertical overlap child/ parent
	var StartTop=0;				// Menu offset x coordinate
	var StartLeft=0;				// Menu offset y coordinate
	var VerCorrect=0;				// Multiple frames y correction
	var HorCorrect=0;				// Multiple frames x correction
	var LeftPaddng=3;				// Left padding
	var TopPaddng=2;				// Top padding
	var FirstLineHorizontal=1;			// SET TO 1 FOR HORIZONTAL MENU, 0 FOR VERTICAL
	var MenuFramesVertical=1;			// Frames in cols or rows 1 or 0
	var DissapearDelay=500;			// delay before menu folds in
	var TakeOverBgColor=1;			// Menu frame takes over background color subitem frame
	var FirstLineFrame='navig';			// Frame where first level appears
	var SecLineFrame='space';			// Frame where sub levels appear
	var DocTargetFrame='space';			// Frame where target documents appear
	var TargetLoc='menu';				// span id for relative positioning
	var HideTop=0;				// Hide first level when loading new document 1 or 0
	var MenuWrap=1;				// enables/ disables menu wrap 1 or 0
	var RightToLeft=0;				// enables/ disables right to left unfold 1 or 0
	var UnfoldsOnClick=0;			// Level 1 unfolds onclick/ onmouseover
	var WebMasterCheck=0;			// menu tree checking on or off 1 or 0
	var ShowArrow=1;				// Uses arrow gifs when 1
	var KeepHilite=1;				// Keep selected path highligthed
	var Arrws=['tri.gif',5,10,'tridown.gif',10,5,'trileft.gif',5,10];	// Arrow source, width and height

function BeforeStart(){return}
function AfterBuild(){return}
function BeforeFirstOpen(){return}
function AfterCloseAll(){return}


// Menu tree
//	MenuX=new Array(Text to show, Link, background image (optional), number of sub elements, height, width);
//	For rollover images set "Text to show" to:  "rollover:Image1.jpg:Image2.jpg"

Menu1=new Array("Home","http://e107.org/news.php","menu1.png",0,15,80);

Menu2=new Array("Downloads","","menu1.png",5);
	Menu2_1=new Array("Core","http://e107.org/e107.php?1","menu1.png",0,15,220);
	Menu2_2=new Array("Themes","http://e107.org/e107.php?3","menu1.png",0);
	Menu2_3=new Array("Themes @ e107themes.org","http://e107themes.org","menu2.png",0);
	Menu2_4=new Array("Plugins/Menus Items","http://e107.org/e107.php?2","menu1.png",0);
	Menu2_5=new Array("Plugins/Menus Items @ e107coders.org","http://e107coders.org","menu2.png",0);

Menu3=new Array("Uploads","","menu1.png",3);
	Menu3_1=new Array("Plugin/Menu","http://e107coders.org/upload.php","menu2.png",0,15,150);
	Menu3_2=new Array("Theme","http://e107themes.org","menu2.png",0);
	Menu3_3=new Array("Translation/Hack/Code","http://e107.org/upload.php","menu1.png",0);

Menu4=new Array("Support","","menu1.png",3);
	Menu4_1=new Array("Documentation","http://e107.org/docs/index.php","menu1.png",0,16,100);
	Menu4_2=new Array("FAQ","http://e107.org/faq.php?rd.1","menu1.png",0);
	Menu4_3=new Array("Problems Forum","http://e107.org/forum_viewforum.php?3","menu1.png",0);

Menu5=new Array("Forums","http://e107.org/forum.php","menu1.png",12);
	Menu5_1=new Array("Forum Front Page","http://e107.org/forum.php","menu1.png",0,16,140);
	Menu5_2=new Array("Announcements","http://e107.org/forum_viewforum.php?2","menu1.png",0,16,140);
	Menu5_3=new Array("Problems","http://e107.org/forum_viewforum.php?3","menu1.png",0,16,140);
	Menu5_4=new Array("Bugs","http://e107.org/forum_viewforum.php?4","menu1.png",0,16,140);
	Menu5_5=new Array("Requests","http://e107.org/forum_viewforum.php?5","menu1.png",0,16,140);
	Menu5_6=new Array("Code","http://e107.org/forum_viewforum.php?10","menu1.png",0,16,140);
	Menu5_7=new Array("Frequently Asked","http://e107.org/forum_viewforum.php?13","menu1.png",0,16,140);
	Menu5_8=new Array("Did You Know?","http://e107.org/forum_viewforum.php?16","menu1.png",0,16,140);
	Menu5_9=new Array("Translations","http://e107.org/forum_viewforum.php?18","menu1.png",0,16,140);
	Menu5_10=new Array("Litestep","http://e107.org/forum_viewforum.php?7","menu1.png",0,16,140);
	Menu5_11=new Array("PHP/mySQL","http://e107.org/forum_viewforum.php?9","menu1.png",0,16,140);
	Menu5_12=new Array("Whatever","http://e107.org/forum_viewforum.php?22","menu1.png",0,16,140);

Menu6=new Array("Bug Tracker","http://e107.org/bugtrack.php","menu1.png",0,15,100);

Menu7=new Array("Latest","http://e107.org/changes.php","menu1.png",0,15,100);

Menu8=new Array("Users","blank.htm","menu1.png",2);
	Menu8_1=new Array("Members","http://e107.org/docs/user.php","menu1.png",0,16,100);
	Menu8_2=new Array("Site Register","http://e107.org/register.php","menu1.png",0);

Menu9=new Array("Statistics","blank.htm","menu1.png",2);
	Menu9_1=new Array("Site Stats","http://e107.org/docs/stats.php","menu1.png",0,16,100);
	Menu9_2=new Array("#e107 IRC Stats","http://e107.org/ircstats/e107.html","menu1.png",0);
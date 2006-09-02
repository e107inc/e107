E107 V0.7.0-0.7.5 Event Calendar

Bug Fixes and various enhancements.

Issued: 26.08.06


Enhancements
------------
1. A 'forthcoming event' menu which can be added to the main page in the normal way.
There is an extra 'admin' page added to the event calendar admin for this; allows setting of:
	a) The title of the menu
	b) Number of days to look ahead
	c) Number of events to display
	d) Whether recurring events are to be displayed
	e) Whether the title for the menu is to be a clickable link to current month's events
	f) Whether the category icon is to be displayed by each event
	g) The categories of events which are to be displayed

2. Enhanced email notification facilities:
	a) One of the emails can be "event is tomorrow" as an alternative to "event is today" - set by category
	b) Optional summary or detailed logging of mailout activity (set as option on the main calendar admin page)
		This requires a subdirectory 'log' in the calendar_menu directory, with '755' access rights. A log
		file (standard ASCII text) 'calendar_mail.txt' is created there.


Bug Fixes
---------
(All dated prior to 01.08.06 were incorporated into CVS subsequent to the release of E107 version 0.7.5)
Core mods:
	calendar_menu.php 	- class display mod (bug fix - #2300)
				- mis-handling of events crossing a month boundary (bug fix - #2450)
	event.php 		- handles end date after start date (bug/database error fix - 25.02.06 - #2485)
		  		- handles multi-day events straddling two months (bug fix 02.03.06 - #2504)
		  		- displays next 10 events correctly when showing single event (bug fix 19.03.06 - #2567)
				- displays year of recurring events correctly (unreported bug fix 15.06.06)
				- next 10 events - calendar supervisor sees all events (unreported bug fix 26.08.06)
	calendar_shortcodes.php - allows bbcode in contact field (so can use [smail])
				- new code to include date in event list
				- expands all events in list if showing a single day
				- expands all events if date clicked from calendar
	calendar_template.php 	- uses new shortcode to display date in event list
	calendar.php		- correction to display of recurring events in years prior to year of entry - bug 2911


Files affected by enhancements
------------------------------
	next_event_menu.php	- Adds configurable 'forthcoming event' menu
	admin_config.php	- Adds options to configure forthcoming event menu
				- Mods to give option to send emails on previous day
				- Mod  to allow control of log for email subscriptions
	languages/English.php	- Additional messages added
	subs_menu.php		- Restructured to have a common sending routine for each 'trigger'
				- Supports sending on previous day
				- Optional logging - basic or detailed - create 'log' directory off plugin directory


Installation
------------
1. Copy the files from this update into the e107_plugins/calendar_menu directory, overwriting existing files.
2. Create a subdirectory 'log' and give it access rights of 755.
3. Go to the event calendar admin screen and configure the extra options as required.
4. Add the 'forthcoming events' menu to your page if desired.

<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Messages for admin pages of event calendar
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/languages/English_admin_calendar_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Event calendar plugin
 *
 *	Language file - 'admin' pages
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

define('EC_ADLAN_1', 'Event Calendar');

define('EC_LAN_12', "Monday");
define('EC_LAN_13', "Tuesday");
define('EC_LAN_14', "Wednesday");
define('EC_LAN_15', "Thursday");
define('EC_LAN_16', "Friday");
define('EC_LAN_17', "Saturday");
define('EC_LAN_18', "Sunday");
define('EC_LAN_19', "Mon");
define('EC_LAN_20', "Tue");
define('EC_LAN_21', "Wed");
define('EC_LAN_22', "Thu");
define('EC_LAN_23', "Fri");
define('EC_LAN_24', "Sat");
define('EC_LAN_25', "Sun");
/*
Following are LANs from user pages - probably not needed in ADMIN
define('EC_LAN_26', "Events this Month");
define('EC_LAN_27', "No events for this month.");
define('EC_LAN_28', "Enter New Event");
define('EC_LAN_29', "When:");
define('EC_LAN_30', "Category:");
define('EC_LAN_31', "Posted by:");
define('EC_LAN_32', "Location:");
define('EC_LAN_33', "Contact:");
define('EC_LAN_34', "Jump to");
define('EC_LAN_35', "Edit");
define('EC_LAN_36', "Delete");
define('EC_LAN_37', "None Listed.");
define('EC_LAN_38', "Not specified");
define('EC_LAN_39', "Click here for more information");
define('EC_LAN_40', "Current Month");
define('EC_LAN_41', "Total -NUM- individual events created");
define('EC_LAN_42', "Event cannot end before it starts.");
define('EC_LAN_43', "You left required field(s) blank.");
define('EC_LAN_44', "New event created and entered into database.");
define('EC_LAN_45', "Event updated in database.");
define('EC_LAN_46', "Confirm Delete Event");
define('EC_LAN_47', "Delete cancelled.");
define('EC_LAN_48', "Please confirm you wish to delete this event - once deleted it cannot be retrieved");
define('EC_LAN_49', "Cancel");
define('EC_LAN_50', "Confirm Delete");
define('EC_LAN_51', "Event deleted.");
define('EC_LAN_52', "Event Category:");
define('EC_LAN_53', "Create new category?:");
define('EC_LAN_54', "Name:");
define('EC_LAN_55', "Icon:");
define('EC_LAN_56', "Create");
define('EC_LAN_57', "Event:");
define('EC_LAN_58', "source info URL:");
define('EC_LAN_59', "Contact email:");
define('EC_LAN_60', "Update Event");
define('EC_LAN_61', "Go");
define('EC_LAN_62', "Next -NUM- Events ...");
define('EC_LAN_63', "Select repeating events between start and end dates. Start and end time as set");
define('EC_LAN_64', "Check for an all-day event");
define('EC_LAN_65', "Recurring:");
define('EC_LAN_66', "Edit Event");
define('EC_LAN_67', "Start:");
define('EC_LAN_68', "All day event:");
define('EC_LAN_69', "Ends:");
define('EC_LAN_70', "Event Title:");
define('EC_LAN_71', "Event Time:");
define('EC_LAN_72', "Event Date:");
define('EC_LAN_73', "End:");
define('EC_LAN_74', "View Category");
define('EC_LAN_76', "Events can be added by:");
define('EC_LAN_77', "Update Settings");
define('EC_LAN_78', "Calendar Settings");
define('EC_LAN_79', "Calendar View");
define('EC_LAN_80', "Event List");
define('EC_LAN_81', "Configure Event Calendar");
define('EC_LAN_83', "Calendar");
define('EC_LAN_84', " from ");	
define('EC_LAN_85', " until ");
define('EC_LAN_86', "Individual events from entry");
define('EC_LAN_87', "By checking this box you may generate a large number of individual events, which you will have to edit or delete individually if they are wrong");
define('EC_LAN_88', "You have chosen to generate -NUM- individual events.");
define('EC_LAN_89', "If the entry is wrong, you will have to edit or delete the entries individually");

define('EC_LAN_90', "Choose");	
define('EC_LAN_91', "Admin must define first");	
define('EC_LAN_92', "View Category");		
define('EC_LAN_93', "View Events List");		
define('EC_LAN_94', "Enter New Event");			
define('EC_LAN_95', "Today");	
define('EC_LAN_96', "View Calendar");	
define('EC_LAN_97', "All");		
define('EC_LAN_98', "Required fields left blank");		
define('EC_LAN_99', "Event must either be an all day event or finish after it starts");			
define('EC_LAN_100', "Invalid Category Selection");			
//define('EC_LAN_101', "Set to inactive to disable on the new event form.");	
define('EC_LAN_102', "Show link to 'more information' with events");
//define('EC_LAN_103', "On new event entry form.");	
define('EC_LAN_104', "Calendar Administrator Class");		
define('EC_LAN_105', "* Required Field");		
define('EC_LAN_106', "Events");		
define('EC_LAN_107', "This plugin is a fully featured event calendar with calendar menu.");		
define('EC_LAN_108', "Event Calendar Upgraded.  See the 'readme.pdf' file for detailed information.");	
define('EC_LAN_109', "Unable to delete this event.");	
define('EC_LAN_110', "Event Number ");	
define('EC_LAN_111', "All the events on ");	
define('EC_LAN_112', "All the Events in ");	
define('EC_LAN_113', "Event form already submitted.");
define('EC_LAN_114', "Week starts with:");
define('EC_LAN_115', "Sunday");
define('EC_LAN_116', "Monday");
define('EC_LAN_117', "Length of daynames (characters)");
define('EC_LAN_118', "Date format in calendar header:");
define('EC_LAN_119', "month/year");
define('EC_LAN_120', "year/month");
define('EC_LAN_121', "Show Calendar");	

define('EC_LAN_123', "Subscriptions");
define('EC_LAN_124', "Calendar Subscriptions");
define('EC_LAN_125', "Categories available for subscription");
define('EC_LAN_126', "Subscribed");
define('EC_LAN_127', "Category");
define('EC_LAN_128', "No categories available to subscribe to");
define('EC_LAN_129', "Update");
define('EC_LAN_130', "Subscriptions updated");
define('EC_LAN_131', "Return");
define('EC_LAN_132', "Expand details");
define('EC_LAN_133', "[read more]");
define('EC_LAN_134', "You have to provide a category name");
define('EC_LAN_135', "Event");
define('EC_LAN_136', "Category Description");
define('EC_LAN_137', "Future Events");

define('EC_LAN_140', "Forthcoming Events");
define('EC_LAN_141', "No forthcoming events");
define('EC_LAN_142', "Only registered and logged in users can subscribe to events");
define('EC_LAN_143', "Facility not available");
define('EC_LAN_144', " at ");

define('EC_LAN_145', "You must specify a category for the event");
define('EC_LAN_146', "Advance notice of calendar event");
define('EC_LAN_147', "Calendar event today or tomorrow");
define('EC_LAN_148', "No events in specified date range");
define('EC_LAN_149', "Invalid date format");
define('EC_LAN_150', "Enter start and end date for list");
define('EC_LAN_151', "End date after start date");
define('EC_LAN_152', "Maximum one year's events");
define('EC_LAN_153', "Start Date (first day of): ");
define('EC_LAN_154', "End Date (last day of): ");
define('EC_LAN_155', "Category: ");
define('EC_LAN_156', "Create List");
define('EC_LAN_157', "Layout Options:");
define('EC_LAN_158', "Output: ");
define('EC_LAN_159', "Display ");
define('EC_LAN_160', "Print ");
define('EC_LAN_161', "PDF ");
define('EC_LAN_162', "Print this page");
*/
define('EC_LAN_163', "Event Listing");
/*
define('EC_LAN_164', "Printable Lists");
define('EC_LAN_165', "Default Listing");
define('EC_LAN_166', "Tabular List no lines");
define('EC_LAN_167', "Tabular List with lines");
define('EC_LAN_168', "From: ");
define('EC_LAN_169', "To: ");
define('EC_LAN_170', "Printed on: ");
define('EC_LAN_171', "List including category");
define('EC_LAN_172', "Event Categories: ");
define('EC_LAN_173', "First event starts: ");
define('EC_LAN_174', "Last event ends: ");
define('EC_LAN_175', "All Day");
define('EC_LAN_176', "Recurring pattern: ");
define('EC_LAN_177', "Cancel Entry");
define('EC_LAN_178', "Accept Entries");
define('EC_LAN_179', "Confirmation of multiple event entry");
define('EC_LAN_180', " RECORDS NOT SAVED - DB UPDATE ERROR");

define('EC_LAN_VIEWCALENDAR', "View Calendar");
define('EC_LAN_VIEWALLEVENTS', "View all events");
define('EC_LAN_ALLEVENTS', "All events");
*/

define('EC_ADLAN_A09', 'Main Calendar');
define('EC_ADLAN_A10', "Configuration");
define('EC_ADLAN_A11', "Categories");
define('EC_ADLAN_A12', "Calendar");
define('EC_ADLAN_A13', "Edit");
define('EC_ADLAN_A14', "New");
define('EC_ADLAN_A15', "Delete");
define('EC_ADLAN_A16', "Confirm");
define('EC_ADLAN_A17', "Proceed");
define('EC_ADLAN_A18', "Action");
define('EC_ADLAN_A19', "Administer Categories");
define('EC_ADLAN_A20', "Calendar Categories");
define('EC_ADLAN_A21', "Category name");
define('EC_ADLAN_A22', "Adds a field to be used as a link to a forum thread or external site");	
define('EC_ADLAN_A23', "Create category");
define('EC_ADLAN_A24', "Edit category");
define('EC_ADLAN_A25', "Save");
define('EC_ADLAN_A26', "Category created");
define('EC_ADLAN_A27', "Unable to create category");
define('EC_ADLAN_A28', "Changes Saved");
define('EC_ADLAN_A29', "Unable to save changes");

define('EC_ADLAN_A30', "Category Deleted");
define('EC_ADLAN_A31', "Tick the confirm box to delete");
define('EC_ADLAN_A32', "Unable to delete this category");
define('EC_ADLAN_A33', "None defined");
define('EC_ADLAN_A34', "Calendar Administrator Class");
//define('EC_ADLAN_A35', "");
define('EC_ADLAN_A59', "Category is in use. Can not delete.");

define('EC_ADLAN_A80', "Visible to");
define('EC_ADLAN_A81', "Allow subscription");
define('EC_ADLAN_A82', "Forced notification class");
define('EC_ADLAN_A83', "Days ahead to notify of event");
define('EC_ADLAN_A84', "Advanced message");
define('EC_ADLAN_A85', "Message on the day");
define('EC_ADLAN_A86', "Send email");
define('EC_ADLAN_A87', "None");
define('EC_ADLAN_A88', "Only advanced");
define('EC_ADLAN_A89', "Only on the day");
define('EC_ADLAN_A90', "Advanced and on the day");
define('EC_ADLAN_A91', "Email Subject");
define('EC_ADLAN_A92', "Email from (name)");
define('EC_ADLAN_A93', "Email from email address");
define('EC_ADLAN_A94', "Add new event class");
define('EC_ADLAN_A95', "Enable manual subscriptions");
define('EC_ADLAN_A96', "Disabling this removes the subscriptions button and overrides the category manual subscription setting.");


define('EC_ADLAN_A100', "Forthcoming Events");
define('EC_ADLAN_A101', "Days to look forward:");
define('EC_ADLAN_A102', "Number of events to display:");
define('EC_ADLAN_A103', "Include recurring events:");
define('EC_ADLAN_A104', "Title is link to events list:");
define('EC_ADLAN_A105', "Configure Forthcoming Events Menu");
define('EC_ADLAN_A106', "Menu has to be enabled on the 'Menu' page");
define('EC_ADLAN_A107', "Hide menu if no events to show");
define('EC_ADLAN_A108', "Menu Heading");
define('EC_ADLAN_A109', "Forthcoming Events preferences updated");

define('EC_ADLAN_A110', "Only on previous day");
define('EC_ADLAN_A111', "Advanced and previous day");
define('EC_ADLAN_A112', "Previous day and on the day");
define('EC_ADLAN_A113', "Advanced, previous day and on the day");

define('EC_ADLAN_A114', "Logging of Emails");
define('EC_ADLAN_A115', "Summary");
define('EC_ADLAN_A116', "Detailed");
define('EC_ADLAN_A117', "Message on the day or the previous day");
define('EC_ADLAN_A118', "Categories to display");
define('EC_ADLAN_A119', "No categories defined, or error reading database");
define('EC_ADLAN_A120', "Show category icon in menu");
define('EC_ADLAN_A121', "Category Description");
define('EC_ADLAN_A122', "Calendar time reference");
define('EC_ADLAN_A123', "Calendar time format");
define('EC_ADLAN_A124', "Current server time: ");
define('EC_ADLAN_A125', "Current site time: ");
define('EC_ADLAN_A126', "Current user time: ");
define('EC_ADLAN_A127', "Determines time display format throughout event calendar.");
define('EC_ADLAN_A128', "Custom time uses the format in the box on the right");
define('EC_ADLAN_A129', '"Site Time" uses the offset defined in preferences');
define('EC_ADLAN_A130', "Event name is link to:");
define('EC_ADLAN_A131', "Calendar Event");
define('EC_ADLAN_A132', "Source Info URL");
define('EC_ADLAN_A133', "Date format for event entry: ");
define('EC_ADLAN_A134', "Level of logging to main admin log:");
define('EC_ADLAN_A135', "Edit/delete");
define('EC_ADLAN_A136', "All changes");
define('EC_ADLAN_A137', "Can cover additions, updates to and deletions from the event list");
define('EC_ADLAN_A138', "Event start/end times on 5-minute boundaries");
define('EC_ADLAN_A139', "(Reduces number of entries in drop-down list)");
define('EC_ADLAN_A140', "Show number of events for this month in Calendar Menu");
define('EC_ADLAN_A141', "Maintenance");
define('EC_ADLAN_A142', "Remove past events ending more than x months ago");
define('EC_ADLAN_A143', "timed from beginning of current month");
define('EC_ADLAN_A144', "Event Calendar Maintenance");
define('EC_ADLAN_A145', "Delete old entries");
define('EC_ADLAN_A146', "Events older than ");
define('EC_ADLAN_A147', " deleted");
define('EC_ADLAN_A148', "Parameter error - nothing deleted");
define('EC_ADLAN_A149', "No old events to delete, or delete of past events failed");
define('EC_ADLAN_A150', "Confirm delete events older than ");

define('EC_ADLAN_A151', "e107 Web Site");
define('EC_ADLAN_A152', "calendar@yoursite.com");
define('EC_ADLAN_A153', "Log directory must be created manually - create a subdirectory 'log' off your event calendar plugin directory, with '666' access rights");
define('EC_ADLAN_A154', "Could not change log directory permissions");
define('EC_ADLAN_A155', "Log directory permissions may require manual update to 0666 or 0766, although depending on your server setup they may work");
define('EC_ADLAN_A156', "Database upgraded");
define('EC_ADLAN_A157', "this is the rss feed for the calendar entries");
define('EC_ADLAN_A158', "Could not create log directory");

define('EC_ADLAN_A159', "Cache Management");
define('EC_ADLAN_A160', "(Only relevant if cache enabled)");
define('EC_ADLAN_A161', "Empty Calendar Cache");
define('EC_ADLAN_A162', "Confirm Empty Cache");
define('EC_ADLAN_A163', "Cache emptied");

define('EC_ADLAN_A164', "Update completed");
define('EC_ADLAN_A165', "Calendar menu header links to:");
define('EC_ADLAN_A166', "Date display in Event List:");
define('EC_ADLAN_A167', "Date display in Forthcoming Events:");
define('EC_ADLAN_A168', "Custom date uses the format in the box on the right");
define('EC_ADLAN_A169', "Determines date display format for event listings");
define('EC_ADLAN_A170', "Determines date display format for forthcoming events menu");
define('EC_ADLAN_A171', "Flag recently added/updated events");
define('EC_ADLAN_A172', "Value is time from update in hours; zero to disable, 'LV' to show from user's last visit");

define('EC_ADLAN_A173', "Subscriptions");
define('EC_ADLAN_A174', "No subscription entries found");
define('EC_ADLAN_A175', "UID");
define('EC_ADLAN_A176', "User Name");
define('EC_ADLAN_A177', "Category");
define('EC_ADLAN_A178', "Problems");
define('EC_ADLAN_A179', "Actions");
define('EC_ADLAN_A180', "Deleted subscription record no ");
define('EC_ADLAN_A181', "Delete failed for record no ");
define('EC_ADLAN_A182', "Total --NUM-- entries in database");
define('EC_ADLAN_A183', "Calendar Menu mouseover shows event title");
define('EC_ADLAN_A184', "may not work with all browsers");
define('EC_ADLAN_A185', "Nothing");
define('EC_ADLAN_A186', "Update settings\nand send test\nemail to self");
define('EC_ADLAN_A187', "Test email sent - ");
define('EC_ADLAN_A188', "Error sending test email - ");
define('EC_ADLAN_A189', "If the message is left blank, the message from the 'Default' category will be used");
define('EC_ADLAN_A190', "Default category - mailout messages are used if none defined for any other category");
define('EC_ADLAN_A191', "Details of event for test email");
define('EC_ADLAN_A192', "Test event location");
define('EC_ADLAN_A193', "Allow users to display/print/PDF lists");
define('EC_ADLAN_A194', "None");
define('EC_ADLAN_A195', "Display/Print");
define('EC_ADLAN_A196', "Display/Print/PDF");
define('EC_ADLAN_A197', "No class membership");
define('EC_ADLAN_A198', "Invalid User");
define('EC_ADLAN_A199', "Show 'recent' icon");
define('EC_ADLAN_A200', "Editor for events");
define('EC_ADLAN_A201', "BBCode (Standard)");
define('EC_ADLAN_A202', "BBCode with help");
define('EC_ADLAN_A203', "WYSIWYG");
define('EC_ADLAN_A204', 'Calendar settings updated.');
define('EC_ADLAN_A205', 'Confirm Delete');
define('EC_ADLAN_A206', 'This plugin is a fully featured event calendar with calendar menu.');
define('EC_ADLAN_A207', 'Calendar Settings');
define('EC_ADLAN_A208', 'Events can be added by:');
define('EC_ADLAN_A209', 'Event List');
define('EC_ADLAN_A210', 'Calendar');
define('EC_ADLAN_A211', 'Calendar Administrator Class');
define('EC_ADLAN_A212', 'Week starts with:');
define('EC_ADLAN_A213', 'Show link to \'more information\' with events');
define('EC_ADLAN_A214', 'Length of daynames (characters)');
define('EC_ADLAN_A215', 'Date format in calendar header:');
define('EC_ADLAN_A216', 'month/year');
define('EC_ADLAN_A217', 'year/month');
define('EC_ADLAN_A218', 'Update Settings');
define('EC_ADLAN_A219', 'Icon:');
define('EC_ADLAN_A220', 'Choose');


/*
// Notify
define("NT_LAN_EC_1", "Event Calendar Events");
define("NT_LAN_EC_2", "Event Updated");
define("NT_LAN_EC_3", "Update by");
define("NT_LAN_EC_4", "IP Address");
define("NT_LAN_EC_5", "Message");
define("NT_LAN_EC_6", "Event Calendar - Event added");
define("NT_LAN_EC_7", "New event posted");
define("NT_LAN_EC_8", "Event Calendar - Event modified");
*/
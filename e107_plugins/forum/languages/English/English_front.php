<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin - frontend language file
 *
*/

// MAJOR REWRITE IN PROGRESS BY MOC, DO NOT EDIT THIS FILE UNTIL THIS MESSAGE HAS BEEN REMOVED PLEASE!

/* 
Even though all the forum LAN"s are shared accross the entire plugin, here"s some general direction:
- 0xxx => forum.php
- 1xxx => forum_viewforum.php
- 2xxx => forum_viewtopic.php
- 3xxx => forum_post.php

This is done to offer flexibility when changing or adding in new LAN defines. 

vf, vt, etc. in the comments refer to the LAN defined in the previously separate language files:
vf = viewforum, vt = viewtopic, p = post, etc.
*/

define("e_PAGETITLE", "Forum");

// forum.php (forum_template)
// define("LAN_FORUM_0001", "Forum"); // LAN_46	
define("LAN_FORUM_0002", "Topics"); // LAN_47 / FORLAN_21
define("LAN_FORUM_0003", "Replies"); // LAN_48 / LAN_55
define("LAN_FORUM_0004", "Last Post"); // LAN_49 / FORLAN_22 / LAN_57
define("LAN_FORUM_0005", "This forum is restricted to administrators only"); // LAN_406
define("LAN_FORUM_0006", "This forum is restricted to members only"); // LAN_407
define("LAN_FORUM_0007", "This is a read-only forum"); // LAN_408
define("LAN_FORUM_0008", "This is a class restricted forum"); // LAN_409
define("LAN_FORUM_0009", "Information"); // LAN_191 
define("LAN_FORUM_0010", "Top Posters"); // LAN_429
define("LAN_FORUM_0011", "Most Active Topics"); // LAN_430
define("LAN_FORUM_0012", "My Posts"); // LAN_431
define("LAN_FORUM_0013", "My Settings"); // LAN_432
define("LAN_FORUM_0014", "My Profile"); // LAN_435
define("LAN_FORUM_0015", "My Uploads"); // FORLAN_442
define("LAN_FORUM_0016", "Forum Rules"); // LAN_433
define("LAN_FORUM_0017", "View forum statistics"); // LAN_441
define("LAN_FORUM_0018", "Welcome"); // LAN_30
define("LAN_FORUM_0019", "There are no new posts"); // LAN_31
define("LAN_FORUM_0020", "There is one new post "); // LAN_32
define("LAN_FORUM_0021", "There are"); // LAN_33
define("LAN_FORUM_0022", "new posts"); // LAN_34
define("LAN_FORUM_0023", "since your last visit.");  // LAN_35
define("LAN_FORUM_0024", "You last visited at ");  // LAN_36
define("LAN_FORUM_0025", "It is now");  // LAN_37
//define("LAN_FORUM_0026", ", all times are ");  // LAN_38 
define("LAN_FORUM_0027", "You have read"); // LAN_196
define("LAN_FORUM_0028", "of these posts."); // LAN_197
define("LAN_FORUM_0029", "All new posts have been read."); // LAN_198
define("LAN_FORUM_0030", "Tracked topics"); // LAN_393
define("LAN_FORUM_0031", "The users of this forum have made a total of [x] posts."); // LAN_192 and LAN_404
define("LAN_FORUM_0032", "topic"); // LAN_411
define("LAN_FORUM_0033", "topics"); // LAN_413
define("LAN_FORUM_0034", "reply"); // LAN_412
define("LAN_FORUM_0035", "replies"); // LAN_414
define("LAN_FORUM_0036", "Who's Online"); // LAN_426
define("LAN_FORUM_0037", "View detailed list"); // LAN_427
define("LAN_FORUM_0038", "(Will open in a new window)"); // LAN_436
define("LAN_FORUM_0039", "New posts"); // LAN_79 // LAN_79 (vf)
define("LAN_FORUM_0040", "No new posts"); // LAN_80 / LAN_80 (vf)
define("LAN_FORUM_0041", "Closed forum"); // LAN_394
//define("LAN_FORUM_0042", "Search"); /// LAN_180 => Generic term, moved to e107_languages/English.php LAN_SEARCH
define("LAN_FORUM_0043", "You can start new topics"); // LAN_204 // FIXME "can / cannot" in bold? 0043-0048
define("LAN_FORUM_0044", "You cannot start new topics"); // LAN_205
define("LAN_FORUM_0045", "You can post replies"); // LAN_206
define("LAN_FORUM_0046", "You cannot post replies"); // LAN_207
define("LAN_FORUM_0047", "You can edit your posts"); // LAN_208
define("LAN_FORUM_0048", "You cannot edit your posts"); // LAN_209

// FIXME LAN - check if this section (0049 - 0055) could use some LAN optimization
define("LAN_FORUM_0049", "Welcome guest"); // LAN_410
define("LAN_FORUM_0050", "These forums can be used by non-registered users, but please be aware that your IP address will be logged if you make a post.<br />To access the full features of this forum you will need to"); // LAN_44 // FIXME <br />
define("LAN_FORUM_0051", "register"); // LAN_437
define("LAN_FORUM_0052", "and login."); // LAN_438
define("LAN_FORUM_0053", "These forums can only be posted to by registered and logged in members, please click"); // LAN_45
define("LAN_FORUM_0054", "here"); // LAN_438
define("LAN_FORUM_0055", "to go to the registration page."); // LAN_440

define("LAN_FORUM_0056", "Restricted Access"); // LAN_405

define("LAN_FORUM_0057", "Mark all posts as read"); // LAN_199 
define("LAN_FORUM_0058", "Show new posts"); // LAN_421

// FIXME LAN - check if this section (0059 - 0064) could use some LAN optimization
define("LAN_FORUM_0059", "user is browsing the forums at the moment"); // LAN_415 / LAN_405 (vf)
define("LAN_FORUM_0060", "users are browsing the forums at the moment"); //LAN_416 / LAN_406 (vf)
define("LAN_FORUM_0061", "member"); // LAN_417 / LAN_407 (vf)
define("LAN_FORUM_0062", "members"); // LAN_419 / LAN_409 (vf)
define("LAN_FORUM_0063", "guest"); // LAN_418 / LAN_408 (vf)
define("LAN_FORUM_0064", "guests"); // LAN_420 / LAN_410 (vf)

define("LAN_FORUM_0065", "Newest member:"); // LAN_41
define("LAN_FORUM_0066", "Registered members:"); // LAN_42

define("LAN_FORUM_0067", "No forums yet, please check back soon."); // LAN_51
define("LAN_FORUM_0068", "No forums in this section yet, please check back soon."); // LAN_52
define("LAN_FORUM_0069", "Sub-forums"); // FORLAN_444 

define("LAN_FORUM_0070", "Cancel Topic Tracking"); // LAN_392 
//define("LAN_FORUM_0071", "Forum Rules"); // LAN_433
define("LAN_FORUM_0072", "No rules defined."); // FORLAN_441
define("LAN_FORUM_0073", "Tracked topics"); // LAN_397
define("LAN_FORUM_0074", "Posted by");  // LAN_423
define("LAN_FORUM_0075", "New topics"); // LAN_424

// starting at LAN_FORUM_1xxx => forum_viewforum.php
define("LAN_FORUM_1001", "Forums"); // LAN_01

define("LAN_FORUM_1002", "Sub-forum"); // FORLAN_20
define("LAN_FORUM_1003", "Topic"); // LAN_55
define("LAN_FORUM_1004", "Starter"); // LAN_54
define("LAN_FORUM_1005", "Views"); // LAN_56

define("LAN_FORUM_1006", "Important Topics"); // LAN_411 (vf)
define("LAN_FORUM_1007", "Topics"); // LAN_412 (vf)
define("LAN_FORUM_1008", "There are no topics in this forum yet."); // LAN_58
define("LAN_FORUM_1009", "This forum is moderated by"); // LAN_404
define("LAN_FORUM_1010", "[popular]"); // LAN_395 (vf)
define("LAN_FORUM_1011", "Sticky"); // LAN_202
define("LAN_FORUM_1012", "Sticky/Closed"); // LAN_203 / 
define("LAN_FORUM_1013", "Announcement"); // LAN_396 // FORLAN_17
define("LAN_FORUM_1014", "Closed topic"); // LAN_81 / FORLAN_18
define("LAN_FORUM_1015", "[user deleted]"); // FORLAN_19 (vf)
define("LAN_FORUM_1016", "Poll"); // FORLAN_23

define("LAN_FORUM_1017", "Go to"); // LAN_401 (p?)
define("LAN_FORUM_1018", "New topic");
// define("LAN_FORUM_1019", "Are you sure?"); // new
define("LAN_FORUM_1020", "Modify");
define("LAN_FORUM_1021", "No Replies");


// starting at LAN_FORUM_2xxx => forum_viewtopic.php
define("LAN_FORUM_2001", "Previous topic"); // LAN_389 
define("LAN_FORUM_2002", "Next topic"); // LAN_390
define("LAN_FORUM_2003", "Moderator(s)"); // LAN_321
define("LAN_FORUM_2004", "This topic is now closed"); // LAN_66
define("LAN_FORUM_2005", "Create a new topic"); // new
define("LAN_FORUM_2006", "Post a reply"); // new
define("LAN_FORUM_2007", "Post a quick reply"); // new
define("LAN_FORUM_2008", "HIDDEN - LOGIN AND REPLY TO REVEAL"); // FORLAN_HIDDEN - used in [hide] bbcode?
define("LAN_FORUM_2009", "Are you sure you want to delete this topic and any replies?"); // LAN_409
define("LAN_FORUM_2010", "Are you sure you want to delete this reply?"); // LAN_410
//define("LAN_FORUM_2011", "posted by"); // LAN_410 // LAN_FORUM_0074
define("LAN_FORUM_2012", "No previous topic"); // LAN_404 (vt)
define("LAN_FORUM_2013", "No next topic"); // LAN_405 (vt)

define("LAN_FORUM_2015", "Post"); // LAN_403 (vt)
define("LAN_FORUM_2016", "Edited"); // LAN_29 
define("LAN_FORUM_2017", "by"); // FORLAN_BY
define("LAN_FORUM_2018", "This post has been reported from site"); // LAN_422 (vt)
define("LAN_FORUM_2019", "Message from"); // LAN_425 (vt)
define("LAN_FORUM_2020", "Forum topic report from"); // LAN_421 (vt)
define("LAN_FORUM_2021", "Post has been reported"); // LAN_428
define("LAN_FORUM_2022", "Click here to return to the forum"); // LAN_429
define("LAN_FORUM_2023", "Report this topic to a moderator"); // LAN_414 (vt)
define("LAN_FORUM_2024", "Reporting post in topic"); // LAN_426
define("LAN_FORUM_2025", "Topic title"); // LAN_415
define("LAN_FORUM_2026", "View post"); // LAN_420
define("LAN_FORUM_2027", "The moderator(s) will be made aware of this topic. You may post a message explaining what you found to be objectionable."); // LAN_417
define("LAN_FORUM_2028", "[Do not] use this form to contact the admin for any other reason."); // LAN_418 - [ and ] are replaced by <b> </b>
define("LAN_FORUM_2029", "Send Report"); // LAN_419 
define("LAN_FORUM_2030", "Back to top"); // LAN_10
define("LAN_FORUM_2031", "Joined"); // LAN_06
define("LAN_FORUM_2032", "Posts"); // LAN_67
define("LAN_FORUM_2033", "Visits to site since registration"); // LAN_09
define("LAN_FORUM_2034", "Website"); // LAN_08
define("LAN_FORUM_2035", "Registered Member"); // LAN_195
define("LAN_FORUM_2036", "Send Private Message"); // new / LAN_399
define("LAN_FORUM_2037", "Post deleted on"); // new
define("LAN_FORUM_2038", "Reason"); // new
// define("LAN_FORUM_2039", "Edit"); // LAN_400
// define("LAN_FORUM_2040", "Delete"); // LAN_435 (vf)?
define("LAN_FORUM_2041", "Quote"); // LAN_401
define("LAN_FORUM_2042", "Move"); // LAN_FORUM_5019 ?
define("LAN_FORUM_2043", "Split"); // new
define("LAN_FORUM_2044", "Email"); // FORLAN_101
define("LAN_FORUM_2045", "Print"); // FORLAN_102
define("LAN_FORUM_2046", "Report"); // LAN_413



// starting at LAN_FORUM_3xxx => forum_post.php 
define("LAN_FORUM_3001", "You are not authorized to post to this forum."); // LAN_399 (p)
define("LAN_FORUM_3002", "This topic is locked."); // LAN_397
define("LAN_FORUM_3003", "Replying to"); // LAN_02 (p)
// define("LAN_FORUM_3004", "Anonymous"); // LAN_311
define("LAN_FORUM_3005", "Preview"); // LAN_323
define("LAN_FORUM_3006", "Duplicate post"); // LAN_FORUM_2
define("LAN_FORUM_3007", "You left required field(s) blank"); // LAN_27
define("LAN_FORUM_3008", "Unauthorised"); // LAN_95
define("LAN_FORUM_3009", "You are not authorised to edit this forum post."); //LAN_96
define("LAN_FORUM_3010", "Your name"); // LAN_61
define("LAN_FORUM_3011", "Subject"); // LAN_62
define("LAN_FORUM_3012", "Attach file(s) / image(s)"); // LAN_390
define("LAN_FORUM_3013", "Attach file"); // LAN_416
define("LAN_FORUM_3014", "File to attach"); // LAN_392
define("LAN_FORUM_3015", "[Please note]"); // LAN_393 1st half - [ and ] are replaced by <b> </b>
define("LAN_FORUM_3016", "Allowed file types"); // LAN_393 2nd half
define("LAN_FORUM_3017", "Any other file types uploaded will be instantly deleted."); // LAN_394
define("LAN_FORUM_3018", "Maximum file size"); // LAN_395
define("LAN_FORUM_3019", "bytes"); // LAN_396
define("LAN_FORUM_3020", "Add another attachment"); // LAN_417
define("LAN_FORUM_3021", "Uploads disabled: [x] directory is not writable"); // LAN_FORUM_1 - [x] will be replaced automatically
define("LAN_FORUM_3022", "Latest [y] replies"); // LAN_101 and LAN_102 - [y] will be replaced automatically
define("LAN_FORUM_3023", "Update topic"); // LAN_77
define("LAN_FORUM_3024", "Update reply"); // LAN_78
define("LAN_FORUM_3025", "Type"); // new
define("LAN_FORUM_3026", "Post topic as"); // LAN_400 (p)
//define("LAN_FORUM_3027", "Type"); // new -
define("LAN_FORUM_3028", "Add Poll"); // new // poll - partly in e107_plugins/poll/poll_class.php
define("LAN_FORUM_3038", "Normal"); // LAN_1
define("LAN_FORUM_3039", "Deactivate emoticons for this post"); // LAN_FORUMPOST_EMOTES
define("LAN_FORUM_3040", "Enable/disable email tracking (email sent when reply is posted)"); // LAN_380
define("LAN_FORUM_3041", "Enable/disable tracking of this topic");
define("LAN_FORUM_3042", "New Topic/Subject");
// missing 41-21

// forum_posted_template
define("LAN_FORUM_3043", "Thank you"); // LAN_133 (p)
// define("LAN_FORUM_3044", "Click here to return to the forum"); // LAN_326
define("LAN_FORUM_3045", "Your poll has been successfully posted."); // LAN_413
define("LAN_FORUM_3046", "Click here to view your poll"); // LAN_414
define("LAN_FORUM_3047", "Your message has been successfully posted."); // LAN_324
define("LAN_FORUM_3048", "Click here to view your message"); // LAN_325
define("LAN_FORUM_3049", "Your reply has been successfully posted."); // LAN_415
define("LAN_FORUM_3050", "Split point");
define("LAN_FORUM_3051", "New location");
define("LAN_FORUM_3052", "Split Thread");


// forum_icons_template
define("LAN_FORUM_4001", "Unread post exists"); // LAN_199 (vf)
define("LAN_FORUM_4002", "No unread posts"); // new
define("LAN_FORUM_4003", "New posts on popular topic"); // FORLAN_13
define("LAN_FORUM_4004", "No new posts on popular topic"); // FORLAN_14
// define("LAN_FORUM_4005", "Website"); // LAN_396
// define("LAN_FORUM_4006", "Email"); // LAN_397
define("LAN_FORUM_4007", "Profile"); // LAN_398
define("LAN_FORUM_4008", "Private Message"); // LAN_399
define("LAN_FORUM_4009", "Track topic"); // LAN_391 (vt) 
define("LAN_FORUM_4010", "Untrack topic"); // LAN_392 (vt) / new
define("LAN_FORUM_4011", "Stick thread"); // LAN_401
define("LAN_FORUM_4012", "Unstick thread"); // LAN_398
define("LAN_FORUM_4013", "Lock thread"); // LAN_399
define("LAN_FORUM_4014", "Unlock thread"); // LAN_400


// Ajax and actions 
define("LAN_FORUM_CLOSE", "Thread closed.");
define("LAN_FORUM_OPEN", "Thread reopened.");
define("LAN_FORUM_STICK", "Thread made sticky.");
define("LAN_FORUM_UNSTICK", "Thread unstuck.");




// Config 
define("LAN_FORUM_5001", "Poll deleted."); // LAN_FORUM_5001
define("LAN_FORUM_5005", "Thread moved."); // LAN_FORUM_5005
define("LAN_FORUM_5006", "Move cancelled."); // LAN_FORUM_5006
define("LAN_FORUM_5007", "Back To Forums"); // LAN_FORUM_5007
define("LAN_FORUM_5008", "Forum Configuration"); // LAN_FORUM_5008
define("LAN_FORUM_5009", "Are you absolutely certain you want to delete this poll?<br />Once deleted it <b><u>cannot</u></b> be retrieved.");
define("LAN_FORUM_5010", "Confirm Delete Forum Post"); // LAN_FORUM_5010
define("LAN_FORUM_5019", "Move Thread"); // LAN_FORUM_5019
define("LAN_FORUM_5021", "moved"); // LAN_FORUM_5021
define("LAN_FORUM_5022", "Do not rename thread title"); // LAN_FORUM_5022
define("LAN_FORUM_5024", "Add [x] prefix to the subject/title"); // LAN_FORUM_5024
define("LAN_FORUM_5025", "Rename to:"); // LAN_FORUM_5025
define("LAN_FORUM_5026", "Rename thread options:"); // LAN_FORUM_5026


// Statistics (lan_forum_stats.php ) ----------------
define("LAN_FORUM_6000", "General"); // FSLAN_1
define("LAN_FORUM_6001", "Forum opened"); // LAN_FORUM_6001
define("LAN_FORUM_6002", "Open for"); // FSLAN_3
define("LAN_FORUM_6003", "Total posts"); // FSLAN_4
define("LAN_FORUM_6004", "Forum replies"); // FSLAN_6
define("LAN_FORUM_6005", "Forum thread views"); // FSLAN_7
define("LAN_FORUM_6006", "Database size (forum tables only)"); // FSLAN_8
define("LAN_FORUM_6007", "Average row length in forum table"); // FSLAN_9
define("LAN_FORUM_6008", "Rank"); // FSLAN_11
define("LAN_FORUM_6009", "Started by"); // FSLAN_14
define("LAN_FORUM_6010", "Most viewed topics"); // FSLAN_16
define("LAN_FORUM_6011", "Top topic starters"); // FSLAN_21
define("LAN_FORUM_6012", "Top repliers"); // FSLAN_22
define("LAN_FORUM_6013", "Forum Statistics"); // FSLAN_23
define("LAN_FORUM_6014", "Average posts per day"); // FSLAN_24


// ---- Uploads ----------

define("LAN_FORUM_7001","Uploaded Files in forum");
define("LAN_FORUM_7002","File deleted"); // LAN_FORUM_7002
define("LAN_FORUM_7003","Error: Unable to delete file"); // LAN_FORUM_7003
define("LAN_FORUM_7004","File deletion"); // LAN_FORUM_7004
define("LAN_FORUM_7006","Result"); // LAN_FORUM_7006
define("LAN_FORUM_7007","Found in thread"); // LAN_FORUM_7007
define("LAN_FORUM_7008","NOT FOUND"); // LAN_FORUM_7008
define("LAN_FORUM_7009","No uploaded files found"); // LAN_FORUM_7009



// -------- Tracking Email ------------

define("LAN_FORUM_8001", "A new post has been made by [x] under the topic [y] at [z].");
define("LAN_FORUM_8002", "Please click the following link to view the full post ...");
define("LAN_FORUM_8003", "Email notifications for this topic are now turned on.");
define("LAN_FORUM_8004", "Email notifications for this topic are now turned off.");
define("LAN_FORUM_8005", "You are now tracking this topic.");
define("LAN_FORUM_8006", "You are no longer tracking this topic.");

// -------- View Forum ------------
define("LAN_FORUM_8007", "Stick");
define("LAN_FORUM_8008", "Unstick");
define("LAN_FORUM_8009", "Lock");
define("LAN_FORUM_8010", "Unlock");
define("LAN_FORUM_8011", "Status Keys");
define("LAN_FORUM_8012", "Viewable by");
define("LAN_FORUM_8013", "Options");

// -------- Forum Post------------
define("LAN_FORUM_8014", "This post, and every post below it will be moved into a new thread/topic.");
define("LAN_FORUM_8015", "Warning!");
define("LAN_FORUM_8016", "(Current)");

// -------- Forum Class------------
define("LAN_FORUM_8017", "There was a problem disabling the tracking.");
define("LAN_FORUM_8018", "There was a problem.");
define("LAN_FORUM_8019", "Couldn't delete the topic!");
define("LAN_FORUM_8020", "Deleted topic");
define("LAN_FORUM_8021", "Deleted post");
define("LAN_FORUM_8022", "Couldn't delete post");
define("LAN_FORUM_8023", "Failed to close thread");
define("LAN_FORUM_8024", "Failed to open thread");
define("LAN_FORUM_8025", "Failed to stick thread");
define("LAN_FORUM_8026", "Failed to unstick thread");
define("LAN_FORUM_8027", "No action selected");
define("LAN_FORUM_8028", "Return"); 
define("LAN_FORUM_8029", "New topic created!");

/*  THIS WILL BE DELETED ONCE THE REWRITE IS DONE
==================================================
 * 
 * 
 * 
define("LAN_FORUM_5020", "Reply deleted"); // LAN_FORUM_5020
define("LAN_FORUM_5011", "posted by"); // LAN_FORUM_5011
define("LAN_FORUM_5012", "Are you absolutely certain you want to delete this forum");
define("LAN_FORUM_5013", "thread and it"s related posts?");
define("LAN_FORUM_5014", "the poll will also be deleted");
define("LAN_FORUM_5015", "Once deleted they");
define("LAN_FORUM_5016", "post?<br />Once deleted it"); // LAN_FORUM_5016
define("LAN_FORUM_5017", "cannot</u></b> be retrieved"); // LAN_FORUM_5017 // 
define("LAN_FORUM_5018", "Move thread  to forum"); // LAN_FORUM_5018
define("LAN_FORUM_5023", "Add"); // LAN_FORUM_5023

define("LAN_01", "Forums");
define("LAN_02", "Go to page");
define("LAN_03", "Go");
define("LAN_04", "Previous");
define("LAN_05", "Next");
define("LAN_06", "Joined");
define("LAN_07", "Location");
define("LAN_08", "Website");
define("LAN_09", "Visits to site since registration");
define("LAN_10", "Back to top");
define("LAN_65", "Jump");

define("LAN_53", "Thread");
define("LAN_54", "Starter");
define("LAN_55", "Replies");
define("LAN_56", "Views");
define("LAN_57", "Latest Post");
define("LAN_58", "There are no topics in this forum yet.");
define("LAN_59", "You must be a registered member and logged in to post on this forum. Click on signup or login from the login menu.");
define("LAN_202", "Sticky");
define("LAN_203", "Sticky/Closed");

define("LAN_66", "This thread is now closed");
define("LAN_67", "Posts");
define("LAN_194", "Guest");
define("LAN_195", "Registered Member");
define("LAN_321", "Moderators: ");
define("LAN_389", "Previous thread");
define("LAN_390", "Next thread");
define("LAN_391", "Track Thread");
define("LAN_392", "Cancel Thread Tracking");
define("LAN_393", "Quick Reply");
define("LAN_394", "Preview");
define("LAN_395", "Reply To Thread");
define("LAN_396", "Website");
define("LAN_397", "Email");
define("LAN_398", "Profile");
define("LAN_399", "Private Message");
define("LAN_400", "Edit");
define("LAN_401", "Quote");

define("LAN_402", "Author");
define("LAN_403", "Post");
define("LAN_404", "No previous thread");
define("LAN_405", "No next thread");

define("LAN_406", "Moderator: Edit");
define("LAN_435", "Moderator: Delete");
define("LAN_408", "Moderator: Move");
define("LAN_409", "Are you sure you want to delete this thread and any replies?");
define("LAN_410", "Are you sure you want to delete this reply?");
define("LAN_411", "posted by ");

//v.616
//define("LAN_412", "Title");//LAN_TITLE
define("LAN_413", "Report");
define("LAN_414", "Report this thread to a moderator");
define("LAN_415", "Thread title");
define("LAN_416", "Enter your report");
define("LAN_417", "The admin will be made aware of this thread. You may post a message explaining what you found to be objectionable.");
define("LAN_418", "<b>Do not</b> use this form to contact the admin for any other reason.");
define("LAN_419", "Send Report");
define("LAN_420", "Click to view post");
define("LAN_421", "Forum thread report from");
define("LAN_422", "This post has been reported from site ");
define("LAN_423", "Message could not be sent. ");
define("LAN_424", "Post has been reported to moderator.<br />Thank You.");
define("LAN_425", "Message from: ");
define("LAN_426", "Reporting post in topic: ");
define("LAN_427", "Error sending mail");
define("LAN_428", "Post has been reported");
define("LAN_429", "Click here to return to forum");
define("LAN_430", "poll");
define("FORLAN_26", "Reply deleted");
define("FORLAN_10", "Begin New Thread");
define("LAN_29", "Edited");

define("LAN_431", "Syndicate this thread: rss 0.92");
define("LAN_432", "Syndicate this thread: rss 2.0");
define("LAN_433", "Syndicate this thread: RDF");

define("FORLAN_101", "Email Thread");
define("FORLAN_102", "Print View");
define("FORLAN_103", "[user deleted]");
define("FORLAN_104", "Thread not found");
define("FORLAN_105", "Moderator: Split");
define("FORLAN_BY", "by");
define("FORLAN_HIDDEN", "HIDDEN - LOGIN AND REPLY TO REVEAL");	
	
define("LAN_06", "Joined");	
	
define("LAN_30", "Welcome");
define("LAN_31", "There are no new posts ");
define("LAN_32", "There is 1 new post ");
define("LAN_33", "There are");
define("LAN_34", "new posts");
define("LAN_35", "since your last visit.");
define("LAN_36", "You last visited at ");
define("LAN_37", "It is now ");
define("LAN_38", ", all times are ");
define("LAN_41", "Newest member: ");
define("LAN_42", "Registered members: ");
define("LAN_44", "These forums can be used by non-registered users, but please be aware that your IP Address will be logged if you make a post.<br />To access the full features of this forum you will need to");
define("LAN_45", "These forums can only be posted to by registered and logged in members, please click");
define("LAN_46", "Forum");
define("LAN_47", "Threads");
define("LAN_48", "Replies");
define("LAN_49", "Last Post");
define("LAN_51", "No forums yet, please check back soon.");
define("LAN_52", "No forums in this section yet, please check back soon.");
define("LAN_79", "New posts");
define("LAN_80", " No new posts");
define("LAN_81", "Closed thread");
define("LAN_100", "articles");
define("LAN_180", "Search");
define("LAN_191", "Information");
define("LAN_192", "The users of this forum have made a total of ");
define("LAN_196", "You have read ");
define("LAN_197", " of these posts.");
define("LAN_198", " All new posts have been read.");
define("LAN_199", "Mark all posts as read");
define("LAN_204", "You <b>can</b> start new threads");
define("LAN_205", "You <b>cannot</b> start new threads");
define("LAN_206", "You <b>can</b> post replies");
define("LAN_207", "You <b>cannot</b> post replies");
define("LAN_208", "You <b>can</b> edit your posts");
define("LAN_209", "You <b>cannot</b> edit your posts");
define("LAN_392", "stop tracking this thread");
define("LAN_393", "List tracked threads");
define("LAN_394", "Closed forum");
define("LAN_397", "Tracked threads");
define("LAN_398", "Closed");
define("LAN_399", "Restricted");
define("LAN_400", "This forum can only be browsed by registered members");
define("LAN_401", "Members only");
	
define("LAN_402", "This forum is read only");
	
define("LAN_403", "No posts yet");
define("LAN_404", "posts");

	
define("LAN_406", "This forum is restricted to administrators only");
define("LAN_407", "This forum is restricted to members only");
define("LAN_408", "This is a read-only forum");
define("LAN_409", "This is a class restricted forum");
define("LAN_410", "Welcome guest");
	
define("LAN_411", "thread");
define("LAN_412", "reply");
define("LAN_413", "threads");
define("LAN_414", "replies");
define("LAN_415", "user is browsing the forums at the moment");
define("LAN_416", "users are browsing the forums at the moment");
	
define("LAN_417", "member");
define("LAN_418", "guest");
define("LAN_419", "members");
define("LAN_420", "guests");
	
define("LAN_421", "Show new posts");
define("LAN_422", "New posts since your last visit");
define("LAN_423", "Posted by");
define("LAN_424", "New threads");
define("LAN_425", "Re:");
	
//v.616
define("LAN_426", "Who"s Online: ");
define("LAN_427", "View detailed list.");
define("LAN_428", "Re:");
define("LAN_429", "Top Posters");
define("LAN_430", "Most Active Threads");
define("LAN_431", "My Posts");
define("LAN_432", "My Settings");
define("LAN_433", "Forum Rules");
define("LAN_434", "Return to forums");
define("LAN_435", "My Profile");
define("LAN_436", " (Will open a new window.)");
	
define("LAN_437", "register");
define("LAN_438", "and login.");
define("LAN_439", "here");
define("LAN_440", "to go to the registration page.");

define("LAN_441", "View forum statistics");

define("FORLAN_21", "Threads");
define("FORLAN_22", "Last Post");
define("FORLAN_23", "Poll");
	
define("FORLAN_441", "No rules defined.");
define("FORLAN_442", "My Uploads");
define("FORLAN_443", "[user deleted]");
define("FORLAN_444", "sub-forums");

define("PAGE_NAME", "Forum");

define("LAN_01", "Forums");
define("LAN_02", "Replying to: ");
define("LAN_03", "New Thread");
define("LAN_1", "Normal");
define("LAN_2", "Sticky");
define("LAN_3", "Announcement");
define("LAN_4", "Post Poll");
define("LAN_5", "Poll Question:");
define("LAN_6", "Add another option");
define("LAN_7", "Vote option:");
define("LAN_8", "Allow votes from all");
define("LAN_9", "Allow votes from members only");
define("LAN_10", "Login");
define("LAN_11", "Remember me");
define("LAN_16", "Username: ");
define("LAN_17", "Password: ");
define("LAN_20", "Error");//new > LAN_ERROR
define("LAN_27", "You left required field(s) blank");
define("LAN_28", "You didn"t post anything ..");
define("LAN_29", "Edited");
define("LAN_45", "These forums can only be posted to by registered and logged in members, please click");
define("LAN_60", "Start New Thread");
define("LAN_61", "Your Name: ");
define("LAN_62", "Subject: ");
define("LAN_63", "Post: ");
define("LAN_64", "Submit new thread");
define("LAN_73", "Reply: ");
define("LAN_74", "Reply to thread");
define("LAN_77", "Update Thread");
define("LAN_78", "Update Reply");
define("LAN_94", "Posted by");
define("LAN_95", "Unauthorised");
define("LAN_96", "You are not authorised to edit this forum post.");
define("LAN_100", "Thread Topic");
define("LAN_101", "Latest ");
define("LAN_102", " replies");
define("LAN_103", "Review complete thread. (Will open a new window.)");
define("LAN_133", "Thank you");
define("LAN_174", "Signup");
define("LAN_175", "Login");
define("LAN_212", "Forgot password?");
define("LAN_310", "Unable to accept post as that username is registered - if it is your username please login to post.");
define("LAN_311", "Anonymous");
define("LAN_322", "Posted: ");
define("LAN_323", "Preview");
define("LAN_324", "Your message has been successfully posted.");
define("LAN_325", "Click Here to view your message");
define("LAN_326", "Click here to return to the forum");
define("LAN_327", "Review");
define("LAN_380", "Enable email tracking (email sent when reply is posted)");
define("LAN_381", "Forum reply from ");
define("LAN_382", "Post made: ");
define("LAN_383", "Please click the following link to view the full thread ...");
define("LAN_384", "Forum reply at ");
define("LAN_385", "Post: ");
define("LAN_386", "If you do not wish to add a poll to your thread leave the fields blank ");
define("LAN_387", "Go");
define("LAN_388", "Back to top");
define("LAN_389", "Duplicate post, redirecting ...");
define("LAN_390", "Attach file / image");
define("LAN_391", "Options");
define("LAN_392", "File to attach");
define("LAN_393", "<b>Please note</b><br />Allowed file types:");
define("LAN_394", "Any other file types uploaded will be instantly deleted.");
define("LAN_395", "Maximum file size");
define("LAN_396", " bytes");
define("LAN_397", "This thread is locked.");
define("LAN_398", "This forum is read only");
define("LAN_399", "You are not authorized to post to this forum.");
define("LAN_400", "post thread as");
define("LAN_401", "Jump");

define("LAN_402", "poll");
define("LAN_403", "announcement");
define("LAN_404", "sticky");
define("LAN_405", "Forums");
define("LAN_406", "Re:");

//v.616
define("LAN_407", "Redirect");
define("LAN_408", "If your browser does not support meta redirection please click");
define("LAN_409", "HERE");
define("LAN_410", "to be redirected");
define("LAN_411", "here");
define("LAN_412", "to go to the registration page.");

define("LAN_413", "Your poll has been successfully posted.");
define("LAN_414", "Click Here to view your poll");
define("LAN_415", "Your reply has been successfully posted.");

define("LAN_416", "Attach file");
define("LAN_417", "Add another attachment");

define("POLL_506", "Allow multiple choices?");
define("POLL_507", "yes");
define("POLL_508", "no");

define("LAN_FORUM_1", "Uploads disabled: ".e_FILE."public directory is not writable");
define("LAN_FORUM_2", "Duplicate post");

define("LAN_FORUMPOST_EMOTES", "Deactivate emoticons for this post");
*/

?>

<?php
// $Id: modinfo.php,v 1.9 2003/04/01 22:51:22 mvandam Exp $
// Module Info

// The name of this module
define('_MI_SERVER_NAME','Server Booking');

// A brief description of this module
define('_MI_SERVER_DESC','Creates a section for Server Booking');

// Names of blocks for this module (Not all module has blocks)
define('_MI_SERVER_BNAME1','Bookings');
define('_MI_SERVER_BNAME2', "Top Bookers");
define('_MI_SERVER_BNAME3', "Top Servers");
define('_MI_SERVER_BNAME4', 'Navigation');

// Names of admin menu items
define('_MI_SERVER_ADMENU1', 'Manage Servers');
define('_MI_SERVER_ADMENU2', 'Manage Bookings');
define('_MI_SERVER_ADMENU3', 'Manage Serveradmins');

define('_MI_SERVER_SMNAME2', 'Calendar');
define('_MI_SERVER_SMNAME3', 'Book Server');
define('_MI_SERVER_CONFMAIL', 'Send Confirmation Emails?');
define('_MI_SERVER_CONFMAILDESC', 'When a booking status is changed, e.g. it is approved, the module can send a confirmation email to the booker, letting him know about the status');
define('_MI_SERVERADMIN_CONFMAIL', 'Send Booking Emails to Server Admins?');
define('_MI_SERVERADMIN_CONFMAILDESC', 'When a booking is requested, the module can send a confirmation email to the server admins');
define('_MI_SERVER_MAIL', 'Server Booking Email');
define('_MI_SERVER_MAILDESC', 'Which email address should be in the From field when sending mails?');
define('_MI_SERVER_ANONBOOK', 'Allow Anonymous Bookings');
define('_MI_SERVER_ANONBOOKDESC', 'Toggles, whether non-members can book or not');
define('_MI_SERVER_DEFVIEW', 'Default Calendar View');
define('_MI_SBSERVMANAGE','Server Management');

//added 140104
define('_MI_MANAGE', 'Manage');
define('_MI_SEARCH', 'Search for Server');
define('_MI_MYSERVERS', 'My Servers');
define('_MI_SB_QUERY', 'Query Server');
define('_MI_SIGNUPSERVER', 'Signup Server');

define("_MI_SERVER_SCRIMM_NOTIFY", "Scrimm");
define("_MI_SERVER_SCRIMM_NOTIFYDSC", "Scrimm Creation Notifications");

define("_MI_SERVER_REPLY_NOTIFY", "Reply");
define("_MI_SERVER_REPLY_NOTIFYDSC", "Scrimm Reply Notification");

define("_MI_SERVER_NEWSCRIMM_NOTIFY", "New Scrimm");
define("_MI_SERVER_NEWSCRIMM_NOTIFYCAP", "Notify me on new scrimm creation");
define("_MI_SERVER_NEWSCRIMM_NOTIFYDSC", "Receive notification, when a new scrimm is created");
define("_MI_SERVER_NEWSCRIMM_NOTIFYSBJ", '[{X_SITENAME}] {X_MODULE} auto-notify : New scrimm');

define("_MI_SERVER_NEWREPLY_NOTIFY", "New Reply");
define("_MI_SERVER_NEWREPLY_NOTIFYCAP", "Notify me on reply");
define("_MI_SERVER_NEWREPLY_NOTIFYDSC", "Receive notification when the scrimm is replied to");
define("_MI_SERVER_NEWREPLY_NOTIFYSBJ", '[{X_SITENAME}] {X_MODULE} auto-notify : Scrimm replied');
?>
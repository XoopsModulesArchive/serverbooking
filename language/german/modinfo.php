<?php
// $Id: modinfo.php,v 1.9 2003/04/01 22:51:22 mvandam Exp $
// Module Info

// The name of this module
define('_MI_SERVER_NAME','Serverbuchungen');

// A brief description of this module
define('_MI_SERVER_DESC','Erzeugt einen Bereich f&uuml;r Serverbuchungen');

// Names of blocks for this module (Not all module has blocks)
define('_MI_SERVER_BNAME1','Buchungen');

// Names of admin menu items
define('_MI_SERVER_ADMENU1', 'Server verwalten');
define('_MI_SERVER_ADMENU2', 'Buchungen verwalten');
define('_MI_SERVER_ADMENU3', 'Serveradmins verwalten');

define('_MI_SERVER_SMNAME2', 'Kalender');
define('_MI_SERVER_SMNAME3', 'Server buchen');
define('_MI_SERVER_CONFMAIL', 'Best&auml;tigungs-Mails senden?');
define('_MI_SERVER_CONFMAILDESC', 'Wenn der Buchungs-Status ge&auml;ndert wird, z. B. bei einer Freigabe, kann das Modul eine Best&auml;tigungs-Mail an den Buchenden schicken um ihn auf diese &Auml;nderung aufmerksam zu machen');
define('_MI_SERVERADMIN_CONFMAIL', 'Buchungs-E-Mails an Serveradmins schicken?');
define('_MI_SERVERADMIN_CONFMAILDESC', 'Wenn eine Buchung angefragt wird, kann das Modul eine E-Mail an die Serveradmins schicken');
define('_MI_SERVER_MAIL', 'Serverbuchungs-E-Mail');
define('_MI_SERVER_MAILDESC', 'Welche E-Mail-Adresse soll im "Von-Feld" erscheinen wenn E-Mails verschickt werden?');
define('_MI_SERVER_ANONBOOK', 'Anonyme Buchungen erlauben');
define('_MI_SERVER_ANONBOOKDESC', 'Stellt um, ob Nicht-Mitglieder buchen k&ouml;nnen oder nicht');
define('_MI_SERVER_DEFVIEW', 'Standard Kalenderansicht');
define('_MI_SBSERVMANAGE','Servermanagement');
?>

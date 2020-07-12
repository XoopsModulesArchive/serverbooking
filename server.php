<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include_once "class/server.php";
include_once 'functions.php';
include_once 'admin/functions.php';
include_once 'language/'.$xoopsConfig['language'].'/admin.php';

if (!$xoopsUser) {
    redirect_header('index.php', 3, _NOPERM);
}

if (isset($_POST)) {
	foreach ($_POST as $k => $v) {
		${$k} = $v;
	}
}
if (!isset($op)) {
    $op = "";
}
switch ($op) {
       case "saveserver":
         if (!isset($exclusive)) {
             $exclusive = 0;
         }
         if (!isset($bookable)) {
             $bookable = 0;
         }
         if (!isset($approved)) {
             $approved = 0;
         }
         $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
         $server = $server_handler->create(true);
         $server->setVars(array('servername' => $servername,
                                    'serverip' => $serverip,
                                    'serverport' => $serverport,
                                    'serverzone' => $serverzone,
                                    'is_bookable' => $bookable,
                                    'exclusive' => $exclusive,
                                    'region' => $region,
                                    'approved' => $approved));
         if ($server_handler->insert($server)) {
             $comment = $server->getVar('servername')." "._AM_SBADDED."";
             $comment .= "<br />Path to serverprefs file: ".XOOPS_URL."/modules/".$xoopsModule->getVar('dirname')."/prefs/leasedSA".$server->getVar('serverid').".txt";
             if (addTeamAdmin($xoopsUser->getVar('uid'), $server->getVar('serverid'))) {
                 $comment .= "<br />Server Admin Status Granted";
             }
             else {
                 $comment .= "<br />Server Admin Status Failed";
             }
         }

         else {
             $comment = _AM_SBERRWHILESAVSERV;
         }
         echo $comment;

	default:
	   include XOOPS_ROOT_PATH."/class/xoopsformloader.php";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
		echo "<tr><td><img src='".XOOPS_ROOT_PATH."/images/servers.gif'></td>";
		echo "</tr></table><table width='100%' border='0' cellpadding='4' cellspacing='1'>";
        echo "<tr><td>";
        addServerForm("Add", '', 'server.php');
        echo "</td><td>";
        echo "</td></tr>";
		echo "</table>";
		break;
}
include_once XOOPS_ROOT_PATH.'/footer.php';
?>
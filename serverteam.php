<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include_once 'language/'.$xoopsConfig['language'].'/admin.php';

if (!$xoopsUser) {
    redirect_header('index.php', 3, _NOPERM);
}

$op = isset($_POST['op']) ? $_POST['op'] : "";
$serverteam_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
switch ($op) {
       case "saveteam":
         $serverteam = $serverteam_handler->create(true);
         $serverteam->setVars(array('name' => $_POST['name'],
                                    'irc' => $_POST['irc'],
                                    'tag' => $_POST['tag'],
                                    'homepage' => $_POST['homepage']));
         if ($serverteam_handler->insert($serverteam)) {
             $comment = $serverteam->getVar('name')." Created";
             if ($serverteam->addAdmin($xoopsUser->getVar('uid'))) {
                 $comment .= "<br />Team Admin Status Granted";
             }
             else {
                 $comment .= "<br />Team Admin Status Failed";
             }
         }

         else {
             $comment = _AM_SBERRWHILESAVTEAM;
         }
         echo $comment;

	default:
	   include XOOPS_ROOT_PATH."/class/xoopsformloader.php";
		echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
		echo "<tr><td><img src='".XOOPS_ROOT_PATH."/images/servers.gif'></td>";
		echo "</tr></table><table width='100%' border='0' cellpadding='4' cellspacing='1'>";
        echo "<tr><td>";
        $serverteam_handler->Form("Add", '', 'serverteam.php');
        echo "</td><td>";
        echo "</td></tr>";
		echo "</table>";
		break;
}
include_once XOOPS_ROOT_PATH.'/footer.php';
?>
<?php
// $Id: index.php,v 1.15 2003/04/01 22:51:22 mvandam Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
include '../../../mainfile.php';
include '../../../include/cp_header.php';
//include(XOOPS_ROOT_PATH.'/header.php');
//include(XOOPS_ROOT_PATH."/class/xoopsmodule.php");
include('../functions.php');
include('functions.php');
include_once ('../class/server.php');

$op = isset($_GET['op']) ? $_GET['op'] : 'default';
$op = isset($_POST['op']) ? $_POST['op'] : $op;
xoops_cp_header();
switch($op){
    case "deleteserver":
		if ( !empty($_POST['ok']) ) {
            if (empty($_POST['serverid'])) {
                redirect_header('index.php?op=default',2,_AM_EMPTYNODELETE);
                exit();
            }
            $server_handler = xoops_getmodulehandler('server', 'serverbooking');
            $server = $server_handler->get($_POST['serverid']);            
            if ($server_handler->delete($server)) {
                redirect_header("index.php?op=servermanager",3,_AM_SBSERVERDELETED);
            }
            else {
                redirect_header("index.php?op=servermanager",3,_AM_SBERRSERVNOTDEL);
            }
       }
       else {
			echo "<h4>"._AM_CONFIG."</h4>";
			xoops_confirm(array('op' => 'deleteserver', 'serverid' => $_POST['serverid'], 'ok' => 1), 'index.php', _AM_RUSUREDEL);
       }
       break;

       case "saveserver":
         $bookable = isset($_POST['bookable']) ? 1 : 0;
         
         $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
         if ($_POST['serverid']) {
             $server =& $server_handler->create(false);
             $server->assignVar('serverid', $_POST['serverid']);
         }
         else {
             $server =& $server_handler->create();
         }
         $options = (!isset($_POST['options']) || count($_POST['options']) == 0) ? array() : $_POST['options'];
         
         $server->setVar('servername', $_POST['servername']);
         $server->setVar('serverip', $_POST['serverip']);
         $server->setVar('serverport', $_POST['serverport']);
         $server->setVar('serverzone', $_POST['serverzone']);
         $server->setVar('is_bookable', $bookable);
         $server->setVar('region', $_POST['region']);
         if ($server_handler->insert($server) && $server->getVar('serverid') > 0) {
             if ($server->isNew()) {
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
                 $comment = $server->getVar('servername')." "._AM_SBEDITED;
             }
         }
         else {
             $comment = _AM_SBERRWHILESAVSERV;
         }
         echo $comment;

	case "servermanager":
		echo "<table border='0' cellpadding='0' cellspacing='0' valign='top' width='100%'>";
        echo "<tr><td><table width='100%' border='0' cellpadding='0' cellspacing='0'>";
		echo "<tr><td><img src='".XOOPS_ROOT_PATH."/images/servers.gif'></td>";
		echo "</tr></table></td></tr>";
        echo "<tr><td><table width='100%' border='0' cellpadding='4' cellspacing='1'>";
        echo "<tr><td>";
        $sql = "SELECT * FROM ".$xoopsDB->prefix("team_server")." ORDER BY servername ASC";
        addServerForm("Add");
        echo "</td><td>";
        echo "</td></tr>";
		echo "</table></td></tr>";
        echo "<tr><td><table border='0' cellpadding='4' cellspacing='1'><tr class='bg5'>
        <th><b>"._AM_SBSERVERNAME."</b></th><th><b>"._AM_SBSERVERIP."</b></th><th><b>"._AM_SBSERVERPORT."</b></th><th><b>"._AM_SBSERVERTIMEZONE."</b></th><th><b>"._AM_SBBOOKABLE."</b></th><th><b>"._AM_SBREGION."</b></th><th><b>"._AM_DELETE."</b></th>";
		echo "</tr>\n";
        if ( $result = $xoopsDB->query($sql) ) {
            while ( $myrow = $xoopsDB->fetchArray($result) ) {
                $serverid=$myrow["serverid"];
                $servername = $myrow["servername"];
                $serverip = $myrow["serverip"];
                $serverport = $myrow["serverport"];
                $serverzone = $myrow["serverzone"];
                $bookable = $myrow["is_bookable"];
                $region = $myrow["region"];
                echo "<tr class='bg1'><td><a href='serveradmin.php?serverid=".$serverid."'>".$servername."</a></td><td>";
                echo $serverip ."</td>";
                echo "<td>".$serverport."</td>";
                echo "<td>".$serverzone."</td>";
                if ($bookable==1) {
                    $bookimage = "check.gif";
                }
                else {
                    $bookimage = "uncheck.gif";
                }
                echo "<td> <img src='../images/".$bookimage."'</td>";
                echo "<td>".$region."</td>";
                echo "<td><form method='post' action='index.php'>";
                echo "<input type=hidden name='serverid' value='".$serverid."'>";
                echo "<input type=hidden name='op' value='deleteserver'>";
                echo "<input type=submit value='Delete'></form></td>";
                echo "</tr>\n";
            }
		}
        echo "</table></td></tr></table>";
		break;

	case "default":
	default:
		echo "<h4>"._AM_CONFIG."</h4>";
		echo"<table width='100%' border='0' cellspacing='1' class='outer'><tr><td class=\"odd\">";
		echo " - <b><a href='index.php?op=servermanager'>"._AM_SERVERMNGR."</a></b><br /><br />";
        echo " - <a href='".XOOPS_URL."/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=".$xoopsModule->getVar('mid')."'>"._AM_SBSERVBOOKPREF."</a>";
   		echo "</td></tr></table>";
		break;
}
xoops_cp_footer();
?>

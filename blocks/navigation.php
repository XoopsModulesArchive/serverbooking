<?php
// $Id: news_bigstory.php,v 1.6 2003/02/12 11:37:52 okazu Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------- //
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
function sh_nav() {
    global $xoopsDB, $xoopsUser;
    include_once XOOPS_ROOT_PATH."/modules/serverbooking/class/serverteam.php";
    include_once XOOPS_ROOT_PATH."/modules/serverbooking/class/server.php";
	if (is_object($xoopsUser)) {
		$uid = $xoopsUser->getVar('uid');
    }
    else {
        $uid = 0;
    }
    $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
    $servers = $server_handler->getAdminServers($xoopsUser->getVar('uid'), true);

    //Calendar, Search, Book, Signup, My Servers, Manage [], Query Server
    $block['content']  = "<table border='0' cellspacing='1'><tr><td id='mainmenu'>";
    $block['content'] .= "<a class='menuTop' href='".XOOPS_URL."/modules/serverbooking/index.php' target='_self'>Calendar</a>";
    $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/search.php' target='_self'>Search Servers</a>";
    $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/BookMatch.php' target='_self'>Book Server</a>";
    $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/query.php' target='_self'>Query Server</a>";
    $block['content'] .= "<a class='menuMain' href='".XOOPS_URL."/modules/serverbooking/server.php' target='_self'>Signup Server</a>";
    if (count($servers)>0) {
        foreach ($servers as $serverid => $server) {
            $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/serveradmin.php?serverid=$serverid' target='_self'>Manage ".$server['name']."</a>";
            if ($server['bookable']) {
                if (!isset($serverlink)) {
                    $serverlink = "?";
                }
                else {
                    $serverlink .= "&";
                }
                $serverlink .= 'serverid%5B%5D='.$serverid;
            }
        }
        if (isset($serverlink)) {
            $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/index.php".$serverlink."' target='_self'>My Servers</a>";
        }
    }    
    
    $block['content'] .= "<a class='menuMain' href='".XOOPS_URL."/modules/serverbooking/teams.php' target='_self'>Team List</a>";
    $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/serverteam.php' target='_self'>Signup Team</a>";
    
    $serverteam_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
    $teams = $serverteam_handler->getTeams($uid);
    
    $teamadmin = false;
    if (count($teams) > 0) {
        $teamadmin = true;
        foreach ($teams as $teamid => $team) {
            $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/teamadmin.php?teamid=".$teamid."' target='_self'>Manage ".$team->getVar('tag')."</a>";
        }
    }
    $block['content'] .= "<a class='menuMain' href='".XOOPS_URL."/modules/serverbooking/scrimms.php' target='_self'>Scrimm List</a>";
    if ($teamadmin) {
        $block['content'] .= "<a class='menuSub' href='".XOOPS_URL."/modules/serverbooking/addscrimm.php' target='_self'>Create Scrimm</a>";
    }
    $block['content'] .= "</td></tr></table>";
 	return $block;
}

?>
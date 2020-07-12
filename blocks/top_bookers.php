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

function b_bookers_show($options) {
    global $xoopsDB;
    $block = array();
    $numbookers = $options[0];
    $order = $options[1];
    $numdays = time() - $options[2] * 24 * 3600;
    $block['title'] = _MB_SB_TOPBOOKERS;
    $sql = "SELECT u.uid, u.uname, count( b.bookid ) AS Bookings, SUM( (b.end - b.begin) / 3600 ) AS BookedHours
            FROM ".$xoopsDB->prefix('server_bookings')." b, ".$xoopsDB->prefix('users')." u
            WHERE u.uid = b.bookerid AND b.status = 'Approved' AND b.begin > $numdays
            GROUP BY u.uname ORDER BY $order DESC LIMIT 0, $numbookers";
    $result= $xoopsDB->query($sql);
    if ($xoopsDB->getRowsNum($result) > 0) {
        $block['content']  = "<table border='0' cellspacing='1'>";
        $block['content'] .= "<tr class='head'><td>"._MB_SB_BOOKER."</td><td>"._MB_SB_BOOKINGS."</td><td>"._MB_SB_HOURS."</td></tr>";
    }
    while ($myrow = $xoopsDB->fetchArray($result)) {
        if (!isset($class) || $class == 'even') {
            $class = 'odd';
        }
        else {
            $class = 'even';
        }
        $block['content'] .= "<tr class='$class'><td><a href='".XOOPS_URL."/userinfo?uid=".$myrow['uid']."'>".$myrow['uname']."</a></td><td>".$myrow['Bookings']."</td><td>".$myrow['BookedHours']."</td></tr>";
    }
    $block['content'] .= "</table>";
    return $block;
}

function b_bookers_edit($options) {
	$form = _MB_SB_DISP."&nbsp;<input type='text' name='options[]' value='".$options[0]."' />&nbsp;"._MB_SB_BOOKERS."\n";
	$form .= _MB_SB_ORDER."&nbsp;<select name='options[]'>";
	$form .= "<option value='Bookings'";
	if ( $options[1] == "Bookings" ) {
		$form .= " selected='selected'";
	}
	$form .= ">"._MB_SB_BOOKINGS."</option>\n";
	$form .= "<option value='BookedHours'";
	if($options[1] == "BookedHours"){
		$form .= " selected='selected'";
	}
	$form .= ">"._MB_SB_HOURS."</option>\n";
	$form .= "</select>\n";
	$form .= "Period &nbsp;<input type='text' name='options[]' value='".$options[2]."' />&nbsp;days\n";
	return $form;
}
?>
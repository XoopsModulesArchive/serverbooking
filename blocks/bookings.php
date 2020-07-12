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

function sh_bookings() {
    global $xoopsDB, $xoopsUser, $xoopsConfig;
	if (is_object($xoopsUser)) {
		$userid = $xoopsUser->getVar('uid');
		$offset = ($xoopsUser->timezone() * 3600);
		$block = array();
		$block['title'] = ""._BL_SBBOOKINGSFOR."".$xoopsUser->getVar("uname");
		$block['content']  = "<table border='0' cellspacing='1'><div align='left'>";
        $now = time() - ($xoopsConfig['server_TZ'] * 3600);
		$sql = "SELECT * FROM ".$xoopsDB->prefix("server_bookings")." b, 
		       ".$xoopsDB->prefix("team_server")." s 
		          WHERE s.serverid=b.serverid AND b.bookerid=".$userid." AND b.begin>$now ORDER BY b.begin ASC";
		$result= $xoopsDB->query($sql);
		while ($myrow = $xoopsDB->fetchArray($result)) {
	        $weekday = date( 'D', $myrow["begin"] + $offset);
			$day= date('d/m', $myrow["begin"] + $offset);
            $timebegin = date('H:i', $myrow["begin"] + $offset);
            $timeend = date('H:i', $myrow["end"] + $offset);
            $serverid = $myrow["serverid"];
            $servername = $myrow["servername"];
            $status = $myrow["status"];
			if ($status=="Pending") {
				$bookings=1;
				$fontcl="Orange";
			}
			elseif ($status=="Declined") {
				$bookings=1;
				$fontcl="Red";
    		}
			elseif ($status=="Approved") {
				$bookings=1;
				$fontcl="green";
			}
			elseif ($status=="Put On Hold") {
				$bookings=1;
				$fontcl="blue";
			}
            if ((isset($class))&&($class=="odd")) {
                $class = "even";
            }
            else {
                $class = "head";
            }
			$block['content'] .= "<tr><td><font color='".$fontcl."'>".$weekday." ".$day." ".$timebegin."-".$timeend." ".$servername." - <a href='".XOOPS_URL."/modules/serverbooking/display-event.php?id=".$myrow["bookid"]."' target='_self'>".$status."</a></td></tr>";
		}
		if (!isset($bookings)) {
			$block['content'] .= "<tr><td><font color='green'></br><b>"._BL_SBNOBOOKINGS."</b></font></td></tr>";
		}
		$sql = "SELECT sa.serverid, s.servername, b.begin, b.end, b.status, b.bookid FROM
                        ".$xoopsDB->prefix("server_serveradmins")." sa, 
                        ".$xoopsDB->prefix("team_server")." s, 
		                ".$xoopsDB->prefix("server_bookings")." b 
		                  WHERE s.serverid=sa.serverid AND 
		                        b.serverid=s.serverid AND 
		                        b.begin > $now AND 
		                        status='Pending' AND
		                        sa.uid=".$userid;
		$result = $xoopsDB->query($sql);
		while ($myrow = $xoopsDB->fetchArray($result)) {
		        if (!isset($first)) {
		            $block['content'] .= "<tr><th><font color=black>".$myrow['servername']."</font></th></tr>";
		            $first = 1;
		        }
		        $weekday = date( 'D', $myrow["begin"]+$offset);
		        $day= date('d/m', $myrow["begin"]+$offset);
		        $timebegin = date('H', $myrow["begin"]+$offset);
		        $timeend = date('H', $myrow["end"]+$offset);
		        $status = $myrow["status"];
		        if ((isset($class))&&($class=="odd")) {
		            $class = "even";
		        }
		        else {
		            $class = "head";
		        }
		        $block['content'] .= "<tr><td><font color='Yellow'>".$weekday." ".$day." ".$timebegin."-".$timeend." - <a href='".XOOPS_URL."/modules/serverbooking/display-event.php?id=".$myrow["bookid"]."' target='_self'>".$status."</a></td></tr>";
		}
		$block['content'] .= "</div></table>";
		return $block;
	}
}
?>
<?php
function getBookings($start, $view, $servers = array(), $userzone) {
    global $xoopsDB;
    $ret = array();
    if ($view == 2) {
        $end = $start + 7 * 24 * 3600;
    }
    else {
        $lastday = date('t', $start);
        $end = $start + $lastday * 24 * 3600;
    }
    $clause = "";
    
    if (count($servers)>0) {
        $serverclause = " AND b.serverid=s.serverid";
        foreach ($servers as $thisserver) {
            if (is_object($thisserver)) {
                $thisserver = $thisserver->getVar('serverid');
            }
            if (!isset($counter)) {
                $serverclause .= " AND (b.serverid=".$thisserver;
            }
            else {
                $serverclause .= " OR b.serverid=".$thisserver;
            }
            $counter = 1;
        }
        $serverclause .= ")";
    }
    else {
        $serverclause = " AND b.serverid=s.serverid AND s.is_bookable=1";
    }
    $clause = "AND status!='Declined'";
    $sql = "SELECT * FROM ".$xoopsDB->prefix("server_bookings")." b, ".$xoopsDB->prefix("team_server")." s WHERE b.begin >= $start AND b.begin <= $end ".$clause." ".$serverclause." ORDER BY b.begin ASC";
    $query2 = $xoopsDB->query($sql);
    while ($results = $xoopsDB->fetchArray($query2)) {
        $bookid = $results['bookid'];
        if ($results["booker"]) {
            if ($results["status"]=="Approved") {
                $ret[$bookid]["status"] = " (A)";
                $ret[$bookid]['statuscol'] = "green";
            }
            elseif ($results["status"]=="Put On Hold") {
                $ret[$bookid]['status'] = " (H)";
                $ret[$bookid]['statuscol'] = "blue";
            }
            elseif ($results["status"]=="Pending") {
                $ret[$bookid]['status'] = " (P)";
                $ret[$bookid]['statuscol'] = "yellow";
            }
            elseif ($results["status"]=="Declined") {
                $ret[$bookid]['status'] = " (D)";
                $ret[$bookid]['statuscol'] = "red";
            }
            $ret[$bookid]['timestamp'] = $results['begin'] + $userzone*3600;
            $ret[$bookid]['begin'] = date('H:i', ($results["begin"] + ($userzone * 3600 )) );
            $ret[$bookid]['end'] = date('H:i', ($results["end"] + ($userzone * 3600 )) );
            $ret[$bookid]['servername'] = $results['servername'];
            $ret[$bookid]['title'] = $results["booker"]." vs ".$results["opponent"];
            if (($results["matchtype"] == "Ladder Match") || ($results["matchtype"] == "Arena Ladder Match")) {
                $ret[$bookid]['type'] = "ladder";
            }
            elseif ($results["matchtype"]=="Practice") {
                $ret[$bookid]['type'] = "practice";
            }
            else {
                $ret[$bookid]['type'] = "scrimm";
            }
            $ret[$bookid]['bookid'] = $results['bookid'];
        }
    }
    return $ret;
}
function getRegions() {
    global $xoopsDB;
    $ret = array();
    $sql = "SELECT DISTINCT region FROM ".$xoopsDB->prefix("team_server");
    $query = $xoopsDB->query($sql);
    while ($result = $xoopsDB->fetchArray($query)) {
        $ret[$result['region']] = $result['region'];
    }
    return $ret;
}

function TrimLeasedFile($timestamp) {
    global $xoopsDB;
    include_once('class/server.php');
    $sql = "SELECT * FROM ".$xoopsDB->prefix("server_bookings")." WHERE end>$timestamp AND wonid>0 AND status='Approved'";
    $result = $xoopsDB->query($sql);
    $comment = "";
    $content = array();
    $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
    $servers = $server_handler->getObjects(null, true);
    while ($booking=$xoopsDB->fetchArray($result)) {
        $thisserver = $servers[$booking['serverid']];
        $serverid = $booking["serverid"];
        $serveroffset = $thisserver->getServerZone() * 3600;
        $begin = $booking["begin"]+$serveroffset;
        $end = $booking["end"]+$serveroffset;
        $wonid = explode(';', $booking["wonid"]);
        $timebegin= date('ymdHi', $begin);
        $timeend=date('ymdHi', $end);
        if (!isset($content[$serverid])) {
            $content[$serverid] = "";
        }
        foreach ($wonid as $thiswonid) {
            $thiswonid = intval($thiswonid);
            if ($thiswonid > 0) {
                $content[$serverid] .= $thiswonid." ".$timebegin." ".$timeend."\n";
            }
        }
    }
    foreach ($servers as $serverid => $servername) {
        $filename = XOOPS_ROOT_PATH."/modules/serverbooking/prefs/leasedSA".$serverid.".txt"; //Relative path to the text file on webserver - not game server
        $handle = fopen($filename, 'w');
        if (isset($content[$serverid]) && $content[$serverid] != '') {
            if (!fwrite($handle, $content[$serverid])) {
                $comment .= "<br>"._MA_SBCANTWRITEFILE." ($filename)";
            }
        }
        else {
            //$comment .="<br>$servername "._MA_SBOPTIMISED."";
        }
        fclose($handle);
    }
    return $comment;
}

function serverSelect($op, $defserver, $all="") {
    global $xoopsDB, $view;
    include XOOPS_ROOT_PATH."/class/xoopsformloader.php";
    $mform = new XoopsThemeForm("Server Selection", "serverform", xoops_getenv('PHP_SELF'), "get");
    $server_select = new XoopsFormSelect('Server', 'serverid', $defserver);
    //get serverhandler
    $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
    //get all bookable servers
    $criteria = new Criteria('is_bookable', 1);
    $servers = $server_handler->getObjects($criteria, true);
    unset($criteria);
    if (count($servers)>1) {
        foreach ($servers as $sid => $sname) {
            $server_select->addOption($sid, $sname);
        }
        if (isset($all)) {
            $server_select->addOption(0, 'All Servers');
        }
        $button_tray = new XoopsFormElementTray('' ,'');
        $submit = new XoopsFormButton('', 'select', 'Select', 'submit');
        $button_tray->addElement($submit);
        $op_hidden = new XoopsFormHidden('op', $op);
        $view_hidden = new XoopsFormHidden('view', $view);
        $mform->addElement($server_select);
        $mform->addElement($button_tray);
        $mform->addElement($op_hidden);
        $mform->addElement($view_hidden);
        $mform->display();
    }
}

function selectcheck($val1, $val2) {
    if ($val1==$val2) {
        return "selected";
    }
    else {
        return;
    }
}

function TimeCheck($bookbegin) {
    global $xoopsConfig;
	$currenttime = time() - ($xoopsConfig['server_TZ'] * 3600);
	if ($currenttime > $bookbegin) {
		return _MA_SBDATETIMEPASS;
	}
	else {
		return false;
	}
}

function getwonid($uid) {
    global $xoopsDB;
    $sql = "SELECT wonid FROM ".$xoopsDB->prefix("server_bookers")." WHERE uid=".$uid;
    $results = $xoopsDB->query($sql);
    if ($xoopsDB->getRowsNum($results)>0) {
        $row = $xoopsDB->fetchArray($results);
        return $row["wonid"];
    }
    else {
        return false;
    }
}
function getirc($uid) {
    global $xoopsDB;
    $sql = "SELECT irc FROM ".$xoopsDB->prefix("server_bookers")." WHERE uid=".$uid;
    $results = $xoopsDB->query($sql);
    if ($xoopsDB->getRowsNum($results)>0) {
        $row = $xoopsDB->fetchArray($results);
        return $row["irc"];
    }
    else {
        return false;
    }
}
function isBooker($uid) {
    global $xoopsDB;
    $sql = "SELECT uid FROM ".$xoopsDB->prefix("server_bookers")." WHERE uid=".$uid;
    $results = $xoopsDB->query($sql);
    if ($xoopsDB->getRowsNum($results)>0) {
        return true;
    }
    else {
        return false;
    }
}

function getServeridFromBookid($id) {
    global $xoopsDB;
    $sql = "SELECT serverid FROM ".$xoopsDB->prefix("server_bookings")." WHERE bookid=".$id;
    $result = $xoopsDB->query($sql);
    if ($xoopsDB->getRowsNum($result) > 0) {
        $thisserver = $xoopsDB->fetchArray($result);
        return $thisserver["serverid"];
    }
    else {
        return false;
    }
}
?>

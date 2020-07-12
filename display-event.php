<?php

//Requires a link as "display-event.php?bookid=$bookid" (from bookings calendar page)
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include "functions.php";
include_once('class/server.php');
include_once('class/booking.php');
$op = isset($_GET['op']) ? $_GET['op'] : 'default';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (isset($_POST)) {
	foreach ($_POST as $k => $v) {
		${$k} = $v;
	}
}

$server_handler =& xoops_getmodulehandler('server', 'serverbooking');
$booking_handler =& xoops_getmodulehandler('booking', 'serverbooking');
switch ($op) {
    case 'confirm':
         $uid = $xoopsUser->getVar('uid');
         $bookid = $_POST["bookid"];
         $thisbooking =& $booking_handler->get($bookid);
         $thisbooking->setVar('status', $_POST['status']);
         $thisserver =& $server_handler->get(intval($serverid));
         if (($thisserver->isServerAdmin($xoopsUser->getVar("uid")))||($thisbooking->isOwner($xoopsUser->getVar('uid')))) {
             if ($thisbooking->setStatus($status, $thisserver)) {
                 redirect_header("index.php",3, ""._MA_SBMATCHSTATUSSETTO." ".$status);
                 break;
             }
             else {
                 break;
             }
         }
         else {
             redirect_header("index.php",3, _MA_SBACCESSDENIED);
         }
         break;

    case 'delete':
         if ($xoopsUser) {
             include("language/".$xoopsConfig['language']."/admin.php");
             $thisbooking =& $booking_handler->get($id);
             $thisserver = $server_handler->get($thisbooking->getVar('serverid'));
             $serverid = $thisserver->getVar('serverid');
             $status = $thisbooking->getVar('status');
             if (($thisserver->isServerAdmin($xoopsUser->getVar("uid")))||($thisbooking->isOwner($xoopsUser->getVar('uid')))) {
                 if ( !empty($ok) ) {
                     if (empty($id)) {
                         redirect_header('index.php?op=default',2,_AM_EMPTYNODELETE);
                         exit();
                     }
                     $comment = $booking_handler->delete($thisbooking);
                     redirect_header("index.php",3,""._MA_SBBOOKREQDEL."".$comment);
                     break;
                 }
                 else {
                     echo "<h4>"._AM_CONFIG."</h4>";
                     xoops_confirm(array('op' => 'delete', 'id' => $id, 'ok' => 1), 'display-event.php', _AM_RUSUREDELBOOK);
                 }
                 break;
             }
             else {
                 redirect_header("index.php",3, _MA_SBACCESSDENIED);
                 break;
             }
         }
         else {
             redirect_header("index.php",3, _MA_SBACCESSDENIED2);
         }
         break;

    default:
    $view = $_GET['view'];
    $thisbooking =& $booking_handler->get($id);
    $serverid = $thisbooking->getVar('serverid');
    $thisserver =& $server_handler->get($serverid);
    $serverName = $thisserver->getVar('servername');
    if ($xoopsUser) {
        $offset = $xoopsUser->timezone() * 3600;
    }
    else {
        $offset = $xoopsConfig['default_TZ'] * 3600;
    }
    $begin = date('H:i',$thisbooking->getVar('begin') + $offset);
    $end = date('H:i',$thisbooking->getVar('end')+ $offset);
	$mdate= date('D d-m-y',$thisbooking->getVar('begin'));
	echo "<table border='0' cellpadding='0' cellspacing='0' valign='top' width='100%'><tr><td>";
	echo "<tr><td><table width='100%' border='0' cellpadding='0' cellspacing='0'>";
	echo "<tr class='outer'><td><img src='images/serverbooking.gif'></td>";
    echo "<td align='right'><a href='index.php?serverid=".$serverid."&view=$view'>"._MA_SBSERVBOOKING."</a></td>";
	echo "</tr></table>";
	echo "<tr><td class='odd' colspan='2'><table width='100%' border='0' cellpadding='4' cellspacing='1'>";
    echo "<tr><td class='head' width='13%'><b>"._MA_SBSERVER."</b></td><td class='even' colspan='5'>".$serverName."</td>";
	echo "<tr><td class='head' width='13%'><b>"._MA_SBDATE."</b></td><td class='even' width='13%'>".$mdate."</td>";
	echo "<td class='head' width='13%'><b>"._MA_SBFROM."</b></td><td class='even' width='20%'>".$begin."</td>";
	echo "<td class='head' width='13%'><b>"._MA_SBTO."</b></td><td class='even' width='15%'>".$end."</td>";
	echo "</tr><tr>";
	echo "<td class='head'><b>"._MA_SBBOOKER."</b></td><td class='even'>".$thisbooking->getVar('booker')."</td>";
	echo "<td class='head'><b>"._MA_SBOPPONENT."</b></td><td class='even'>".$thisbooking->getVar('opponent')."</td>";
	echo "<td class='head'><b>"._MA_SBIRCCHANNEL."</b></td><td class='even'>".$thisbooking->getVar('irc')."</td>";
	echo "</tr>";
	echo "<tr><td class='head'><b>"._MA_SBMATCHTYPE."</b></td><td class='even'>".$thisbooking->getVar('matchtype')."</td><td class='head'><b>"._MA_SBSTATUS."</b></td><td class='even'>".$thisbooking->getVar('status')."</td>";
	if ($xoopsUser) { //User authentication.
	    $uid = $xoopsUser->getVar("uid"); //Function to get the userid of the currently logged in
	    $isadmin = 0;
	    if ($thisserver->isServerAdmin($uid)) {
	        $isadmin = 1;
	    }
	    if ($thisbooking->getVar('admin')) {
	        $admin = new XoopsUser($thisbooking->getVar('admin')); //Find userid of the serveradmin, who has last changed the status of a booking
	        $by = $admin->getVar("uname");
	    }
	    else {
	        $by = '';
	    }
	    
	    $thisbooker = new XoopsUser($thisbooking->getVar('bookerid'));
        $bookerName = $thisbooker->getVar("uname");
	    echo "<td class='head'><b>"._MA_SBBOOKEDBY."</b></td><td class='even'><a href='".XOOPS_URL."/userinfo.php?uid=".$thisbooking->getVar("bookerid")."'>".$bookerName."</a></td>";
	    echo "</tr>";
	    echo "<tr><td class='head'><b>"._MA_SBADMIN."</b></td><td class='even' valign='top'>".$by."</td>";
	    if ($isadmin) {
	        echo "<td class='head'><b>"._MA_SBACTION."</b></td>";
	        echo "<td class='even'>";
	        $status=$thisbooking->getVar('status');
			echo "<form method='post' action='display-event.php'>";
			echo "<input type='hidden' name='bookid' value=".$id.">";
			echo "<input type='hidden' name='op' value='confirm'>";
            echo "<input type='hidden' name='serverid' value=".$serverid.">";
   			echo "<SELECT name='status'>
         		      <OPTION value='Approved' ".selectcheck('Approved',$status).">"._MA_SBAPPROVED."</OPTION>
				      <OPTION value='Put On Hold' ".selectcheck('Put On Hold',$status).">"._MA_SBPUTONHOLD."</OPTION>
				      <OPTION value='Declined' ".selectcheck('Declined',$status).">"._MA_SBDECLINED."</OPTION>
				      </SELECT>&nbsp";
			echo "<input type=submit value='Set'></form></td>";
		}
		if ($thisserver->isServerAdmin($uid) || $thisbooking->isOwner($uid))
		{
            	echo "<td class='head'><a href='BookMatch.php?bookid=".$id."&action=edit'><img src='images/edit.gif'></a></td>";
            	echo "<td class='even' valign='top'><a href='display-event.php?id=".$id."&op=delete'><img src='images/delete.gif'></a></td>";
       	}
   	}
   	echo "</tr>";
   	echo "<tr align=left><td><b>"._MA_SBCOMMENTS."</b></td><td colspan=3>".$thisbooking->getVar('bookcomments')."</td><td></td>";
    echo "</tr></table></td></tr></table>";
    break;
}
include_once XOOPS_ROOT_PATH.'/footer.php';
?>

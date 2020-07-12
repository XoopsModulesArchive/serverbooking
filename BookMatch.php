<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include_once "class/server.php";
include_once "class/booking.php";
include "functions.php";

if (!$xoopsUser) {
    redirect_header(XOOPS_URL."/login.php",3,_MA_SBSORRYLOGMAKBOOK);
    exit();
}

$server_handler =& xoops_getmodulehandler('server', 'serverbooking');
$booking_handler =& xoops_getmodulehandler('booking', 'serverbooking');
$uid = $xoopsUser->getVar("uid");
$action = isset($_GET['action']) ? $_GET['action'] : "";
$action = isset($_POST['action']) ? $_POST['action'] : $action;

$zone = isset($_GET['zone']) ? $_GET['zone'] : null;
$zone = isset($_POST['zone']) ? $_POST['zone'] : $zone;
if (!isset($zone)) {
    $zone = $xoopsUser->timezone();
}

switch($action) {
    case "edit":
        if (isset($_GET['bookid'])) {
        	$id = intval($_GET['bookid']);
            $booking =& $booking_handler->get($id);
            $server =& $server_handler->get($booking->getVar('serverid'));
            $serverid = $server->getVar('serverid');
            //Only server admins or bookers gain access
            if ( ($server->isServerAdmin($uid) ) || ($booking->isOwner($uid) ) ) {
                $booking->setVar('begin', $booking->getVar('begin') + $zone * 3600);
                $booking->setVar('end', $booking->getVar('end') + $zone * 3600);
            }
            else {
                redirect_header("display-event.php?id=".$id,3,_MA_SBSORRYSERVADMIN);
                exit();
            }
            $submit=_MA_SUBMITEDIT;
            $bookerName = XoopsUser::getUnameFromId($booking->getVar('bookerid'));
        }
        break;
    
    case "submit":
        //Strip timezone
        $begin = $_POST['begin'];
        $end = $_POST['end'];
        $userzone = $zone;
        $serverid = intval($_POST['serverid']);
        $begin = strtotime($begin['date']) + $begin['time'] - ($userzone*3600);
        $end = strtotime($end['date']) + $end['time'] - ($userzone*3600);
        $server =& $server_handler->get($serverid);
        if (isset($_POST['id'])) {
            $booking = $booking_handler->create(false);
            $booking->setVar('bookid', $_POST['id']);
            $submit=_MA_SUBMITEDIT;
        }
        else {
            $booking = $booking_handler->create();
            $submit = _AM_SUBMITBOOKING;
        }
        $bookerid = intval($_POST['bookerid']);
        $booking->setVar('bookerid', $bookerid);
        $booking->setVar('begin', $begin);
        $booking->setVar('end', $end);
        $booking->setVar('booker', $_POST['booker']);
        $booking->setVar('opponent', $_POST['opponent']);
        $booking->setVar('bookeremail', $_POST['bookerEmail']);
        $booking->setVar('matchtype', $_POST['matchtype']);
        $booking->setVar('wonid', $_POST['wonid']);
        $booking->setVar('bookcomments', $_POST['bookcomments']);
        $booking->setVar('irc', $_POST['irc']);
        $booking->setVar('serverid', $serverid);
        
        $bookerName = XoopsUser::getUnameFromId($bookerid);
        
        $error = $booking->validate($server);
        if (!$error) {
            if ($server->isServerAdmin($uid)) {
                $status = 'Approved';
                $admin = $xoopsUser->getVar("uid");
            }
            else {
                $status = 'Pending';
                if (!isset($_POST['admin'])) {
                    $admin = '';
                }
            }
            $booking->setVar('status', $status);
            $booking->setVar('admin', $admin);
            if ($comment = $booking_handler->insert($booking)) {
            	$matchtype = $myts->htmlSpecialChars($_POST['matchtype']);
                redirect_header("index.php?serverid=".$serverid,3, $matchtype ." ".$comment);
                exit();
            }
            else {
                echo _MA_SBERRFIREWALL;
            }
        }
        else {
            echo $error;
        }
        //Put back timezone correction
        $booking->setVar('begin', $booking->getVar('begin') + ($userzone *3600));
        $booking->setVar('end', $booking->getVar('end') + ($userzone *3600));
        break;
    
    default:
        $submit = _AM_SUBMITBOOKING;
        $bookerName = $xoopsUser->getVar("uname");
        
        $booking = $booking_handler->create();
        $booking->setVar('bookeremail', $xoopsUser->getVar("email"));
        $booking->setVar('wonid', getwonid($uid));
        $booking->setVar('irc', getirc($uid));
        $booking->setVar('bookerid', $xoopsUser->getVar('uid'));
        if ((isset($_POST['timebegin']))&&(isset($_POST['timeend']))) {
            $booking->setVar('begin', $_POST['timebegin']);
            $booking->setVar('end', $_POST['timeend']);
        }
        else {
            $booking->setVar('begin', time() + ($zone * 3600) + 600);
            $booking->setVar('end', time() + ($zone * 3600) + 7800);
        }
        $serverid = isset($_GET['serverid']) ? $_GET['serverid'] : null;
        $serverid = isset($_POST['serverid']) ? $_POST['serverid'] : $serverid;
        $booking->setVar('serverid', $serverid);
        
        $booking->setVar('booker', "");
        $booking->setVar('matchtype', null);
        $booking->setVar('bookcomments', "");

        break;
}

//get all bookable servers
$criteria = new Criteria('is_bookable', 1);
$criteria->setSort('servername');
$bookservers = $server_handler->getObjects($criteria, true, false);

$matchtypes = array('Scrimm' => _MA_SBSCRIMMAGE,
                    'Ladder Match' => _MA_SBLADDERMATCH,
                    'Practice' => _MA_SBPRACTICE);

include XOOPS_ROOT_PATH."/class/xoopsformloader.php";
$sform = new XoopsThemeForm(_AM_SUBMITBOOKING." Server", "serverform", "BookMatch.php");
$server_select = new XoopsFormSelect(_MA_SBSERVER, 'serverid', $booking->getVar('serverid'));
$server_select->addOption('', "-=-");
$server_select->addOptionArray($bookservers);
$zone_select = new XoopsFormSelectTimezone(_MA_SBYOURTIMEZONE, 'zone', $zone, 1);
$begin_select = new XoopsFormDateTime(_MA_SBFROMYOURTIME, 'begin', 15, $booking->getVar('begin'));
$end_select = new XoopsFormDateTime(_MA_SBTOYOURTIME, 'end', 15, $booking->getVar('end'));
$bookerid_hidden = new XoopsFormHidden('bookerid', $booking->getVar('bookerid'));
$yourteam_text = new XoopsFormText(_MA_SBYOURTEAM, 'booker', 15, 20, $booking->getVar('booker'));
$yournick_label = new XoopsFormLabel(_MA_SBYOURNAMENICK, $bookerName);
$youremail_text = new XoopsFormText(_MA_SBYOUREMAIL, 'bookerEmail', 30, 40, $booking->getVar('bookeremail'));
$ircchan_text = new XoopsFormText(_MA_SBIRCCHANNEL, 'irc', 15, 40, $booking->getVar('irc'));
$opponent_text = new XoopsFormText(_MA_SBOPPONENT, 'opponent', 15, 40, $booking->getVar('opponent'));
$matchtype_select = new XoopsFormSelect(_MA_SBMATCHTYPE, 'matchtype', $booking->getVar('matchtype'));
$matchtype_select->addOptionArray($matchtypes);
$wonid_text = new XoopsFormText(_MA_SBWONIDAUTOADMIN, 'wonid', 25, 70, $booking->getVar('wonid'));
$comments_text = new XoopsFormTextArea(_MA_SBCOMMENTS, 'bookcomments', $booking->getVar('bookcomments'), 10, 25);
$submit_button = new XoopsFormButton('', 'submit', $submit, 'submit');
$action_hidden = new XoopsFormHidden('action', 'submit');
if ($booking->getVar('bookid')) {
    $id_hidden = new XoopsFormHidden('id', $booking->getVar('bookid'));
    $sform->addElement($id_hidden);
}
$sform->addElement($server_select);
$sform->addElement($zone_select);
$sform->addElement($begin_select);
$sform->addElement($end_select);
$sform->addElement($bookerid_hidden);
$sform->addElement($yournick_label);
$sform->addElement($youremail_text, true);
$sform->addElement($ircchan_text);
$sform->addElement($yourteam_text, true);
$sform->addElement($opponent_text, true);
$sform->addElement($matchtype_select);
$sform->addElement($wonid_text);
//$sform->addElement($wonid_label);
$sform->addElement($comments_text);
$sform->addElement($action_hidden);
$sform->addElement($submit_button);
$sform->display();


include_once XOOPS_ROOT_PATH.'/footer.php';
?>

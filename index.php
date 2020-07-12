<?php
include '../../mainfile.php';
include 'functions.php';
$serverid = isset($_GET['serverid']) ? $_GET['serverid'] : null;
$region = isset($_GET['region']) ? $_GET['region'] : null;
if (isset($_POST)) {
	foreach ($_POST as $k => $v) {
		${$k} = $v;
	}
}
if (isset($_POST['zone'])) {
   $userzone = $_POST['zone'];
}
elseif (isset($_GET['zone'])) {
   $userzone = $_GET['zone'];
}

if (isset($_POST['timestamp'])) {
    $timestamp = $_POST['timestamp'];
}
elseif (isset($_GET['timestamp'])) {
    $timestamp = $_GET['timestamp'];
}
if (!isset($userzone)) {
    if ($xoopsUser) {
        $userzone = $xoopsUser->timezone();
    }
    else {
        $userzone = $xoopsConfig['server_TZ'];
    }
}
$usertimevent = time() + (($userzone-$xoopsConfig['server_TZ']) * 3600);
if (!isset($_GET['view'])) {
    $view = $xoopsModuleConfig['defview'];
}
else {
    if ($_GET['view'] == 'week' || $_GET['view'] == 2) {
        $view = 2;
    }
    else {
        $view = 1;
    }
}

if ($view == 2) {
    $xoopsOption['template_main'] = 'serverbooking_week.html';
    include XOOPS_ROOT_PATH.'/header.php';
    if (isset($timestamp)) {
        $start = $timestamp;        
    }
    else {
        $start = $usertimevent;
    }
    $day = date ('j', $start);
    $month = date('n', $start);
    $year = date('Y', $start);
}
else {
    $xoopsOption['template_main'] = 'serverbooking_month.html';
    include XOOPS_ROOT_PATH.'/header.php';
    if (isset($timestamp)) {
        $start = $timestamp;
        $month = date('n', $timestamp);
        $year = date('Y', $timestamp);
    }
    elseif (isset($_POST['Month'])) {
        $month = $_POST['Month'];
        $year = $_POST['Year'];
        $start = mktime(0,0,0, $month, 1, $year);
    }
    elseif (isset($_GET['month'])) {
        $month = $_GET['month'];
        $year = $_GET['year'];
        $start = mktime(0,0,0, $month, 1, $year);
    }
    else {
        $month = date("n", $usertimevent);
        $year = date("Y", $usertimevent);
        $start = mktime(0,0,0, $month, 1, $year);
    }    
}

//get serverhandler
$server_handler =& xoops_getmodulehandler('server', 'serverbooking');

//get all bookable servers
$criteria = new Criteria('is_bookable', 1);
$criteria->setSort('servername');
$servers = $server_handler->getObjects($criteria, true);
unset($criteria);

//Find selected/filtered servers
if (!isset($serverid)) {
    $selected_servers = array();
    if (isset($region) ) {
        
        if (!isset($region)) {
            $region = array();
        }
        $selected_servers = $server_handler->filterServers($region);
        $xoopsTpl->assign('selected_reg', $region);
    }
}
else {
    if (!is_array($serverid)) {
        $serverid = array($serverid);
    }
    foreach ($serverid as $sid) {
        $selected_servers[$sid] =& $servers[$sid];
    }    
}

//Get server names(region) IP:Port
//Set links for next/previous
$serverName = "";
$serverlink = "";
if (count($selected_servers)>0) {
    foreach ($selected_servers as $sid => $thisserver) {
        $serverName .= $thisserver->getVar('servername')." (".$thisserver->getVar('region').") ".$thisserver->getVar('serverip').":".$thisserver->getVar('serverport')."<br />";
        $serverlink .= '&serverid%5B%5D='.$thisserver->getVar('serverid');
    }
}
else {
    $serverName = _MA_SBALLSERVERS;
}

//Find current time
$currentday = date("j", $usertimevent);
$currentmonth = date("n", $usertimevent);
$currentyear = date("Y", $usertimevent);
if ($selected_servers != array()) {
    $admins = $server_handler->getServerAdmins($selected_servers);
    if (count($admins)>0) {
        $xoopsTpl->assign('showserveradmin', 1);
    }
    foreach ($admins as $said => $saname) {
        $xoopsTpl->append('serveradmins', array('name' => $saname));
    }
    $rules = $server_handler->getRules($selected_servers, $userzone);
}
if (!isset($start) && $view == 1) {
    $start = mktime(0,0,0,$month, 1, $year);
}
//Get bookings for selected/filtered servers
$bookings = getBookings($start, $view, $selected_servers, $userzone);

if (count($bookings > 0)) {
    foreach ($bookings as $key => $thisbooking) {
        if (($thisbooking['timestamp'] > $usertimevent) || ($thisbooking['status'] == ' (A)')) {
            $dayofmonth = date ('j', $thisbooking['timestamp']);
            $allevents[$dayofmonth][] = $thisbooking;
        }
    }
}

//Get bookings for selected/filtered servers
$scrimm_handler =& xoops_getmodulehandler('scrimm', 'serverbooking');
if ($view == 2) {
    $end = $start + 7 * 24 * 3600;
}
else {
    $lastday = date('t', $start);
    $end = $start + $lastday * 24 * 3600;
}
$scrimmcriteria = new CriteriaCompo(new Criteria('begin', $start, '>='));
$scrimmcriteria->add(new Criteria('begin', $end, '<='));
$scrimmcriteria->add(new Criteria('begin', $usertimevent, '>='));
$scrimmcriteria->setSort('begin');
$scrimms = $scrimm_handler->getObjects($scrimmcriteria);

if (count($scrimms > 0)) {
    foreach ($scrimms as $key => $thisscrimm) {
        if ($thisscrimm->getVar('begin') > $usertimevent) {
            $dayofmonth = date ('j', $thisscrimm->getVar('begin'));
            $allscrimms[$dayofmonth][] = array('begin' => date('H:i', $thisscrimm->getVar('begin')), 'scrimmid' => $thisscrimm->getVar('scrimmid'));
        }
    }
}


//Convert server object array to id => name array
foreach ($servers as $sid => $thisserver) {
    $servers[$sid] = $thisserver->getVar('servername');
}

$lastday = date('t', $start);
switch ($view) {
    case 2:
        for ($k = $day; $k < $day+7; $k++) {
            $thismonth = $month;
            $thisyear = $year;
            $i = $k;
            if ($i > $lastday) {
                $i = $i - $lastday;
                $thismonth = $thismonth + 1;
                if ($thismonth > 12 ) {
                    $thismonth = 1;
                    $thisyear = $year+1;
                }
            }
            $weekday = date('w', mktime(0,0,0, $thismonth, $i, $thisyear));
            $weekdaytext = date('D', mktime(0,0,0, $thismonth, $i, $thisyear));
            $thismonth = date('n', mktime(0,0,0, $thismonth, $i, $thisyear));
            if (isset($allevents[$i])) {
                $noevents = false;
            }
            else {
                $noevents = 1;
                $allevents[$i] = array();
            }
            if (isset($rules[$weekday])) {
                $thisdayrules = $rules[$weekday];
            }
            else {
                $thisdayrules = array();
            }
            $thisdayscrimms = isset($allscrimms[$i]) ? $allscrimms[$i] : array();
            $xoopsTpl->append('days', array('noevents' => $noevents,
                                            'weekday' => $weekdaytext,
                                            'day' => $i,
                                            'month' => $thismonth,
                                            'bookings' => $allevents[$i],
                                            'rules' => $thisdayrules,
                                            'scrimms' => $thisdayscrimms));
        }
        $beginweek = date('W', mktime(0,0,0,$month, $day, $year));
        $endweek = date('W', mktime(0,0,0,$month, $k, $year));
        if ($beginweek == $endweek) {
            $xoopsTpl->assign('week', $beginweek);
        }
        else {
            $xoopsTpl->assign('week', $beginweek."/".$endweek);
        }
        $nextstamp = mktime(0,0,0,$month, $day, $year) + (7*24*3600);
        $prevstamp = mktime(0,0,0,$month, $day, $year) - (7*24*3600);
        $xoopsTpl->assign('servers', $servers);
        $xoopsTpl->assign('nextlink', $serverlink.'&timestamp='.$nextstamp);
        $xoopsTpl->assign('monthname', date('F', $start));
        $xoopsTpl->assign('todayyear', $currentyear);
        $xoopsTpl->assign('todaymonth', $currentmonth);
        $xoopsTpl->assign('today', $currentday);
        $xoopsTpl->assign('prevlink', $serverlink.'&timestamp='.$prevstamp);
        $xoopsTpl->assign('lastday', $lastday);
        break;        
    
    case 1:
    default:
        $rowno = 0;
        $firstday = date('w', mktime(0,0,0,$month, 1, $year));
        if ($firstday == 0) {
            $firstday = 7;
        }
        for ($k = 1; $k < $firstday; $k++) {
            if ($k == 1) {
                //Get week number
                $row[$rowno]['row'] = date('W', mktime(0,0,0,$month, 1, $year));
            }
            $row[$rowno]['events'][] = array('noevents' => true,
            'day' => "&nbsp;",
            'bookings' => array(),
            'rules' => array());
        }
        for ($i = 1; $i <= $lastday; $i++) {
            $weekday = date('w', mktime(0,0,0, $month, $i, $year));
            //If weekday is a monday
            if ($weekday == 1) {
                //Get week number
                $row[$rowno]['row'] = date('W', mktime(0,0,0,$month, $i, $year));
            }             	
            if (isset($rules[$weekday])) {
                $thisdayrules = $rules[$weekday];
            }
            else {
                $thisdayrules = array();
            }
            $thisdayscrimms = isset($allscrimms[$i]) ? $allscrimms[$i] : array();
            if (isset($allevents[$i])) {
                $noevents = false;
            }
            else {
                $allevents[$i] = array();
                $noevents = 1;
            }
            $row[$rowno]['events'][] = array('noevents' => $noevents,
                                            'day' => $i,
                                            'bookings' => $allevents[$i],
                                            'rules' => $thisdayrules,
                                            'scrimms' => $thisdayscrimms);
            if (($i+$firstday) % 7 == 1) {
                $rowno++;                 
            }
        }
        $xoopsTpl->assign('rows', $row);
        $xoopsTpl->assign('servers', $servers);
        $xoopsTpl->assign('nextlink', $serverlink.'&timestamp='.mktime(0,0,0,$month+1, 1,$year));
        $xoopsTpl->assign('monthname', date('F', $start));
        $xoopsTpl->assign('todayyear', $currentyear);
        $xoopsTpl->assign('todaymonth', $currentmonth);
        $xoopsTpl->assign('today', $currentday);
        $xoopsTpl->assign('thismonth', $month);
        $xoopsTpl->assign('prevlink', $serverlink.'&timestamp='.mktime(0,0,0,$month-1, 1,$year));
        $xoopsTpl->assign('lastday', $lastday);
        break;
}

for ($i = -12; $i <= 12; $i++) {
    if ($i ==0) {
        $suffix = "";
    }
    elseif ($i > 0) {
        $suffix = "+".$i;
    }
    else {
        $suffix = $i;
    }    
    $zones[$i] = "GMT ".$suffix;
}
$xoopsTpl->assign('zones', $zones);
$xoopsTpl->assign('zone', $userzone);
$xoopsTpl->assign('timestamp', $start);
$xoopsTpl->assign('todaytime', date('D j/n G:i:s', $usertimevent));
$xoopsTpl->assign('lang_selecttitle', "Server Selection");
$xoopsTpl->assign('lang_selectcaption', "Server");
$xoopsTpl->assign('lang_submit', "Select");
$xoopsTpl->assign('lang_serverid', "serverid");
$xoopsTpl->assign('reg_title', _MA_SELREGION);
$xoopsTpl->assign('reg_caption', _MA_SBREGION);
$xoopsTpl->assign('regoptions', getRegions());
$xoopsTpl->assign('hidden', array('view' => $view, 'zone' => $userzone));
$xoopsTpl->assign('serverlink', $serverlink);
if (isset($selected_servers)) {
    $selected_servers = array_keys($selected_servers);
    $xoopsTpl->assign('serverid', $selected_servers);
}
else {
    $xoopsTpl->assign('serverid', '');
}
$xoopsTpl->assign('serverName', $serverName);
include_once XOOPS_ROOT_PATH.'/footer.php';
?>
<?php
function addServerForm($action, $serverid="", $target='index.php') {
    include_once(XOOPS_ROOT_PATH.'/modules/serverbooking/class/server.php');
    global $xoopsConfig;
    if ($serverid) {
        $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
        $server =& $server_handler->get($serverid);
        $name = $server->getVar('servername');
        $ip = $server->getVar('serverip');
        $port = $server->getVar('serverport');
        $zone = $server->getServerZone();
        $bookable = $server->getVar('is_bookable');
        $region = $server->getVar('region');
    }
    else {
        $name = "Name";
        $ip = "IP";
        $port = "Port";
        $zone = $xoopsConfig['default_TZ'];
        $bookable = 0;
        $region = "Central";
    }
    include XOOPS_ROOT_PATH."/class/xoopsformloader.php";
    $mform = new XoopsThemeForm($action." Server", "serverform", $target);
    $op_hidden = new XoopsFormHidden('op', "saveserver");
    $submit = new XoopsFormButton('', 'submit', $action.' Server', 'submit');
    $action_hidden = new XoopsFormHidden('action', $action);
    $serverid_hidden = new XoopsFormHidden('serverid', $serverid);
    $button_tray = new XoopsFormElementTray('' ,'');
    $name = new XoopsFormText(_AM_SBSERVERNAME, 'servername', 30, 30, $name, 'E');
    $ip = new XoopsFormText(_AM_SBSERVERIP, 'serverip', 20, 20, $ip, 'E');
    $port = new XoopsFormText(_AM_SBSERVERPORT, 'serverport', 10, 10, $port, 'E');
    $zone_select = new XoopsFormSelectTimezone(_AM_SBSERVERTIMEZONE, 'serverzone', $zone, 1);
    $bookable = new XoopsFormCheckBox(_AM_SBBOOKABLE, 'bookable', $bookable);
    $bookable->addOption(1, _AM_YES);
    $region = new XoopsFormSelect(_AM_SBREGION, 'region', $region);
    $region->addOption('West');
    $region->addOption('Central');
    $region->addOption('North');
    $region->addOption('South');
    $button_tray->addElement($submit);
    $mform->addElement($name, true);
    $mform->addElement($ip, true);
    $mform->addElement($port, true);
    $mform->addElement($zone_select);
    $mform->addElement($bookable);
    $mform->addElement($region);    
    $mform->addElement($op_hidden);
    $mform->addElement($action_hidden);
    $mform->addElement($serverid_hidden);
    
    $mform->addElement($button_tray);
    $mform->display();
}
function addServerRule($serverid) {
    include XOOPS_ROOT_PATH."/class/xoopsformloader.php";
    $mform = new XoopsThemeForm(_AM_SBADDSERVRULE, "serverform", xoops_getenv('PHP_SELF'));
    $op_hidden = new XoopsFormHidden('op', "saverule");
    $submit = new XoopsFormButton('', 'submit', _AM_SBADDRULE, 'submit');
    $serverid_hidden = new XoopsFormHidden('serverid', $serverid);
    $button_tray = new XoopsFormElementTray('' ,'');
    $weekday_select = new XoopsFormSelect(_AM_SBWEEKDAY, 'weekday', '0');
    for ($i = 0; $i <= 6; $i++) {
        if ($i == 0) {
            $name = _AM_SBSUNDAY;
        }
        if ($i == 1) {
            $name = _AM_SBMONDAY;
        }
        if ($i == 2) {
            $name = _AM_SBTUESDAY;
        }
        if ($i == 3) {
            $name = _AM_SBWEDNESDAY;
        }
        if ($i == 4) {
            $name = _AM_SBTHURSDAY;
        }
        if ($i == 5) {
            $name = _AM_SBFRIDAY;
        }
        if ($i == 6) {
            $name = _AM_SBSATURDAY;
        }
        $weekday_select->addOption($i, $name);
    }
    $from_select = new XoopsFormSelect(_AM_SBFROMGMT, 'begin', '19');
    for ($i = 0; $i <=23; $i++) {
        $from_select->addOption($i, $i.":00");
    }
    $to_select = new XoopsFormSelect('To (GMT)', 'end', '21');
    for ($i = 0; $i <=23; $i++) {
        $to_select->addOption($i, $i.":00");
    }
    $reason = new XoopsFormText(_AM_SBREASON, 'reason', 20, 20, _AM_SBSERVCLOSED, 'E');
    $button_tray->addElement($submit);
    $mform->addElement($weekday_select);
    $mform->addElement($from_select);
    $mform->addElement($to_select);
    $mform->addElement($reason);
    $mform->addElement($op_hidden);
    $mform->addElement($serverid_hidden);
    $mform->addElement($button_tray);
    $mform->display();
}

function addTeamAdmin($userid, $serverid) {
    global $xoopsDB;
    $sql = "INSERT INTO ".$xoopsDB->prefix("server_serveradmins")." (uid, serverid) VALUES ('$userid', '$serverid')";
    if (!$xoopsDB->query($sql)) {
        return false;
    }
    else {
        return true;
    }
}
function delTeamAdmin($said) {
    global $xoopsDB;
    $sql = "DELETE FROM ".$xoopsDB->prefix("server_serveradmins")." WHERE said='$said'";
    if (!$xoopsDB->query($sql)) {
        return false;
    }
    else {
        return true;
    }
}
function getAllMembers() {
    global $xoopsDB;
    $sql = "SELECT uid, uname FROM ".$xoopsDB->prefix("users")." ORDER BY uname ASC";
    $result = $xoopsDB->query($sql);
    $count = 0;
    while ($row=$xoopsDB->fetchArray($result)) {
        $allmembers[$row["uid"]]=$row["uname"];
        $count++;
    }
    return $allmembers;
}
?>

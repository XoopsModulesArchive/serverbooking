<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include 'functions.php';
include 'admin/functions.php';
include 'language/'.$xoopsConfig['language'].'/admin.php';
if (isset($_POST)) {
    foreach ($_POST as $k => $v) {
        ${$k} = $v;
    }
}
if (isset($_GET['serverid'])) {
    $serverid = $_GET['serverid'];
}

if (!$xoopsUser) {
    redirect_header('index.php', 2, _NOPERM);
    exit();
}

$server_handler =& xoops_getmodulehandler('server', 'serverbooking');
$thisserver =& $server_handler->get($serverid);

$uid = $xoopsUser->getVar('uid');

if (!$thisserver->isServerAdmin($uid)) {
    redirect_header('index.php', 2, _NOPERM);
    exit();
}

if (isset($op)) {
    switch ($op) {
        case "saverule":
        $sql = "INSERT INTO ".$xoopsDB->prefix("server_rules")." (weekday, begin, end, serverid, reason) VALUES ($weekday, $begin, $end, $serverid, '$reason')";
        $xoopsDB->query($sql);
        if ($xoopsDB->getInsertId()) {
            $comment = _AM_SBSERVRULESUPDATE;
        }
        else {
            $comment = _AM_SBERRSERVRULNOTADD;
        }
        echo $comment;
        break;
        
        case "deleterule":
        $xoopsDB->query("DELETE FROM ".$xoopsDB->prefix("server_rules")." WHERE ruleid=$ruleid");
        if ($xoopsDB->getAffectedRows()>0) {
            $comment = _AM_SBSERVRULEDELSUCCESS;
        }
        else {
            $comment = _AM_SBSERVRULENOTDEL;
        }
        echo $comment;
        break;
        
        case 'saveserver':
            if (!isset($exclusive)) {
                $exclusive = 0;
            }
            if (!isset($bookable)) {
                $bookable = 0;
            }
            if (!isset($approved)) {
                $approved = 0;
            }
            $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
             $server =& $server_handler->create(false);
             $server->setVar('serverid', $serverid);
             $server->setVars(array('servername' => $servername,
                                        'serverip' => $serverip,
                                        'serverport' => $serverport,
                                        'serverzone' => $serverzone,
                                        'is_bookable' => $bookable,
                                        'exclusive' => $exclusive,
                                        'region' => $region,
                                        'approved' => $approved));
             if ($server_handler->insert($server)) {
                 $comment = $server->getVar('servername')." "._AM_SBEDITED;
             }
             else {
                 $comment = _AM_SBERRWHILESAVSERV;
             }
            echo $comment;
            break;
        
        case "addadmin":
        $success=0;
        $failure=0;
        foreach ($addserveradmins as $admin_id) {
            if (addTeamAdmin($admin_id, $serverid)) {
                $success++;
            }
            else {
                $failure++;
            }
        }
        $feedback = $success." "._AM_SBADMINSADDED."";
        if ($failure) {
            $feedback .= $failure." "._AM_SBADMINSNOTADDED."";
        }
        redirect_header("serveradmin.php?serverid=".$serverid,3,$feedback);
        break;
        
        case "deladmin":
        $success=0;
        $failure=0;
        foreach ($removeserveradmins as $admin_id) {
            if ($admin_id != $xoopsUser->getVar('uid')) {
                if (delTeamAdmin($admin_id)) {
                    $success++;
                }
                else {
                    $failure++;
                }
            }
        }
        $feedback = $success." "._AM_SBADMINSREMOVED."";
        if ($failure) {
            $feedback .= $failure." "._AM_SBADMINSNOTREMOVED."";
        }
        redirect_header("serveradmin.php?serverid=".$serverid,3,$feedback);
        break;
    }
}
addServerForm(_AM_EDIT, $serverid,'serveradmin.php');

$serverpreffile = XOOPS_URL."/modules/serverbooking/prefs/leasedSA".$serverid.".txt";

echo "<table><tr><td>Path to your serverprefs file: $serverpreffile - <a href='$serverpreffile'>Check Contents</a></td></tr></table>";

//Server Admin setup
$allmembers = getAllMembers();
$admins = $server_handler->getServerAdmins(array($thisserver));
$noadmins =& array_diff($allmembers, $admins);
echo "<table><tr><th><b>"._AM_SBNONADMINS."</b></th><th align=center></th><th><b>"._AM_SBSERVERADMINS."</b></th>";
echo "</tr>\n";
echo '<tr><td class="even"><form action="serveradmin.php" method="post">';
echo '<select name="addserveradmins[]" size="10" multiple="multiple">'."\n";
foreach ($noadmins as $admin_id => $admin_name) {
    echo '<option value="'.$admin_id.'">'.$admin_name.'</option>'."\n";
}
echo '</select>';
echo "</td><td align='center' class='odd'>
		<input type='hidden' name='op' value='addadmin' />
		<input type='hidden' name='serverid' value='".$serverid."' />
		<input type='submit' name='submit' value='"._AM_ADDBUTTON."' />
		</form><br />
		<form action='serveradmin.php' method='post' />
		<input type='hidden' name='op' value='deladmin' />
		<input type='hidden' name='serverid' value='".$serverid."' />
		<input type='submit' name='submit' value='"._AM_DELBUTTON."' />
		</td>
		<td class='even'>";
echo "<select name='removeserveradmins[]' size='10' multiple='multiple'>";
foreach ($admins as $admin_id => $admin_name) {
    echo '<option value="'.$admin_id.'">'.$admin_name.'</option>'."\n";
}
echo "</select>";
echo "</form></td></tr>";
echo "</table>";

//Server rule setup
addServerRule($serverid);
$sql = "SELECT * FROM ".$xoopsDB->prefix("server_rules")." WHERE serverid=$serverid ORDER BY weekday ASC";
if ( $result = $xoopsDB->query($sql) ) {
    echo "<table border='0' cellpadding='4' cellspacing='1'><tr class='bg5'>";
    echo "<th><b>"._AM_SBDAY."</b></th><th><b>"._AM_SBFROM."</b></th><th><b>"._AM_SBTO."</b></th><th><b>"._AM_SBREASON."</b></th><th><b>"._AM_DELETE."</b></th>";
    echo "</tr>\n";
    while ( $myrow = $xoopsDB->fetchArray($result) ) {
        $from = $myrow["begin"].":00";
        $to = $myrow["end"].":00";
        $weekday = $myrow["weekday"];
        if ($weekday == 0) {
            $weekday = _AM_SBSUNDAY;
        }
        if ($weekday == 1) {
            $weekday = _AM_SBMONDAY;
        }
        if ($weekday == 2) {
            $weekday = _AM_SBTUESDAY;
        }
        if ($weekday == 3) {
            $weekday = _AM_SBWEDNESDAY;
        }
        if ($weekday == 4) {
            $weekday = _AM_SBTHURSDAY;
        }
        if ($weekday == 5) {
            $weekday = _AM_SBFRIDAY;
        }
        if ($weekday == 6) {
            $weekday = _AM_SBSATURDAY;
        }
        echo "<tr class='bg1'><td>";
        echo $weekday ."</td>";
        echo "<td>".$from."</td>";
        echo "<td>".$to."</td>";
        echo "<td>".$myrow["reason"]."</td>";
        echo "<td><form method='post' action='serveradmin.php?serverid=".$serverid."&ruleid=".$myrow["ruleid"]."'>";
        echo "<input type=hidden name='op' value='deleterule'>";
        echo "<input type=submit value='Delete'></form></td>";
        echo "</tr>\n";
    }
}
echo "</table>";

include_once XOOPS_ROOT_PATH.'/footer.php';
?>
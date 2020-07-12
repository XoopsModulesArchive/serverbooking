<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include_once('admin/functions.php');
include 'language/'.$xoopsConfig['language'].'/admin.php';

if (!$xoopsUser) {
    redirect_header('index.php', 2, _NOPERM);
    exit();
}

$serverteamid = isset($_GET['teamid']) ? $_GET['teamid'] : null;
$serverteamid = isset($_POST['teamid']) ? $_POST['teamid'] : $serverteamid;

$serverteam_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
$thisteam =& $serverteam_handler->get($serverteamid, true);

$uid = $xoopsUser->getVar('uid');

if (!$thisteam->isAdmin($uid)) {
    redirect_header('index.php', 2, _NOPERM);
    exit();
}

if (isset($op)) {
    switch ($op) {
        case 'saveteam':
             $thisteam->setVars(array('name' => $_POST['name'],
                                        'irc' => $_POST['irc'],
                                        'tag' => $_POST['tag'],
                                        'homepage' => $_POST['homepage']));
             if ($serverteam_handler->insert($thisteam)) {
                 $comment = $thisteam->getVar('name')." "._AM_SBEDITED;
             }
             else {
                 $comment = _MA_SBERRWHILESAVTEAM;
             }
            echo $comment;
            break;
        
        case "addadmin":
        $success=0;
        $failure=0;
        foreach ($_POST['addadmins'] as $admin_id) {
            if ($thisteam->addAdmin($admin_id)) {
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
        //echo $feedback;
        break;
        
        case "deladmin":
        $success=0;
        $failure=0;
        foreach ($_POST['removeadmins'] as $admin_id) {
            if ($admin_id != $xoopsUser->getVar('uid')) {
                if ($thisteam->delAdmin($admin_id)) {
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
        //echo $feedback;
        break;
    }
}
$serverteam_handler->Form(_AM_EDIT, $serverteamid,'teamadmin.php');

//Admin setup
$allmembers = getAllMembers();
$admins = $thisteam->teamadmins;
$noadmins =& array_diff($allmembers, $admins);
echo "<table><tr><th><b>"._AM_SBNONADMINS."</b></th><th align=center></th><th><b>"._MA_SBTEAMADMINS."</b></th>";
echo "</tr>\n";
echo '<tr><td class="even"><form action="teamadmin.php" method="post">';
echo '<select name="addadmins[]" size="10" multiple="multiple">'."\n";
foreach ($noadmins as $admin_id => $admin_name) {
    echo '<option value="'.$admin_id.'">'.$admin_name.'</option>'."\n";
}
echo '</select>';
echo "</td><td align='center' class='odd'>
		<input type='hidden' name='op' value='addadmin' />
		<input type='hidden' name='teamid' value='".$serverteamid."' />
		<input type='submit' name='submit' value='"._AM_ADDBUTTON."' />
		</form><br />
		<form action='teamadmin.php' method='post' />
		<input type='hidden' name='op' value='deladmin' />
		<input type='hidden' name='teamid' value='".$serverteamid."' />
		<input type='submit' name='submit' value='"._AM_DELBUTTON."' />
		</td>
		<td class='even'>";
echo "<select name='removeadmins[]' size='10' multiple='multiple'>";
foreach ($admins as $admin_id => $admin_name) {
    echo '<option value="'.$admin_id.'">'.$admin_name.'</option>'."\n";
}
echo "</select>";
echo "</form></td></tr>";
echo "</table>";

include_once XOOPS_ROOT_PATH.'/footer.php';
?>
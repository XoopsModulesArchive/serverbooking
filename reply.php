<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';

if (!$xoopsUser) {
    redirect_header(_NOPERM, 3, 'scrimms.php');
    exit();
}
if (!isset($_POST['scrimmid'])) {
    redirect_header('No scrimm selected', 3, 'scrimms.php');
    exit();
}
$team_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
if (!isset($_POST['teamid'])) {
    $teams = $team_handler->getTeams($xoopsUser->getVar('uid'));
    $teamcount = count($teams);
    if ($teamcount == 0) {
        redirect_header(_NOPERM, 3, 'index.php');
        exit();
    }
    elseif ($teamcount > 1) {
        $hidden = array('scrimmid' => $_POST['scrimmid']);
        $team_handler->selectForm('reply.php', $hidden);
        include XOOPS_ROOT_PATH.'/footer.php';
        exit();
    }
    else {
        $thisteam = array_slice($teams, 0);
    }
}
else {
    $thisteam = $team_handler->get($_POST['teamid']);
}
if (isset($thisteam)) {
    
    $scrimm_handler =& xoops_getmodulehandler('scrimm', 'serverbooking');
    $scrimm =& $scrimm_handler->get($_POST['scrimmid']);
    if ($scrimm->getVar('teamid') == $thisteam->getVar('serverteamid')) {
        redirect_header('scrimms.php', 3, 'Cannot reply to your own scrimm');
    }
    if ($scrimm->reply($thisteam->getVar('serverteamid'))) {
        redirect_header('viewscrimm.php?scrimmid='.$scrimm->getVar('scrimmid'), 3, 'Reply received from '.$thisteam->getVar('name'));
        exit();
    }
    else {
        redirect_header('viewscrimm.php?scrimmid='.$scrimm->getVar('scrimmid'), 3, 'Error - Reply from '.$thisteam->getVar('name').' not saved');
        exit();
    }
}
else {
    trigger_error('No team found', E_USER_ERROR);
}
?>
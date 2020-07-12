<?php
include '../../mainfile.php';
include_once "class/scrimm.php";
include_once "class/serverteam.php";
include XOOPS_ROOT_PATH.'/header.php';

$team_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
if (!isset($_POST['teamid'])) {
    $teams =& $team_handler->getTeams($xoopsUser->getVar('uid'));
    $teamcount = count($teams);
    if ($teamcount == 0) {
        redirect_header(_NOPERM, 3, 'index.php');
        exit();
    }
    
    $team_handler->selectForm('addscrimm.php');
    include XOOPS_ROOT_PATH.'/footer.php';
    exit();
    
}
else {
    $op = isset($_POST['op']) ? $_POST['op'] : 'default';
    
    switch ($op) {
        case "savescrimm":
            $begin = $_POST['begin'];
            $scrimm_handler =& xoops_getmodulehandler('scrimm', 'serverbooking');
            $newscrimm =& $scrimm_handler->create();
            $newscrimm->setVar('begin', strtotime($begin['date']) + $begin['time']);
            $newscrimm->setVar('teamid', $_POST['teamid']);
            $newscrimm->setVar('status', 0);
            $newscrimm->setVar('teamadm', $xoopsUser->getVar('uid'));
            $newscrimm->setVar('serverid', 0);
            if ($scrimm_handler->insert($newscrimm)) {
                redirect_header("viewscrimm.php?scrimmid=".$newscrimm->getVar('scrimmid'),3, "Scrimm Added");
                exit();
            }
            echo "Error - Scrimm not saved";
            
        default:
            $thisteam =& $team_handler->get($_POST['teamid']);
            break;
    }
}
$st_handler =& xoops_getmodulehandler('scrimm', 'serverbooking');
$st_handler->form("Add", $thisteam, null, "addscrimm.php");
include XOOPS_ROOT_PATH.'/footer.php';
?>
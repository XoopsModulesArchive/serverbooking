<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
$st_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
$teams = $st_handler->getObjects(null, true, true);
foreach ($teams as $teamid => $team) {
    $xoopsTpl->append('teams', $team->getVars());
}
$xoopsOption['template_main'] = "server_teamlist.html";
include XOOPS_ROOT_PATH.'/footer.php';
?>
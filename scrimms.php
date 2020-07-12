<?php
include '../../mainfile.php';
$xoopsOption['template_main'] = "server_scrimmlist.html";
include XOOPS_ROOT_PATH.'/header.php';

$st_handler =& xoops_getmodulehandler('scrimm', 'serverbooking');
$team_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
$criteria = new Criteria('begin', time() - (24 * 3600), ">");
$criteria->setSort('begin');
$scrimms = $st_handler->getObjects($criteria, true, true);
foreach ($scrimms as $scrimmid => $scrimm) {
    $thisteam =& $team_handler->get($scrimm->getVar('teamid'));
    $thisscrimm = $scrimm->toArray();
    $thisscrimm['team'] = $thisteam->getVars();
    $xoopsTpl->append('scrimms', $thisscrimm);
    unset($thisteam);
}
include XOOPS_ROOT_PATH.'/footer.php';
?>
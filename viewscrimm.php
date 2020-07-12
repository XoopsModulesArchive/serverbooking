<?php
include '../../mainfile.php';

$scrimmid = isset($_GET['scrimmid']) ? $_GET['scrimmid'] : false;
if (!$scrimmid) {
    redirect_header('scrimms.php', 3, "No Scrimm Selected");
    exit();
}
$xoopsOption['template_main'] = "server_viewscrimm.html";
include XOOPS_ROOT_PATH.'/header.php';
$st_handler =& xoops_getmodulehandler('scrimm', 'serverbooking');
$scrimm =& $st_handler->get($scrimmid);

$team_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
$adminteams = $team_handler->getTeams($xoopsUser->getVar('uid'));
if (count($adminteams)>0) {
    $xoopsTpl->assign('teamadmin', 1);
}
else {
    $xoopsTpl->assign('teamadmin', 0);
}

$thisteam =& $team_handler->get($scrimm->getVar('teamid'), true);
if ($thisteam->isAdmin($xoopsUser->getVar('uid'))) {
    $xoopsTpl->assign('scrimmowner', 1);
    $xoopsTpl->assign('replies', $scrimm->getReplies());
}
else {
    $xoopsTpl->assign('scrimmowner', 0);
    $xoopsTpl->assign('xoops_notification', array('show' => 0));
}

$xoopsTpl->assign('scrimm', $scrimm->toArray());
$xoopsTpl->assign('team', $thisteam->getVars());

include XOOPS_ROOT_PATH.'/include/comment_view.php';
include XOOPS_ROOT_PATH.'/footer.php';
?>
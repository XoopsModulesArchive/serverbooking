<?php

include("../../mainfile.php");
$xoopsOption['template_main'] = 'serverbooking_query.html';
include(XOOPS_ROOT_PATH."/header.php");
include ('functions.php');
include("query/tribes2query.php");

$serverid = isset($_GET['serverid']) ? $_GET['serverid'] : null;
$serverid = isset($_POST['serverid']) ? $_POST['serverid'] : $serverid;
$server_handler =& xoops_getmodulehandler('server', 'serverbooking');
############################################################
# tribes2query engine and initial display source by VeKToR #
############################################################

#############################################################
# Static server display option.  Must be commented in order #
# for forms based querying to work.  Uncomment to enable    #
# default server querying.                                  #
#############################################################
if (isset($serverid)) {
    $thisserver =& $server_handler->get($serverid);
    $ip = $thisserver->getVar('serverip');
    $port = $thisserver->getVar('serverport');
}
elseif (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    $port = $_GET['port'];
}
else {
    $error = "no server selected";
}

if (!isset($error)) {
    # Call the Tribes2Query engine
    $results = query_tribes2($ip, $port);
    
    if ($results == false) {
        $xoopsTpl->assign('error', 'Error getting server stats');
    }
    else {
        $xoopsTpl->assign('error', false);
        $xoopsTpl->assign('results', $results);
    }
}
else {
    $xoopsTpl->assign('error', $error);
}
$criteria = new Criteria('is_bookable', 1);
$criteria->setSort('servername');
$servers = $server_handler->getObjects($criteria, true, false);
unset($criteria);


$xoopsTpl->assign('servers', $servers);
$xoopsTpl->assign('ip', $ip);
$xoopsTpl->assign('port', $port);
include_once(XOOPS_ROOT_PATH."/footer.php");
?>
<?php 
function getAdminServers($uid) {
    global $xoopsDB;
    $servers = array();
    $uid = intval($uid);
    $sql = "SELECT s.servername, s.serverid FROM ".$xoopsDB->prefix('team_server')." s, ".$xoopsDB->prefix("server_serveradmins")." sa 
            WHERE s.serverid=sa.serverid AND sa.uid=$uid ORDER BY servername";
    $result = $xoopsDB->query($sql);
    while ($row = $xoopsDB->fetchArray($result)) {
        $servers[$row['serverid']] = $row['servername'];
    }
    return $servers;
}
?>
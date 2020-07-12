<?php
class XoopsServer extends XoopsObject {
    var $db;
    var $serveradmins;
    
    function XoopsServer($serverid=null) {
        $this->initVar('serverid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('servername', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('serverip', XOBJ_DTYPE_TXTBOX, null, false, 20);
        $this->initVar('serverport', XOBJ_DTYPE_INT, null, false);
        $this->initVar('is_bookable', XOBJ_DTYPE_INT, null, false);
        $this->initVar('serverzone', XOBJ_DTYPE_INT, null, false);
        $this->initVar('region', XOBJ_DTYPE_TXTBOX, null, false, 15);
        $this->db =& Database::getInstance();
        $this->table = $this->db->prefix('team_server');
        if (is_array($serverid)) {
            $this->assignVars($serverid);
        }
        elseif ($serverid) {
            $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
            $server =& $server_handler->get($serverid);
            foreach ($server->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }
    
    function isServerAdmin($uid) {
        if (count($this->serveradmins)>0) {
            foreach ($this->serveradmins as $userid => $thisadmin) {
                if ($userid == $uid) {
                    return true;
                }
            }
        }
        else {
            $sql = "SELECT uid FROM ".$this->db->prefix("server_serveradmins")." WHERE serverid=".$this->getVar('serverid');
            $results = $this->db->query($sql);
            $numrows = $this->db->getRowsNum($results);
            if ($numrows > 0) {
                while ($myrow = $this->db->fetchArray($results)) {
                    $member_handler =& xoops_gethandler('member');
                    $res[$myrow['uid']] = $member_handler->getUser($myrow['uid']);
                }
                $this->serveradmins = $res;
                return $this->isServerAdmin($uid);
            }
            else {
                return false;
            }
        }
    }

    function getServerName() {
        if (!isset($this->servername)) {
            return _MA_SBALLSERVERS;
        }
        else {
            return $this->getVar('servername');
        }
    }
    
    function getServerIP() {
        return $this->getVar('serverip');
    }
    
    function getServerPort() {
        return $this->getVar('serverport');
    }
    
    function ApproveCheck($begin,$end, $edit_itemID="") {
        //returns false if no errors
        if($edit_itemID != ""){
            $edit = " AND bookid != $edit_itemID";
        }
        else {
            $edit = "";
        }
        $sql = "select * from ".$this->db->prefix('server_bookings')." where serverid=".$this->getVar('serverid')." AND ((begin >= $begin AND begin < $end) OR (end > $begin AND end <= $end) OR (begin <= $begin AND end >= $end)) AND status='Approved'".$edit;
        $result1 = $this->db->query($sql);
        if ($this->db->getRowsNum($result1)>0) {
            return _MA_SBFEEDBACK;
        }
        else {
            return false;
        }
    }
    function ruleCheck($begin, $end) {
        //False = no errors
        $weekday = date('w', $begin);
        $begin = date('G', $begin);
        $end = date('G', $end);
        $sql = "SELECT ruleid FROM ".$this->db->prefix("server_rules")." WHERE serverid=".$this->getVar('serverid')." AND weekday='$weekday' AND ((begin >= $begin AND begin < $end) OR (end > $begin AND end <= $end) OR (begin <= $begin AND end >= $end)) ";
        $result = $this->db->query($sql);
        if ($this->db->getRowsNum($result)>0) {
            $feedback = _MA_SBFEEDBACK2;
        }
        if (isset($feedback)) {
            return $feedback;
        }
        else {
            return false;
        }
    }
    function getServerZone() {
        return $this->getVar('serverzone');
    }
}

class ServerbookingServerHandler extends XoopsObjectHandler {
    /**
     * create a new server object
     * 
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link XoopsServer}
     */
    function &create($isNew = true)
    {
        $server = new XoopsServer();
        if ($isNew) {
            $server->setNew();
        }
        return $server;
    }

    /**
     * retrieve a server
     * 
     * @param int $id SERVERID of the server
     * @return mixed reference to the {@link XoopsServer} object, FALSE if failed
     */
    function &get($id) {
        $id = intval($id);
        if ($id>0) {
            $sql = "SELECT serverid, servername, serverip, serverport, serverzone, is_bookable, region, serverzone
                FROM ".$this->db->prefix("team_server")." WHERE serverid=$id";
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $server = new XoopsServer();
            $server->assignVars($this->db->fetchArray($result));
            return $server;
        }
        return false;
    }
    /*
    * Save server in database
    * @param object $server reference to the {@link XoopsServer} object
    * @param bool $force 
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */
    function insert(&$server, $force = false) {
        global $xoopsModule;
        if (get_class($server) != 'xoopsserver') {
            return false;
        }
        if (!$server->isDirty()) {
            return true;
        }
        if (!$server->cleanVars()) {
            return false;
        }
        foreach ($server->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        $servername = $this->db->quoteString($servername);
        $region = $this->db->quoteString($region);
        $serverip = $this->db->quoteString($serverip);
        if ($server->isNew()) {
            $sql = "INSERT INTO ".$this->db->prefix("team_server")." (servername, serverip, serverport, serverzone, is_bookable, region) VALUES ($servername, $serverip, $serverport, $serverzone, $is_bookable, $region)";
        }
        else {
            $sql = "UPDATE ".$this->db->prefix("team_server")." SET serverip = $serverip, servername=$servername, serverport=$serverport, serverzone=$serverzone, is_bookable=$is_bookable, region=$region WHERE serverid=$serverid";
        }
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        
        if (empty($serverid)) {
            $serverid = $this->db->getInsertId();
        }
        $server->assignVar('serverid', $serverid);
        
        if ($server->isNew()) {
            $filename = XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/prefs/leasedSA".$serverid.".txt"; //Relative path to the text file for bookings (Webserver, not gameserver)
            $handle = fopen($filename, 'w');
            if (!fwrite($handle, 'File Created')) {
                trigger_error("<br>"._MA_SBCANTWRITEFILE." ($filename)");
            }
            fclose($handle);
        }
        
        return true;
    }
    
    /**
    * delete a server from the database
    *
    * @param object $server reference to the {@link XoopsServer} to delete
    * @param bool $force
    * @return bool FALSE if failed.
    */
    function delete(&$server, $force = false)
    {
        if (get_class($server) != 'xoopsserver') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE serverid = %u", $this->db->prefix("team_server"), $server->getVar('serverid'));
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            trigger_error(_AM_SBERRSERVNOTDEL, E_NOTICE);
            return false;
        }
        $sql = "DELETE FROM ".$this->db->prefix("server_bookings")." WHERE serverid=".$server->getVar('serverid')." AND begin > ".time();
        if (!$this->db->query($sql)) {
            trigger_error(_AM_SBSERVERDELETEDBOOKNOT, E_NOTICE);
            return false;
        }
        $sql = "DELETE FROM ".$this->db->prefix("server_serveradmins")." WHERE serverid=".$server->getVar('serverid');
        if (!$this->db->query($sql)) {
            trigger_error(_AM_SBSERVERDELETEDBOOKNOT, E_NOTICE);
            return false;
        }
        return true;
    }
    /**
     * retrieve servers from the database
     * 
     * @param object $criteria {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key use the SERVERID as key for the array?
     * @param bool $as_objects if false, return serverid => servername array
     * @return array array of {@link XoopsServer} objects
     */
    function &getObjects($criteria = null, $id_as_key = false, $as_objects = true)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('team_server');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            if ($criteria->getSort() != '') {
                $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
            }
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            if ($as_objects) {
                $server = new XoopsServer();
                $server->assignVars($myrow);
                if (!$id_as_key) {
                    $ret[] =& $server;
                } else {
                    $ret[$myrow['serverid']] =& $server;
                }
                unset($server);
            }
            else {
                $ret[$myrow['serverid']] = $myrow['servername'];
            }
        }
        return $ret;
    }
    
    /**
    * retrieve servers available in a certain time slot
    *
    * @param object $criteria {@link CriteriaElement} conditions to met
    * @param bool $id_as_key use the SERVERID as key for the array?
    * @param int $begin time for time slot begin
    * @param int $end time for time slot end
    * @return array array of {@link XoopsServer} objects
    */
    function &getAvailableObjects($criteria1 = null, $id_as_key, $begin, $end) {
        $ret = array();
        $limit = $start = 0;
        $begin = intval($begin);
        $end = intval($end);
        $criteria = new CriteriaCompo($criteria1);
        
        $sql = "SELECT DISTINCT s.serverid FROM ".$this->db->prefix('team_server')." s, ".$this->db->prefix('server_bookings')." b WHERE s.serverid=b.serverid AND b.begin BETWEEN ".$begin." AND ".$end." OR b.end BETWEEN ".$begin." AND ".$end." AND b.status='Approved'";

        $serverids = array();
        $result = $this->db->query($sql);
        while (list($serverid) = $this->db->fetchRow($result)) {
        	$serverids[] = $serverid;
        }
        
        
        $sql = "SELECT DISTINCT serverid, servername, region FROM ".$this->db->prefix('team_server');
        if (count($serverids) > 0) {
        	$sql .= " WHERE serverid NOT IN (".implode(',', $serverids).")";
        }
        if ($criteria->getSort() != '') {
            $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
        }
        $limit = $criteria->getLimit();
        $start = $criteria->getStart();
        
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $server = new XoopsServer();
            $server->assignVars($myrow);
            
            if (!$server->ruleCheck($begin, $end)) {
                if (!$id_as_key) {
                    $ret[] =& $server;
                } else {
                    $ret[$myrow['serverid']] =& $server;
                }
            }
            unset($server);
        }
        return $ret;
    }
    
    /**
    * Get filtered list of servers
    * 
    * @param array $regions array of regions
    * @return array array of {@link XoopsServer} objects or serverids only
    */
    function filterServers($regions = array()) {
        $ret = array();
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('is_bookable', 1));
        if (count($regions)>0) {
            $criteria->setSort('servername');
            foreach ($regions as $key => $thisregion) {
                $regions[$key] = $this->db->quoteString($thisregion);
            }
            $regions = implode(",", $regions);
            $regions = "(".$regions.")";
            $criteria->add(new Criteria('region', $regions, 'IN'));
            
        }
        return $this->getObjects($criteria, true);
    }
    
    /**
    * Get array of server admins
    * 
    * @param mixed $servers {@link XoopsServer}
    * @param bool $id_as_key if true, returned array has the admins' userids as keys, if false it will be the relational serveradminid
    * @return array array of serveradminid => username
    */
    function getServerAdmins($servers, $id_as_key = false) {
        foreach ($servers as $sid => $thisserver) {
            $serverids[] = $thisserver->getVar('serverid');
        }
        $serverids = implode(',', $serverids);
        $serverid = "(".$serverids.")";        
        $criteria = new Criteria('ts.serverid', $serverid, 'IN');
        $criteria->setSort('u.uname');
        if (!$id_as_key) {
            $sql = "SELECT u.uname, ts.said FROM ".$this->db->prefix("server_serveradmins")." ts, ".$this->db->prefix("users")." u WHERE u.uid=ts.uid AND";
        }
        else {
            $sql = "SELECT u.uname, ts.uid FROM ".$this->db->prefix("server_serveradmins")." ts, ".$this->db->prefix("users")." u WHERE u.uid=ts.uid AND";
        }
        $sql .= ' '.$criteria->render();
        if ($criteria->getSort() != '') {
            $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
        }
        $result = $this->db->query($sql);
        $admins=array();
        $prevadmin = "";
        while ($row=$this->db->fetchArray($result)) {
            $thisadmin = $row["uname"];
            if ($thisadmin != $prevadmin) {
                if (!$id_as_key) {
                    $admins[$row["said"]]=$row["uname"];
                }
                else {
                    $admins[$row["uid"]]=$row["uname"];
                }
            }
            $prevadmin = $thisadmin;
        }
        return $admins;
    }
    
    /*
    * Get array of server rules
    * 
    * @param mixed $servers {@link XoopsServer}
    * @param int $userzone timezone to compensate for
    * @return array array of serveradminid => username
    */
    function getRules($servers, $userzone = 0) {
        $serverclause = "serverid IN (";
        foreach ($servers as $thisserver) {
            if (isset($counter)) {
                $serverclause .= ", ";
            }
            else {
                $counter = 1;
            }
            if (is_object($thisserver)) {
                $thisserver = $thisserver->getVar('serverid');
            }
            $serverclause .= $thisserver;
        }
        $serverclause .= ")";
        $rules = array();
        $sql = "SELECT * FROM ".$this->db->prefix("server_rules")." WHERE ".$serverclause." ORDER BY begin ASC";
        $query = $this->db->query($sql);
        while ($results = $this->db->fetchArray($query)) {
            $rules[$results['weekday']][] = ($results["begin"] + $userzone )."-".($results["end"] + $userzone) ." - ".$results["reason"];
        }
        return $rules;
    }
    
    /*
    * Retrieve servers, a user admins
    *
    * @param int $uid ID of user
    * @param bool $flag_bookable flag whether the server is bookable or not
    *
    * @return array array of serverid => servername
    */
    
    function getAdminServers($uid, $flag_bookable = false) {
        $servers = array();
        $uid = intval($uid);
        $sql = "SELECT s.servername, s.serverid, s.is_bookable FROM ".$this->db->prefix('team_server')." s, ".$this->db->prefix("server_serveradmins")." sa
            WHERE s.serverid=sa.serverid AND sa.uid=$uid ORDER BY servername";
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchArray($result)) {
            if ($flag_bookable) {
                $servers[$row['serverid']] = array('name' => $row['servername'], 'bookable' => $row['is_bookable']);
            }
            else {
                $servers[$row['serverid']] = $row['servername'];
            }
        }
        return $servers;
    }
}
    
?>

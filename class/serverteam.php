<?php
class XoopsServerTeam extends XoopsObject {
    var $db;
    var $teamadmins = array();
    
    function XoopsServerTeam($teamid=null) {
        $this->initVar('serverteamid', XOBJ_DTYPE_INT);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX);
        $this->initVar('irc', XOBJ_DTYPE_TXTBOX);
        $this->initVar('tag', XOBJ_DTYPE_TXTBOX);
        $this->initVar('homepage', XOBJ_DTYPE_URL);
        $this->db =& Database::getInstance();
        $this->table = $this->db->prefix('server_team');
        if (is_array($teamid)) {
            $this->assignVars($teamid);
        }
        elseif ($teamid > 0) {
            $serverteam_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
            $serverteam =& $serverteam_handler->get($teamid);
            foreach ($serverteam->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }

    function addAdmin($userid) {
        $userid = intval($userid);
        $sql = "INSERT INTO ".$this->db->prefix("server_teamadmins")." (uid, serverteamid) VALUES ($userid, ".$this->getVar('serverteamid').")";
        if (!$this->db->query($sql)) {
            return false;
        }
        else {
            $this->teamadmins[$userid] = XoopsUser::getUnameFromId($userid);
            return true;
        }
    }
    
    function delAdmin($userid) {
        $sql = "DELETE FROM ".$this->db->prefix("server_teamadmins")." WHERE uid=$userid AND serverteamid=".$this->getVar('serverteamid');
        if (!$this->db->query($sql)) {
            return false;
        }
        else {
            $newadmins = array();
            foreach ($this->teamadmins as $id => $name) {
                if ($id != $userid) {
                    $newadmins[$id] = $name;
                }
            }
            $this->teamadmins = $newadmins;
            return true;
        }
    }
    
    function isAdmin($uid) {
        if (!isset($this->teamadmins)) {
            return false;
        }
        foreach ($this->teamadmins as $adminid => $adminname) {
            if ($uid == $adminid) {
                return true;
            }
        }
        return false;
    }
    
}


class ServerbookingServerteamHandler extends XoopsObjectHandler {
    /**
     * create a new serverteam object
     * 
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link XoopsServerTeam}
     */
    function &create($isNew = true)
    {
        $serverteam = new XoopsServerTeam();
        if ($isNew) {
            $serverteam->setNew();
        }
        return $serverteam;
    }

    /**
     * retrieve a serverteam
     * 
     * @param int $id ID of the serverteam
     * @param bool $get_admins true to fetch team admins
     * @return mixed reference to the {@link XoopsServerTeam} object, FALSE if failed
     */
    function &get($id, $get_admins = false) {
        $id = intval($id);
        if ($id>0) {
            $sql = "SELECT serverteamid, name, irc, tag, homepage
                FROM ".$this->db->prefix("server_team")." WHERE serverteamid=$id";
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $serverteam = new XoopsServerTeam();
            $serverteam->assignVars($this->db->fetchArray($result));
            if ($get_admins) {
                $sql = "SELECT u.uname, u.uid FROM ".$this->db->prefix("users")." u, ".$this->db->prefix("server_teamadmins")." ta
                        WHERE ta.serverteamid = $id AND u.uid = ta.uid ORDER BY u.uname";
                if (!$result = $this->db->query($sql)) {
                    trigger_error('Team Admin retrieval failed', E_USER_NOTICE);
                }
                else {
                    while ($thisadmin = $this->db->fetchArray($result)) {
                        $admins[$thisadmin['uid']] = $thisadmin['uname'];
                    }
                    $serverteam->teamadmins = $admins;
                }
            }
            return $serverteam;
        }
        return false;
    }
    /*
    * Save serverteam in database
    * @param object $serverteam reference to the {@link XoopsServerTeam} object
    * @param bool $force 
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */
    function insert(&$serverteam, $force = false) {
        if (get_class($serverteam) != 'xoopsserverteam') {
            return false;
        }
        if (!$serverteam->isDirty()) {
            return true;
        }
        if (!$serverteam->cleanVars()) {
            return false;
        }
        foreach ($serverteam->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        $irc = $this->db->quoteString($irc);
        $homepage = $this->db->quoteString($homepage);
        $name = $this->db->quoteString($name);
        $tag = $this->db->quoteString($tag);
        if ($serverteam->isNew()) {
            $sql = "INSERT INTO ".$this->db->prefix("server_team")." (name, irc, homepage, tag) VALUES ($name, $irc, $homepage, $tag)";
        }
        else {
            $sql = "UPDATE ".$this->db->prefix("server_team")." SET name=$name, irc=$irc, homepage=$homepage, tag=$tag WHERE serverteamid=$serverteamid";
        }
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }        
        if (!$serverteam->getVar('serverteamid')) {
            $serverteamid = $this->db->getInsertId();
            $serverteam->assignVar('serverteamid', $serverteamid);
        }
        return true;
    }
    
    /**
    * delete a serverteam from the database
    *
    * @param object $server reference to the {@link XoopsServerTeam} to delete
    * @param bool $force
    * @return bool FALSE if failed.
    */
    function delete(&$serverteam, $force = false)
    {
        if (get_class($serverteam) != 'xoopsserverteam') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE serverteamid = %u", $this->db->prefix("server_team"), $serverteam->getVar('serverteamid'));
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            trigger_error(_AM_SBERRSERVNOTDEL, E_NOTICE);
            return false;
        }
        $sql = "DELETE FROM ".$this->db->prefix("server_scrimms")." WHERE serverteamid=".$serverteam->getVar('serverteamid')." AND begin > ".time();
        if (!$this->db->query($sql)) {
            trigger_error(_AM_SBSERVERDELETEDBOOKNOT, E_NOTICE);
            return false;
        }
        $sql = "DELETE FROM ".$this->db->prefix("server_teamadmins")." WHERE serverteamid=".$serverteam->getVar('serverteamid');
        if (!$this->db->query($sql)) {
            trigger_error(_AM_SBSERVERDELETEDBOOKNOT, E_NOTICE);
            return false;
        }
        return true;
    }
    
    /**
     * retrieve teams from the database
     * 
     * @param object $criteria {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key use the ID as key for the array?
     * @param bool $as_objects if false, return serverteamid => name array
     * @return array array of {@link XoopsServerTeam} objects
     */
    function &getObjects($criteria = null, $id_as_key = false, $as_objects = true)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('server_team');
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
                $server = new XoopsServerTeam();
                $server->assignVars($myrow);
                if (!$id_as_key) {
                    $ret[] =& $server;
                } else {
                    $ret[$myrow['serverteamid']] =& $server;
                }
                unset($server);
            }
            else {
                $ret[$myrow['serverteamid']] = $myrow['name'];
            }
        }
        return $ret;
    }
    
    /*
    * Show input/edit form
    *
    * @param string $action Add/Edit
    * @param int $serverteamid id of {@link XoopsServerTeam} to edit
    * @param string $target receiving page
    */
    function Form($action, $serverteamid="", $target='index.php') {
        if ($serverteamid) {
            $serverteam =& $this->get($serverteamid);
            $name = $serverteam->getVar('name');
            $irc = $serverteam->getVar('irc');
            $tag = $serverteam->getVar('tag');
            $homepage = $serverteam->getVar('homepage');
        }
        else {
            $name = "Name";
            $irc = "#irc";
            $tag = "tag";
            $homepage = "";
        }
        include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
        $mform = new XoopsThemeForm($action." Team", "teamform", $target);
        $op_hidden = new XoopsFormHidden('op', "saveteam");
        $action_hidden = new XoopsFormHidden('action', $action);
        $serverteamid_hidden = new XoopsFormHidden('teamid', $serverteamid);
        $name = new XoopsFormText(_MA_SB_TEAMNAME, 'name', 30, 30, $name, 'E');
        $irc = new XoopsFormText(_MA_SBIRCCHANNEL, 'irc', 20, 20, $irc, 'E');
        $tag = new XoopsFormText(_MA_SBYOURTEAM, 'tag', 10, 10, $tag, 'E');
        $homepage = new XoopsFormText(_MA_SB_HOMEPAGE, 'homepage', 50, 100, $homepage, 'E');
        $submit = new XoopsFormButton('', 'submit', $action.' Team', 'submit');
        
        $button_tray = new XoopsFormElementTray('' ,'');
        $button_tray->addElement($submit);
        
        $mform->addElement($name, true);
        $mform->addElement($irc, true);
        $mform->addElement($tag, true);
        $mform->addElement($homepage);
        $mform->addElement($op_hidden);
        $mform->addElement($action_hidden);
        $mform->addElement($serverteamid_hidden);
        $mform->addElement($button_tray);
        
        $mform->display();
    }
    
    /*
    * Show select form
    *
    * @param string $target receiving page
    * @param array $hidden hidden variables to include in the form
    */
    function selectForm($target='index.php', $hidden = array()) {
        global $xoopsUser;
        include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
        $mform = new XoopsThemeForm("Select Team", "teamform", $target);
        
        $teams = $this->getTeams($xoopsUser->getVar('uid'));
        
        $team_select = new XoopsFormSelect("Team", 'teamid', '');
        foreach ($teams as $teamid => $thisteam) {
            $team_select->addOption($teamid, $thisteam->getVar('name'));
        }
        if (count($hidden) > 0) {
            foreach ($hidden as $name => $value) {
                $mform->addElement(new XoopsFormHidden($name, $value));
            }
        }
        $submit = new XoopsFormButton('', 'submit', 'Select', 'submit');
        $button_tray = new XoopsFormElementTray('' ,'');
        $button_tray->addElement($submit);

        $mform->addElement($team_select);
        $mform->addElement($button_tray);
        
        $mform->display();
    }
    
    /*
    * Get teams, which a user admins
    *
    * @param int $uid ID of user
    */
    function getTeams($uid) {
        $ret = array();
        $uid = intval($uid);
        $sql = "SELECT t.serverteamid, t.name, t.irc, t.tag, t.homepage
                FROM ".$this->db->prefix("server_team")." t, ".$this->db->prefix("server_teamadmins")." a WHERE a.uid=$uid AND a.serverteamid = t.serverteamid";
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $thisteam =& $this->create(false);
            $thisteam->assignVars($myrow);
            $ret[$myrow['serverteamid']] = $thisteam;
            unset($thisteam);
        }
        return $ret;
    }
}
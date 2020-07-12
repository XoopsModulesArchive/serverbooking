<?php
class XoopsScrimm extends XoopsObject {
    var $db;
    
    function Xoopsscrimm($scrimmid=null) {
        $this->initVar('scrimmid', XOBJ_DTYPE_INT);
        $this->initVar('begin', XOBJ_DTYPE_INT);
        $this->initVar('teamid', XOBJ_DTYPE_INT);
        $this->initVar('teamadm', XOBJ_DTYPE_INT);
        $this->initVar('status', XOBJ_DTYPE_INT);
        $this->db =& Database::getInstance();
        if (is_array($scrimmid)) {
            $this->assignVars($scrimmid);
        }
        elseif ($scrimmid > 0) {
            $scrimm_handler = xoops_getmodulehandler('scrimm', 'serverbooking');
            $scrimm =& $scrimm_handler->get($scrimmid);
            foreach ($scrimm->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }
    
    function reply($teamid) {
        $teamid = intval($teamid);
        $sql = "INSERT INTO ".$this->db->prefix("server_scrimm_reply")." VALUES (".$this->getVar('scrimmid').", $teamid)";
        if ($this->db->query($sql)) {
            include_once(XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/class/serverteam.php");
            $team_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
            $thisteam =& $team_handler->get($teamid);
            $tags = array();
            $tags['TEAM_NAME'] = $thisteam->getVar('team');
            $tags['CREATOR'] = XoopsUser::getUnameFromId($teamadm);
            $tags['MATCHTIME'] = formatTimestamp($begin);
            $tags['DETAILS_URL'] = XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/viewscrimm.php?scrimmid='.$scrimmid;
            $notification_handler =& xoops_gethandler('notification');
            $notification_handler->triggerEvent('scrimm', $scrimmid, 'new_reply', $tags);
            return true;
        }
        return false;
    }
    
    function getReplies() {
        $ret = array();
        $sql = "SELECT t.serverteamid, t.name, t.irc, t.tag, t.homepage 
                FROM ".$this->db->prefix('server_scrimm_reply')." r,
                    ".$this->db->prefix('server_team')." t
                WHERE t.serverteamid=r.serverteamid AND r.scrimmid=".$this->getVar('scrimmid');
        $result = $this->db->query($sql);
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow;
        }
        return $ret;
    }
    
    function toArray() {
        $ret = array();
        $ret['scrimmid'] = $this->getVar('scrimmid');
        $ret['begin'] = formatTimestamp($this->getVar('begin'));
        $ret['admin'] = XoopsUser::getUnameFromId($this->getVar('teamadm'));
        $ret['adminid'] = $this->getVar('teamadm');
        return $ret;
    }
}


class ServerbookingScrimmHandler extends XoopsObjectHandler {
    /**
     * create a new scrimm object
     * 
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link XoopsScrimm}
     */
    function &create($isNew = true)
    {
        $scrimm = new XoopsScrimm();
        if ($isNew) {
            $scrimm->setNew();
        }
        return $scrimm;
    }

    /**
     * retrieve a scrimm
     * 
     * @param int $id ID of the scrimm
     * @return mixed reference to the {@link XoopsScrimm} object, FALSE if failed
     */
    function &get($id) {
        $id = intval($id);
        if ($id>0) {
            $sql = "SELECT scrimmid, begin, teamid, teamadm, status
                FROM ".$this->db->prefix("server_scrimm")." WHERE scrimmid=$id";
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $scrimm = new XoopsScrimm();
            $scrimm->assignVars($this->db->fetchArray($result));
            return $scrimm;
        }
        return false;
    }
    /*
    * Save scrimm in database
    * @param object $scrimm reference to the {@link XoopsScrimm} object
    * @param bool $force 
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */
    function insert(&$scrimm, $force = false) {
        if (get_class($scrimm) != 'xoopsscrimm') {
            return false;
        }
        if (!$scrimm->isDirty()) {
            return true;
        }
        if (!$scrimm->cleanVars()) {
            return false;
        }
        foreach ($scrimm->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($scrimm->isNew()) {
            $sql = "INSERT INTO ".$this->db->prefix("server_scrimm")." (begin, teamid, teamadm, status) VALUES ($begin, $teamid, $teamadm, $status)";
        }
        else {
            $sql = "UPDATE ".$this->db->prefix("server_scrimm")." SET begin = $begin, teamid = $teamid, teamadm = $teamadm, status = $status WHERE scrimmid=$scrimmid";
        }
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        
        if (empty($scrimmid)) {
            $scrimmid = $this->db->getInsertId();
            $scrimm->assignVar('scrimmid', $scrimmid);
        }
        global $xoopsModule;
        include_once(XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/class/serverteam.php");
        $team_handler =& xoops_getmodulehandler('serverteam', 'serverbooking');
        $thisteam =& $team_handler->get($teamid);
        $tags = array();
        $tags['TEAM_NAME'] = $thisteam->getVar('name');
        $tags['CREATOR'] = XoopsUser::getUnameFromId($teamadm);
        $tags['MATCHTIME'] = formatTimestamp($begin);
        $tags['DETAILS_URL'] = XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/viewscrimm.php?scrimmid='.$scrimmid;
        $notification_handler =& xoops_gethandler('notification');
        $notification_handler->triggerEvent('global', 0, 'new_scrimm', $tags);
        return true;
    }
    
    /**
    * delete a scrimm from the database
    *
    * @param object $server reference to the {@link XoopsScrimm} to delete
    * @param bool $force
    * @return bool FALSE if failed.
    */
    function delete(&$scrimm, $force = false)
    {
        if (get_class($scrimm) != 'xoopsscrimm') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE scrimmid = %u", $this->db->prefix("server_scrimm"), $scrimm->getVar('scrimmid'));
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            trigger_error(_AM_SBERRSERVNOTDEL, E_NOTICE);
            return false;
        }
        return true;
    }
    
    /**
     * retrieve scrimms from the database
     * 
     * @param object $criteria {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key use the ID as key for the array?
     * @return array array of {@link XoopsScrimm} objects
     */
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('server_scrimm');
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
            $scrimm = new XoopsScrimm();
            $scrimm->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $scrimm;
            } else {
                $ret[$myrow['scrimmid']] =& $scrimm;
            }
            unset($scrimm);
        }
        return $ret;
    }
    
    /*
    * Show input/edit form
    *
    * @param string $action Add/Edit
    * @param object $team {@link XoopsServerTeam} team creating the scrimm
    * @param int $scrimmid id of {@link XoopsScrimm} to edit
    * @param string $target receiving page
    */
    function form($action, &$team, $scrimmid="", $target='index.php') {
        include_once XOOPS_ROOT_PATH."/modules/serverbooking/class/server.php";
        $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
      
        if ($scrimmid) {
            $scrimm =& $this->get($scrimmid);
        }
        else {
            $scrimm =& $this->create();
        }
        include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
        $mform = new XoopsThemeForm($action." Scrimm for ".$team->getVar('name'), "serverform", $target);
        $op_hidden = new XoopsFormHidden('op', "savescrimm");
        $action_hidden = new XoopsFormHidden('action', $action);
        $teamid_hidden = new XoopsFormHidden('teamid', $team->getVar('serverteamid'));
        $submit = new XoopsFormButton('', 'submit', $action.' Scrimm', 'submit');
        $button_tray = new XoopsFormElementTray('' ,'');
        $button_tray->addElement($submit);
        $begin = new XoopsFormDateTime(_MA_SBFROM, 'begin', 15, time() + 600);

        $mform->addElement($begin, true);
        $mform->addElement($op_hidden);
        $mform->addElement($action_hidden);
        $mform->addElement($teamid_hidden);
        if ($scrimm->getvar('scrimmid')) {
            $scrimmid_hidden = new XoopsFormHidden('scrimmid', $scrimmid);
            $mform->addElement($scrimmid_hidden);            
        }
                
        $mform->addElement($button_tray);
        $mform->display();
    }
}
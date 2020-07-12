<?php 
class XoopsBooking extends XoopsObject  {
  var $db;
  var $table;
  
  function XoopsBooking($bookid = -1) {
      $this->initvar('bookid', XOBJ_DTYPE_INT, null, false);
      $this->initvar('begin', XOBJ_DTYPE_INT, null, false);
      $this->initvar('end', XOBJ_DTYPE_INT, null, false);
      $this->initvar('booker', XOBJ_DTYPE_TXTBOX, null, false); //Name of team/clan, making the booking
      $this->initvar('bookerid', XOBJ_DTYPE_INT, null, false);
      $this->initvar('bookeremail', XOBJ_DTYPE_EMAIL, null, false);
      $this->initvar('serverid', XOBJ_DTYPE_INT, null, false);
      $this->initvar('admin', XOBJ_DTYPE_INT, null, false);
      $this->initvar('wonid', XOBJ_DTYPE_INT, null, false);
      $this->initvar('irc', XOBJ_DTYPE_TXTBOX, null, false);
      $this->initvar('opponent', XOBJ_DTYPE_TXTBOX, null, false);
      $this->initvar('matchtype', XOBJ_DTYPE_TXTBOX, null, false);
      $this->initvar('status', XOBJ_DTYPE_TXTBOX, null, false);
      $this->initvar('bookcomments', XOBJ_DTYPE_TXTAREA, null, false);
      
      $this->db = Database::getInstance();
      $this->table = $this->db->prefix('server_bookings');
      if (is_array($bookid)) {
          $this->assignVars($bookid);
      }
      elseif ($bookid != -1) {
          $booking_handler =& xoops_getmodulehandler('booking', 'serverbooking');
          $booking =& $booking_handler->get($bookid);
          foreach ($booking->vars as $k => $v) {
              $this->assignVar($k, $v['value']);
          }
      }
  }
  
  function isOwner($uid) {
      if ($this->getVar('bookerid') == $uid) {
          return true;
      }
      return false;
  }
  
  function approve(&$server) {
      global $xoopsModuleConfig, $xoopsUser, $xoopsConfig, $xoopsModule;
      $comment = "";
      if ($this->getVar('wonid') > 0) {
          $comment = TrimLeasedFile(time());
      }
      $comment .= $this->sendNotification();
      if ($comment != "") {
          return $comment;
      }
      return true;
  }
  
  function setStatus($status, $server) {
      global $xoopsUser;
      $sql = "UPDATE ".$this->table." SET status=".$this->db->quoteString($status).", admin=".$xoopsUser->getVar('uid')." WHERE bookid=".$this->getVar('bookid');
      if (!$this->db->query($sql)) {
          trigger_error('Error while updating');
          return false;
      }
      if ($status == "Approved") {
          if (!$server->ApproveCheck($this->getVar('begin'),$this->getVar('end'), $this->getVar('bookid'))) {
              if ($comment = $this->approve($server)) {
                  return true;
              }
          }
          else {
              $this->_errors[] = _MA_SBCOLLISION;
              return false;
          }
      }
      return true;
  }
  
  function validate($server) {
      $error = "";
      if (!checkEmail($this->getVar('bookeremail'))) {
          $error = _MA_SBINVALIDEMAIL;
      }
      if ($this->getVar('begin') >= $this->getVar('end')) {
          $error .= _MA_ENDBEFOREBEGIN;
      }
      $error .= $server->ApproveCheck($this->getVar('begin'), $this->getVar('end'), $this->getVar('bookid'));
      $error .= TimeCheck($this->getVar('begin'));
      $error .= $server->ruleCheck($this->getVar('begin'), $this->getVar('end'));
      return $error;
  }
  
  function sendNotification() {
      global $xoopsModuleConfig, $xoopsModule, $xoopsConfig, $xoopsUser;
      if ($xoopsModuleConfig['serveradminmail']) {
          $server_handler =& xoops_getmodulehandler('server', 'serverbooking');
          $server =& $server_handler->get($this->getVar('serverid'));
          $serveradmins = $server_handler->getServerAdmins(array($server->getVar('serverid') => $server), true);
          foreach ( $serveradmins as $uid => $uname) {
              if ($uid == $xoopsUser->getVar('uid')) {
                  return true;
              }
              $to_user = $uid;
              $added[] = new XoopsUser($to_user);
              //$added_id[] = $to_user;
          }
          $added_count = count($added);
          if ( $added_count > 0 ) {
              $begin = date('H:i', $this->getVar('begin'));
              $end = date('H:i', $this->getVar('end'));
              $servername = $server->getVar('servername');
              $subject= "New Server Booking by ".$this->getVar('booker');
              $myts =& MyTextSanitizer::getInstance();
              for ( $i = 0; $i < $added_count; $i++) {
                  $xoopsMailer =& getMailer();
                  $method = $added[$i]->getVar('notify_method');
                  include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
                  switch($method) {
                      case XOOPS_NOTIFICATION_METHOD_PM:
                      $xoopsMailer->usePM();
                      //$config_handler =& xoops_gethandler('config');
                      //$xoopsMailerConfig =& $config_handler->getConfigsByCat(XOOPS_CONF_MAILER);
                      $xoopsMailer->setFromUser($xoopsUser);
                      break;
                      
                      case XOOPS_NOTIFICATION_METHOD_EMAIL:
                      $xoopsMailer->useMail();
                      $xoopsMailer->setFromEmail($xoopsModuleConfig['serveremail']);
                      $xoopsMailer->setFromName($xoopsConfig['sitename']." Server Booking");
                      break;
                  }
                  $xoopsMailer->setTemplateDir(XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/language/".$xoopsConfig['language']."/mail_template");
                  $xoopsMailer->setTemplate('booking.tpl');
                  $xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);
                  $xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);
                  $xoopsMailer->assign('BOOKERNAME', $xoopsUser->getVar('uname'));
                  $xoopsMailer->assign('BOOKER', $this->getVar('booker'));
                  $xoopsMailer->assign('SERVERNAME', $servername);
                  $xoopsMailer->assign('DATE', date('j/n-Y', $this->getVar('begin')));
                  $xoopsMailer->assign('BEGIN', $begin);
                  $xoopsMailer->assign('END', $end);
                  $xoopsMailer->assign('IRC', $this->getVar('irc'));
                  $xoopsMailer->assign('MATCHTYPE', $this->getVar('matchtype'));
                  $xoopsMailer->assign('OPPONENT', $this->getVar('opponent'));
                  $xoopsMailer->assign('BOOKEREMAIL', $this->getVar('bookeremail'));
                  $xoopsMailer->assign('BOOKINGLINK', XOOPS_URL."/modules/".$xoopsModule->getVar('dirname')."/display-event.php?id=".$this->getVar('bookid'));
                  $xoopsMailer->assign('SITEURL', XOOPS_URL."/");
                  $xoopsMailer->setToUsers($added[$i]);
                  $xoopsMailer->setSubject($myts->oopsStripSlashesGPC($subject));
                  if (!$xoopsMailer->send()) {
                      $this->_errors[] .= "<br />"._MA_SBEMAILERROR;
                  }
                  if (count ($this->_errors) > 0) {
                      return false;
                  }
              }
              return true;
          }
      }
      return true;
  }
}

class ServerbookingBookingHandler extends XoopsObjectHandler {
    /**
     * create a new booking object
     * 
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link XoopsBooking}
     */
    function &create($isNew = true)
    {
        $booking = new XoopsBooking();
        if ($isNew) {
            $booking->setNew();
        }
        return $booking;
    }

    /**
     * retrieve a booking
     * 
     * @param int $id bookingID of the server
     * @return mixed reference to the {@link XoopsBooking} object, FALSE if failed
     */
     
    function &get($id) {
        $id = intval($id);
        $booking = new XoopsBooking();
        if ($id > 0) {
            $sql = "SELECT bookid, begin, end, booker, bookerid, bookeremail, serverid, admin, wonid, irc, opponent, matchtype, status, bookcomments FROM ".$this->db->prefix('server_bookings')." WHERE bookid=$id";
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $booking->assignVars($this->db->fetchArray($result));
        }
        return $booking;
    }
    
    /*
    * Save booking in database
    * @param object $booking reference to the {@link XoopsBooking} object
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */
    function insert(&$booking) {
        if (get_class($booking) != 'xoopsbooking') {
            return false;
        }
        if (!$booking->isDirty()) {
            return true;
        }
        if (!$booking->cleanVars()) {
            return false;
        }
        foreach ($booking->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($booking->isNew()) {
            $sql = "INSERT INTO ".$this->db->prefix("server_bookings")." (begin, end, booker, opponent, matchtype, status, bookerid, bookeremail, wonid, bookcomments, irc, admin, serverid) VALUES ($begin, $end, ".$this->db->quoteString($booker).", ".$this->db->quoteString($opponent).", ".$this->db->quoteString($matchtype).", ".$this->db->quoteString($status).", $bookerid, ".$this->db->quoteString($bookeremail).", $wonid, ".$this->db->quoteString($bookcomments).", ".$this->db->quoteString($irc).", $admin, $serverid)";
        }
        else {
            $sql = "UPDATE ".$this->db->prefix("server_bookings")." SET status=".$this->db->quoteString($status).", admin=".$admin.", booker=".$this->db->quoteString($booker).", opponent=".$this->db->quoteString($opponent).", bookeremail=".$this->db->quoteString($bookeremail).",  begin=".$begin.",  end=".$end.",  matchtype=".$this->db->quoteString($matchtype).", wonid=".$wonid.", bookcomments=".$this->db->quoteString($bookcomments).", irc=".$this->db->quoteString($irc).", serverid=".$serverid." WHERE bookid=".$bookid;
        }
        if (!$this->db->query($sql)) {
            return false;
        }
        if ($booking->isNew()) {
            $booking->setVar('bookid', $this->db->getInsertId());
        }
        $comment = _MA_BOOKINGSAVED;
        if (($booking->getVar('status') == "Approved")&&($booking->getVar('wonid')>0)) {
            $comment .= TrimLeasedFile(time());
        }
        if (isBooker($bookerid)) {
            $this->db->query("UPDATE ".$this->db->prefix("server_bookers")." SET wonid=$wonid, irc=".$this->db->quoteString($irc)." WHERE uid=".$bookerid);
        }
        else {
            $this->db->query("INSERT INTO ".$this->db->prefix("server_bookers")." VALUES ($bookerid, ".$this->db->quoteString($irc).", $wonid)");
        }
        if ($booking->isNew()) {
            $comment .= $booking->sendNotification();
        }
        return $comment;
    }
    /**
    * delete a booking from the database
    *
    * @param object $booking reference to the {@link XoopsBooking} to delete
    * @param bool $force
    * @return bool FALSE if failed.
    */
    function delete(&$booking, $force = false) {
        if (get_class($booking) != 'xoopsbooking') {
            return false;
        }
        $this->db->query("DELETE FROM ".$this->db->prefix('server_bookings')." WHERE bookid=".$booking->getVar('bookid')."");
        return TrimLeasedFile(time());
    }
    
}
?>
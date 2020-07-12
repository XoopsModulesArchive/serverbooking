<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include 'functions.php';
if (isset($_POST)) {
	foreach ($_POST as $k => $v) {
		${$k} = $v;
	}
}
if (!isset($op)) {
	$op = "";
}

$server_handler =& xoops_getmodulehandler('server', 'serverbooking');

include XOOPS_ROOT_PATH."/class/xoopsformloader.php";
switch ($op) {
	case 'searchresults':
		$available = array();
		if (!isset ($approved)) {
			$approved = 0;
		}
		$begin = strtotime($begin['date']) + $begin['time'] - ($zone * 3600);
		$end = strtotime($end['date']) + $end['time'] - ($zone * 3600);
		if ($region == "Any") {
			$region = array();
		}
		$servers = $server_handler->filterServers($region);
		if (count($servers) > 0) {
			$serverids = "(".implode(',', array_keys($servers)).")";
			$availservers = $server_handler->getAvailableObjects(new CriteriaCompo(new Criteria('s.serverid', $serverids, 'IN')), true, $begin, $end);
			foreach ($availservers as $sid => $thisserver) {
				$available[$sid]['name'] = $thisserver->getVar('servername');
				$available[$sid]['region'] = $thisserver->getVar('region');
			}
			$begin += ($zone * 3600);
			$end += ($zone * 3600);
			if (count($available)>0) {
				$class = 'even';
				echo "<table width='100%' border='0' cellspacing='1' cellpadding='8' style='border: 2px solid #2F5376;'>";
				echo "<tr><th colspan='3'>"._MA_AVAILSERVERS."</th></tr>";
				foreach ($available as $sid => $server) {
					if ($class == 'even') {
						$class = 'odd';
					}
					else {
						$class = 'even';
					}
					echo "<tr class='".$class."'><td valign='top'>";
					echo $server['name'];
					echo "</td><td>";
					echo $server['region'];
					echo "</td><td>";
					echo "<form action='BookMatch.php' method='POST'>";
					echo "<input type='hidden' name='timebegin' value='".$begin."'>";
					echo "<input type='hidden' name='timeend' value='".$end."'>";
					echo "<input type='hidden' name='zone' value='".$zone."'>";
					echo "<input type='hidden' name='serverid' value='".$sid."'>";
					echo "<input type='submit' value='"._MA_BOOKSERVER."'>";
					echo "</form></td></tr>";
				}
				echo '</table>';
			}
		}
		else {
			echo "<h3>No Servers Found</h3>";
			$begin += $zone * 3600;
			$end += $zone * 3600;
		}
		break;

	default:
		$begin = 0;
		$end = time() + (120 * 60);
		$region = "Any";
		if ($xoopsUser) {
			$zone = $xoopsUser->timezone();
		}
		else {
			$zone = $xoopsConfig['server_TZ'];
		}
		break;
}

$sform = new XoopsThemeForm(_MA_SEARCHSERVER, "serverform", "search.php");
$sform->addElement(new XoopsFormSelectTimezone(_MA_SBYOURTIMEZONE, 'zone', $zone, 1));
$sform->addElement(new XoopsFormDateTime(_MA_SBFROMYOURTIME, 'begin', 15, $begin), true);
$sform->addElement(new XoopsFormDateTime(_MA_SBTOYOURTIME, 'end', 15, $end), true);
$region = new XoopsFormSelect(_MA_SBREGION, 'region', $region);
$region->addOption('Any');
$region->addOption('West');
$region->addOption('Central');
$region->addOption('North');
$region->addOption('South');
$sform->addElement($region);
$sform->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
$sform->addElement(new XoopsFormHidden('op', 'searchresults'));
$sform->display();
include_once XOOPS_ROOT_PATH.'/footer.php';
?>
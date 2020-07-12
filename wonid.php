<?php
include '../../mainfile.php';
include XOOPS_ROOT_PATH.'/header.php';
include(XOOPS_ROOT_PATH."/class/xoopsmodule.php");
include('functions.php');
echo '<H2>'._MA_SBWONID.'</H2>';
echo _MA_SBWONIDTEXT;
OpenTable();
?>
<tr align=center><td align=center>
<a href="javascript:window.close();"><?php echo _MA_SBCLOSEWINDOWS; ?></a></td></tr>

<?php
CloseTable();
?>

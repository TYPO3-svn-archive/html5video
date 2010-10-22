<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_html5video_pi1.php', '_pi1', 'list_type', 1);
			
			// you add pi_flexform to be renderd when your plugin is shown
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

 // now, add your flexform xml-file
if (t3lib_extMgm::isLoaded('dam')) {
  t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY . '/flexformDAM_ds.xml');
  $TYPO3_CONF_VARS['EXTCONF']['html5video']['DAM']=1;
} else { 
  t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY . '/flexform_ds.xml');
}	
?>
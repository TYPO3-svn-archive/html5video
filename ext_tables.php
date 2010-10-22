<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:html5video/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_html5video_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_html5video_pi1_wizicon.php';
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/html5_video_player/', 'HTML5 Video Player');

// Flexforms
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] ='pi_flexform';
if (t3lib_extMgm::isLoaded('dam')) {
  t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY . '/flexformDAM_ds.xml');
  $TYPO3_CONF_VARS['EXTCONF']['html5video']['DAM']=1;
} else { 
  t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY . '/flexform_ds.xml');
}

?>
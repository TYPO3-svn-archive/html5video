<?php
/***************************************************************
 *  Copyright notice
 *  (c) 2010 Gordon Brüggemann <gb
 *
 * @gb-web.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'HTML5 Video Player' for the 'html5video' extension.
 *
 * @author	Gordon Brüggemann <gb@gb-web.de>
 * @package	TYPO3
 * @subpackage	tx_html5video
 */
class tx_html5video_pi1 extends tslib_pibase {

	var $prefixId = 'tx_html5video_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_html5video_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'html5video'; // The extension key.
	var $pi_checkCHash = true;

	public $conf = array();
	private $data= array();
	private $poster = array();
	private $download ='';
	private $baseURL='';


	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->data = 	$this->cObj->data;
		if ( !is_array($conf['type.']) ) {
			$this->conf['type.'] = array(
				'flv' => 'video/x-flv',
				'mp4' => 'video/mp4',
				'ogg' => 'video/ogg',
				'webm' => 'video/webm'
			);
		}
		$this->pi_initPIflexForm();
		$this->flex2conf($this);
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->getFileData();
		$this->getHeader();
		$this->getPoster();
		$this->baseURL = $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];
		$support = $this->conf['SupportVideoJS'] ? $this->cObj->cObjGetSingle($this->conf['support'], $this->conf['support.']) : '';
		$content = $this->cObj->stdWrap($this->getVideo() . $this->getDownload(), $this->conf['video.']);

		return $this->pi_wrapInBaseClass($content . $support);
	}

	/**
	 * give the video tag
	 *
	 * @return The video tag
	 */
	public function getVideo() {
		if ( trim($this->conf['skin']) !== 'orginal' ) {
			$class['video'] = empty($this->conf['video.']['class']) ? ' class="' . $this->conf['skin'] . '" ' : ' class="' . $this->conf['video.']['class'] . ' ' . $this->conf['skin'] . '" ';
		} else {
			$class['video'] = empty($this->conf['video.']['class']) ? ' ' : ' class="' . $this->conf['video.']['class'] . '"';
		}
		$id['video'] = empty($this->conf['video.']['id']) ? 'v' . $this->data['uid'] : ' id="' . $this->conf['video.']['id'] . '-' . $this->data['uid']  . '"';
		$setup = ' data-setup=\'{}\' ';
		$preload = $this->conf['PreloadVideo'] ? ' preload="true" ' : '';
		$autoplay = $this->conf['AutoplayVideo'] ? ' autoplay="true" ' : '';
		$source = '';
		foreach ( $this->conf['source.'] as $type => $file ) {
			if ( is_file($file) ) {
				//source file für Video
				if ( $type != 'poster'and $type != 'flv' ) {
					$sourc['src'] = 'src="' . $this->baseURL . $file . '"';
					$sourctype = 'type=\'' . $this->conf['type.'][$type] . '\'';
					$source .= '<source ' . $sourc['src'] . $sourctype . ' /> ';
				}
				//download für die Filme bei error
				if ( $type != 'poster' ) {
					$this->download .= '<a href="' . $file . '">' . $this->conf['download.'][$type] . '</a>'; //TYPO3 link verwenden
				}
			}
		}
		$video = '<video' . $id['video'] . ' ' . $class['video'] . 'width="' . $this->conf['width'] . '" height="' . $this->conf['height'] . '" controls="" ' . $preload . $autoplay . $setup . ' ' . $this->poster['video'] . '>';
		$video .= $source;
		$video .= '</video>';
		return $video;
	}

	/**
	 * creat the image for the player

	 */
	public function getPoster() {
		$this->poster['video'] = '';
		if ( !empty($this->conf['source.']['poster']) ) {
			$this->poster['alt'] = empty($this->conf['poster.']['alt']) ? '' : ' alt="' . $this->conf['poster.']['alt'] . '"';
			$this->poster['title'] = empty($this->conf['poster.']['titel']) ? '' : ' title="' . $this->conf['poster.']['titel'] . '"';
			$this->poster['video'] = ' poster="' . $this->conf['source.']['poster'] . '"';
		}
	}

	/**
	 * @return The downloads for the video
	 */
	public function getDownload() {
		$download = $this->conf['download'] ? $this->cObj->stdWrap($this->download, $this->conf['download.']) : '';

		return $download;
	}

	/**
	 * creat the header information
	 */
	public function getHeader() {
		//JS File
		$videoJS = empty($this->conf['videoJS']) ? 'typo3conf/ext/html5video/res/videoJS/video.min.js' : $this->conf['videoJS'];
		$cdnURLjs = empty($this->conf['cdn.']['js']) ? $videoJS : $this->conf['cdn.']['js'];
		//CSS File
		$videoCSS = empty($this->conf['videoCSS']) ? 'typo3conf/ext/html5video/res/videoJS/video-js.min.css' : $this->conf['videoCSS'];
		$cdnURLcss = empty($this->conf['cdn.']['css']) ? $videoCSS : $this->conf['cdn.']['css'];
		// include with CDN or none
		if ( $this->conf['cdn.']['enable'] ) {
			$cdnCssJs = '<link href="' . $cdnURLcss . '" rel="stylesheet">
						 <script src="' . $cdnURLjs . '"></script><script>window.VideoJS || document.write(\'<script src="' . $videoJS . '"><\/script><link rel="stylesheet" href="' . $videoCSS . '" type="text/css" media="screen" >\')</script>';
		} else {
			$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey] = $videoCSS;
			$GLOBALS['TSFE']->pSetup['includeJS.'][$this->extKey] = $videoJS;
		}
		//extra CSS File für wechselnde Skin
		$skinCSS = '';
		if ( !empty($this->conf['skin']) ) {
			if ( trim($this->conf['skin']) !== 'orginal' ) {
				$skinCSS = '<link rel="stylesheet" href="' . $this->conf['skin.'][$this->conf['skin']] . '" type="text/css" media="screen" title="Video ' . $this->conf['skin'] . '" charset="utf-8">';
			}
		}
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $cdnCssJs . $skinCSS;
	}

	/**
	 *creat JavaScript Options

	 */
	public function jsOptions() {
		$jsOptions = '';
		if ( is_array($this->conf['options.']) ) {
			//$jsOptions = $this->conf['options'];
			$jsOptions .= ' ,{';
			foreach ( $this->conf['options.'] as $option => $value ) {
				$jsOptions .= empty($value) ? '' : $option . ':' . $value . ',';
			}
			$jsOptions = substr($jsOptions, 0, -1);
			$jsOptions .= '}';
			return $jsOptions;
		}
		return;
	}

	/**
	 * creat the patch for the videos dam and from upload folder
	 */
	public function getFileData() {
		$row = $this->cObj->data;
		if ( isset($row['_ORIG_uid']) && ($row['_ORIG_uid'] > 0) ) {
			$uid = $row['_ORIG_uid'];
		} else {
			$uid = $row['uid'];
		}
		if ( $row['_LOCALIZED_UID'] ) {
			$uid = $row['_LOCALIZED_UID'];
		}
		$tableofcontent = 'tt_content';
		foreach ( $this->conf['source.'] as $key => $value ) {
			if ( t3lib_extMgm::isLoaded('dam') ) {
				$damArray[$key] = tx_dam_db::getReferencedFiles($tableofcontent, $uid, 'html5video' . $key);
				foreach ( $damArray[$key]['files'] as $id => $files ) {
					$this->conf['source.'][$key] = $files;
					$this->conf['source.'][$key . '.']['rows'] = $damArray[$key][$id];
				}
			} else {
				$this->conf['patch'] = empty($this->conf['patch']) ? 'uploads/html5video/' : $this->conf['patch'];
				if ( is_file($this->conf['patch'] . $this->conf['source.'][$key]) ) {
					$this->conf['source.'][$key] = $this->conf['patch'] . $this->conf['source.'][$key];
				}
			}
		}
	}

	/**
	 * write the flex daten in $this->conf
	 *
	 * @author	unbekannt
	 */
	public function flex2conf(&$pObj) {
		if ( is_array($pObj->cObj->data['pi_flexform']['data']) ) { // if there are flexform values
			foreach ( $pObj->cObj->data['pi_flexform']['data'] as $key => $value ) { // every flexform category
				if ( is_array($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF']) ) {
					foreach ( $pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'] as $key2 => $value2 ) { // every flexform option
						if ( is_array($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2]) ) {
							foreach ( $pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2] as $key3 => $value3 ) {
								if ( $key3 === "el" ) {
									foreach ( $pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2][$key3] as $key4 => $value4 ) {
										if ( !empty($value4['vDEF']) AND $value4['vDEF'] != '0' ) {
											$pObj->conf[$key2 . '.'][$key4] = $value4['vDEF'];
										}
									}
								} else {
									if ( !empty($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2]['vDEF']) ) {
										$pObj->conf[$key2] = $pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2]['vDEF'];
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

if ( defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/html5video/pi1/class.tx_html5video_pi1.php'] ) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/html5video/pi1/class.tx_html5video_pi1.php']);
}
?>
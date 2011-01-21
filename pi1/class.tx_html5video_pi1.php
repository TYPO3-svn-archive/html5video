<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Gordon Brüggemann <gb@gb-web.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin 'HTML5 Video Player' for the 'html5video' extension.
 *
 * @author	Gordon Brüggemann <gb@gb-web.de>
 * @package	TYPO3
 * @subpackage	tx_html5video
 */
class tx_html5video_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_html5video_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_html5video_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'html5video';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
                if(!is_array($conf['type.'])){
                    return "<strong>Bitte static TS einbinden ! <br /> Pleas static TS include!</strong>";
                }
		$this->pi_initPIflexForm();
		$this->flex2conf($this);
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		$this->getFileData();
                $this->getHeader();
                 $this->getPoster();

		$this->baseUrl=$GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];
		 $support=$this->conf['SupportVideoJS']?$this->cObj->cObjGetSingle($this->conf['support'],$this->conf['support.']):'';
		
		
		
		$content = $this->cObj->stdWrap($this->getVideo().$this->getDownload().$support , $this->conf['video.']);
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * give the video tag 
	 * @return The video tag
	 */
	public function getVideo(){
		$class['video'] = empty($this->conf['video.']['class'])?' ':' class="'.$this->conf['video.']['class'].'"';
		$id['video'] = empty($this->conf['video.']['id'])?' ':' id="'.$this->conf['video.']['id'].'"';
		
		$preload= $this->conf['PreloadVideo']?' preload="true"':'';
		$autoplay=$this->conf['AutoplayVideo']?'autoplay="true"':'';
		
	    foreach($this->conf['source.'] as $type => $file){	    	
		    if(is_file($file)){
		    	//source file für Video
		    	if($type != 'poster'and $type != 'flv'){
			    	$sourc['src']= 'src="'.$file.'"';
			    	$sourctype='type="'.$this->conf['type.'][$type].'"';			
			    	$source.='<source '.$sourc['src'].$sourctype.'>';
		    	}
		    	//download für die Filme bei error
		    	if($type != 'poster'){
		    		$this->download.='<a href="'.$file.'">'.$this->conf['download.'][$type].'</a>';  //TYPO3 link verwenden
		    	}
	    	}
	    }	    
		$video='<video '.$class['video'].'width="'.$this->conf['width'].'" height="'.$this->conf['height'].'" controls="" '.$preload.$autoplay.$this->poster['video'].'>';
		$video.= $source;
		$video.= $this->getFlash();
		$video.='</video>';		
		return $video;
	}
	
	/**
	 * 
	 * @return The opject tag
	 */
	public function getFlash(){
		$class['flash'] = empty($this->conf['flash.']['class'])?' ':' class="'.$this->conf['flash.']['class'].'"';
		$id['flash'] = empty($this->conf['flash.']['id'])?' ':' id="'.$this->conf['flash.']['class'].'"';
		 //TODO: change or make a if for URL provided Extern file
		$baseUrl=$GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];
		$flashplayer=empty($this->conf['flash.']['player'])?'http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf':$this->conf['flash.']['player'];
                $this->conf['source.']['flv']= empty($this->conf['source.']['flv'])?$this->conf['source.']['mp4']:$this->conf['source.']['flv'];
		$video= $this->conf['FlashMP4']?$this->conf['source.']['mp4']:$this->conf['source.']['flv'];

		$autoplay=$this->conf['AutoplayVideo']?',"autoPlay":true':'","autoPlay":false';
		$preload= $this->conf['PreloadVideo']?',"autoBuffering":true':',"autoBuffering":false';

                $poster = '';
                if(!empty($this->conf["source."]["poster"])){
                    $poster= $this->baseUrl.$this->conf["source."]["poster"];
                }
                $flashconfig='value=\'config={"clip":{"url":"'.$video.$autoplay.$preload.' }}\'';

                $flash='<object '.$class['flash'].' width="'.$this->conf['width'].'" height="'.$this->conf['height'].'" type="application/x-shockwave-flash" data="'.$flashplayer.'">';
                $flash.='<param name="movie" value="'.$flashplayer.'" />';
                $flash.='<param name="allowfullscreen" value="true" />';
                $flash.='<param name="flashvars" '.$flashconfig.' />';
                $flash.= $this->poster['flash'].'</object>';
		
		return $flash;
	}
	
	/**
	 * creat the image for the player
	 * 
	 */
	public function getPoster(){
		$this->poster['video']='';
		$this->poster['flash']='';
		if(!empty($this->conf['source.']['poster'])){
			$this->poster['alt']= empty($this->conf['poster.']['alt'])?'':' alt="'.$this->conf['poster.']['alt'].'"';
			$this->poster['title']= empty($this->conf['poster.']['titel'])?'':' title="'.$this->conf['poster.']['titel'].'"';
		    $this->poster['video']= ' poster="'.$this->conf['source.']['poster'].'"';
		    $this->poster['flash']= '<img src="'.$this->conf['source.']['poster'].'" '.'width="'.$this->conf['width'].'" height="'.$this->conf['height'].$posterAlt.' />'; //TS Images
		} 
	}
	
	/**
	 * @return The downloads for the video
	 */
	public function getDownload(){
		$download=$this->conf['download']?$this->cObj->stdWrap($this->download, $this->conf['download.']):''; 
		
		return $download;
	}
	
	/**
	 * creat the header information
	 */
	public function getHeader(){
		
		$jsOptions='';
		$jsOptions=$this->jsOptions();
     	$this->conf['options'] = empty($this->conf['options'])?'myVideoJSPlayers':trim($this->conf['options']);
		//JS File
		$videoJS = empty($this->conf['videoJS'])?'typo3conf/ext/html5video/res/videoJS/video.js':$this->conf['videoJS'];
		$GLOBALS['TSFE']->pSetup['includeJS.'][$this->extKey] = $videoJS;
		//CSS File
		$videoCSS = empty($this->conf['videoCSS'])?'typo3conf/ext/html5video/res/videoJS/video-js.css':$this->conf['videoCSS'];
		$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey] = $videoCSS;
		//extra CSS File für wechselnde Skin
	    $skinCSS='';
		if( !empty($this->conf['skin'])){
			if(trim($this->conf['skin'])!=='orginal'){ 	
				$skinCSS = '<link rel="stylesheet" href="'.$this->conf['skin.'][$this->conf['skin']].'" type="text/css" media="screen" title="Video '.$this->conf['skin'].'" charset="utf-8">';		
			}
		}
		//start script		
		$setupJS ='';
		if( empty($this->conf['setupJS'])){
			$setupJS ='<script type="text/javascript" charset="utf-8"> VideoJS.DOMReady(function(){ var '.$this->conf['options'].' = VideoJS.setup("All"'.$jsOptions.');
});
			 </script>';
		}else{
			// wenn gesetzt auch keine jsOptions
			$setupJS = $this->conf['setupJS'];
		}
		
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $skinCSS.$setupJS;
	}
	/**
	 * 
	 *creat JavaScript Options
	 *
	 */
	public function jsOptions(){
		if(is_array($this->conf['options.'])){
     	//    $jsOptions = $this->conf['options'];     	    
     	    $jsOptions.= ' ,{';
     	     foreach($this->conf['options.'] as $option => $value){	
     	     	$jsOptions.= empty($value)?'':$option.':'.$value.',';
     	     }    
     	     $jsOptions.= '}';
     	    return $jsOptions;
     	}
     	return;
	}
	/**
	 * creat the patch for the videos dam and from upload folder
	 */
	public function getFileData(){
		$row = $this->local_cObj->data;			
		if (isset($row['_ORIG_uid']) && ($row['_ORIG_uid'] > 0)) {
	      $uid = $row['_ORIG_uid'];
		}else{
		  $uid = $row['uid'];
		}
		if ($row['_LOCALIZED_UID']) {
		  $uid = $row['_LOCALIZED_UID'];
		}
		
		$tableofcontent='tt_content';
		
		foreach($this->conf['source.'] as $key => $value){			
			if(t3lib_extMgm::isLoaded('dam')){
				$damArray[$key]= tx_dam_db::getReferencedFiles($tableofcontent,  $uid, 'html5video'.$key);
				foreach ($damArray[$key]['files'] as $id => $files){
					$this->conf['source.'][$key] = $files;
					$this->conf['source.'][$key.'.']['rows']=$damArray[$key][$id];
				}
			
			}else{
				$this->conf['patch']=empty($this->conf['patch'])?'uploads/html5video/':$this->conf['patch'];
			
				if(is_file( $this->conf['patch'].$this->conf['source.'][$key])){
					$this->conf['source.'][$key]= $this->conf['patch'].$this->conf['source.'][$key];
				}
			}
		}
	}
	
	/**
	 * write the flex daten in $this->conf
	 */
	public function flex2conf(&$pObj) {
		if (is_array($pObj->cObj->data['pi_flexform']['data'])) { // if there are flexform values
			foreach ($pObj->cObj->data['pi_flexform']['data'] as $key => $value) { // every flexform category
				if (count($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF']) > 0) {
					foreach ($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'] as $key2 => $value2) { // every flexform option
						if (count($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2]) > 0) {
							foreach($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2] as $key3 => $value3){
								if( $key3==="el"){
									foreach($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2][$key3] as $key4 => $value4){
										if(!empty($value4['vDEF']) AND $value4['vDEF']!='0'){
											$pObj->conf[$key2.'.'][$key4] = $value4['vDEF'];
										}
									}
								}else{
									if(!empty($pObj->cObj->data['pi_flexform']['data'][$key]['lDEF'][$key2]['vDEF'])){
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/html5video/pi1/class.tx_html5video_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/html5video/pi1/class.tx_html5video_pi1.php']);
}


?>
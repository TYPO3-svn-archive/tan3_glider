<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Stefan Mielke <stefan@tan3.de>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_tan3glider_pi1 extends tslib_pibase
 *   66:     function main($content, $conf)
 *   93:     function getRecords($contentelements, $description, $conf)
 *  135:     function loadJS($conf,$config)
 *  165:     function loadCSS($width, $height)
 *  183:     function init()
 *  207:     function getFlexform ($sheet, $key, $confOverride='')
 *  226:     function getPath($path)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Glider' for the 'tan3_glider' extension.
 *
 * @author	Stefan Mielke <stefan@tan3.de>
 * @package	TYPO3
 * @subpackage	tx_tan3glider
 */
class tx_tan3glider_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_tan3glider_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_tan3glider_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'tan3_glider';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->init();
		//$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
		// load the JS into the header
	    $this->loadJS($this->conf,$this->config);

		// load css width and height to header
		$this->loadCSS($this->config['width'], $this->config['height']);

		// get the content elements
		$content.= $this->getRecords($this->config['contentelements'] , $this->config['description'], $this->conf );

		// wrap them
		$content = $this->cObj->stdWrap($content, $this->conf['slider.']);

		return $content;
	}

	/**
	 * get and renders the selected content elements
	 *
	 * @param	array		$contentelements: ...
	 * @param	array		$description: ...
	 * @param	array		$conf: ...
	 * @return	string		all contentelements with tab navigation
	 */
	function getRecords($contentelements, $description, $conf) {
		// check if there any contenelements available
	    if ($contentelements =='') {
	       return ($conf['warnings'] == 1) ? $this->pi_getLL('warning_noContent') : '';
	    }
		// get all contentelements + description in an array
		$contentList = explode(',',$contentelements);
		$descriptionList = explode("\n",$description);
		$i=0;
		// run every contentelement
	    foreach ($contentList as $key=>$rawContent) {
			$i++;
			// if the description is empty, take the title of contentelement
			$row = $this->pi_getRecord('tt_content', $rawContent, 0); // get the record
			$panelId = (empty($descriptionList[$key])) ? $row['header'] : $descriptionList[$key] ;

			$navigation = '<a href="#panelid'.$i.'">'.htmlspecialchars($panelId).'</a>';
			// wrap navigation object
			$navObj.=  $this->cObj->stdWrap($navigation, $this->conf['navigation.']['link.']);

			// get the record from the content list
			$conf = array('tables' => 'tt_content','source' => $rawContent,'dontCheckPid' => 1);

			$contentObj.= '<div class="panel" id="panelid'.$i.'">';
			$contentObj.= $this->cObj->RECORDS($conf);
			$contentObj.= '</div>';
		}
		// show navigation tabs if checked
		if ($this->config['tabs']==1) {
			$content = $this->cObj->stdWrap($navObj, $this->conf['navigation.']);
		}
		$content.= $this->cObj->stdWrap($contentObj,$this->conf['scrollarea.']);
		return $content;
	}

	/**
	 * load the required javascript in the head
	 *
	 * @param	array		$conf: ...
	 * @param	array		$config: ...
	 */
  	function loadJS($conf,$config) {
		if ($this->conf['includeJQuery'] == 1) { // include jquery
			$header = '<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/jquery-1.3.2.min.js" type="text/javascript"></script>';
		}
		$header.= '<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/jquery.scrollTo-1.4.2-min.js" type="text/javascript"></script>';
		$header.= '<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/jquery.localscroll-1.2.7-min.js" type="text/javascript"></script>';
		$header.= '<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/jquery.serialScroll-1.2.1-min.js" type="text/javascript"></script>';
		$header.= '<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/glider.js" type="text/javascript"></script>';
		// initialize glider with options
		$header.= '<script type="text/javascript">
			$(document).ready(function() {
			var myglider = new Glider({
				duration: 400,
				showArrows: "'.$this->config['arrows'].'",
				arrowLeft: "'.$this->getPath($this->conf['arrowLeft']).'",
				arrowRight: "'.$this->getPath($this->conf['arrowRight']).'",
			});
			});
		</script>';
		// add the whole js for the header
	  	$GLOBALS['TSFE']->additionalHeaderData['tan3_glider'] = $header;
	}

	/**
	 * load css without javascript so that glider still works without javascript enabled
	 *
	 * @param	string		$width: ...
	 * @param	string		$height: ...
	 */
	function loadCSS($width, $height) {
		$header = (isset($this->conf['pathToCSS'])) ? '<link rel="stylesheet" href="'.$this->getPath($this->conf['pathToCSS']).'" type="text/css" />' : '';
		$header.= '<style type="text/css" media="screen">
			#slider { width:'.$width.'px; }
			.scroll { height:'.$height.'px; }
			.scrollContainer div.panel { width:'.$width.'px; height:'.$height.'px; }
		</style
		';
		$GLOBALS['TSFE']->additionalHeaderData['tan3_glidercss'] = $header;

	}

	/**
	 * Initialize variables
	 * 	...
	 *
	 * @return	[type]		...
	 */
	function init() {
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->content = '';

		// get values from flexform
		$this->pi_initPIflexForm();
		$this->config['contentelements'] = $this->getFlexform('sDEF','contentelements','contentelements');
		$this->config['description'] = $this->getFlexform('sDEF','description','description');
		$this->config['width'] = $this->getFlexform('sDEF', 'width', 'width');
		$this->config['height'] = $this->getFlexform('sDEF','height','height');
		$this->config['respectProportions'] = $this->getFlexform('advanced','respectProportions', 'respectProportions');
		$this->config['tabs'] = $this->getFlexform('sDEF','tabs', 'tabs');
		$this->config['arrows'] = $this->getFlexform('sDEF','arrows', 'arrows');
 	}
	/**
	 * Get the value out of the flexforms and if empty, take if from TS
	 *
	 * @param	string		$sheet: The sheed of the flexforms
	 * @param	string		$key: the name of the flexform field
	 * @param	string		$confOverride: The value of TS for an override
	 * @return	string		The value of the locallang.xml
	 */
	function getFlexform ($sheet, $key, $confOverride='') {
		// Default sheet is sDEF
		$sheet = ($sheet=='') ? $sheet = 'sDEF' : $sheet;
		$flexform = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, $sheet);

		// possible override through TS
		if ($confOverride=='') {
			return $flexform;
		} else {
			$value = $flexform ? $flexform : $this->conf[$confOverride];
			return $value;
		}
	}
	/**
	 * Gets the path to a file, needed to translate the 'EXT:extkey' into the real path
	 *
	 * @param	string		$path: Path to the file
	 * @return	the		real path
	 */
	function getPath($path) {
		if (substr($path,0,4)=='EXT:') {
			$keyEndPos = strpos($path, '/', 6);
			$key = substr($path,4,$keyEndPos-4);
			$keyPath = t3lib_extMgm::siteRelpath($key);
			$newPath = $keyPath.substr($path,$keyEndPos+1);
		return $newPath;
		}	else {
			return $path;
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tan3_glider/pi1/class.tx_tan3glider_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tan3_glider/pi1/class.tx_tan3glider_pi1.php']);
}

?>
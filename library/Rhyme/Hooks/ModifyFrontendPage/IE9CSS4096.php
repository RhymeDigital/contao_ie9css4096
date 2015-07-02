<?php

/**
 * Fixes for IE9CSS4096
 *
 * Copyright (C) 2015 Rhyme Digital
 *
 * @package    IE9CSS4096
 * @link       http://rhyme.digital
 * @license    LGPL
 */
 
namespace Rhyme\Hooks\ModifyFrontendPage;

use CssSplitter\Splitter;

/**
 * Class IE9CSS4096 
 *
 * Performs various IE9 fixes for the 4096 CSS selector limit
 * @copyright  2015 Rhyme Digital
 * @author     Blair Winans <blair@rhyme.digital>
 * @author     Adam Fisher <adam@rhyme.digital>
 * @package    IE9CSS4096
 */
class IE9CSS4096 extends \Controller
{
    
    /**
	 * Chop up any local CSS files that are too big and add an IE conditional
	 *
	 * @param string $strBuffer
	 * @param string $strTemplate
	 *
	 * @returns string $strBuffer
	 */
	public function run($strBuffer, $strTemplate)
	{
        $ua = \Environment::get('agent');
    	if(stripos($strTemplate, 'fe_') !== false && $ua->browser=='ie')
    	{
        	$objDOM = new \DOMDocument();
        	$objDOM->loadHTML($strBuffer);
        	$objCSSLinks = $objDOM->getElementsByTagName('link');
        	
        	foreach($objCSSLinks as $link)
        	{
            	//Local files only
            	if(strpos($link->getAttribute('href'), 'assets/css/') !== false)
            	{
                	//Replace ASSETS_URL
                	$strFile = str_replace(TL_ASSETS_URL, '', $link->getAttribute('href'));
                	
                	$objFile = new \File($strFile);
                	$strContent = $objFile->getContent();
                	
                	//Split up into chunklets
                	$splitter = new Splitter();
                	$count = $splitter->countSelectors($strContent)  - 4095;
                	if ($count > 0) 
                	{
                        $part = 2;
                        for($i = $count; $i > 0; $i -= 4095) 
                        {
                            $strFilename = 'assets/css/' . $objFile->filename .'-ie9-' . $part . '.css';
                            if(!file_exists(TL_ROOT . '/' . $strFilename))
                            {
                                //Write content
                                $strChunkContent = $splitter->split($strContent, $part);
                                
                                $objNewFile = new \File($strFilename);
                                $objNewFile->write($strChunkContent);
                                $objNewFile->close();
                                
                                $part++;
                            }
                            
                            //Add IE Conditional
                            foreach($objDOM->getElementsByTagName('head') as $head) 
                            {
                                $strComment  = '[if lte IE 9]><link rel="stylesheet" href="';
                                $strComment .= TL_ASSETS_URL . $strFilename .'"><![endif]';
                                $objChildNode = new \DOMComment($strComment);
                                $head->appendChild($objChildNode);
                            }
                        }
                    }
            	}
        	}
        	
        	$strBuffer = $objDOM->saveHTML();
    	}
    	
    	return $strBuffer;
    }
}

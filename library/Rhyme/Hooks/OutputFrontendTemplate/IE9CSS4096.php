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
 
namespace Rhyme\Hooks\OutputFrontendTemplate;

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
     * @hook   $GLOBALS['TL_HOOKS']['outputFrontendTemplate']
     *
     * @param  string $strBuffer
     * @param  string $strTemplate
     *
     * @return string $strBuffer
     */
    public function run($strBuffer, $strTemplate)
    {
        if(stripos($strTemplate, 'fe_') !== false)
        {
            //We need to pre-assemble the CSS files and determine their length, then split up if necessary
            global $objPage;
            $blnXhtml = ($objPage->outputFormat == 'xhtml');
            $objCombiner = new \Combiner();
            $arrNewCSS = array();
            
            //Most of this is taken from Controller::replaceDynamicScriptTags()
            //Unfortunately this is the only way to effectively check the length of the CSS
            //Because we have to use the outputFrontendTemplate Hook so that things happen pre-cache
            
            // Add the CSS framework style sheets
    		if (!empty($GLOBALS['TL_FRAMEWORK_CSS']) && is_array($GLOBALS['TL_FRAMEWORK_CSS']))
    		{
    			foreach (array_unique($GLOBALS['TL_FRAMEWORK_CSS']) as $stylesheet)
    			{
    				$objCombiner->add($stylesheet);
    			}
    		}
    		
    		// Add the internal style sheets
    		if (!empty($GLOBALS['TL_CSS']) && is_array($GLOBALS['TL_CSS']))
    		{
    			foreach (array_unique($GLOBALS['TL_CSS']) as $stylesheet)
    			{
    				$options = \String::resolveFlaggedUrl($stylesheet);
    
    				if ($options->static)
    				{
    					$objCombiner->add($stylesheet, filemtime(TL_ROOT . '/' . $stylesheet), $options->media);
    				}
    				else
    				{
    					static::splitCssFile($stylesheet, $options->media, $blnXhtml);
    					$arrNewCSS[] = $stylesheet;
    				}
    			}
    		}
    		
    		// Add the user style sheets
    		if (!empty($GLOBALS['TL_USER_CSS']) && is_array($GLOBALS['TL_USER_CSS']))
    		{
    			foreach (array_unique($GLOBALS['TL_USER_CSS']) as $stylesheet)
    			{
    				$options = \String::resolveFlaggedUrl($stylesheet);
    
    				if ($options->static)
    				{
    					if ($options->mtime === null)
    					{
    						$options->mtime = filemtime(TL_ROOT . '/' . $stylesheet);
    					}
    
    					$objCombiner->add($stylesheet, $options->mtime, $options->media);
    				}
    				else
    				{
    					static::splitCssFile($stylesheet, $options->media, $blnXhtml);
    					$arrNewCSS[] = $stylesheet;
    				}
    			}
    		}
    
    		// Create the aggregated style sheet
    		if ($objCombiner->hasEntries())
    		{
    			$strCSSFile = $objCombiner->getCombinedFile();
    			static::splitCssFile($strCSSFile, 'all', $blnXhtml);
    			$arrNewCSS[] = $strCSSFile;
    		}
    		
    		//Reset Contao defaults so they don't need to all be regenerated
    		$GLOBALS['TL_CSS'] = $arrNewCSS;
    		$GLOBALS['TL_FRAMEWORK_CSS'] = array();
    		$GLOBALS['TL_USER_CSS'] = array();

        }
        
        return $strBuffer;
    }
    
    /**
     * Chop up any CSS files that are too big and add an IE conditional
     *
     * @param string $strFile
     */
    public static function splitCssFile($strFile, $media, $blnXhtml)
    {
        //Replace TL_ASSETS_URL
        $strFile = str_replace(TL_ASSETS_URL, '', $strFile);
        
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
                }
                
                //Add IE Conditional
                $strLink = \Template::generateStyleTag(\Controller::addStaticUrlTo($strFilename), $media, $blnXhtml);
                $strComment  = '<!--[if lte IE 9]>'.$strLink.'<![endif]-->';
                $GLOBALS['TL_HEAD'][] = $strComment;
                
                $part++;
            }
        }
    }
    
}

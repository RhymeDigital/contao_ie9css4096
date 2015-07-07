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
 
/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('\Rhyme\Hooks\OutputFrontendTemplate\IE9CSS4096', 'run');

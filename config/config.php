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
$GLOBALS['TL_HOOKS']['modifyFrontendPage'][] = array('\Rhyme\Hooks\ModifyFrontendPage\IE9CSS4096', 'run');

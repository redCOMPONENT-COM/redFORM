<?php
/**
 * @package     Redform
 * @subpackage  mod_redform_latest_submissions
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Get helper
require_once 'helper.php';

$list = ModRedformLatestSubmissionHelper::getList($params);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_redform_latest_submissions', $params->get('layout', 'default'));

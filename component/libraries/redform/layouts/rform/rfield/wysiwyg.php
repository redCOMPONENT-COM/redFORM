<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$editor = JFactory::getEditor();
$element = $editor->display($data->getFormElementName(), $data->value, '100%;', '200', '75', '20', false);
?>
<?php echo $element; ?>

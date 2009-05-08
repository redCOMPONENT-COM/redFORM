<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.html.pane'); 
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
	<?php $pane = JPane::getInstance('tabs');
	$row = 0;
	echo $pane->startPane("settings");
	echo $pane->startPanel( JText::_('Newsletter'), 'newsletter_tab' );
	?>
		<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td width="15%">
			<?php echo JHTML::tooltip(JText::_('Enable to integrate PHPList registration'), JText::_('Use PHPList'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Use PHPList'); ?>
			</td>
			<td>
			<?php echo $this->lists['use_phplist']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = $row - 1; ?>">
			<td>
			<?php echo JHTML::tooltip(JText::_('Give the full pathname relative to the webroot'), JText::_('Path to PHPList'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Path to PHPList'); ?>
			</td>
			<td>
			<input type="text" id="phplist_path" name="configuration[phplist_path]" value="<?php echo $this->configuration['phplist_path']->value;?>"></input>
			</td>
		</tr>
		<tr class="row<?php echo $row = $row - 1; ?>">
			<td>
			<?php echo JHTML::tooltip(JText::_('Enable to integrate ccNewsletter registration'), JText::_('Use ccNewsletter'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Use ccNewsletter'); ?>
			</td>
			<td>
			<?php echo $this->lists['use_ccnewsletter']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = $row - 1; ?>">
			<td>
			<?php echo JHTML::tooltip(JText::_('Enable to integrate Acajoom registration'), JText::_('Use Acajoom'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Use Acajoom'); ?>
			</td>
			<td>
			<?php echo $this->lists['use_acajoom']; ?>
			</td>
		</tr>
		</table>
	<?php
	echo $pane->endPanel();
	echo $pane->startPanel( JText::_('FILES'), 'files_tab' );
	?>
	<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td width="15%">
			<?php echo JHTML::tooltip(JText::_('REDFORM_FILES_TIP'), JText::_('REDFORM_FILES'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('REDFORM_FILES'); ?>
			</td>
			<td>
			<input type="text" id="filelist_path" name="configuration[filelist_path]" size="120" value="<?php echo $this->configuration['filelist_path']->value;?>"></input>
			</td>
		</tr>
	</table>
	<?php
	echo $pane->endPanel();
	echo $pane->endPane();
	?>
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="configuration" />
	<input type="hidden" name="controller" value="configuration" />
</form>

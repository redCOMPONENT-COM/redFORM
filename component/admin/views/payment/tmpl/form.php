<?php
/**
 * @version    $Id: form.php 94 2008-05-02 10:28:05Z julienv $
 * @package    JoomlaTracks
 * @copyright	Copyright (C) 2008 Julien Vonthron. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla Tracks is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>
<?php JHTML::_('behavior.formvalidation'); ?>

<?php
// Set toolbar items for the page
$edit		= JRequest::getVar('edit',true);
$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
JToolBarHelper::title( JText::_( 'Payment history' ) );
JToolBarHelper::save();
JToolBarHelper::apply();
if (!$edit)  {
	JToolBarHelper::cancel();
} else {
	// for existing items the button is renamed `close`
	JToolBarHelper::cancel( 'cancel', 'Close' );
}
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

    // do field validation
    var validator = document.formvalidator;
//    if ( validator.validate(form.name) === false ){
//      alert( "<?php echo JText::_( 'NAME IS REQUIRED', true ); ?>" );
//    } else {
		submitform( pressbutton );
//    }
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col50">
<fieldset class="adminform"><legend><?php echo JText::_( 'Payment' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100" align="right" class="key">
			<label for="date"><?php echo JText::_( 'Date' ); ?>:</label>
		</td>
		<td>
			<?php echo JHTML::calendar($this->object->date, 'date', 'date', '%Y-%m-%d %H:%M:%S'); ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label for="status"><?php echo JText::_( 'Status' ); ?>:</label>
		</td>
		<td>
			<input name="status" type="text" value="<?php echo $this->object->status; ?>" />
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label for="data"><?php echo JText::_( 'Data' ); ?>:</label>
		</td>
		<td>
			<textarea name="data" cols="80" rows="6"><?php echo $this->object->data; ?></textarea>			
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label><?php echo JText::_( 'Paid' ); ?>:</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'paid', '', $this->object->paid); ?>
		</td>
	</tr>
</table>
</fieldset>
</div>

<div class="clr"></div>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redform" /> 
<input type="hidden" name="controller" value="payments" /> 
<input type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
<input type="hidden" name="submit_key" value="<?php echo $this->submit_key; ?>" />
<input type="hidden" name="task" value="" />

</form>
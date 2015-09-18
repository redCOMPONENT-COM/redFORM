<?php
/**
 * @version 1.0 $Id: default.php 30 2009-05-08 10:22:21Z roland $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$invoices = array();

$sids = array_map(
	function ($registration) {
		return $registration->sid;
	},
	$this->attending
);

JPluginHelper::importPlugin('redform');
$dispatcher = JDispatcher::getInstance();
$dispatcher->trigger('onGetSubmittersInvoices', array($sids, &$invoices));

$pdfImg = JHtml::image('plugins/redform/economic/images/pdf.png', 'get pdf');
?>
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="attended-events" class="redevent-ajaxnav">

	<table class="eventtable" summary="attending">
		<thead>
			<tr>
				<th class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_DATE'), 'x.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<th class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_TITLE'), 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php if ($this->params->get('showlocate', 1)) :?>
					<th class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_VENUE'), 'l.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php endif; ?>

				<?php if ($this->params->get('showcity', 0)) : ?>
					<th class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_CITY'), 'l.city', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php endif; ?>

				<?php if ($this->params->get('showstate', 0)) : ?>
					<th class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_STATE'), 'l.state', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php endif; ?>

				<?php if ($this->params->get('showcat', 1)) : ?>
					<th id="el_category" class="sectiontableheader" align="left"><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TABLE_HEADER_CATEGORY'), 'c.name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php endif; ?>

				<th id="payementlcol"><?php echo JText::_('COM_REDEVENT_TABLE_HEADER_PAYMENT'); ?></th>
			</tr>
		</thead>

		<tbody>
		<?php if (count((array)$this->attended) == 0) : ?>
			<tr align="center"><td colspan="15"><?php echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?></td></tr>
		<?php else :
		$i = 0;
		foreach ((array) $this->attended as $row) : ?>
	  		<tr class="sectiontableentry<?php echo $i +1 . $this->params->get( 'pageclass_sfx' ); ?>" >
	  			<td align="left">
	   				<?php echo RedeventHelperDate::formatEventDateTime($row);	?>
				</td>

				<?php
				//Link to details
				$detaillink = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
				//title
				?>
				<td headers="el_title" align="left" valign="top">
					<a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape(RedeventHelper::getSessionFullTitle($row)); ?></a>
				</td>

				<?php if ($this->params->get('showlocate', 1)) : ?>
					<td headers="el_location" align="left" valign="top">
						<?php
						if ($this->params->get('showlinkvenue',1) == 1 ) :
							echo $row->locid != 0 ? "<a href='".JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug))."'>".$this->escape($row->venue)."</a>" : '-';
						else :
							echo $row->locid ? $this->escape($row->venue) : '-';
						endif;
						?>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('showcity', 0)) : ?>
					<td headers="el_city" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>
				<?php endif; ?>
				<?php if ($this->params->get('showstate', 0)) : ?>
					<td headers="el_state" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>
				<?php endif; ?>

				<?php if ($this->params->get('showcat', 1)) : ?>
					<td headers="el_category" align="left" valign="top">
						<?php foreach ($row->categories as $k => $cat): ?>
						<?php if ($this->params->get('catlinklist', 1) == 1) : ?>
						<a href="<?php echo JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($cat->slug)); ?>">
							<?php echo $cat->name ? $this->escape($cat->name) : '-' ; ?>
						</a>
	            <?php else: ?>
	            	<?php echo $cat->name ? $this->escape($cat->name) : '-'; ?>
	            <?php endif; ?>
	            <?php echo ($k < count($row->categories)) ? '<br/>' : '' ; ?>
	          <?php endforeach; ?>
	          </td>
	        <?php endif; ?>

			    <td class="payment">
				    <?php echo RLayoutHelper::render('redevent.myevents.invoices', compact('row', 'pdflink', 'pdfImg', 'invoices')); ?>
			    </td>
			</tr>

	  		<?php
	  		$i = 1 - $i;
			endforeach;
			endif;
			?>

		</tbody>
	</table>

	<input type="hidden" name="limitstart_attended" value="<?php echo $this->lists['limitstart_attended']; ?>" class="redajax_limitstart" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" class="redajax_order"/>
	<input type="hidden" name="filter_order_Dir" value="" class="redajax_order_dir"/>
	<input type="hidden" name="task" value="myevents.attended" />

</form>

<!--pagination-->
<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->attended_pageNav->get('pages.total') > 1)) : ?>
<div class="pagination">
	<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
				<?php echo $this->attended_pageNav->getPagesCounter(); ?>
		</p>

		<?php endif; ?>
	<?php echo $this->attended_pageNav->getPagesLinks(); ?>
</div>
<?php  endif; ?>
<!-- pagination end -->

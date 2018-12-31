<?php
/**
 * @package    Redform.admin
 * @copyright  redform (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

RHelperAsset::load('redform-backend.css');
?>

<?php if ($field = JFactory::getApplication()->input->getString('field')): ?>
	<script type="text/javascript">
		(function($){
			$(function(){
				$('td.tagname').click(function(){
					$('#selectedField input').val($(this).text());
					$('#selectedField input').focus();
				});

				$('#selectedField button').click(function(){
					window.parent.redformEditorInsertTag($('#selectedField input').val(), '<?= $field ?>');
				});
			})
		})(jQuery);
	</script>

	<div id="selectedField" class="form-inline">
		<input type="text" placeholder="<?= JText::_('COM_REDFORM_TAGS_MODAL_CLICK_TAG_TO_SELECT') ?>"/>
		<button type="button" class="btn btn-success"><?= JText::_('COM_REDFORM_TAGS_MODAL_INSERT') ?></button>
	</div>
<?php endif; ?>

<?php $active = true; ?>
<ul class="nav nav-tabs" id="tagsTab">
	<?php foreach ($this->items as $section => $tags): ?>
		<li<?php echo ($active ? ' class="active"' : ''); ?>>
			<a href="#tags<?php echo $section; ?>" data-toggle="tab">
				<strong><?php echo JText::_($section); ?></strong>
			</a>
		</li>
		<?php $active = false; ?>
	<?php endforeach; ?>

	<li>
		<a href="#tags-advanced" data-toggle="tab">
			<strong><?php echo JText::_('COM_REDFORM_TAGS_ADVANCED'); ?></strong>
		</a>
	</li>
</ul>

<?php $active = true; ?>
<div class="tab-content">
	<?php foreach ($this->items as $section => $tags): ?>
		<div class="tab-pane <?php echo ($active ? ' active' : ''); ?>" id="tags<?php echo $section; ?>">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="span4"><?php echo JText::_('COM_REDFORM_TAGS_NAME')?></th>
						<th class="span8"><?php echo JText::_('COM_REDFORM_TAGS_DESCRIPTION')?></th>
					</tr>
				</thead>
				<tbody>
					<?php $k = 0; ?>
					<?php foreach ($tags as $tag): ?>
					<tr>
						<td class="tagname">[<?php echo addslashes($this->escape($tag->name)); ?>]</td>
						<td><?php echo $tag->description; ?></td>
					</tr>
					<?php $k = 1 - $k; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endforeach; ?>
	<div class="tab-pane" id="tags-advanced">
		<h3>RDFIF</h3>

		<p><?php echo JText::_('COM_REDFORM_TAGS_RDFIF_INTRO')?></p>
		<h4><?php echo JText::_('COM_REDFORM_TAGS_RDFIF_TITLE_USAGE')?></h4>
		<p>[rdfif:&lt;condition&gt;;&lt;operand&gt;(;&lt;operand&gt;)....]...[rdfendif]</p>
		<p><?php echo JText::_('COM_REDFORM_TAGS_RDFIF_SUPPORTED_CONDITIONS')?></p>
		<dl>
			<?php foreach(RdfHelperTagsreplace::getConditions() as $condition):  ?>
				<dt><?php echo $condition['usage'] ?></dt>
				<dd><?php echo $condition['description'] ?></dd>
			<?php endforeach; ?>
		</dl>
	</div>
</div>

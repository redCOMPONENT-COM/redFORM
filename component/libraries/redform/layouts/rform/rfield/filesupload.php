<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

/** @var \RdfRfieldFilesupload $data */
$data = $displayData;

$properties = $data->getInputProperties();
$options    = ['version' => 'auto', 'relative' => true];

HTMLHelper::_('script', 'com_redform/dropzone/dropzone.min.js', $options);
HTMLHelper::_('stylesheet', 'com_redform/dropzone/dropzone.min.css', $options);

if (!empty($data->getValue()))
{
	foreach ($data->getValue() as $uuid => $val)
	{
		echo '<input type="hidden" name="'
			. $data->getFormElementName() . '[' . $uuid . ']" value="'
			. $val . '" />';
	}
}
?>
<div class="dropzone" id="redFormDropZone<?php echo $data->form_id ?>"></div>
<script>
	jQuery(document).ready(function () {
		var form = jQuery("#redForm_<?php echo $data->form_id ?>");

		// camelized version of the `id`
		Dropzone.options.redFormDropZone<?php echo $data->form_id ?> = {
			url: '<?php echo Uri::base() . 'index.php?option=com_redform&task=redform.upload&format=json' ?>',
			uploadMultiple: true,
			parallelUploads: <?php echo (int) $data->getParam('maxfiles', 10) ?>,
			maxFiles: <?php echo (int) $data->getParam('maxfiles', 10) ?>,
			paramName: "file",
			addRemoveLinks: true,
			maxFilesize: <?php echo (int) $data->getParam('maxsize', 1000) ?>,
			acceptedFiles: <?php echo (empty($data->getParam('accepted_files', '')) ? 'null' : '\'' . $data->getParam('accepted_files', '') . '\'') ?>,
			params: {
				'<?php echo Session::getFormToken() ?>': 1,
				'form_id': '<?php echo $data->form_id ?>',
				'field_id': '<?php echo $data->id ?>'
			},

			// The setting up of the dropzone
			init: function () {
				<?php
				if (!empty($data->getValue()))
				{
					foreach ($data->getValue() as $uuid => $val)
					{
						$encoded = base64_decode($val);
						$decoded = json_decode($encoded, true);
						$decoded['status'] = 'added';
						$decoded['uuid']   = $uuid;
						$encoded = json_encode($decoded);

						echo 'this.displayExistingFile('
							. $encoded . ', \''
							. Uri::root() . $decoded['path'] . '\');';
					}
				}
				?>this.on('sendingmultiple', function (files, xhr, formData) {
					for (var i = 0; i < files.length; i++) {
						formData.append(
							'uuid['+i+']',
							files[i]['upload']['uuid']
						);
					}
				})
				.on('removedfile', function (file) {
					var uuid, token, fileName;
					if (file.uuid) {
						uuid = file.uuid;
						token = file.token;
						fileName = file.stored_name;
					} else {
						uuid = file.upload.uuid;
						token = file.additionalInfo.token;
						fileName = file.additionalInfo.tmp_name;
					}
					var found = form.find('input[name=\"<?php echo $data->getFormElementName() ?>['+uuid+']\"]');
					if (found) {
						found.remove();
						jQuery.ajax({
							url: '<?php echo Uri::base() . 'index.php?option=com_redform&task=redform.remove&format=json' ?>',
							type : "GET",
							data : {
								file_name: fileName,
								token: token,
								form_id: '<?php echo $data->form_id ?>'
							}
						});
					}
				})
				.on("successmultiple", function (files, responseStr) {
					// Gets triggered when the files have successfully been sent.
					var responseObj = JSON.parse(responseStr);

					if (!responseObj.success && responseObj.message)
					{
						for (var f = 0; f < files.length; f++) {
							var file = files[f];
							var message = responseObj.message;
							file.previewElement.classList.add("dz-error");
							file.status = Dropzone.ERROR;
							var els = file.previewElement.querySelectorAll("[data-dz-errormessage]");
							for (var i = 0; i < els.length; i++) {
								els[i].textContent = message;
							}
						}
					}

					if (responseObj.success)
					{
						for (var f = 0; f < files.length; f++) {
							var file = files[f];
							file.additionalInfo = responseObj.data[file.upload.uuid];
							form.append(
								'<input type="hidden" name="<?php echo $data->getFormElementName() ?>['
								+(file.upload.uuid)+']" value="'+(file.additionalInfo.base64)+'" />'
							);
						}
					}
				});
			},

			/**
			 * The text used before any files are dropped.
			 */
			dictDefaultMessage: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTDEFAULTMESSAGE', true) ?>",

			/**
			 * The text that replaces the default message text it the browser is not supported.
			 */
			dictFallbackMessage: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTFALLBACKMESSAGE', true) ?>",

			/**
			 * The text that will be added before the fallback form.
			 * If you provide a  fallback element yourself, or if this option is `null` this will
			 * be ignored.
			 */
			dictFallbackText: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTFALLBACKTEXT', true) ?>",

			/**
			 * If the filesize is too big.
			 * `{{filesize}}` and `{{maxFilesize}}` will be replaced with the respective configuration values.
			 */
			dictFileTooBig: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTFILETOOBIG', true) ?>",

			/**
			 * If the file doesn't match the file type.
			 */
			dictInvalidFileType: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTINVALIDFILETYPE', true) ?>",

			/**
			 * If the server response was invalid.
			 * `{{statusCode}}` will be replaced with the servers status code.
			 */
			dictResponseError: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTRESPONSEERROR', true) ?>",

			/**
			 * If `addRemoveLinks` is true, the text to be used for the cancel upload link.
			 */
			dictCancelUpload: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTCANCELUPLOAD', true) ?>",

			/**
			 * The text that is displayed if an upload was manually canceled
			 */
			dictUploadCanceled: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTUPLOADCANCELED', true) ?>",

			/**
			 * If `addRemoveLinks` is true, the text to be used for confirmation when cancelling upload.
			 */
			dictCancelUploadConfirmation: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTCANCELUPLOADCONFIRMATION', true) ?>",

			/**
			 * If `addRemoveLinks` is true, the text to be used to remove a file.
			 */
			dictRemoveFile: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTREMOVEFILE', true) ?>",

			/**
			 * Displayed if `maxFiles` is st and exceeded.
			 * The string `{{maxFiles}}` will be replaced by the configuration value.
			 */
			dictMaxFilesExceeded: "<?php echo Text::_('LIB_REDFORM_FILES_UPLOAD_DICTMAXFILESEXCEEDED', true) ?>"
		};
	});
</script>

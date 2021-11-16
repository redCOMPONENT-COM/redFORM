<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die( 'Restricted access');

/**
 * Class PlgRedform_captchaHcaptcha
 */
class PlgRedform_captchaHcaptcha extends CMSPlugin
{
	/**
	 * onGetCaptchaField trigger
	 *
	 * @param   string  &$text  text to modify
	 *
	 * @return bool
	 */
	public function onGetCaptchaField(&$text)
	{
		Factory::getDocument()
			->addScript('https://hcaptcha.com/1/api.js', [], ['defer' => true, 'async' => true]);

		$attributes = [
			'data-sitekey' => $this->params->get('siteKey'),
			'data-theme' => $this->params->get('theme', 'light'),
			'data-size' => $this->params->get('size', 'normal'),
		];

		$text = '<div class="h-captcha" ' . ArrayHelper::toString($attributes) . '></div>';

		return true;
	}

	/**
	 * onCheckCaptcha trigger
	 *
	 * @param   bool  &$result  result
	 *
	 * @return bool
	 */
	public function onCheckCaptcha(&$result)
	{
		$app      = Factory::getApplication();
		$input    = $app->input;
		$response = $input->post->getString('h-captcha-response');
		$result   = false;

		if (empty($response))
		{
			return $result;
		}

		try
		{
			$response = HttpFactory::getHttp()
				->post(
					'https://hcaptcha.com/siteverify',
					[
						'secret' => $this->params->get('secretKey'),
						'response' => $response,
						'sitekey' => $this->params->get('siteKey'),
						'remoteip' => $input->server->get('REMOTE_ADDR')
					]
				);

			$responseData = json_decode($response->body);
			$result       = $responseData->success;
		}
		catch (Exception $e)
		{
		}

		return $result;
	}
}

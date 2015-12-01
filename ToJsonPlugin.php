<?php
namespace Craft;

class ToJsonPlugin extends BasePlugin
{

	/* --------------------------------------------------------------
	 * PLUGIN INFO
	 * ------------------------------------------------------------ */

	public function getName()
	{
		return Craft::t('toJSON');
	}

	public function getVersion()
	{
		return '0.9.0';
	}

	public function getDeveloper()
	{
		return 'Torben Sko';
	}

	public function getDeveloperUrl()
	{
		return 'http://torbensko.com';
	}

	/**
	 * Load the TruncateTwigExtension class from our ./twigextensions
	 * directory and return the extension into the template layer
	 */
	public function addTwigExtension()
	{
		Craft::import('plugins.tojson.twigextensions.ToJsonTwigExtension');
		return new ToJsonTwigExtension();
	}

	public function init() {
		craft()->log->removeRoute('WebLogRoute');
		craft()->log->removeRoute('ProfileLogRoute');
		parent::init();
	}

	protected function defineSettings() {
    return array(
    	'linkUrl' => array(AttributeType::String, 'default' => craft()->getSiteUrl()),
    );
  }

  public function getSettingsHtml() {
		return craft()->templates->render('tojson/_settings', array(
			'settings' => $this->getSettings()
		));
	}

}

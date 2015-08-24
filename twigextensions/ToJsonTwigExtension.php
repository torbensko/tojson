<?php
namespace Craft;

class ToJsonTwigExtension extends \Twig_Extension {

	public function getName() {
		return Craft::t('toJSON');
	}

	public function getFilters() {
		return array(
			'to_json' => new \Twig_Filter_Method($this, 'toJsonFilter')
		);
	}

	public function toJsonFilter($entries) {
		$expandedContent = craft()->toJson->toJson($entries);
		return JsonHelper::encode($expandedContent);
	}

}

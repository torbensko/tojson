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

	public function toJsonFilter($entries, $filter, $depth = false) {

		$allowableFields = array();

		if ( !$filter ) {
			$filter = craft()->request->getParam('filter');
		}
		if ( is_string($filter) && strlen($filter) ) {
			$allowableFields = explode('|', $filter);
		}
		if ( !$depth ) {
			$depth = craft()->request->getParam('depth');
		}
		if ( !is_int($depth) ) {
			$depth = -1;
		}

		$expandedContent = craft()->toJson->toJson(
				$entries, 
				$allowableFields,
				$depth);
		
		return JsonHelper::encode( $expandedContent );
	}

}

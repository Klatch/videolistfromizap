<?php

class Sclay_Embeddable_Iframe extends Sclay_Embeddable_Media {
	protected $rawAttributes = array();

	public $attributes = array(
		'frameborder' => '0',
		'allowfullscreen' => true,
		'webkitallowfullscreen' => true,
		'mozallowfullscreen' => true,
	);

	public function __construct(array $rawAttributes) {
		$this->rawAttributes = $rawAttributes;

		foreach (array('width', 'height') as $key) {
			if (!empty($rawAttributes[$key])) {
				$this->{$key} = (int)$rawAttributes[$key];
			}
		}

		if (!empty($rawAttributes['src']) && preg_match('~^https?\\://~', $rawAttributes['src'])) {
			$this->src = $rawAttributes['src'];
		}
	}

	public function getRawAttributes() {
		return $this->rawAttributes;
	}

	public function getAttributes() {
		$a = $this->attributes;
		$a['src'] = $this->src;
		$a['width'] = $this->width;
		$a['height'] = $this->height;
		return $a;
	}
}

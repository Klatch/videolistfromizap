<?php

class Sclay_Embeddable_Object extends Sclay_Embeddable_Media {
	protected $objectAttributes = array();
	protected $embedAttributes = array();
	protected $rawParams = array();

	const CLASSID = 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000';
	const PLUGINSPAGE = 'http://get.adobe.com/flashplayer/';
	const TYPE = 'application/x-shockwave-flash';

	public $flashvars;
	public $params = array();

	public function __construct(array $objectAttributes, array $embedAttributes, array $rawParams) {
		$this->objectAttributes = $objectAttributes;
		$this->embedAttributes = $embedAttributes;
		$this->rawParams = $rawParams;

		foreach (array('width', 'height') as $key) {
			if (!empty($objectAttributes[$key])) {
				$this->{$key} = (int)$objectAttributes[$key];
			} elseif (!empty($embedAttributes[$key])) {
				$this->{$key} = (int)$embedAttributes[$key];
			}
		}

		$this->setFlashvars($rawParams)
			|| $this->setFlashvars($embedAttributes)
			|| $this->setFlashvars($objectAttributes);

		$this->setSrc($objectAttributes, 'data')
			|| $this->setSrc($rawParams, 'movie')
			|| $this->setSrc($embedAttributes, 'src');

		$attributePatterns = array(
			'play' => '~^(true|false)$~i',
			'loop' => '~^(true|false)$~i',
			'menu' => '~^(true|false)$~i',
			'quality' => '~^((auto)?(low|high)|medium|best)$~i',
			'scale' => '~^(default|showall|noborder|exactfit|noscale)$~i',
			'align' => '~^[tlr]$~i',
			'salign' => '~^([tlr]|t[lr])$~i',
			'wmode' => '~^(window|direct|opaque|transparent|gpu)$~i',
			'bgcolor' => '~^#[a-f0-9]{6}$~i',
			'allowfullscreen' => '~^(true|false)$~i',
			'allowscriptaccess' => '~^(always|samedomain|never)$~i',
			'fullscreenaspectratio' => '~^(portrait|landscape)$~i',
		);
		foreach ($attributePatterns as $prop => $pattern) {
			$this->setAttribute($prop, $pattern, $rawParams)
				|| $this->setAttribute($prop, $pattern, $embedAttributes);
		}
	}

	protected function setAttribute($prop, $pattern, $arr) {
		if (empty($arr[$prop]) || !preg_match($pattern, $arr[$prop])) {
			return false;
		}
		$this->params[$prop] = $arr[$prop];
		return true;
	}

	protected function setSrc($arr, $key) {
		if (empty($arr[$key]) || !preg_match('~^https?\\://~', $arr[$key])) {
			return false;
		}
		$this->src = $arr[$key];
		return true;
	}

	protected function setFlashvars($arr) {
		if (empty($arr['flashvars']) || false === strpos($arr['flashvars'], '=')) {
			return false;
		}
		$this->flashvars = $arr['flashvars'];
		return true;
	}

	public function getRawObjectAttributes() {
		return $this->objectAttributes;
	}

	public function getRawParams() {
		return $this->rawParams;
	}

	public function getRawEmbedAttributes() {
		return $this->embedAttributes;
	}

	public function getEmbedAttributes() {
		$a = $this->params;
		$a['src'] = $this->src;
		$a['width'] = $this->width;
		$a['height'] = $this->height;
		$a['type'] = self::TYPE;
		if (!empty($this->flashvars)) {
			$a['flashvars'] = $this->flashvars;
		}
		return $a;
	}

	public function getObjectAttributes() {
		$a = array();
		$a['width'] = $this->width;
		$a['height'] = $this->height;
		return $a;
	}

	public function getParams() {
		$a = $this->params;
		$a['movie'] = $this->src;
		if (!empty($this->flashvars)) {
			$a['flashvars'] = $this->flashvars;
		}
		return $a;
	}
}

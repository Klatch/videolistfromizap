<?php

/**
 * Modelled on YouTube's embed code
 */
class Sclay_Embeddable_DefaultRenderer {

	public $xml = true;

	public function render(Sclay_Embeddable_Media $media) {
		if ($media instanceof Sclay_Embeddable_Iframe) {
			return $this->renderIframe($media);
		}
		if ($media instanceof Sclay_Embeddable_Object) {
			return $this->renderObject($media);
		}
		return '';
	}

	protected function renderIframe(Sclay_Embeddable_Iframe $iframe) {
		return $this->renderOpenTag('iframe', $iframe->getAttributes()) . '</iframe>';
	}

	/**
	 * @param Sclay_Embeddable_Object $object
	 * @return string
	 */
	protected function renderObject(Sclay_Embeddable_Object $object) {
		$html = $this->renderOpenTag('object', $object->getObjectAttributes());
		foreach ($object->getParams() as $name => $value) {
			$html .= $this->renderOpenTag('param', array(
				'name' => $name,
				'value' => $value,
			)) . '</param>';
		}
		$html .= $this->renderOpenTag('embed', $object->getEmbedAttributes()) . '</embed>';
		$html .= "</object>";
		return $html;
	}

	protected function renderOpenTag($name, array $attrs) {
		return "<$name " . $this->formatAttributes($attrs) . '>';
	}

	protected function formatAttributes(array $attrs) {
		$attributes = array();

		foreach ($attrs as $name => $val) {
			if ($val === true) {
				if ($this->xml) {
					$val = $name;
				} else {
					$attributes[] = $name;
					continue;
				}
			}
			$val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
			$attributes[] = "$name=\"$val\"";
		}
		return implode(' ', $attributes);
	}
}

<?php

/**
 * Parse a fragment of HTML
 */
class Sclay_Embeddable_FragmentParser {

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var DOMXPath|null
	 */
	protected $xpath;

	/**
	 * @return array
	 */
	public function getParseErrors()
	{
		return $this->errors;
	}

	/**
	 * Return a DOMElement containing the nodes of the markup fragment
	 *
	 * @param string $markupFragment
	 * @param bool $allowParseError
	 * @return DOMElement|bool false on failure to parse
	 */
	public function parse($markupFragment, $allowParseError = false) {
		$markupFragment = str_replace(array("\r\n", "\r"), "\n", $markupFragment);
		$libxmlErrorHandling = libxml_use_internal_errors(true);
		$libxmlEntityLoading = libxml_disable_entity_loader(true);

		$ret = false;

		$doc = new DOMDocument();
		$html = "<html><meta http-equiv='content-type' "
			. "content='text/html; charset=UTF-8'><body><div>{$markupFragment}</div></body>"
			. "</html>";
		$success = $doc->loadHTML($html);
		$this->errors = libxml_get_errors();
		if ($success && ($allowParseError || !$this->errors)) {
			$this->xpath = new DOMXPath($doc);
			$nodeList = $this->xpath->query('/html/body/div[1]');
			if ($nodeList->length) {
				$ret = $nodeList->item(0);
			} else {
				$this->errors[] = 'Could not find fragment container';
			}
		}

		libxml_clear_errors();
		libxml_use_internal_errors($libxmlErrorHandling);
		libxml_disable_entity_loader($libxmlEntityLoading);
		return $ret;
	}

	/**
	 * @param DOMElement $el
	 * @return string
	 */
	public function extractName(DOMElement $el) {
		return $this->strToLower($el->nodeName);
	}

	/**
	 * @param DOMElement $el
	 * @return array
	 */
	public function extractAttributes(DOMElement $el) {
		$attrs = array();
		foreach ($el->attributes as $attrNode) {
			/* @var DOMAttr $attrNode */
			$name = $this->strToLower($attrNode->name);
			$attrs[$name] = $attrNode->value;
		}
		return $attrs;
	}

	/**
	 * @return DOMXPath|null
	 */
	public function getXpath()
	{
		return $this->xpath;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	protected function strToLower($str) {
		if (is_callable('mb_strtolower')) {
			return mb_strtolower($str, 'UTF-8');
		}
		return strtolower($str);
	}
}

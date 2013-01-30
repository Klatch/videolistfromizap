<?php

abstract class Sclay_Embeddable_Parser {

	public $errors = array();

	/**
	 * @var callable
	 */
	public $lowercaseFunc = 'mb_strtolower';

	/**
	 * @var DOMDocument
	 */
	protected $doc = null;

	/**
	 * @param string $html
	 * @return Sclay_Embeddable_Media|bool
	 */
	public function parse($html) {
		// normalize whitespace
		$html = str_replace(array("\r\n", "\r"), "\n", $html);
		$html = trim($html);

		$libxmlErrorHandling = libxml_use_internal_errors(true);

		$this->doc = new DOMDocument();
		if ($this->doc->loadHTML("<html><meta http-equiv='content-type' "
			. "content='text/html; charset=UTF-8'><body>{$html}</body>"
			. "</html>")) {
			$ret = $this->processDoc();
		} else {
			$this->errors = libxml_get_errors();
			$ret = false;
		}

		libxml_use_internal_errors($libxmlErrorHandling);
		return $ret;
	}

	/**
	 * @param DOMNode $node
	 * @return array
	 */
	public function getAttrs(DOMNode $node) {
		$attrs = array();
		foreach ($node->attributes as $attrNode) {
			/* @var DOMAttr $attrNode */
			$name = call_user_func($this->lowercaseFunc, $attrNode->name, 'UTF-8');
			$attrs[$name] = $attrNode->value;
		}
		return $attrs;
	}
}

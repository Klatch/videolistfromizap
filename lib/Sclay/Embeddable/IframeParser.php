<?php

/**
 * Parse an IFRAME element
 */
class Sclay_Embeddable_IframeParser {

	/**
	 * @var Sclay_Embeddable_FragmentParser
	 */
	protected $parser;

	public function __construct(Sclay_Embeddable_FragmentParser $parser = null) {
		if (!$parser) {
			$parser = new Sclay_Embeddable_FragmentParser();
		}
		$this->parser = $parser;
	}

	/**
	 * @param string $html
	 * @return Sclay_Embeddable_Iframe|bool
	 */
	public function parse($html) {
		$container = $this->parser->parse($html);
		if (!$container) {
			return false;
		}
		$iframeNodes = $this->parser->getXpath()->query('//iframe[1]', $container);
		if (!$iframeNodes->length) {
			return false;
		}
		$attrs = $this->parser->extractAttributes($iframeNodes->item(0));
		return new Sclay_Embeddable_Iframe($attrs);
	}
}

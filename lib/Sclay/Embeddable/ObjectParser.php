<?php

/**
 * Parse an OBJECT element (and PARAMs/EMBED) used for Flash.
 */
class Sclay_Embeddable_ObjectParser {

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
	 * @return Sclay_Embeddable_Object|bool
	 */
	public function parse($html) {
		$container = $this->parser->parse($html);
		if (!$container) {
			return false;
		}
		$ret = array(
			'embed' => array(),
			'object' => array(),
			'params' => array(),
		);
		$xpath = $this->parser->getXpath();
		$objectNodes = $xpath->query('//object[1]', $container);
		if ($objectNodes->length) {
			$objectNode = $objectNodes->item(0);
			$ret['object'] = $this->parser->extractAttributes($objectNode);
			foreach ($xpath->query('param[@name and (@value)]', $objectNode) as $param) {
				/* @var DOMElement $param  */
				$paramAttrs = $this->parser->extractAttributes($param);

				if (is_callable('mb_strtolower')) {
					$name = mb_strtolower($paramAttrs['name'], 'UTF-8');
				} else {
					$name = strtolower($paramAttrs['name']);
				}

				$ret['params'][$name] = $paramAttrs['value'];
			}
			$embedNodes = $xpath->query('embed[1]', $objectNode);
		} else {
			$embedNodes = $xpath->query('//embed[1]');
		}
		if ($embedNodes->length) {
			$ret['embed'] = $this->parser->extractAttributes($embedNodes->item(0));
		} elseif (!$ret['object']) {
			return false;
		}
		return new Sclay_Embeddable_Object($ret['object'], $ret['embed'], $ret['params']);
	}
}

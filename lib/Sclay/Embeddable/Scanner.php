<?php

class Sclay_Embeddable_Scanner {

	protected $iframeParser;
	protected $objectParser;

	public $acceptIframe = true;
	public $acceptObject = true;

	/**
	 * @param Sclay_Embeddable_IframeParser $iframeParser
	 * @param Sclay_Embeddable_ObjectParser $objectParser
	 */
	public function __construct(Sclay_Embeddable_IframeParser $iframeParser = null, Sclay_Embeddable_ObjectParser $objectParser = null) {
		if (!$iframeParser) {
			$iframeParser = new Sclay_Embeddable_IframeParser();
		}
		if (!$objectParser) {
			$objectParser = new Sclay_Embeddable_ObjectParser();
		}
		$this->iframeParser = $iframeParser;
		$this->objectParser = $objectParser;
	}

	/**
	 * @param string $html
	 * @return Sclay_Embeddable_Media
	 */
	public function scan($html) {
		$media = false;
		if (!$media && $this->acceptObject && preg_match('~<(object|embed)\\s~i', $html)) {
			$media = $this->objectParser->parse($html);
			if (!$media->height) {
				$media = false;
			}
		}
		if (!$media && $this->acceptIframe && preg_match('~<iframe\\s~i', $html)) {
			$media = $this->iframeParser->parse($html);
			if ($media) {
				// @todo find better place for code like this
				if (0 === strpos($media->src, 'http://www.facebook.com/plugins/like.php')) {
					$media = false;
				}
			}
		}
		if (empty($media->src)) {
			return false;
		}
		return $media;
	}

	/**
	 * Example validator
	 *
	 * @param Sclay_Embeddable_Media $media
	 * @return bool
	 */
	static public function validateMedia(Sclay_Embeddable_Media $media) {
		if (!preg_match('~^https?\\://www\\.youtube(\\-nocookie)?\\.com/(v|embed)/~', $media->src)) {
			return false;
		}
		$media->width = max(50, min(600, $media->width));
		$media->height = max(50, min(600, $media->height));
		return true;
	}
}

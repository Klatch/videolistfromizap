<?php

namespace UFCOE;

/**
 * Get the path of the thumbnail saved by izap
 *
 * @param \ElggObject $o
 * @return string path of the old thumbnail, or empty string.
 */
function getOldImg(\ElggObject $o) {
	$f = new \ElggFile();
	$f->owner_guid = $o->owner_guid;
	if (empty($o->imagesrc)) {
		if (!empty($o->videotype)) {
			$user_dir = $f->getFilenameOnFilestore();
//			$glob_pattern = "izap_videos/{$o->videotype}/{$o->time_created}*.jpg";
			$glob_pattern = "videos/{$o->videotype}/{$o->time_created}*.jpg";
			foreach (glob($user_dir . $glob_pattern) as $match) {
				return $match;
			}
		}
	} else {
		$f->setFilename($o->imagesrc);
		$path = $f->getFilenameOnFilestore();
		if (is_file($path)) {
			return $path;
		}
	}
	return "";
}

/**
 * Get an unsaved file object pointing to the location where the videolist
 * thumbnail should go
 *
 * @param \ElggObject $o
 * @return \ElggFile
 */
function getNewImg(\ElggObject $o) {
	$f = new \ElggFile();
	$f->owner_guid = $o->owner_guid;
	$f->setFilename("videolist/{$o->guid}.jpg");
	return $f;
}

/**
 * Analyze
 *
 * @param \ElggObject $o
 * @return string
 */
function analyzeObject(\ElggObject $o) {
	if ($o->getSubtype() === 'videolist_item') {
		return 'already converted';
	}
	$spec = getNewSpec($o);
	$spec['videosrc'] = $o->videosrc;
	if (empty($spec['videotype'])) {
		// just dump the metadata
		$mds = elgg_get_metadata(array(
			'guid' => $o->guid,
			'limit' => 0,
		));
		$mds = array_map(function (\ElggMetadata $md) {
			return "{$md->name} = {$md->value}";
		}, $mds);
		$spec['md'] = $mds;
		$spec['guid'] = $o->guid;
		return var_export($spec, true);
	} else {
		convertObject($o, $spec);
		return "Converted to videolist!";
	}
}

/**
 * Convert an iZap video object to a videolist object based on a spec
 *
 * @param \ElggObject $o
 * @param array $spec
 */
function convertObject(\ElggObject $o, array $spec) {
	if (!empty($spec['old_img'])) {
		$newImg = getNewImg($o);
		$newImg->open('write');
		$newImg->write(file_get_contents($spec['old_img']));
		$newImg->close();
		if (!$o->thumbnail) {
			$o->thumbnail = 'yes';
		}
		unlink($spec['old_img']);
	} else {
		$o->deleteMetadata('thumbnail');
	}

	$o->video_id = $spec['video_id'];
	$o->videotype = $spec['videotype'];
	$o->description = filter_tags($o->description);
	$deletes = array(
		'videosrc', 'imagesrc', 'converted', 'filename',
		'filestore::dir_root', 'filestore::filestore',
		'views', 'video_views');
	foreach($deletes as $key) {
		$o->deleteMetadata($key);
	}
	if (!empty($spec['embed_html'])) {
		$o->embed_html = $spec['embed_html'];
	}
	if (!empty($spec['oembed_subtype'])) {
		$o->oembed_subtype = $spec['oembed_subtype'];
	}
	$o->save();

	changeObjectSubtype($o, 'videolist_item');

	if (empty($spec['old_img'])) {
		updateThumbnail($o);
	}
}

/**
 * Change the subtype of an object via DB
 *
 * @param \ElggObject $o
 * @param string $new_subtype
 */
function changeObjectSubtype(\ElggObject $o, $new_subtype) {
	$o->subtype = $new_subtype;
	// subtype not saved by save(), so manually alter DB
	$dbprefix = elgg_get_config('dbprefix');
	$subtype_id = add_subtype('object', $new_subtype);
	$guid = $o->guid;
	update_data("UPDATE {$dbprefix}entities SET subtype='$subtype_id' WHERE guid=$guid");

	// If memcache is available then delete this entry from the cache
	static $newentity_cache;
	if ($newentity_cache === null) {
		$newentity_cache = is_memcache_available()
			? new \ElggMemcache('new_entity_cache')
			: false;
	}
	if ($newentity_cache) {
		$newentity_cache->delete($guid);
	}
}

/**
 * Build a spec for converting the object
 *
 * @param \ElggObject $o
 * @return array
 */
function getNewSpec(\ElggObject $o) {
	$r = array();
	if ($img = getOldImg($o)) {
		$r['old_img'] = $img;
	}

	if (!($m = scanForUrl($o->videosrc))) {
		$m = scanForUrl($o->description);
	}
	if (!$m && preg_match('~/youtube/\\d{10}([0-9a-zA-Z\\-_]+).jpg$~', (string)$o->imagesrc, $ma)) {
		$r['videotype'] = 'youtube';
		$r['video_id'] = $ma[1];
	}
	if ($m) {
		$r = array_merge($r, $m);
	}
	return $r;
}

/**
 * Build a set of videolist metadata that can be used to display the video. Returns
 * empty array if the video cannot be converted
 *
 * @param string $src markup or URL
 * @return array
 */
function scanForUrl($src) {
	static $scanner, $renderer;
	if (null === $scanner) {
		$scanner = new \Sclay_Embeddable_Scanner();
		$renderer = new \Sclay_Embeddable_DefaultRenderer();
	}

	$r = array();
	preg_match_all('~https?\\://[^"\'>\\s]+~', $src, $m);
	foreach ($m[0] as $url) {
		$parsed_platform = videolist_parse_url($url);
		if ($parsed_platform) {
			list($parsed, $platform) = $parsed_platform;
			$r['videotype'] = $platform->getType();
			$r['video_id'] = $parsed['video_id'];
			return $r;
		}
	}
	if ($media = $scanner->scan($src)) {
		// @todo validate media and make "markup" video type
		if (validateMedia($media)) {
			$r['videotype'] = 'oembed';
			$r['embed_html'] = $renderer->render($media);
			$r['oembed_subtype'] = 'unknown';
			return $r;
		}
	}
	return array();
}

/**
 * Determine if the media object (iframe/object) should be trusted (and also
 * limit the dimensions)
 *
 * @param \Sclay_Embeddable_Media $media
 * @return bool
 */
function validateMedia(\Sclay_Embeddable_Media $media) {
	if ($media->width < 100 || $media->height < 100) {
		return false;
	}
	$media->width = min($media->width, 700);
	$media->height = min($media->height, 500);

	$roots = array(
		'http://picasaweb.google.com/',
		'http://video.ted.com/',
		'http://fora.tv/',
	);
	foreach ($roots as $root) {
		if (0 === strpos($media->src, $root)) {
			return true;
		}
	}
	return false;
}

/**
 * Fetch a thumbnail (only called if the old thumb couldn't be found)
 *
 * @param \ElggObject $o
 */
function updateThumbnail(\ElggObject $o) {
	$url = '';
	$videotype = $o->videotype;
	switch ($videotype) {
		case 'youtube':
			$url = "http://www.youtube.com/watch?v={$o->video_id}";
			break;
		case 'bliptv':
			$url = "http://blip.tv{$o->video_id}";
			break;
		case 'metacafe':
			$url = "http://www.metacafe.com/watch/{$o->video_id}";
			break;
		case 'vimeo':
			$url = "http://vimeo.com/{$o->video_id}";
			break;
		case 'oembed':
			$url = $o->video_id;
			break;
	}
	if (empty($url)) {
		return;
	}
	$success = videolist_parse_url($url);
	if ($success) {
		list($attrs, $platform) = $success;
		$attrs = $platform->getData($attrs);
		if (!empty($attrs['thumbnail'])) {
			$thumbnail = file_get_contents($attrs['thumbnail']);
			if ($thumbnail) {
				$file = getNewImg($o);
				$file->open('write');
				$file->write($thumbnail);
				$file->close();
				$o->thumbnail = $attrs['thumbnail'];
			}
		}
	}
}

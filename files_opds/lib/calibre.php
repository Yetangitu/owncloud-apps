<?php

/**
 * ownCloud - Files_Opds App
 *
 * @author Frank de Lange
 * @copyright 2014 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Files_Opds;

/**
 * Calibre metadata class for OPDS
 */
class Calibre
{
	/**
	 * @brief get Calibre-generated metadata
	 *
	 * @param string $path path to file
	 * @param arrayref $meta reference to array of metadata
	 * @return bool true if metadata found
	 */
	public static function get($path,&$meta) {
		$dir = pathinfo($path)['dirname'];
		$opf = $dir . '/metadata.opf';
		if ((file_exists($opf)) && ($package = simplexml_load_file($opf))) {
			$package->registerXPathNamespace('o', "http://www.idpf.org/2007/opf");
			if(($cover = $package->xpath('//o:reference[@type="cover"]/@href')[0]) && (file_exists($dir . '/' . $cover))) {
				$meta['cover'] = $cover;
			}
			return self::parse($package->metadata->children('dc', true),$meta);
		} else {
			return false;
		}
	}

	/**
	 * @brief parse Calibre metadata.opf into OPDS $meta array
	 *
	 * @param SimpleXMLElement $data SimpleXMLElement object containing DC/OPF metadata
	 * @param arrayref &$meta
	 * @return bool true if metadata is valid
	 */
	static function parse($data,&$meta) {
		foreach ($data as $key => $value) {
			switch ($key) {
				case 'title':
					$meta['title'] = strip_tags($value);
					break;
				case 'creator':
					if(!($author = json_decode($meta['author'],true))) {
						$author = array();
					}
					$fileAs = $value->attributes('opf',true)->{'file-as'};
					$author[(string) $fileAs] = strip_tags($value);
					$meta['author'] = json_encode($author);
					break;
				case 'description':
					$meta['description'] = strip_tags($value);
					break;
				case 'publisher':
					$meta['publisher'] = strip_tags($value);
					break;
				case 'identifier':
					if('ISBN' == $value->attributes('opf',true)->scheme) {
						$meta['isbn'] = strip_tags($value);
					}
					break;
				case 'subject':
					if(!($subject = json_decode($meta['subjects'],true))) {
                                                $subject = array();
                                        }
					array_push($subject,strip_tags($value));
					$meta['subjects'] = json_encode($subject);
					break;
				case 'date':
					$meta['date'] = strip_tags($value);
					break;
				case 'language':
					$meta['language'] = strip_tags($value);
					break;
			}
		}

		return Meta::isValid($meta);
	}
}

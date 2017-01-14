<?php

/**
 * Nextcloud - Files_Opds App
 *
 * @author Frank de Lange
 * @copyright 2016 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Files_Opds;

/**
 * FB2 class, a simpleminded read-only fb2 parser intended for metadata extraction
 */
class FB2 {
	protected $file;

	/**
	 * @brief Constructor
	 *
	 * @param string $file path to fb2 file to work on
	 * @throws Exception if metadata could not be loaded
	 */
	public function __construct($file) {
		// open file
		$this->file = $file;
		if (!($this->xml = simplexml_load_file($file))) {
			throw new \Exception("Failed to read FB2 file");
		}

		// check for valid XML content 
		if(!$this->xml) {
			throw new \Exception("Failed to access XML content");
		}
	}

	/**
	 * @brief file name getter
	 * @return string filename
	 */
	public function file() {
		return $this->file;
	}

	/**
	 * @brief get author(s)
	 *
	 * @return array $authors
	 */
	public function Authors() {
		$authors = array();
		foreach($this->xml->description->{'title-info'}->author as $author) {
			$authors[] = (string) implode(' ', array_filter(array ($author->{'first-name'} , $author->{'middle-name'} , $author->{'last-name'}, $author->{'nickname'}),function($v){ return (!empty((string) $v));}));
		}
		return $authors;
	}

	/**
	 * @brief get book title
	 *
	 * @return string $title
	 */
	public function Title(){
		return $this->get_string($this->xml->description->{'title-info'}->{'book-title'});
	}

	/**
	 * @brief get language
	 *
	 * @return string $lang
	 */
	public function Language(){
		return $this->get_string($this->xml->description->{'title-info'}->{'lang'});
	}

	/**
	 * @brief get date
	 *
	 * @return string $date
	 */
	public function Date(){
		return $this->get_string($this->xml->description->{'document-info'}->{'date'});
	}

	/**
	 * @brief get publisher info
	 *
	 * @return string $publisher
	 */
	public function Publisher(){
		return $this->get_string($this->xml->description->{'publish-info'}->{'publisher'});
	}

	/**
	 * @brief get copyright info
	 *
	 * @return string $rights
	 */
	public function Copyright(){
		/* no copyright info in fb2 files */
		return '';
	}

	/**
	 * @brief get description
	 *
	 * @return string $description
	 */
	public function Description(){
		$description = null;
		if (isset($this->xml->description->{'title-info'}->{'annotation'})) {
			$description = $this->get_innerxml($this->xml->description->{'title-info'}->{'annotation'});
		}

		return $description;
	}

	/**
	 * @brief get ISBN number
	 *
	 * @return string $isbn
	 */
	public function ISBN(){
		return $this->get_string($this->xml->description->{'publish-info'}->{'isbn'});
	}

	/**
	 * @brief get subjects (aka. tags)
	 *
	 * @return array $subjects
	 */
	public function Subjects(){
		$subjects = array();
		foreach($this->xml->description->{'title-info'}->keywords as $keyword) {
			$subjects[] =  $keyword->__toString();
		}
		return $subjects;
	}

	/**
	 * @brief get cover data
	 *
	 * Returns an associative array with the following keys:
	 *
	 *   mime  - filetype (usually image/jpeg)
	 *   data  - binary image data
	 *   found - internal path, or null if no image is set in fb2
	 * 
	 * @return array or null
	 */
	public function Cover(){
		$mime = "";
		$data = null;
		$path = false;

		if ($this->xml->description->{'title-info'}->coverpage) {
			$cover_link = isset($this->xml->description->{'title-info'}->coverpage->image->attributes('xlink',true)->href)
				?  $this->xml->description->{'title-info'}->coverpage->image->attributes('xlink',true)->href->__toString() : "" ;
			$cover_link = isset($this->xml->description->{'title-info'}->coverpage->image->attributes('l',true)->href)
				?  $this->xml->description->{'title-info'}->coverpage->image->attributes('l',true)->href->__toString() : $cover_link ;

			$cover_link = trim($cover_link,'#');

			foreach ($this->xml->binary as $binary) {
				if ($binary['id']->__toString() === $cover_link) {
					$data = base64_decode($binary->__toString());
					$mime = $binary['content-type']->__toString();
					$path = $cover_link;
					break;
				}
			}
		}

		return (!is_null($data))
			? array(
				'mime'  => $mime,
				'data'  => $data,
				'found' => $path
			)
			: null;
	}

	/**
	 * @brief get innerXML as string
	 * 
	 * Returns the innerXML for an XML element as string
	 *
	 * @return string
	 */
	protected function get_innerxml($element) {
		$innerxml = '';
		foreach (dom_import_simplexml($element)->childNodes as $child) {
			$innerxml .= $child->ownerDocument->saveXML($child);
		}
	
		return $innerxml;
	}

	/**
	 * @brief get element as string
	 * 
	 * Returns simplexml element as string, can cope with null elements
	 * 
	 * @return string or null
	 */
	protected function get_string($element) {
		if (!empty($element)) {
			return $element->__toString();
		} else {
			return '';
		}
	}
}


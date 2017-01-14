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

use \DOMXpath;
use \DOMElement;
use \DOMDocument;

/**
 * Epub class, a simpleminded read-only epub parser intended for metadata extraction
 * based on https://github.com/splitbrain/php-epub-meta
 */
class Epub {
	public $xml;
	protected $xpath;
	protected $file;
	protected $meta;

	/**
	 * @brief Constructor
	 *
	 * @param string $file path to epub file to work on
	 * @throws Exception if metadata could not be loaded
	 */
	public function __construct($file) {
		// open file
		$this->file = $file;
		$zip = new \ZipArchive();
		if(!($zip->open($this->file))){
			throw new \Exception("Failed to read epub file");
		}

		// read container data
		if(!($data = $zip->getFromName('META-INF/container.xml'))) {
			throw new \Exception("Failed to access epub container data");
		}

		$xml = new DOMDocument();
		$xml->registerNodeClass('DOMElement','\OCA\Files_Opds\EPubDOMElement');
		$xml->loadXML($data);
		$xpath = new EPubDOMXPath($xml);
		$nodes = $xpath->query('//n:rootfiles/n:rootfile[@media-type="application/oebps-package+xml"]');
		$this->meta = $nodes->item(0)->attr('full-path');

		// load metadata
		if(!($data = $zip->getFromName($this->meta))) {
			throw new \Exception("Failed to access epub metadata");
		}

		$this->xml =  new \DOMDocument();
		$this->xml->registerNodeClass('DOMElement','\OCA\Files_Opds\EPubDOMElement');
		$this->xml->loadXML($data);
		$this->xml->formatOutput = true;
		if(!($this->xpath = new EPubDOMXPath($this->xml))) {
			throw new \Exception("Failed to instantiate xpath");
		}

		$zip->close();
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
		// read current data
		$rolefix = false;
		$authors = array();
		$nodes = $this->xpath->query('//opf:metadata/dc:creator[@opf:role="aut"]');
		if($nodes->length == 0){
			// no nodes where found, let's try again without role
			$nodes = $this->xpath->query('//opf:metadata/dc:creator');
			$rolefix = true;
		}
		foreach($nodes as $node){
			$name = $node->nodeValue;
			$as   = $node->attr('opf:file-as');
			if(!$as){
				$as = $name;
				$node->attr('opf:file-as',$as);
			}
			if($rolefix){
				$node->attr('opf:role','aut');
			}
			$authors[$as] = $name;
		}
		return $authors;
	}

	/**
	 * @brief get book title
	 *
	 * @return string $title
	 */
	public function Title(){
		return $this->get('dc:title');
	}

	/**
	 * @brief get language
	 *
	 * @return string $lang
	 */
	public function Language(){
		return $this->get('dc:language');
	}

	/**
	 * @brief get date
	 *
	 * @return string $date
	 */
	public function Date(){
		return $this->get('dc:date');
	}

	/**
	 * @brief get publisher info
	 *
	 * @return string $publisher
	 */
	public function Publisher(){
		return $this->get('dc:publisher');
	}

	/**
	 * @brief get copyright info
	 *
	 * @return string $rights
	 */
	public function Copyright(){
		return $this->get('dc:rights');
	}

	/**
	 * @brief get description
	 *
	 * @return string $description
	 */
	public function Description(){
		return $this->get('dc:description');
	}

	/**
	 * @brief get ISBN number
	 *
	 * @return string $isbn
	 */
	public function ISBN(){
		return $this->get('dc:identifier','opf:scheme','ISBN');
	}

	/**
	 * @brief get Google Books ID
	 *
	 * @return string $google
	 */
	public function Google(){
		return $this->get('dc:identifier','opf:scheme','GOOGLE');
	}

	/**
	 * @brief get Amazon ID
	 *
	 * @return string $amazon
	 */
	public function Amazon(){
		return $this->get('dc:identifier','opf:scheme','AMAZON');
	}

	/**
	 * @brief get subjects (aka. tags)
	 *
	 * @return array $subjects
	 */
	public function Subjects(){
		$subjects = array();
		$nodes = $this->xpath->query('//opf:metadata/dc:subject');
		foreach($nodes as $node){
			$subjects[] =  $node->nodeValue;
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
	 *   found - internal path, or false if no image is set in epub
	 * 
	 * @return array or null
	 */
	public function Cover(){
		$nodes = $this->xpath->query('//opf:metadata/opf:meta[@name="cover"]');
		if($nodes->length) {
			$coverid = (String) $nodes->item(0)->attr('opf:content');
			if ($coverid) {
				$nodes = $this->xpath->query('//opf:manifest/opf:item[@id="'.$coverid.'"]');
				if ($nodes->length) {
					$mime = $nodes->item(0)->attr('opf:media-type');
					$path = $nodes->item(0)->attr('opf:href');
					$path = dirname('/'.$this->meta).'/'.$path; // image path is relative to meta file
					$path = ltrim($path,'/');
					$zip = new \ZipArchive();
					if($zip->open($this->file)){
						$data = $zip->getFromName($path);
						return array(
							'mime'  => $mime,
							'data'  => $data,
							'found' => $path
						);
					}
				}
			}
		} else {
			return null;
		}
	}

	/**
	 * @brief simple getter for simple meta attributes
	 *
	 * It should only be used for attributes that are expected to be unique
	 *
	 * @param string $item   XML node to get
	 * @param string $att    Attribute name
	 * @param string $aval   Attribute value
	 * @return string node value
	 */
	protected function get($item, $att=false, $aval=false){
		$xpath = '//opf:metadata/'.$item;
		if ($att) {
			$xpath .= "[@$att=\"$aval\"]";
		}

		$nodes = $this->xpath->query($xpath);
		if($nodes->length){
			return $nodes->item(0)->nodeValue;
		}else{
			return '';
		}
	}
}

class EPubDOMXPath extends DOMXPath {
        public function __construct(DOMDocument $doc){
                parent::__construct($doc);

                if(is_a($doc->documentElement, '\OCA\Files_Opds\EPubDOMElement')){
                        foreach($doc->documentElement->namespaces as $ns => $url){
                                $this->registerNamespace($ns,$url);
                        }
                }
        }
}

class EPubDOMElement extends DOMElement {
        public $namespaces = array(
                        'n'   => 'urn:oasis:names:tc:opendocument:xmlns:container',
                        'opf' => 'http://www.idpf.org/2007/opf',
                        'dc'  => 'http://purl.org/dc/elements/1.1/'
                        );


        public function __construct($name, $value='', $namespaceURI=''){
                list($ns,$name) = $this->splitns($name);
                $value = htmlspecialchars($value);
                if(!$namespaceURI && $ns){
                        $namespaceURI = $this->namespaces[$ns];
                }
                parent::__construct($name, $value, $namespaceURI);
        }

        /**
         * @brief split given name in namespace prefix and local part
         *
         * @param  string $name
         * @return array  (namespace, name)
         */
        public function splitns($name){
                $list = explode(':',$name,2);
                if(count($list) < 2) array_unshift($list,'');
                return $list;
        }

        /**
         * @brief simple EPub namespace aware attribute getter
	 *
	 * @param string attribute
	 * @return string attribute value
         */
        public function attr($attr){
                list($ns,$attr) = $this->splitns($attr);

                $nsuri = '';
                if($ns){
                        $nsuri = $this->namespaces[$ns];
                        if(!$this->namespaceURI){
                                if($this->isDefaultNamespace($nsuri)){
                                        $nsuri = '';
                                }
                        }elseif($this->namespaceURI == $nsuri){
                                $nsuri = '';
                        }
                }

		if($nsuri){
			return $this->getAttributeNS($nsuri,$attr);
		}else{
			return $this->getAttribute($attr);
		}
        }
}




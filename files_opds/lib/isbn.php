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
 * ISBN class for OPDS
 */
class Isbn
{
	const SUCCESS = 0;
	const REQUEST_LIMIT_EXCEEDED = -1;
	const NOT_FOUND = -2;
	const ERROR = -3;
	const RESCAN_LIMIT_EXCEEDED = 86400;
	const RESCAN_NOT_FOUND = 604800;
	const RESCAN_ERROR = 86400;

	/**
	 * @brief try to find a valid ISBN in the given text, using a cascade of
	 *         regexps. 
	 *
	 * @param string $text text to search through
	 * @return string $hit ISBN on success, false otherwise
	 */
	public static function scan($text) {
		$match = array();
		foreach($text as $line) {
			/* generic ISBN 10/13 pattern. Checks for unicode dashes ('‒–—―‑‐﹣－-') as well as regular hyphens. */
			if(preg_match_all('/ISBN(?:[ ‒–—―‑‐﹣－-]?(?:1[03])?)?:?\s*(?=[\d Xx‒–—―‑‐﹣－-]{10,17})(((?:97[89])[\d ‒–—―‑‐﹣－-]{9,14})|([\d ‒–—―‑‐﹣－-]{9,12}[\dXx]))/u', $line, $match)) {
				foreach($match[1] as $hit) {
					$hit = preg_replace('/[^0-9X]/i','',$hit);
					if(self::validate($hit)) {
						return $hit;
					}
				}
			}
		}

		/* If nothing found, try prefix-less versions. Even though ISBN numbers should be
		 * presented with a 'ISBN' prefix, some publications omit this. These patterns
		 * are liable to generate false positives, so they should only be run after the
		 * prefixed version has exhausted the search without returning results.
		 */

		foreach($text as $line) {
			/* prefix-less ISBN-13 targeted pattern */
			if(preg_match_all('/(97[89][\d ‒–—―‑‐﹣－-]\d{9,13}\d)/u',$line,$match)) {
				foreach($match[1] as $hit) {
                                	$hit = preg_replace('/[^0-9]/','',$hit);
                         	        if(self::validate($hit)) {
                                	        return $hit;
                          	      }
				}
                	}

			/* single ISBN-10 targeted pattern */
			if(preg_match_all('/(\d[\d ‒–—―‑‐﹣－-]{8,11}[\dXx])/u',$line,$match)) {
				foreach($match[1] as $hit) {
					$hit = preg_replace('/[^0-9X]/i','',$hit);
					if(self::validate($hit)) {
						return $hit;
					}
				}
			}
		}

		/* No ISBN found */
		return false;
	}

	/**
	 * @brief get metadata for given ISBN
	 *
	 * @param string $isbn ISBN to use
	 * @param arrayref &$meta OPDS metadata
	 * @return bool $success (true if metadata found)
	 */
        public static function get($isbn,&$meta) {
		/* set ISBN in metadata; can be overwritten later with ISBN13 */
		$meta['isbn'] = $isbn;
		/* Try ISBNdb, then Google */
		if (!(Isbn::SUCCESS == Isbndb::get($isbn,$meta)) && (!(Isbn::SUCCESS == Google::get($isbn,$meta)))) {
			return false;
		} else {
			return true;
		}
        }

	/**
	 * @brief validate ISBN
	 *
	 * @param string $isbn ISBN to validate
	 * @return bool true if ISBN is valid
	 */
        public static function validate($isbn) {
                if (null === $isbn || '' === $isbn) {
                        return false;
                }

                switch (strlen($isbn)) {
                        case 10:
                                return self::isIsbn10($isbn);
                                break;
                        case 13:
                                return self::isIsbn13($isbn);
                                break;
                }
		return false;
        }

	/**
	 * @brief check for valid ISBN10
	 * @param string $isbn ISBN to check
	 * @return bool true if valid ISBN-10
	 */
        static function isIsbn10 ($isbn) {
		$len = strlen($isbn);
		if ($len == 10) {
			//Calculate check digit
			$check = 0;
			for ($i = 0; $i < 9; $i++) {
				if ($isbn[$i] === "X") {
					$check += 10 * intval(10 - $i);
				} else {
					$check += intval($isbn[$i]) * intval(10 - $i);
				}
			}
			$check = 11 - $check % 11;
			if ($check === 10) {
				$check = 'X';
			} elseif ($check === 11) {
				$check = 0;
			}

			return ($check == $isbn{$len - 1});
		}

		return false;
        }

	/**
	 * @brief check for valid ISBN13
	 * @param string $isbn ISBN to check
	 * @return bool true if valid ISBN-13
	 */
        static function isIsbn13 ($isbn) {
		$len = strlen($isbn);
		if ($len == 13) {
			//Calculate check digit
			$check = 0;
			for ($i = 0; $i < 12; $i += 2) {
				$check += substr($isbn, $i, 1);
			}
			for ($i = 1; $i < 12; $i += 2) {
				$check += 3 * substr($isbn, $i, 1);
			}
			$check = 10 - $check % 10;
			if ($check === 10) {
				$check = 0;
			}

			return ($check == $isbn{$len - 1});
		}

		return false;
        }
}



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
	 *         regexps. Can be optimized.
	 *
	 * @param string $text text to search through
	 * @return string $hit ISBN on success, false otherwise
	 */
	public static function scan($text) {
		if ($hits = preg_grep('/isbn/i',$text)) {
			foreach ($hits as $hit) {
				$hit = preg_replace('/.*ISBN(?:[ -]?1[03]:)?\s*([xX0-9-]{10,17}).*/i','$1',$hit,1);
				//$hit = preg_replace('/isbn([ -]\(?1[03]\)?)?/i','',$hit);
				$hit = preg_replace('/[^0-9X]/i','',$hit);
				if(self::validate($hit)) {
					return $hit;
				}
			}
		}

		/* single ISBN-13 targeted pattern */
		if ($hits = preg_grep('/\d{3}[ -]?\d[ -]?\d{4}[ -]?\d{4}[ -]?\d/', $text)) {
			foreach ($hits as $hit) {
				$hit = preg_replace('/.*(\d{3}[ -]?\d[ -]?\d{4}[ -]?\d{4}[ -]?\d).*/','$1',$hit,1);
				$hit = preg_replace('/[^0-9]/i','',$hit);
				if(self::validate($hit)) {
					return $hit;
				}
			}
		}

		/* single ISBN-10 targeted pattern */
		if ($hits = preg_grep('/\d[\d -]{8,11}[\dX]/i', $text)) {
			foreach ($hits as $hit) {
				$hit = preg_replace('/.*(\d[\d -]{8,11}[\dX]).*/','$1',$hit,1);
				$hit = preg_replace('/[^0-9X]/i','',$hit);
				if(self::validate($hit)) {
					return $hit;
				}
			}
		}
	
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
                $checksum = 0;
                for ($i = 0; $i < 10; ++$i) {
                        if (!isset($isbn{$i})) {
                                return false;
                        }
                        if ('X' === $isbn{$i}) {
                                $digit = 10;
                        } elseif (ctype_digit($isbn{$i})) {
                                $digit = $isbn{$i};
                        } else {
                                return false;
                        }
                        $checksum += $digit * intval(10 - $i);
                }

                return 0 === $checkSum % 11 ? true : false;
        }

	/**
	 * @brief check for valid ISBN13
	 * @param string $isbn ISBN to check
	 * @return bool true if valid ISBN-13
	 */
        static function isIsbn13 ($isbn) {
                $checksum = 0;
                for ($i = 0; $i < 13; $i +=2) {
                        $checksum += $isbn{$i};
                }
                for ($i = 1; $i < 12; $i +=2) {
                        $checksum += $isbn{$i} * 3;
                }

                return 0 === $checkSum % 10 ? true : false;
        }
}



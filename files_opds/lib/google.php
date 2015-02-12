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
 * Google class for OPDS
 */
class Google
{
        /**
         * @brief get ISBN data for $isbn at Google (books)
         *
         * @param string $isbn ISBN to search for
         * @param arrayref &$meta
         * @return int $status (0 on success, ERRORCODE otherwise)
         */
        public static function get($isbn,&$meta) {
               	$command = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . $isbn;
		if ($keyString = Config::getApp('google-key','')) {
			$keys = explode(',', $keyString);
			$key = $keys[rand(0,count($keys) - 1)];
			$command .= '&key=' . $key;
		}
                $data = json_decode(file_get_contents($command),true);
                if($data['totalItems'] > 0) {
			self::parse($data['items'][0]['volumeInfo'],$meta);
                        return true;
                } else {
			$meta['rescan'] = date("Y-m-d\TH:i:sP", time() + Isbn::RESCAN_NOT_FOUND);
                	return Isbn::NOT_FOUND;
		}

		/* not reached */
                return Isbn::ERROR;
        }

        /**
         * @brief parse Google response into OPDS $meta array
         *
         * @param array $data Google response (json_decoded into array)
         * @param arrayref &$meta OPDS metadata array
         * @return int errorcode (0 if success)
         */
        static function parse($data,&$meta) {
                foreach ($data as $key => $value) {
                        switch ($key) {
                                case 'description':
                                        $meta['description'] = $value;
                                        if(array_key_exists('notes',$data)) {
                                                $meta['description'] .= ((trim($value) == false) ? '' : "\n\n") . $data['notes'];
                                        }
                                        break;
                                case 'subject_ids':
                                        $meta['subjects'] = json_encode($value);
                                        break;
				/* rather pointless, ISBN is what brought us here in the first place and is alread set
                                case 'industryIdentifiers':
					foreach($value as $array) {
						if ($array['type'] = 'ISBN_13') {
							$isbn13 = $array['identifier'];
						} elseif ($array['type'] = 'ISBN_10') {
							$isbn10 = $array['identifier'];
						}
					}
							
                                        $meta['isbn'] = (isset($isbn13)) ? $isbn13 : $isbn10;
                                        break;
				*/
                                case 'title':
                                        $meta['title'] = $value;
                                        break;
                                case 'authors':
                                        $meta['author'] = json_encode($value);
                                        break;
                                case 'language':
                                        $meta['language'] = $value;
                                        break;
                                case 'publisher':
                                        $meta['publisher'] = $value;
                                        break;
				case 'publishedDate':
					$meta['date'] = $value;
					break;
                        }
                }

                return true;
        }
}

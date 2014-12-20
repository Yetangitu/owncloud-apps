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
 * ISBNdb class for OPDS
 */
class Isbndb
{
        /**
         * @brief get ISBN data for $isbn at ISBNdb
         *
         * @param string $isbn ISBN to search for
         * @param arrayref &$meta
         * @return int $status (0 on success, ERRORCODE otherwise)
         */
        public static function get($isbn,&$meta) {
                if ($keyString = Config::getApp('isbndb-key','')) {
                        $keys = explode(',', $keyString);
                        $key = $keys[rand(0,count($keys) - 1)];
                        $data = false;
                        $isbn = preg_replace('/[^0-9X]/i', '', $isbn);
                        if (Isbn::validate($isbn)) {
                                $command = 'http://isbndb.com/api/v2/json/' . $key . '/book/' . $isbn;
                                $data = json_decode(file_get_contents($command),true);
                                if (isset($data['error'])) {
                                        Util::logWarn("ISBNDB: " . $data['error']);
                                        if (!(stripos($data['error'], 'Daily request limit exceeded') === false)) {
                                                return Isbn::REQUEST_LIMIT_EXCEEDED;
                                        } elseif(!(stripos($data['error'], 'Unable to locate') === false))  {
                                                return Isbn::NOT_FOUND;
                                        } else {
                                                return Isbn::ERROR;
                                        }
                                } else {
                                        self::parse($data['data'][0],$meta);
                                        return Isbn::SUCCESS;
                                }
                        }
                }

                return false;
        }

        /**
         * @brief parse ISBNdb response into OPDS $meta array
         *
         * @param array $data ISBNdb response (json_decoded into array)
         * @param arrayref &$meta OPDS metadata array
         * @return int errorcode (0 if success)
         */
        static function parse($data,&$meta) {
                /* did the call succeed? If not, schedule a rescan */
                if (Isbn::REQUEST_LIMIT_EXCEEDED == $data) {
                        $meta['rescan'] = date("Y-m-d\TH:i:sP", time() + Isbn::RESCAN_LIMIT_EXCEEDED);
                        return false;
                } elseif (Isbn::NOT_FOUND == $data) {
                        $meta['rescan'] = date("Y-m-d\TH:i:sP", time() + Isbn::RESCAN_NOT_FOUND);
                        return false;
                } elseif (Isbn::ERROR == $data) {
                        $meta['rescan'] = date("Y-m-d\TH:i:sP", time() + Isbn::RESCAN_ERROR);
                        return false;
                }

                foreach ($data as $key => $value) {
                        switch ($key) {
                                case 'summary':
                                        $meta['description'] = $value;
                                        if(array_key_exists('notes',$data)) {
                                                $meta['description'] .= ((trim($value) == false) ? '' : "\n\n") . $data['notes'];
                                        }
                                        break;
                                case 'subject_ids':
                                        $meta['subjects'] = json_encode($value);
                                        break;
                                case 'isbn10':
                                        if(!(array_key_exists('isbn13', $data))) {
                                                $meta['isbn'] = $value;
                                        }
                                        break;
                                case 'isbn13':
                                        $meta['isbn'] = $value;
                                        break;
                                case 'title':
                                        if(!(array_key_exists('title_long',$data))) {
                                                $meta['title'] = $value;
                                        }
                                        break;
                                case 'title_long':
                                        $meta['title'] = $value;
                                        break;
                                case 'author_data':
                                        $meta['author'] = json_encode(array_column($value, 'name','id'));
                                        break;
                                case 'language':
                                        $meta['language'] = $value;
                                        break;
                               case 'publisher_name':
                                        $meta['publisher'] = $value;
                                        if(array_key_exists('publisher_text',$data)) {
                                                $meta['publisher'] .= ((trim($value) == false) ? '' : ', ') . $data['publisher_text'];
                                        }
                                        break;
                        }
                }

                return true;
        }
}

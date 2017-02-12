<?php

/**
 * ownCloud - Files_Reader App
 *
 * @author Frank de Lange
 * @copyright 2015 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

return ['routes' => [
    // Page
    ['name' => 'page#showReader', 'url' => '/', 'verb' => 'GET'],

    // Bookmarks
    ['name' => 'bookmark#get_cursor', 'url' => '/position/cursor/{fileId}', 'verb' => 'GET'],
    ['name' => 'bookmark#set_cursor', 'url' => '/position/cursor/{fileId}/{value}', 'verb' => 'POST'],
    ['name' => 'bookmark#get', 'url' => '/position/{fileId}/{name}', 'verb' => 'GET', 'defaults' => ['name' => '']],
    ['name' => 'bookmark#set', 'url' => '/position/{fileId}/{name}/{value}', 'verb' => 'POST'],

    // Metadata
    ['name' => 'metadata#get', 'url' => '/metadata/{fileId}/{name}', 'verb' => 'GET', 'defaults' => ['name' => '']],
    ['name' => 'metadata#set', 'url' => '/metadata/{fileId}/{name}/{value}', 'verb' => 'POST'],

    // Preferences
    ['name' => 'preference#get_default', 'url' => '/preference/default/{scope}/{name}', 'verb' => 'GET', 'defaults' => ['name' => '']],
    ['name' => 'preference#set_default', 'url' => '/preference/default/{scope}/{name}/{value}', 'verb' => 'POST'],
    ['name' => 'preference#get', 'url' => '/preference/{fileId}/{scope}/{name}', 'verb' => 'GET', 'defaults' => ['name' => '']],
    ['name' => 'preference#set', 'url' => '/preference/{fileId}/{scope}/{name}/{value}', 'verb' => 'POST'],
]];


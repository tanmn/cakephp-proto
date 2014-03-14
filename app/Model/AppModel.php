<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
    public $validationDomain = 'Model';
    public $cacheQueries = true;
    public $recursive = -1;

    // apply caching for find
    public function find($type = 'first', $query = array()) {
        $doQuery = true;

        //check if we want the cache
        if (!empty($query['cache'])) {
            $cacheConfig = 'default';
            $cacheName = $this->name;

            //check if we have specified a custom config, e.g. different expiry time
            if (!empty($query['cacheConfig'])) {
                $cacheConfig = $query['cacheConfig'];
            }

            if (is_string($query['cache']))
                $cacheName .= $query['cache'];

            //if so, check if the cache exists
            if (($data = Cache::read($cacheName, $cacheConfig)) === false) {
                $data = parent::find($type, $query);
                Cache::write($cacheName, $data, $cacheConfig);
            }

            $doQuery = false;
        }

        if ($doQuery) {
            $data = parent::find($type, $query);
        }

        return $data;
    }
}

<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package     app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class ApiController extends Controller {
    public $components = array();
    public $uses = array();

    public $output = null;
    public $errors = null;

    public $paginate = array('limit' => 10);

    public function beforeFilter($options = array()) {
        if (function_exists('ob_start'))
            ob_start();

        // no need to call parent init
        // parent::beforeFilter($options);

        //locale
        if (!empty($this->request->query['locale'])) {
            $locale = $this->request->query['locale'];

            Configure::write('Config.language', $locale);
        }

        // merge variables
        $this->request->data = Set::merge($this->request->data, $this->request->query);
    }

    public function beforeRender() {
        if (function_exists('ob_clean'))
            ob_clean();

        header('Content-type: text/json; charset=utf-8');

        if (!empty($this->errors)) {
            $message = $this->errors;

            if (is_array($this->errors)) {
                $errors = Set::flatten($this->errors);
                $message = array_shift($errors);
            } else {
                $message = $this->errors;
            }

            $this->output = array(
                'error' => true,
                'message' => $message
            );
        }

        // append sql logs
        if (Configure::read('debug')) {
            if (empty($this->errors)) {
                $data = $this->output;
                $this->output = array();
                $this->output['data'] = $data;
            }

            App::uses('ConnectionManager', 'Model');
            $sources = ConnectionManager::sourceList();

            $this->output['logs'] = array();
            foreach ($sources as $source){
                $db = ConnectionManager::getDataSource($source);

                if (!method_exists($db, 'getLog'))
                    continue;

                $this->output['logs'][$source] = $db->getLog(false, false);
            }
        }

        // paging
        if (!empty($this->request->params['paging'])) {
            if (!Configure::read('debug')) {
                $data = $this->output;
                $this->output = array();
                $this->output['data'] = $data;
            }

            $paging = array_shift($this->request->params['paging']);
            unset($paging['order']);
            unset($paging['options']);
            unset($paging['paramType']);
            $this->output['paging'] = $paging;
        }

        echo json_encode($this->output);

        if (function_exists('ob_end_flush'))
            ob_end_flush();

        exit;
    }


    public function index(){
        $this->output = 'Hello API';
    }
}
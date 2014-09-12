<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */

class Log_action_json{

    private $logAction;

    public function __construct(){
        $this->logAction = array (
                                    'idaction'      =>  '',
                                    'name'          =>  '',
                                    'hash'          =>  '',
                                    'type'          =>  '',
                                    'url_prefix'    =>  ''
                                 );
    }

    public function getLogAction(){
        return $this->logAction;
    }

    public function setLogAction($data){
        $logAction = $this->logAction;
        $logAction['idaction']      =  $data['idaction'];
        $logAction['name']          =  $data['name'];
        $logAction['hash']          =  $data['hash'];
        $logAction['type']          =  $data['type'];
        $logAction['url_prefix']    =  $data['url_prefix'];
        $this->logAction = $logAction;
    }
}
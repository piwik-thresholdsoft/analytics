<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */

class Log_link_visit_json{

    private $logLinkVisit;

    public function __construct($data){
        $this->logLinkVisit = array (
                                        'idlink_va'                 =>   '',
                                        'idsite'                    =>   $data['idsite'],
                                        'idvisitor'                 =>   '',
                                        'idvisit'                   =>   '',
                                        'idaction_url_ref'          =>   '',//TODO $data['idaction'],
                                        'idaction_name_ref'         =>   '',//TODO $data['type'],
                                        'custom_float'              =>   '',//TODO $data['custom_float'],
                                        'example_action_dimension'  =>   '',//TODO $data['example_action_dimension'],
                                        'idaction_name'             =>   '',
                                        'idaction_url'              =>   '',
                                        'server_time'               =>   '',
                                        'time_spent_ref_action'     =>   '',//TODO
                                        'idaction_event_action'     =>   '', //TODO
                                        'idaction_event_category'   =>   '', //TODO
                                        'custom_var_k1'             =>   '', //TODO
                                        'custom_var_v1'             =>   '', //TODO
                                        'custom_var_k2'             =>   '', //TODO
                                        'custom_var_v2'             =>   '', //TODO
                                        'custom_var_k3'             =>   '', //TODO
                                        'custom_var_v3'             =>   '', //TODO
                                        'custom_var_k4'             =>   '', //TODO
                                        'custom_var_v4'             =>   '', //TODO
                                        'custom_var_k5'             =>   '', //TODO
                                        'custom_var_v5'             =>   '', //TODO
                                    );

    }

    public function getLogLinkVisit(){
        return $this->logLinkVisit;
    }

    public function setLogLinkVisit($data){

        $idLink_a = time();
        //print_r($data);
        $logLinkVisit = $this->logLinkVisit;

        $logLinkVisit['idlink_va']                 =   $data['idlink_va'];
        $logLinkVisit['idsite']                    =   $data['idsite'];
        $logLinkVisit['idvisitor']                 =   $data['idvisitor'];
        $logLinkVisit['idvisit']                   =   $data['idvisit'];
        $logLinkVisit['idaction_url_ref']          =   $data['idaction_url_ref'];//TODO $data['idaction'];
        $logLinkVisit['idaction_name_ref']         =   $data['idaction_name_ref'];//TODO $data['type'];
        $logLinkVisit['custom_float']              =   $data['custom_float'];//TODO $data['custom_float'];
        $logLinkVisit['example_action_dimension']  =   $data['example_action_dimension'];//TODO $data['example_action_dimension'];
        $logLinkVisit['idaction_name']             =   $data['idaction_name']; //------
        $logLinkVisit['idaction_url']              =   $data['idaction_url']; //------
        $logLinkVisit['server_time']               =   $data['server_time'];
        $logLinkVisit['time_spent_ref_action']     =   $data['time_spent_ref_action'];//TODO
        $logLinkVisit['idaction_event_action']     =   $data['idaction_event_action']; //TODO
        $logLinkVisit['idaction_event_category']   =   $data['idaction_event_category']; //TODO
        $logLinkVisit['custom_var_k1']             =   $data['custom_var_k1']; //TODO
        $logLinkVisit['custom_var_v1']             =   $data['custom_var_v1']; //TODO
        $logLinkVisit['custom_var_k2']             =   $data['custom_var_k2']; //TODO
        $logLinkVisit['custom_var_v2']             =   $data['custom_var_v2']; //TODO
        $logLinkVisit['custom_var_k3']             =   $data['custom_var_k3']; //TODO
        $logLinkVisit['custom_var_v3']             =   $data['custom_var_v3']; //TODO
        $logLinkVisit['custom_var_k4']             =   $data['custom_var_k4']; //TODO
        $logLinkVisit['custom_var_v4']             =   $data['custom_var_v4']; //TODO
        $logLinkVisit['custom_var_k5']             =   $data['custom_var_k5']; //TODO
        $logLinkVisit['custom_var_v5']             =   $data['custom_var_v5']; //TODO
        $this->logLinkVisit = $logLinkVisit;
    }
}
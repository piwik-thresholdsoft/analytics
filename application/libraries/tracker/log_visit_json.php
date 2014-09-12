<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 *
 * This is log_visit collection format
 */
class Log_visit_json{

    private $logVisit;

    public function __construct($data){
        $this->logVisit = array (
                                    'idvisit'                   => '',
                                    'idsite'                    => $data['idsite'],
                                    'idvisitor'                 => '',
                                    'visit_last_action_time'    => '',
                                    'config_id'                 => '',
                                    'location_ip'               => '',
                                    'config_resolution'         => '',
                                    'config_device_brand'       => '',
                                    'config_device_model'       => '',
                                    'config_windowsmedia'       => '',
                                    'config_silverlight'        => '',
                                    'config_java'               => '',
                                    'config_gears'              => '',
                                    'config_pdf'                => '',
                                    'config_quicktime'          => '',
                                    'config_realplayer'         => '',
                                    'config_device_type'        => '',
                                    'visitor_localtime'         => '',
                                    'location_region'           => '',
                                    'visitor_days_since_last'   => '',
                                    'location_longitude'        => '',
                                    'visit_total_events'        => '',
                                    'config_os_version'         => '',
                                    'location_city'             => '',
                                    'location_country'          => '',
                                    'location_latitude'         => '',
                                    'config_flash'              => '',
                                    'config_director'           => '',
                                    'visit_total_time'          => '',
                                    'visitor_count_visits'      => '',
                                    'example_visit_dimension'   => '',
                                    'visit_entry_idaction_name' => '',
                                    'visit_entry_idaction_url'  => '',
                                    'visitor_returning'         => '',
                                    'visitor_days_since_order'  => '',
                                    'visit_goal_buyer'          => '',
                                    'visit_first_action_time'   => '',
                                    'visit_goal_converted'      => '',
                                    'visitor_days_since_first'  => '',
                                    'visit_exit_idaction_name'  => '',
                                    'visit_exit_idaction_url'   => '',
                                    'config_browser_version'    => '',
                                    'config_browser_name'       => '',
                                    'location_browser_lang'     => '',
                                    'config_os'                 => '',
                                    'config_cookie'             => '',
                                    'referer_visit_server_date' => '',
                                    'referer_url'               => '',
                                    'visit_total_searches'      => '',
                                    'visit_total_actions'       => '',
                                    'referer_keyword'           => '',
                                    'referer_name'              => '',
                                    'referer_type'              => '',
                                    'location_provider'         => '',
                                    'custom_var_k1'             => '',
                                    'custom_var_v1'             => '',
                                    'custom_var_k2'             => '',
                                    'custom_var_v2'             => '',
                                    'custom_var_k3'             => '',
                                    'custom_var_v3'             => '',
                                    'custom_var_k4'             => '',
                                    'custom_var_v4'             => '',
                                    'custom_var_k5'             => '',
                                    'custom_var_v5'             => '',
                                );
    }

    public function getLogVisit(){
        return $this->logVisit;
    }

    public function setLogVisit($data){
        $logVisit = $this->logVisit;
        $plugins = $data['plugins'];
        $userAgent = $data['userAgent'];

        $logVisit['idvisit']                   = $data['idvisit'];
        $logVisit['idsite']                    = $data['idsite'];
        $logVisit['idvisitor']                 = $data['idvisitor'];
        $logVisit['visit_last_action_time']    = $data['visit_last_action_time'];
        $logVisit['config_id']                 = $data['config_id'];
        $logVisit['location_ip']               = $data['location_ip'];
        $logVisit['config_resolution']         = $data['config_resolution'];
        $logVisit['config_device_brand']       = $data['config_device_brand']; //TODO
        $logVisit['config_device_model']       = $data['config_device_model']; // TODO
        $logVisit['config_windowsmedia']       = $plugins[6];
        $logVisit['config_silverlight']        = $plugins[8];
        $logVisit['config_java']               = $plugins[1];
        $logVisit['config_gears']              = $plugins[7];
        $logVisit['config_pdf']                = $plugins[5];
        $logVisit['config_quicktime']          = $plugins[3];
        $logVisit['config_realplayer']         = $plugins[4];
        $logVisit['config_device_type']        = $data['config_device_type']; // TODO
        $logVisit['visitor_localtime']         = $data['visitor_localtime'];
        $logVisit['location_region']           = $data['location_region']; // TODO
        $logVisit['visitor_days_since_last']   = $data['visitor_days_since_last'];
        $logVisit['location_longitude']        = $data['location_longitude']; // TODO
        $logVisit['visit_total_events']        = $data['visit_total_events']; // TODO check in piwik
        $logVisit['config_os_version']         = $data['config_os_version']; // TODO
        $logVisit['location_city']             = $data['location_city']; // TODO
        $logVisit['location_country']          = $data['location_country']; // TODO
        $logVisit['location_latitude']         = $data['location_latitude']; // TODO
        $logVisit['config_flash']              = $plugins[0];
        $logVisit['config_director']           = $plugins[2];
        $logVisit['visit_total_time']          = $data['visit_total_time']; // TODO NOW
        $logVisit['visitor_count_visits']      = $data['visitor_count_visits']; // TODO $data['visitor_count_visits'];
        $logVisit['example_visit_dimension']   = $data['example_visit_dimension']; // TODO $data['example_visit_dimension'];
        $logVisit['visit_entry_idaction_name'] = $data['visit_entry_idaction_name']; // TODO $data['visit_entry_idaction_name'];
        $logVisit['visit_entry_idaction_url']  = $data['visit_entry_idaction_url']; // TODO $data['visit_entry_idaction_url'];
        $logVisit['visitor_returning']         = $data['visitor_returning']; // TODO $data['visitor_returning'];
        $logVisit['visitor_days_since_order']  = $data['visitor_days_since_order']; // TODO $data['visitor_days_since_order'];
        $logVisit['visit_goal_buyer']          = $data['visit_goal_buyer']; // TODO $data['visit_goal_buyer'];
        $logVisit['visit_first_action_time']   = $data['visit_first_action_time'];
        $logVisit['visit_goal_converted']      = $data['visit_goal_converted']; // TODO $data['visit_goal_converted'];
        $logVisit['visitor_days_since_first']  = $data['visitor_days_since_first']; // TODO $data['visitor_days_since_first'];
        $logVisit['visit_exit_idaction_name']  = $data['visit_exit_idaction_name']; // TODO $data['visit_exit_idaction_name'];
        $logVisit['visit_exit_idaction_url']   = $data['visit_exit_idaction_url']; // TODO $data['visit_exit_idaction_url'];
        $logVisit['config_browser_version']    = $userAgent['browser_version'];
        $logVisit['config_browser_name']       = $userAgent['browser_name'];
        $logVisit['location_browser_lang']     = $userAgent['language'];
        $logVisit['config_os']                 = $userAgent['os'];
        $logVisit['config_cookie']             = $plugins[9];
        $logVisit['referer_visit_server_date'] = Date('Y-m-d H:i:s');
        $logVisit['referer_url']               = $data['referer_url'];
        $logVisit['visit_total_searches']      = $data['visit_total_searches']; // TODO $data['visit_total_searches'];
        $logVisit['visit_total_actions']       = $data['visit_total_actions']; // TODO $data['visit_total_actions'];
        $logVisit['referer_keyword']           = $data['referer_keyword']; // TODO key word of user searched
        $logVisit['referer_name']              = $data['referer_name']; // TODO is it from registred searchengine?
        $logVisit['referer_type']              = $data['referer_type']; // TODO is it from search engine or direct
        $logVisit['location_provider']         = $data['location_provider']; // TODO $data['location_provider'];
        $logVisit['custom_var_k1']             =   $data['custom_var_k1']; //TODO
        $logVisit['custom_var_v1']             =   $data['custom_var_v1']; //TODO
        $logVisit['custom_var_k2']             =   $data['custom_var_k2']; //TODO
        $logVisit['custom_var_v2']             =   $data['custom_var_v2']; //TODO
        $logVisit['custom_var_k3']             =   $data['custom_var_k3']; //TODO
        $logVisit['custom_var_v3']             =   $data['custom_var_v3']; //TODO
        $logVisit['custom_var_k4']             =   $data['custom_var_k4']; //TODO
        $logVisit['custom_var_v4']             =   $data['custom_var_v4']; //TODO
        $logVisit['custom_var_k5']             =   $data['custom_var_k5']; //TODO
        $logVisit['custom_var_v5']             =   $data['custom_var_v5']; //TODO

        $this->logVisit = $logVisit;
    }
}
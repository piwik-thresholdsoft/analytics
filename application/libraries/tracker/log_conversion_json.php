<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */

class Log_conversion_json{

    private $logConversion;

    public function __construct(){
        $this->logConversion = array (
            'idvisit'                               =>   '',
            'idsite'                                =>   '',
            'idvisitor'                             =>   '',
            'server_time'                           =>   '',
            'idaction_url'                          =>   '',
            'idlink_va'                             =>   '',
            'idgoal'                                =>   '',
            'buster'                                =>   '',
            'idorder'                               =>   '',
            'items'                                 =>   '',
            'url'                                   =>   '',
            'location_region'                       =>   '',
            'location_longitude'                    =>   '',
            'location_city'                         =>   '',
            'location_country'                      =>   '',
            'location_latitude'                     =>   '',
            'visitor_count_visits'                  =>   '',
            'visitor_returning'                     =>   '',
            'visitor_days_since_order'              =>   '',
            'visitor_days_since_first'              =>   '',
            'referer_visit_server_date'             =>   '',
            'referer_keyword'                       =>   '',
            'referer_name'                          =>   '',
            'referer_type'                          =>   '',
            'example_conversion_dimension'          =>   '',
            'revenue_discount'                      =>   '',
            'revenue'                               =>   '',
            'revenue_shipping'                      =>   '',
            'revenue_subtotal'                      =>   '',
            'revenue_tax'                           =>   '',
            'custom_var_k1'                         =>   '',
            'custom_var_v1'                         =>   '',
            'custom_var_k2'                         =>   '',
            'custom_var_v2'                         =>   '',
            'custom_var_k3'                         =>   '',
            'custom_var_v3'                         =>   '',
            'custom_var_k4'                         =>   '',
            'custom_var_v4'                         =>   '',
            'custom_var_k5'                         =>   '',
            'custom_var_v5'                         =>   ''
        );
    }

    public function getLogConversion(){
        return $this->logConversion;
    }

    public function setLogConversion($data){
        $log_conversion = array (
                                    'idvisit'                               =>   $data['idvisit'],
                                    'idsite'                                =>   $data['idsite'],
                                    'idvisitor'                             =>   $data['idvisitor'],
                                    'server_time'                           =>   $data['server_time'],
                                    'idaction_url'                          =>   $data['idaction_url'],
                                    'idlink_va'                             =>   $data['idlink_va'],
                                    'idgoal'                                =>   $data['idgoal'],
                                    'buster'                                =>   $data['buster'],
                                    'idorder'                               =>   $data['idorder'],
                                    'items'                                 =>   $data['items'],
                                    'url'                                   =>   $data['url'],
                                    'location_region'                       =>   $data['location_region'],
                                    'location_longitude'                    =>   $data['location_longitude'],
                                    'location_city'                         =>   $data['location_city'],
                                    'location_country'                      =>   $data['location_country'],
                                    'location_latitude'                     =>   $data['location_latitude'],
                                    'visitor_count_visits'                  =>   $data['visitor_count_visits'],
                                    'visitor_returning'                     =>   $data['visitor_returning'],
                                    'visitor_days_since_order'              =>   $data['visitor_days_since_order'],
                                    'visitor_days_since_first'              =>   $data['visitor_days_since_first'],
                                    'referer_visit_server_date'             =>   $data['referer_visit_server_date'],
                                    'referer_keyword'                       =>   $data['referer_keyword'],
                                    'referer_name'                          =>   $data['referer_name'],
                                    'referer_type'                          =>   $data['referer_type'],
                                    'example_conversion_dimension'          =>   $data['example_conversion_dimension'],
                                    'revenue_discount'                      =>   $data['revenue_discount'],
                                    'revenue'                               =>   $data['revenue'],
                                    'revenue_shipping'                      =>   $data['revenue_shipping'],
                                    'revenue_subtotal'                      =>   $data['revenue_subtotal'],
                                    'revenue_tax'                           =>   $data['revenue_tax'],
                                    'custom_var_k1'                         =>   $data['custom_var_k1'],
                                    'custom_var_v1'                         =>   $data['custom_var_v1'],
                                    'custom_var_k2'                         =>   $data['custom_var_k2'],
                                    'custom_var_v2'                         =>   $data['custom_var_v2'],
                                    'custom_var_k3'                         =>   $data['custom_var_k3'],
                                    'custom_var_v3'                         =>   $data['custom_var_v3'],
                                    'custom_var_k4'                         =>   $data['custom_var_k4'],
                                    'custom_var_v4'                         =>   $data['custom_var_v4'],
                                    'custom_var_k5'                         =>   $data['custom_var_k5'],
                                    'custom_var_v5'                         =>   $data['custom_var_v5']
                            );
        $this->logConversion = $log_conversion;
    }
}
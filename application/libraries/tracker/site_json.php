<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 *
 * This is sites collection data format
 */


class Site_json{

    private $sites;

    public function _construct(){

    }

    public function getSites(){
        return $this->sites;
    }

    public function setSites($data){
        $sites = array (
                            '_id'                               =>   $data['_id'],
                            'idsite'                            =>   $data['idsite'],
                            'name'                              =>   $data['name'],
                            'main_url'                          =>   $data['main_url'],
                            'ts_created'                        =>   $data['ts_created'],
                            'ecommerce'                         =>   $data['ecommerce'],
                            'sitesearch'                        =>   $data['sitesearch'],
                            'sitesearch_keyword_parameters'     =>   $data['sitesearch_keyword_parameters'],
                            'sitesearch_category_parameters'    =>   $data['sitesearch_category_parameters'],
                            'timezone'                          =>   $data['timezone'],
                            'currency'                          =>   $data['currency'],
                            'excluded_ips'                      =>   $data['excluded_ips'],
                            'excluded_parameters'               =>   $data['excluded_parameters'],
                            'excluded_user_agents'              =>   $data['excluded_user_agents'],
                            'group'                             =>   $data['group'],
                            'type'                              =>   $data['type'],
                            'keep_url_fragment'                 =>   $data['keep_url_fragment'],
                   );
        $this->sites = $sites;
    }
}
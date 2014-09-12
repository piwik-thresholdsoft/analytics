<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 * Date: 11/9/14
 * Time: 3:40 PM
 *
 * This class prepares data to be inserted into tracking collection based information received from the js client.
 */

class Tracker_main {

    private $logOperation = 'insert';

    public function __construct(){
        $this->CI = get_instance();
    }

    /**
     * Handle tracker information
     * Prepare data tbe inserted into DB based on tracker information
     * @param $request
     * @param $requestData
     * @return array
     */
    public function handle($request, $requestData){
        $returnData = array();

        /**
         * Check if it is from Campaign
         */
        $commonData['campaignData'] = $request->getCampaignData();
        /**
         * Check if it is from social networks
         */
        $commonData['socialNetwork'] = $request->getSocialNetworksData();
        /**
         * Check if it is from search engines
         */
        $commonData['searchEngine'] = $request->getSearchEnginesData();
        /**
         * Check if it is Ecommerce one
         */
        $commonData['ecommerce'] = $request->getEcommerceData();

        $data = $requestData+$commonData;

        //print_r($data);

        $returnData['logVisit'] = $this->logVisit($request, $data);
        $returnData['logAction'] = $this->logAction($request, $returnData['logVisit']);
        $returnData['logLinkVisit'] = $this->logLinkVisit($request, ($returnData['logVisit']+$returnData['logAction']));

        /**
         * Check if goal is set
         */
        $returnData['goalsMatched'] = $this->handleGoals($request, $data, ($returnData['logVisit']+$returnData['logAction']+$returnData['logLinkVisit']));

        /**
         * Handle ecommerce tracking
         */
        $returnData['logConvertedItems'] = $this->handleEcommerceTracking($request, $returnData, $data);

        return $returnData;
    }

    /**
     * Calculate configID
     * @param $request
     * @return string
     */
    public function calculateConfigID($request){
        $plugins = $request->getPlugins();
        $userAgent = $request->getUserAgent();
        $userIP = $request->getIp();

        $currConfigID = md5(
                                 $userAgent['os']
                                .$userAgent['browser_name']
                                .$userAgent['browser_version']
                                .$userAgent['language']
                                .$userIP
                                .implode(',', $plugins)
                            );
        return $currConfigID;
    }

    /**
     * Identify Operation for visitor logging
     * @return string
     */
    public function identifyOperation(){
      return $this->logOperation;
   }

    /**
     * Prepare log_visit collection data
     * @param $request
     * @param $logData
     * @return mixed
     */
    private function logVisit($request, $logData){
        /** LOG VISIT */
        $this->CI->load->library('tracker/log_visit_json', $logData);
        $defData = $this->CI->log_visit_json->getLogVisit();
        $visitLastActionTime = isset($logData['visit_last_action_time']) ? $logData['visit_last_action_time'] :  $defData['visit_last_action_time'];

        $previousVisitTime = new DateTime($visitLastActionTime);
        $currVisitTime = new DateTime(Date("Y-m-d H:i:s"));
        $visitTotalTime = $previousVisitTime->diff($currVisitTime);

        $hr = $visitTotalTime->h;
        $m = $visitTotalTime->i;
        $s = $visitTotalTime->s;
        $timeDiff = (($hr*3600)+($m*60)+$s);

        /** Identify visitor type */
        if(empty($logData['idvisit']) || ($timeDiff > VISIT_TIME_DIFF)){
            /** Visitor type new visitor */
            $this->logOperation = 'insert';

            $defData['idvisit'] = time();
            $defData['idvisitor'] = uniqid();
            $defData['config_resolution'] = $request->getDisplayResolution();
            $defData['visitor_localtime'] = $request->getLocalTime();
            $defData['referer_url'] = $request->urlReferer();

            $userAgent = $request->getUserAgent();
            $plugins = $request->getPlugins();
            $userIP = $request->getIp();

            $defData['config_id'] =  md5(
                                         $userAgent['os']
                                        .$userAgent['browser_name']
                                        .$userAgent['browser_version']
                                        .$userAgent['language']
                                        .$userIP
                                        .implode(',', $plugins)
                                        );
            $defData['visit_first_action_time']   = Date('Y-m-d H:i:s');

            /**
             * Check if is from Campaign
             */
            $campaignData['campaignName'] = '';
            $campaignData['campaignKeyWord'] = '';
            $campaignData['campaignID'] = '';

            if(is_array($logData['campaignData'])){
                $campaign = $logData['campaignData'];

                $campaignData['campaignName'] = $campaign['campaignName'];
                $campaignData['campaignKeyWord'] = $campaign['campaignName'];
                $campaignData['campaignID'] = $campaign['campaignID'];
                $campaignData['campaignType'] = '1';
            }
            $defData['referer_keyword']           = $campaignData['campaignKeyWord'];
            $defData['referer_name']              = $campaignData['campaignName'];
            $defData['referer_type']              = $campaignData['campaignType'];
            $defData['location_provider']         = 'Ip';

        }else{
            /** Visitor type returning visitor */
            $this->logOperation = 'update';
            //print_r($logData);
            $referURL = $request->urlReferer();
            $previousURL = ''; //TODO GET FROM LOG_ACTION Table
            $defData = $logData;
            $defData['visitor_days_since_last'] = $request->getDaysSinceLastVisit();

            if($referURL == $previousURL){
                $defData['visit_entry_idaction_name'] = '';
                $defData['visit_entry_idaction_url']  = '';
                $defData['visit_exit_idaction_name']  = '';
                $defData['visit_exit_idaction_url']   = '';
            }

            //UPDATE THE LOG
            $defData['visit_total_time'] = $visitTotalTime->s;
            $defData['visit_total_searches']      = ''; // TODO $data['visit_total_searches'];
            $defData['visit_total_actions']       = ''; // TODO $data['visit_total_actions'];

            $defData['visitor_count_visits'] = ''; //TODO
            $defData['example_visit_dimension'] = '';
            $defData['visitor_returning']         = '1';

            $defData['visitor_days_since_first']  = $visitTotalTime->d;

            $defData['visitor_days_since_order']  = ''; // TODO from log_conversion_item
        }
        $defData['plugins'] = $request->getPlugins();
        $defData['userAgent'] = $request->getUserAgent();
        $defData['location_ip'] = $request->getIp();

        $defData['visit_last_action_time'] = Date('Y-m-d H:i:s');
        $defData['referer_visit_server_date'] = Date('Y-m-d H:i:s');

        //COMPARE WITH GOAL
        $defData['visit_goal_buyer']          = ''; // TODO
        $defData['visit_goal_converted']      = ''; // TODO

        /**
         * Check if custom variables are set
         */
        $customVars1 = $request->getCustomVariables('page');
        //print_r($customVars1);
        $customVars = $this->prepareCustomVariables($customVars1);
        //print_r($customVars);
        $defData['custom_var_k1']             = $customVars['custom_var_k1'];
        $defData['custom_var_v1']             = $customVars['custom_var_v1'];
        $defData['custom_var_k2']             = $customVars['custom_var_k2'];
        $defData['custom_var_v2']             = $customVars['custom_var_v2'];
        $defData['custom_var_k3']             = $customVars['custom_var_k3'];
        $defData['custom_var_v3']             = $customVars['custom_var_v3'];
        $defData['custom_var_k4']             = $customVars['custom_var_k4'];
        $defData['custom_var_v4']             = $customVars['custom_var_v4'];
        $defData['custom_var_k5']             = $customVars['custom_var_k5'];
        $defData['custom_var_v5']             = $customVars['custom_var_v5'];


        $this->CI->log_visit_json->setLogVisit($defData);
        $data = $this->CI->log_visit_json->getLogVisit();
        return $data;
    }

    /**
     * Prepare data for log_action collection
     * @param $request
     * @param $data
     * @return mixed
     */
    private function logAction($request, $data){
        /* LOG ACTION*/
        $this->CI->load->library('tracker/log_action_json');
        $defData = $this->CI->log_action_json->getLogAction();

        $actionData = $request->getActionData();

        $defData['idaction']      =  time();
        $defData['name']          =  $actionData['e_n'];
        $defData['hash']          =  md5((implode(",", $actionData)));
        $defData['type']          =  $actionData['e_c'];
        $defData['url_prefix']    =  '';

        $this->CI->log_action_json->setLogAction($defData);

        $logActionData = $this->CI->log_action_json->getLogAction();
        return $logActionData;
    }

    /**
     * Prepare log_link_visit_action collection data
     * @param $request
     * @param $logData
     * @return mixed
     */
    private function logLinkVisit($request, $logData){
        /*LOG LINK VISIT */
        $this->CI->load->library('tracker/log_link_visit_json', $logData);
        $defData = $this->CI->log_link_visit_json->getLogLinkVisit();

        $idLinkVA = time();

        $defData['idlink_va']                 =   $idLinkVA;
        $defData['idsite']                    =   $logData['idsite'];
        $defData['idvisitor']                 =   $logData['idvisitor'];
        $defData['idvisit']                   =   $logData['idvisit'];
        $defData['idaction_url_ref']          =   $logData['idaction'];//TODO $logData['idaction'];
        $defData['idaction_name_ref']         =   $logData['idaction'];//TODO $logData['type'];
        $defData['custom_float']              =   '';//TODO $logData['custom_float'];
        $defData['example_action_dimension']  =   '';//TODO $logData['example_action_dimension'];
        $defData['idaction_name']             =   $logData['idaction']; //------
        $defData['idaction_url']              =   $logData['type']; //------
        $defData['server_time']               =   Date('Y-m-d H:i:s');
        $defData['time_spent_ref_action']     =   $logData['visit_total_time'];//TODO
        $defData['idaction_event_action']     =   $logData['type']; //TODO
        $defData['idaction_event_category']   =   $logData['idaction']; //TODO
        $defData['custom_var_k1']             =   $logData['custom_var_k1'];
        $defData['custom_var_v1']             =   $logData['custom_var_v1'];
        $defData['custom_var_k2']             =   $logData['custom_var_k2'];
        $defData['custom_var_v2']             =   $logData['custom_var_v2'];
        $defData['custom_var_k3']             =   $logData['custom_var_k3'];
        $defData['custom_var_v3']             =   $logData['custom_var_v3'];
        $defData['custom_var_k4']             =   $logData['custom_var_k4'];
        $defData['custom_var_v4']             =   $logData['custom_var_v4'];
        $defData['custom_var_k5']             =   $logData['custom_var_k5'];
        $defData['custom_var_v5']             =   $logData['custom_var_v5'];

        $this->CI->log_link_visit_json->setLogLinkVisit($defData);
        $logLinkVisitData = $this->CI->log_link_visit_json->getLogLinkVisit();
        return $logLinkVisitData;
    }

    /**
     * Custom variables preparation
     * @param $data
     * @return mixed
     */
    private function prepareCustomVariables($data){
        $customVars['custom_var_k1'] = '';
        $customVars['custom_var_v1'] = '';
        $customVars['custom_var_k2'] = '';
        $customVars['custom_var_v2'] = '';
        $customVars['custom_var_k3'] = '';
        $customVars['custom_var_v3'] = '';
        $customVars['custom_var_k4'] = '';
        $customVars['custom_var_v4'] = '';
        $customVars['custom_var_k5'] = '';
        $customVars['custom_var_v5'] = '';

        if(is_array($data)){
            if(array_key_exists('custom_var_k1', $data)){
                $customVars['custom_var_k1'] = $data['custom_var_k1'];
                $customVars['custom_var_v1'] = $data['custom_var_v1'];
            }

            if(array_key_exists('custom_var_k2', $data)){
                $customVars['custom_var_k2'] = $data['custom_var_k2'];
                $customVars['custom_var_v2'] = $data['custom_var_v2'];
            }

            if(array_key_exists('custom_var_k3', $data)){
                $customVars['custom_var_k3'] = $data['custom_var_k3'];
                $customVars['custom_var_v3'] = $data['custom_var_v3'];
            }

            if(array_key_exists('custom_var_k4', $data)){
                $customVars['custom_var_k4'] = $data['custom_var_k4'];
                $customVars['custom_var_v4'] = $data['custom_var_v4'];
            }

            if(array_key_exists('custom_var_k5', $data)){
                $customVars['custom_var_k5'] = $data['custom_var_k5'];
                $customVars['custom_var_v5'] = $data['custom_var_v5'];
            }
        }

        return $customVars;
    }

    /**
     * Handle goals and e-commerce conversions
     * @param $request
     * @param $requestData
     * @param $logData
     * @return array
     */
    private function handleGoals($request, $requestData, $logData){
        /**
         * Check for Goal matcher
         */
        $goalData['goalRecords'] = $requestData['goalRecords'];
        $goalData['url'] = $request->getVisitURL();
        $goalData['type'] = 'url';

        $this->CI->load->library('tracker/goal_manager');
        $matchedGoals = $this->CI->goal_manager->checkForGoal($goalData);
        //print_r($matchedGoals);
        //$returnData['matchedGoals'] = $matchedGoals;
        $goals = array();

        foreach($matchedGoals as $goal){
            $goalItem = $this->logConversion($request, $logData);
            $goalItem['idgoal'] = $goal['_id'];
            $goalItem['url'] = $goal['url'];
            $goalItem['revenue'] = $goal['revenue'];

            $goals[] = $goalItem;
        }
        $goalsUpdated = array();

        //Check if it is an ecommerce site
        $siteInfo = $requestData['siteInfo'];
        if($siteInfo['ecommerce']){
            $ecommerce = $requestData['ecommerce'];
            $ecID = $ecommerce['ec_id'];
            $conversionItemsStr = $ecommerce['ec_items'];
            $itemsCount = count(explode('],',$conversionItemsStr));
            $ects = $ecommerce['_ects'];
            $ecst = $ecommerce['ec_st'];
            $ectx = $ecommerce['ec_tx'];
            $ecsh = $ecommerce['ec_sh'];
            $ecdt = $ecommerce['ec_dt'];
            $revenue = $request->getGoalRevenue(0.00);
            if($ecID != 0 ){
                if(!empty($goals)){
                    foreach($goals as $gl){
                        $gl['idorder']                               =   $ecID; //TODO Ecommerce
                        $gl['items']                                 =   $itemsCount; //TODO Ecommerce
                        $gl['visitor_days_since_order']              =   ''; //TODO Ecommerce
                        $gl['revenue_discount']                      =   $ecdt;
                        $gl['revenue']                               =   $revenue;
                        $gl['revenue_shipping']                      =   $ecsh;
                        $gl['revenue_subtotal']                      =   $ecst;
                        $gl['revenue_tax']                           =   $ectx;
                        $goalsUpdated[] = $gl;
                    }
                }else{
                    $goals = $this->logConversion($request, $logData);
                    $goals['idgoal'] = $goal['_id'];
                    $goals['url'] = $goal['url'];
                    $goals['revenue'] = $goal['revenue'];
                    $goals['idorder']                               =   $ecID; //TODO Ecommerce
                    $goals['items']                                 =   $itemsCount; //TODO Ecommerce
                    $goals['visitor_days_since_order']              =   ''; //TODO Ecommerce
                    $goals['revenue_discount']                      =   $ecdt;
                    $goals['revenue']                               =   $revenue;
                    $goals['revenue_shipping']                      =   $ecsh;
                    $goals['revenue_subtotal']                      =   $ecst;
                    $goals['revenue_tax']                           =   $ectx;
                    $goalsUpdated = $goals;
                }
            }
        }else{
            $goalsUpdated = $goals;
        }

        //echo "\n IN goal handler".$revenue." \n";
        //print_r($goalsUpdated);
        return $goalsUpdated;
    }

    /**
     * Prepare data for log_conversion collection
     * @param $request
     * @param $logData
     * @return mixed
     */
    private function logConversion($request, $logData){
        $this->CI->load->library('tracker/log_conversion_json');
        $defData = $this->CI->log_conversion_json->getLogConversion();
        $dateTime = date("Y-m-d H:i:s");

        $defData['idvisit']                               =   $logData['idvisit'];
        $defData['idsite']                                =   $logData['idsite'];
        $defData['idvisitor']                             =   $logData['idvisitor'];
        $defData['server_time']                           =   $dateTime;
        $defData['idaction_url']                          =   $logData['idaction'];
        $defData['idlink_va']                             =   $logData['idlink_va'];
        $defData['idgoal']                                =   '';
        $defData['buster']                                =   ''; //TODO with piwik
        $defData['idorder']                               =   ''; //TODO Ecommerce
        $defData['items']                                 =   ''; //TODO Ecommerce
        $defData['url']                                   =   '';
        $defData['location_region']                       =   ''; //TODO
        $defData['location_longitude']                    =   ''; //TODO
        $defData['location_city']                         =   ''; //TODO
        $defData['location_country']                      =   ''; //TODO
        $defData['location_latitude']                     =   ''; //TODO
        $defData['visitor_count_visits']                  =   ''; //TODO
        $defData['visitor_returning']                     =   $logData['visitor_returning'];
        $defData['visitor_days_since_order']              =   ''; //TODO Ecommerce
        $defData['visitor_days_since_first']              =   $logData['visitor_days_since_first'];
        $defData['referer_visit_server_date']             =   $logData['referer_visit_server_date'];
        $defData['referer_keyword']                       =   $logData['referer_keyword'];
        $defData['referer_name']                          =   $logData['referer_name'];
        $defData['referer_type']                          =   $logData['referer_type'];
        $defData['example_conversion_dimension']          =   ''; //TODO with piwik
        $defData['revenue_discount']                      =   ''; //TODO for ecommerce
        $defData['revenue']                               =   ''; //TODO for ecommerce
        $defData['revenue_shipping']                      =   ''; //TODO for ecommerce
        $defData['revenue_subtotal']                      =   ''; //TODO for ecommerce
        $defData['revenue_tax']                           =   ''; //TODO for ecommerce
        $defData['custom_var_k1']                         =   $logData['custom_var_k1'];
        $defData['custom_var_v1']                         =   $logData['custom_var_v1'];
        $defData['custom_var_k2']                         =   $logData['custom_var_k2'];
        $defData['custom_var_v2']                         =   $logData['custom_var_v2'];
        $defData['custom_var_k3']                         =   $logData['custom_var_k3'];
        $defData['custom_var_v3']                         =   $logData['custom_var_v3'];
        $defData['custom_var_k4']                         =   $logData['custom_var_k4'];
        $defData['custom_var_v4']                         =   $logData['custom_var_v4'];
        $defData['custom_var_k5']                         =   $logData['custom_var_k5'];
        $defData['custom_var_v5']                         =   $logData['custom_var_v5'];
        $this->CI->log_conversion_json->setLogConversion($defData);
        $data = $this->CI->log_conversion_json->getLogConversion($defData);
        return $data;
    }

    /**
     * Prepare log_converted_items data for an e-commerce order
     * @param $logData
     * @return mixed
     */
    private function logConversionItem($logData){
        $this->CI->load->library('tracker/log_conversion_item_json');
        $defData = $this->CI->log_conversion_item_json->getLogConversionItem();

        $defData['idsite']                =   $logData['logVisit']['idsite'];
        $defData['idvisitor']             =   $logData['logVisit']['idvisitor'];
        $defData['server_time']           =   $logData['logLinkVisit']['server_time'];
        $defData['idvisit']               =   $logData['logVisit']['idvisit'];
        $defData['idorder']               =   '';
        $defData['idaction_sku']          =   '';//TODO $logData['idaction_sku'];
        $defData['idaction_name']         =   '';//TODO $logData['idaction_name'];
        $defData['idaction_category']     =   '';//TODO $logData['idaction_category'];
        $defData['idaction_category2']    =   '';//TODO $logData['idaction_category2'];
        $defData['idaction_category3']    =   '';//TODO $logData['idaction_category3'];
        $defData['idaction_category4']    =   '';//TODO $logData['idaction_category4'];
        $defData['idaction_category5']    =   '';//TODO $logData['idaction_category5'];
        $defData['price']                 =   '';//TODO $logData['price'];
        $defData['quantity']              =   '';//TODO $logData['quantity'];
        $defData['deleted']               =   '';//TODO $logData['deleted'];

        $defData = $this->CI->log_conversion_item_json->setLogConversionItem($defData);
        return $this->CI->log_conversion_item_json->getLogConversionItem();
    }


    /**
     * Handle Ecommerce tracking information
     * Track ecommerce data based on site settings
     * @param $request
     * @param $logData
     * @param $data
     * @return array
     */
    private function handleEcommerceTracking($request, $logData, $data){
        //print_r($logData);
        $siteInfo = $data['siteInfo'];
        $conversionItemsData = array();

        if($siteInfo['ecommerce']){

            //print_r($data['ecommerce']);
            $ecommerceData = $data['ecommerce'];
            $ecID = $ecommerceData['ec_id'];

            //log conversion item
            $conversionItemsStr = $ecommerceData['ec_items'];
            //print_r($conversionItemsStr);

            $conversionItemsAry=explode('],',$conversionItemsStr);

            for($i=0;$i<count($conversionItemsAry);$i++){
                $str2=explode(',',$conversionItemsAry[$i]);
                $res = str_replace(array(']','['),"",$str2);
                //print_r($res);
                if(count($res) > 1){
                    $itemQuantity = $res[count($res)-1];
                    $itemPrice = $res[count($res)-2];
                    $itemSKU = $res[0];
                    $itemName = $res[1];
                    $logConvertedItems = $this->logConversionItem($logData);
                    $logConvertedItems['idorder'] = $ecID;
                    $logConvertedItems['idaction_sku'] = $itemSKU;
                    $logConvertedItems['idaction_name'] = $itemName;
                    $logConvertedItems['price'] = $itemPrice;
                    $logConvertedItems['quantity'] = $itemQuantity;
                    $conversionItemsData[] = $logConvertedItems;
                }
            }
        }
        //echo "\n IN ecommerce handler \n";
       // print_r($conversionItemsData);
        return $conversionItemsData;
    }
}
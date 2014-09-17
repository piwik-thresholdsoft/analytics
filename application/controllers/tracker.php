<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 * Date: 11/9/14
 * Time: 3:40 PM
 *
 * This is main tracking file, all the tracking operations are done here
 */

class Tracker extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('tracker/trackermodel');
        $this->load->library('tracker/tracker_main');
    }

    /**
     * Main tracking method
     */
    public function index(){
        $params[] = $_GET+$_POST;

        $this->load->library('tracker/request', $params);
        $siteID = $this->request->getIdSite();
        $request = $this->request;

        /**
         * Check site exists or not
         */
        $res = $this->trackermodel->checkSiteID($siteID);

        if(!empty($res)){
            $commonData = array();
            /**
             * Get Goals
             */
            $goalFilter = array('idsite'=>$siteID);
            $commonData['goalRecords'] = $this->trackermodel->getGoals($goalFilter);

            /**
             * Identify new visitor or returning visitor
             */
            $visitorType = $this->identifyVisitor($this->request, $siteID, $params);

            $siteInfo['siteInfo'] = $res;

            $visitorData = array_merge((array)$visitorType, $commonData, $siteInfo);

            if(is_array($visitorType)){
                /** Visitor is returning visitor **/
                $this->handleVisitor($this->request, $visitorData);
            }else{
                /** Visitor is new visitor */
                $visitorData['idsite'] = $siteID;
                $this->handleVisitor($this->request, $visitorData);
            }
        }
    }

    /**
     * Identify Visitor based on device settings
     * @param {object}
     * @param {array}
     * @return {array}
     */
    private function identifyVisitor($request, $siteID){
        $currConfigID = $this->tracker_main->calculateConfigID($request);

        $isVisitorExists = $this->trackermodel->isVisitorExists($currConfigID, $siteID);
        return $isVisitorExists;
    }

    /**
     * Handle visitor operations
     * identify user tracking information and log them into database
     * @param {object}
     * @param {array}
     */
    private function handleVisitor($request, $siteData){
        if(is_array($siteData)){
            $defData = $siteData;
            if(in_array('_id', $siteData)){
                $id = $siteData['_id'];
            }
            //print_r($defData);
            $data = $this->tracker_main->handle($request, $this->trackermodel, $defData);
            //print_r($data);
            $operation = $this->tracker_main->identifyOperation();
            //echo "\n".$operation."\n";
            //print_r($data['logVisit']);
            //exit;
            if($operation === 'insert'){
                /** Insert unique visits data */
                $res['logVisit'] = $this->trackermodel->insertVisit($data['logVisit']);
            }else{
                /** Update returning visits data */
                $where = array("idsite" => $defData['idsite'], "_id" => $id);
                $updateData = $data['logVisit'];
                $res['logVisit'] = $this->trackermodel->updateVisit($where, $updateData);
            }
            /** Insert visit action data */
            if($this->utility->isMulti($data['logAction'])){
                $res['logAction'] = $this->trackermodel->insertLogAction($data['logAction'], true);
            }else{
                if(!empty($data['logAction'])){
                    $res['logAction'] = $this->trackermodel->insertLogAction($data['logAction']);
                }
            }
            /** Insert link visit action data */
            $res['logLinkVisit'] = $this->trackermodel->insertLinkVisit($data['logLinkVisit']);

            $goalsMatched = $data['goalsMatched'];

            /**
             * Get conversions for this goal if exists
             */
            $goalIDs = array();
            $previousConversions = array();
            foreach($siteData['goalRecords'] as $grecs){
                //print_r($grecs);
                $goalWhere = array("idgoal" => new MongoId($grecs["_id"]));
                $goalIDs[] = $goalWhere;
                $previousConversions[] = $this->trackermodel->getConversions($goalWhere);
            }
            //print_r($goalIDs);

            $filterPreviousConversions1 = array();

            foreach($previousConversions as $conversions1){
                foreach($conversions1 as $conversions2){
                    $filterPreviousConversions1[] = array("idgoal" => $conversions2['idgoal'], "url" => $conversions2["url"]);
                }
            }

            //print_r($filterPreviousConversions1);

            $filterPreviousConversions = array();

            foreach($filterPreviousConversions1 as $conversions3){
                //print_r($conversions3["idgoal"]);
                $gID = $conversions3["idgoal"];
                foreach($siteData["goalRecords"] as $conversions4){
                    if($gID == $conversions4["_id"]){
                        $filterPreviousConversions[] = array("idgoal" => $conversions4["_id"], "url"=>$conversions3["url"], "allow_multiple" => $conversions4["allow_multiple"]);
                    }
                }
            }
            //print_r($goalsMatched);
            //print_r($filterPreviousConversions);
            if($this->utility->isMulti($goalsMatched)){
                foreach($goalsMatched as $goal){
                //print_r($goal);
                if(isset($goal["idgoal"]->{'$id'})){
                    $gID = $goal["idgoal"]->{'$id'};
                    $url = $goal["url"];
                }else{
                    $gID = "";
                    $url = "";
                }
                if(count($filterPreviousConversions) > 0){
                    foreach($filterPreviousConversions as $conversions5){
                        //$mid = new MongoId($conversions5["idgoal"]);
                        //var_dump($conversions5["idgoal"]);
                        if(isset($conversions5["idgoal"]->{'$id'})){
                            $cgID = $conversions5["idgoal"]->{'$id'};
                        }else{
                            $cgID = "";
                        }

                        if(($gID === $cgID) && ($url === $conversions5['url'])){
                            if($conversions5["allow_multiple"]){
                                $this->trackermodel->insertConversion($goal);
                            }
                        }else{
                            $this->trackermodel->insertConversion($goal);
                        }
                    }
                }else{
                    $this->trackermodel->insertConversion($goal);
                }

            }
            }else {
                if(!empty($goalsMatched)){
                    $this->trackermodel->insertConversion($goalsMatched);
                }
            }
           /**
            * Ecommerce Converted Items
            */
            //get previous converted items
            $previousItemsWhere = array("idvisitor" => $data['logVisit']['idvisitor'], "idsite" => $data['logVisit']['idsite']);
            $previousConversionItems = $this->trackermodel->getConversionItems($previousItemsWhere);
            echo "\n converted items \n";
            //print_r($this->utility->isMulti($data['logConvertedItems']));

            if($this->utility->isMulti($data['logConvertedItems'])){
                if(!empty($previousConversionItems)){
                    foreach($data['logConvertedItems'] as $conversionItem){
                            foreach($previousConversionItems as $prevItems){
                                if(($prevItems['idvisitor'] == $conversionItem['idvisitor']) &&
                                    ($prevItems['idsite'] == $conversionItem['idsite']) &&
                                    ($prevItems['idaction_sku'] == $conversionItem['idaction_sku'])){
                                    $updateItem = $conversionItem;
                                    $updateWhere = array("_id" => $prevItems['_id']);
                                    $this->trackermodel->updateConversionItem($updateWhere, $updateItem);
                                }else{
                                    $this->trackermodel->insertBatchConversionItems($data['logConvertedItems']);
                                }
                            }
                    }
                }else{
                    if(!empty($data['logConvertedItems'])){
                        $this->trackermodel->insertBatchConversionItems($data['logConvertedItems']);
                    }
                }
            }
            $response = array("status"=>true, "message"=>"Tracked page successfully");;
            $this->output->set_output(json_encode($response));
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
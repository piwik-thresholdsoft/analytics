<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 * Date: 11/9/14
 * Time: 3:40 PM
 */

class Tracker extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('tracker/trackermodel');
        $this->load->library('tracker/tracker_main');
    }

    public function index(){
        $params[] = $_GET+$_POST;
        $this->load->library('tracker/request', $params);
        $siteID = $this->request->getIdSite();
        $res = $this->trackermodel->checkSiteID($siteID);

        //print_r($res);

        $request = $this->request;

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
                //Visitor is returning visitor
                $this->handleVisitor($this->request, $visitorData);
            }else{
                //Visitor is new visitor
                $visitorData['idsite'] = $siteID;
                $this->handleVisitor($this->request, $visitorData);
            }
        }
    }

    private function identifyVisitor($request, $siteID){
        $currConfigID = $this->tracker_main->calculateConfigID($request);

        $isVisitorExists = $this->trackermodel->isVisitorExists($currConfigID, $siteID);
        return $isVisitorExists;
    }

    private function handleVisitor($request, $siteData){
        if(is_array($siteData)){
            $defData = $siteData;
            if(in_array('_id', $siteData)){
                $id = $siteData['_id'];
            }

            $data = $this->tracker_main->handle($request, $defData);
            //print_r($data);

            $operation = $this->tracker_main->identifyOperation();
            //print_r($operation);
            if($operation === 'insert'){
                $res['logVisit'] = $this->trackermodel->insertVisit($data['logVisit']);
            }else{
                $where = array("idsite" => $defData['idsite'], "_id" => $id);
                $updateData = $data['logVisit'];
                //$updateData['_id'] = $id;
                $res['logVisit'] = $this->trackermodel->updateVisit($where, $updateData);
                //echo "Update visit>>";
                //print_r($res['logVisit']);
            }

            $res['logAction'] = $this->trackermodel->insertLogAction($data['logAction']);
            $res['logLinkVisit'] = $this->trackermodel->insertLinkVisit($data['logLinkVisit']);

            $goalsMatched = $data['goalsMatched'];
            //print_r($goalsMatched);

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

            //print_r($filterPreviousConversions);

            foreach($goalsMatched as $goal){
                //print_r($goal);
                $gID = $goal["idgoal"]->{'$id'};
                $url = $goal["url"];
                if(count($filterPreviousConversions) > 0){
                    foreach($filterPreviousConversions as $conversions5){
                        //$mid = new MongoId($conversions5["idgoal"]);
                        //var_dump($conversions5["idgoal"]);
                        if(($gID === $conversions5["idgoal"]->{'$id'}) && ($url === $conversions5['url'])){
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

           /**
            * Ecommerce Converted Items
            */
            //get previous converted items
            $previousItemsWhere = array("idvisitor" => $data['logVisit']['idvisitor'], "idsite" => $data['logVisit']['idsite']);
            $previousConversionItems = $this->trackermodel->getConversionItems($previousItemsWhere);

            if(count($data['logConvertedItems'])){
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
                                    $this->trackermodel->insertConversionItem($data['logConvertedItems']);
                                }
                            }
                    }
                }else{
                    $this->trackermodel->insertBatchConversionItems($data['logConvertedItems']);
                }
            }
            $response = array("status"=>true, "message"=>"Tracked page successfully");;
            $this->output->set_output(json_encode($response));
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
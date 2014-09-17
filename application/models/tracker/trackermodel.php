<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 * Date: 11/9/14
 * Time: 3:40 PM
 *
 * Tracker Model
 * It provides CURD operations on tracking collections
 *  sites, log_visit, log_action, log_link_visit, log_conversion, log_conversion_item, goals
 */

class Trackermodel extends CI_Model{

    private $collectionLogVisit;
    private $collectionLogAction;
    private $collectionLogLinkVisit;
    private $collectionLogConversion;
    private $collectionLogConversionItem;
    private $collectionGoal;
    private $collectionSites;

    public function __construct(){
        parent::__construct();
        $this->collectionLogVisit = $this->cimongo->db->selectCollection('log_visit');
        $this->collectionLogAction = $this->cimongo->db->selectCollection('log_action');
        $this->collectionLogLinkVisit = $this->cimongo->db->selectCollection('log_link_visit_action');
        $this->collectionLogConversion = $this->cimongo->db->selectCollection('log_conversion');
        $this->collectionLogConversionItem = $this->cimongo->db->selectCollection('log_conversion_item');
        $this->collectionGoal = $this->cimongo->db->selectCollection('goal');
        $this->collectionSites = $this->cimongo->db->selectCollection('sites');
    }

    public function returnException($e){
        $err = $e->getCode().'--'.$e->getMessage();
        $res = array("status"=>'error', 'description'=>$err);
        return $res;
    }

    /**
     * COLLECTION log_visits OPERATIONS
     */
    public function insertVisit($data){
        $logVisit = $this->collectionLogVisit;
        try{
            $logVisit->insert($data);
            $newDocID = $data['_id'];
            $res = array("status"=>'success', 'data'=>array("insertID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function updateVisit($where, $data){
        $logVisit = $this->collectionLogVisit;
        try{
            $newData = array('$set'=>$data);
            $res = $logVisit->update($where, $newData);
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function isVisitorExists($configID, $siteID){
        $collection = $this->collectionLogVisit;
        try{
            /*$cursor = $collection->findOne(array('config_id' =>$configID, 'idsite'=>$siteID));*/
            $res1 = $collection->find(array('config_id' =>$configID, 'idsite'=>$siteID));
            $res1 = iterator_to_array($res1);
            uasort($res1, function($a, $b) {
                return strtotime($a['visit_last_action_time'])<strtotime($b['visit_last_action_time'])?1:-1;
            });
            //print_r($res1);
            return reset($res1);
        }catch (MongoCursorException $e){
            $this->returnException($e);
        }
    }

    public function getLogVisitData($where){
        $collection = $this->collectionLogVisit;
        try{
            $res = $collection->find($where);
            $res = iterator_to_array($res);
            return $res;
        }catch (MongoCursorException $e){
            $this->returnException($e);
        }
    }

    //

    /**
     * COLLECTION log_actions OPERATIONS
     */
    public function insertLogAction($data, $type=false){
        $log_visit = $this->collectionLogAction;

        try{
            if($type){
                //echo "\n log_action batch insert \n";
                //print_r($data);
                $log_visit->batchInsert($data);
                $newDocID = $data;
            }else{
                //echo "\n log_action normal insert \n";
                //print_r($data);
                $log_visit->insert($data);
                $newDocID = $data['_id'];
            }
            $res = array("status"=>'success', 'data'=>array("insertID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function getLogActionData($where, $select = array()){
        $collection = $this->collectionLogAction;
        try{
            $res = $collection->find($where, $select);
            $res = iterator_to_array($res);
            return $res;
        }catch (MongoCursorException $e){
            $this->returnException($e);
        }
    }

    /**
     * COLLECTION log_link_visit OPERATIONS
     */
    public function insertLinkVisit($data){
        $log_link_visit = $this->collectionLogLinkVisit;

        try{
            $log_link_visit->insert($data);
            $newDocID = $data['_id'];
            $res = array("status"=>'success', 'data'=>array("insertID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function getLogLinkVisitData($where, $select = array()){
        $collection = $this->collectionLogLinkVisit;
        try{
            $res = $collection->find($where, $select);
            $res = iterator_to_array($res);
            return $res;
        }catch (MongoCursorException $e){
            $this->returnException($e);
        }
    }


    /**
     * COLLECTION log_conversions OPERATIONS
     */
    public function insertConversion($data){
        $log_conversion = $this->collectionLogConversion;
        //echo "\n IN INSERT CONVERSION \n";
        //print_r($data);
        try{
            $log_conversion->insert($data);
            $newDocID = $data['_id'];
            $res = array("status"=>'success', 'data'=>array("insertID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function getConversions($where){
        $collection = $this->collectionLogConversion;
        try{
            $res = $collection->find($where);
            $res = iterator_to_array($res);
            return $res;
        }catch (MongoCursorException $e){
            $this->returnException($e);
        }
    }

    /**
     * COLLECTION log_conversion_items OPERATIONS
     */
    public function insertBatchConversionItems($data){
        $log_conversion = $this->collectionLogConversionItem;

        try{
            $log_conversion->batchInsert($data);
            $newDocID = "";//$data['_id'];
            $res = array("status"=>'success', 'data'=>array("insertID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function getConversionItems($where){
        $collection = $this->collectionLogConversionItem;
        try{
            $res = $collection->find($where);
            $res = iterator_to_array($res);
            return $res;
        }catch (MongoCursorException $e){
            $this->returnException($e);
        }
    }

    public function insertConversionItem($data){
        $logVisit = $this->collectionLogConversionItem;
        try{
            $logVisit->insert($data);
            $newDocID = $data['_id'];
            $res = array("status"=>'success', 'data'=>array("insertID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function updateConversionItem($where, $data){
        $logVisit = $this->collectionLogConversionItem;
        try{
            $newData = array('$set'=>$data);
            $res = $logVisit->update($where, $newData);
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    /**
     * COLLECTION goals OPERATIONS
     */
    public function insertGoal($data){
        $goal = $this->collectionGoal;
        try{
            $goal->insert($data);
            $newDocID = $data->_id;
            $res = array("status"=>'success', 'data'=>array("insertID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function updateUpdate($where, $data){
        $goal = $this->collectionGoal;
        try{
            $newData = array('$set'=>$data);
            $res = $goal->update($where, $newData);
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }


    public function getGoals($where = array()){
        $goal = $this->collectionGoal;
        try{
            $res = $goal->find($where);
            return iterator_to_array($res);
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }


    /**
     * COLLECTION sites OPERATIONS
     */
    public function addNewSite($data){
        $collection = $this->collectionSites;

        try{
            $collection->insert($data);
            $newDocID = $data['_id'];
            $res = array("status"=>'success', 'data'=>array("siteID"=>$newDocID));
            return $res;
        }catch (MongoCursorException $e) {
            $this->returnException($e);
        }
    }

    public function getSites(){

    }

    public function checkSiteID($siteID){
        $collection = $this->collectionSites;
        try{
            $cursor = iterator_to_array($collection->find(array('_id' => new MongoId($siteID))));
            if(empty($cursor)){
                $data = array();
            }else{
                $data = $cursor[$siteID];
            }
            return $data;
        }catch (MongoCursorException $e){
            $this->returnException($e);
        }
    }

}
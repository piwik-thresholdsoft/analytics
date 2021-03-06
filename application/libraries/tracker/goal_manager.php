<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 *
 * This class identifies if goals created for the site and how to identify them and returns matched goals
 */

class Goal_manager {
    public function __construct(){

    }

    /**
     * Check if goal is set against the current URL
     * @param $data
     * @return array
     */
    public function checkForGoal($data){

        $type = $data['type'];
        $url = $data['url'];
        //echo "<pre>";
        //print_r($data);
        $return = array();
        foreach($data['goalRecords'] as $rec){
            $gdata = $rec;
            $gdata['url'] = $url;
            $gdata['type'] = $rec['match_attribute'];
            $cdata = $this->checkWithPatterns($type, $gdata);
            //echo json_encode($cdata);
            if($cdata['goalMatch']){
                $return[] = $cdata['data'];
            }
        }
        return $return;
    }

    /**
     * Check with goal pattern for current url
     * @param $type
     * @param $data
     * @return array
     */
    private function checkWithPatterns($type, $data){
        $patterns = array('url', 'download', 'click', 'manual');
        $return = array("goalMatch"=>false, "data"=>$data);

        if(in_array($type, $patterns)){
            $patternType = $data['pattern_type'];
            switch($type){
                case 'url':
                        $matchAttr = $data['url'];
                        $return = $this->matchForGoal($patternType, $matchAttr, $data);
                    break;
                case 'download':
                    $matchAttr = $data['url']; //TODO GIVE DOWNLOAD LINK
                    $return = $this->matchForGoal($patternType, $matchAttr, $data);
                    break;
                case 'click':
                    $matchAttr = $data['url']; //TODO GIVE CLICK LINK
                    $return = $this->matchForGoal($patternType, $matchAttr, $data);
                    break;
                case 'manual':
                    $matchAttr = $data['url']; //TODO GIVE MANUAL LINK
                    $return = $this->matchForGoal($patternType, $matchAttr, $data);
                    break;
            }
        }
        return $return;
    }

    /**
     * Check if goal is matched with pattern
     * @param $patternType
     * @param $matchAttr
     * @param $data
     * @return array
     */
    private function matchForGoal($patternType, $matchAttr, $data){
        $defaultPatternTypes = array("contains", "exact", "regex");
        $return = array("goalMatch"=>false, "data"=>$data);

        if(in_array($patternType, $defaultPatternTypes)){
            $pattern = $data['pattern'];

            switch($patternType){
                case 'contains':
                        if (strpos($matchAttr,$pattern) !== false) {
                           $return['goalMatch'] = true;
                           $return['data'] = $data;
                        }
                    break;
                case 'exact':
                        if($pattern == $matchAttr){
                            $return['goalMatch'] = true;
                            $return['data'] = $data;
                        }
                    break;
                case 'regex':
                        if(preg_match($pattern, $matchAttr)){
                            $return['goalMatch'] = true;
                            $return['data'] = $data;
                        }
                    break;
            }
        }
        return $return;
    }

}
<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */

class Goal_json{

    private $_goal;

    public function _construct(){

    }

    public function getGoal(){
        return $this->_goal;
    }

    public function setGoal($data){
        $goal = array (
                        '_id'               =>   $data['_id'],
                        'idsite'            =>   $data['idsite'],
                        'idgoal'            =>   $data['idgoal'],
                        'name'              =>   $data['name'],
                        'match_attribute'   =>   $data['match_attribute'],
                        'pattern'           =>   $data['pattern'],
                        'pattern_type'      =>   $data['pattern_type'],
                        'case_sensitive'    =>   $data['case_sensitive'],
                        'allow_multiple'    =>   $data['allow_multiple'],
                        'revenue'           =>   $data['revenue'],
                        'deleted'           =>   $data['deleted'],
                );
        $this->_goal = $goal;
    }
}
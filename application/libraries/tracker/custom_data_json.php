<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */
class CustomDataJSON{

    private $_custom_data;

    public function _construct(){

    }

    public function getCustomData(){
        return $this->_custom_data;
    }

    public function setCustomData($data){
        $custom_data = array (
                                '_id'       =>   $data['_id'],
                                'ID'        =>   $data['ID'],
                                'items'     =>   $data['items'],
                        );
        $this->_custom_data = $custom_data;
    }
}
<?php
/**
 * Created by Threshold software solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 * Date: 13/9/14
 * Time: 3:04 PM
 */
class Utility{
    public function __construct(){

    }

    public function isMulti($array) {
        if(is_array($array)){
            $rv = array_filter($array,'is_array');
            if(count($rv)>0) return true;
            return false;
        }else{
            return false;
        }
    }
}
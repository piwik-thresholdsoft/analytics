<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */

class Log_conversion_item_json{

    private $logConversionItem;

    public function _construct(){
        $this->logConversionItem = array (
                                            'idsite'                =>   '',
                                            'idvisitor'             =>   '',
                                            'server_time'           =>   '',
                                            'idvisit'               =>   '',
                                            'idorder'               =>   '',
                                            'idaction_sku'          =>   '',
                                            'idaction_name'         =>   '',
                                            'idaction_category'     =>   '',
                                            'idaction_category2'    =>   '',
                                            'idaction_category3'    =>   '',
                                            'idaction_category4'    =>   '',
                                            'idaction_category5'    =>   '',
                                            'price'                 =>   '',
                                            'quantity'              =>   '',
                                            'deleted'               =>   ''
                                        );
    }

    public function getLogConversionItem(){
        return $this->logConversionItem;
    }

    public function setLogConversionItem($data){
        $log_conversion_item = array (
                                        'idsite'                =>   $data['idsite'],
                                        'idvisitor'             =>   $data['idvisitor'],
                                        'server_time'           =>   $data['server_time'],
                                        'idvisit'               =>   $data['idvisit'],
                                        'idorder'               =>   $data['idorder'],
                                        'idaction_sku'          =>   $data['idaction_sku'],
                                        'idaction_name'         =>   $data['idaction_name'],
                                        'idaction_category'     =>   $data['idaction_category'],
                                        'idaction_category2'    =>   $data['idaction_category2'],
                                        'idaction_category3'    =>   $data['idaction_category3'],
                                        'idaction_category4'    =>   $data['idaction_category4'],
                                        'idaction_category5'    =>   $data['idaction_category5'],
                                        'price'                 =>   $data['price'],
                                        'quantity'              =>   $data['quantity'],
                                        'deleted'               =>   $data['deleted'],
                                );
        $this->logConversionItem = $log_conversion_item;
    }
}
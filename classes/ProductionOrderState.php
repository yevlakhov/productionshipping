<?php

class ProductionOrderState
{
    public $idOrderState;
    
    public $name;
    
    public $color;
    
    public function __construct($idOrderState)
    {
        $this->context = Context::getContext();
        $this->idOrderState = $idOrderState;
        $this->initValues();
    }
    
    public function getValues()
    {
        $orderStateValues = DB::getInstance()->getRow('
            SELECT os.*, osl.* FROM '._DB_PREFIX_.'order_state os
            LEFT JOIN  '._DB_PREFIX_.'order_state_lang osl ON (os.id_order_state = osl.id_order_state)
            WHERE os.id_order_state = "'.pSQL($this->idOrderState).'"
            AND osl.id_lang = "'.pSQL($this->context->language->id).'"
        ');
        return $orderStateValues;
    }
    
    public function initValues()
    {
        $orderStateValues = $this->getValues();
        foreach ($orderStateValues as $key => $value) {
            $this->$key = $value;
        }
    }
}
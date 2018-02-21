<?php

class Pagination
{
    public $defaultLimit = 50;
    public $pagination = [20, 50, 100, 300, 1000];
    public $start;
    public $limit;
    public $tableName;
    
    public function __construct($tableName)
    {
        $this->context = Context::getContext();
        $this->tableName = $tableName;
        $this->setPaginationLimit();
        $this->getPaginationStart();
    }
    
    public function getPaginationLimit()
    {
        if (!empty($this->limit) && is_int($this->limit)) {
            return $this->limit;
        } else {
            return $this->defaultLimit;
        }
    }
    
    public function setPaginationLimit($limit = null)
    {
        if (empty($limit)) {
            if (!empty($this->context->cookie->{$this->tableName.'_pagination'})) {
                $limit = $this->context->cookie->{$this->tableName.'_pagination'};
            } else {
                $limit = $this->defaultLimit;
            }
        }
        
        $this->limit = (int)Tools::getValue($this->tableName.'_pagination', $limit);
        
        if (in_array($this->limit, $this->pagination) && $this->limit !== $this->defaultLimit) {
            $this->context->cookie->{$this->tableName.'_pagination'} = $this->limit;
        } else {
            unset($this->context->cookie->{$this->tableName.'_pagination'});
        }
        
        return $this->limit;
    }
    
    public function getPaginationStart()
    {
        $this->start = 0;
        $limit = $this->getPaginationLimit();
        if ((int)Tools::getValue('submitFilter'.$this->tableName)) {
            $this->start = ((int)Tools::getValue('submitFilter'.$this->tableName) - 1) * $limit;
        }
        return $this->start;
    }
}
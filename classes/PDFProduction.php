<?php

class PDFProduction extends PDFCore
{
    public function getTemplateObject($object)
    {
        $class = false;
        $classname = 'HTMLTemplate'.$this->template;
    
        if (class_exists($classname))
        {
            $class = new $classname($object, $this->smarty);
            if (!($class instanceof HTMLTemplate))
                throw new PrestaShopException('Invalid class. It should be an instance of HTMLTemplate');
        }
    
        return $class;
    }
}

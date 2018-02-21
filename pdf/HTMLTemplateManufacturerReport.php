<?php

class HTMLTemplateManufacturerReport extends HTMLTemplate
{
    public $object;
    public $smarty;
    public $date;
    public $title;
    public $shop;

    public function __construct($object, $smarty)
    {
        $this->object = $object;
        
        $this->smarty = $smarty;
        
        $this->title = self::l('Report');

        // footer informations
        $this->shop = new Shop((int)$this->object->context->shop->id);
    }
    
    /**
     * Returns the template's HTML header
     * @return string HTML header
     */
    public function getHeader()
    {
        $shop_name = Configuration::get('PS_SHOP_NAME', null, null, (int)$this->shop->id);
        
        $path_logo = $this->getLogo();

        $width = 0;
        $height = 0;
        if (!empty($path_logo)) {
            list($width, $height) = getimagesize($path_logo);
        }
        
        $shop_address = AddressFormat::generateAddress($this->shop->getAddress(), array(), '<br />', ' ');
        $shop_address = str_replace($shop_name, '', $shop_address);
        
        $tplVars = [
            'logo_path' => $path_logo,
            'img_ps_dir' => 'http://'.Tools::getMediaServer(_PS_IMG_)._PS_IMG_,
            'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
            'title' => $this->title,
            'shop_name' => $shop_name,
            'shop_details' => Configuration::get('PS_SHOP_DETAILS', null, null, (int)$this->shop->id),
            'width_logo' => $width,
            'height_logo' => $height,
            'shop_address' => $shop_address,
        ];
        
        $this->smarty->assign($tplVars);

        return htmlspecialchars_decode($this->smarty->fetch($this->getTemplate('header-ps')), ENT_NOQUOTES);
    }
    
    /**
     * Returns the invoice logo
     */
    protected function getLogo()
    {
        $logo = '';
        $id_shop = $this->shop->id;

        if (Configuration::get('PS_LOGO_INVOICE', null, null, (int)$id_shop) !== false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, null, (int)$id_shop))) {
            $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, null, (int)$id_shop);
        } elseif (Configuration::get('PS_LOGO', null, null, (int)$id_shop) !== false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, (int)$id_shop))) {
            $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, (int)$id_shop);
        }
        return $logo;
    }
    
    /**
     * Returns the template's HTML footer
     * @return string HTML footer
     */
    public function getFooter()
    {
        $shop_address = $this->getShopAddress();

        $this->smarty->assign(array(
            'available_in_your_account' => false,
            'shop_address' => $shop_address,
            'shop_fax' => Configuration::get('PS_SHOP_FAX', null, null, (int)$this->shop->id),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE', null, null, (int)$this->shop->id),
            'shop_email' => Configuration::get('PS_SHOP_EMAIL', null, null, (int)$this->shop->id),
            'free_text' => Configuration::get('PS_INVOICE_FREE_TEXT', (int)Context::getContext()->language->id, null, (int)$this->shop->id),
            'shop_details' => Configuration::get('PS_SHOP_DETAILS'),
        ));

        return htmlspecialchars_decode($this->smarty->fetch($this->getTemplate('footer-ps')), ENT_NOQUOTES);
    }

    /**
     * Returns the template's HTML content
     * @return string HTML content
     */
    public function getContent()
    {
        $this->smarty->assign(array());

        $template = htmlspecialchars_decode($this->object->mailVars['{report}'], ENT_NOQUOTES);
        //echo $template;die();
        return $template;
    }
    
    /**
     * If the template is not present in the theme directory, it will return the default template
     * in _PS_PDF_DIR_ directory
     *
     * @param $template_name
     * @return string
     */
    protected function getTemplate($template_name)
    {
        $template = false;
        
        $bqTemplate = _PS_MODULE_DIR_.'/productionshipping/views/templates/admin/pdf/'.$template_name.'.tpl';
        
        if (file_exists($bqTemplate)) {
            $default_template = $bqTemplate;
        } else {
            $default_template = _PS_PDF_DIR_.'/'.$template_name.'.tpl';
        }
        
        $overriden_template = _PS_THEME_DIR_.'pdf/'.$template_name.'.tpl';

        if (file_exists($overriden_template)) {
            $template = $overriden_template;
        } elseif (file_exists($default_template)) {
            $template = $default_template;
        }

        return $template;
    }

    /**
     * Returns the template filename when using bulk rendering
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'report.pdf';
    }
    
    /**
     * Returns the template filename
     * @return string filename
     */
    public function getFilename()
    {
        return 'report.pdf';
    }
    
}

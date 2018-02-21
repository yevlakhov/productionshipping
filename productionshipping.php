<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/vendor/autoload.php';

class Productionshipping extends Module
{

    /**
     * @var
     */
    public $helperForm;

    /**
     * @var string
     */
    public $teplateFolder;

    /**
     * @var
     */
    public $testEnv;
    
    public $tabObject;
    
    public $container;


    /**
     * Productionshipping constructor.
     * @param HelperForm|null $helperForm
     * @param Tab|null $tabObject
     */
    public function __construct(HelperForm $helperForm = null, Tab $tabObject = null, $container = null)
    {
        $this->init($helperForm, $tabObject, $container);
        $this->name = 'productionshipping';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'ProfSolution';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Production and Shipping');
        $this->description = $this->l('Production and Shipping');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        
        $this->teplateFolder = $this->local_path.'views/templates/';
    }


    /**
     * This function needed for testing
     * 
     * @param $helperForm
     * @param $tabObject
     */
    public function init($helperForm = null, $tabObject = null, $container = null)
    {
        if ($helperForm) {
            $this->helperForm = $helperForm;
        } else {
            $this->helperForm = new HelperForm();
        }
        
        if ($tabObject) {
            $this->tabObject = $tabObject;
        } else {
            $idTab = Tab::getIdFromClassName('AdminProduction');
            $this->tabObject = new Tab($idTab);
        }
        
        if (($container instanceof ModuleContainer) && !empty($container)) {
            $this->container = $container->getContainer();
        } else {
            $container = new ModuleContainer();
            $this->container = $container->getContainer();
        }
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     * 
     * @return bool
     * @throws PrestaShopException
     */
    public function install()
    {
        $pathToContainerBuilder = dirname(__FILE__).'/vendor/symfony/dependency-injection/ContainerBuilder.php';
        if (!is_file($pathToContainerBuilder) && $this->composerInstall() !== true) {
            $this->_errors[] = $this->l('You have to run comand "php composer.phar install" from module folder, to install needed components via composer, at first. After that you can install module via Prestashop Admin Panel.');
            return false;
        }
        
        if ($this->cronIncludeInstall() === false) {
            $this->_errors[] = $this->l('Cron install false');
            return false;
        }
        
        $this->setTabParams('AdminProduction', array(1=>'Production'), 10);
        
        include(dirname(__FILE__).'/sql/install.php');
        
        if (empty(parent::install()) ||
            empty($this->registerHook('header')) ||
            empty($this->registerHook('backOfficeHeader')) ||
            empty($this->tabObject->save()))
        {
            return false;
        }
        return true;
    }
    
    private function cronIncludeInstall()
    {
        $file = dirname(__FILE__).'/cron_include.php';
        $content = "
<?php
if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', '"._PS_ADMIN_DIR_."');
}";
        $result = file_put_contents($file, $content);
        return $result;
    }
    
    private function composerInstall()
    {
        $user = posix_getpwuid(posix_getuid());
        putenv("HOME={$user['dir']}");
        
        $currentDir = getcwd();
        $moduleDir = dirname(__FILE__);
        chdir($moduleDir);
        ob_start();
        system('php ./composer.phar  install 2>&1');
        $output = ob_get_clean();
        chdir($currentDir);
        if (strstr($output, 'Generating autoload files') !== false) {
            return true;
        } else {
            return false;
        }
        return false;
    }
    
    public function setTabParams($tabClass = '', $tabName = [], $idTabParent = '')
    {
        if (!$tabClass || !$tabName || empty($idTabParent) || empty($this->name)) {
            return false;
        }
        $this->tabObject->name = $tabName;
        $this->tabObject->class_name = $tabClass;
        $this->tabObject->module = $this->name;
        $this->tabObject->id_parent = $idTabParent;
        $this->tabObject->active = 1;
        return true;
    }
    
    /**
     * @return bool
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        
        if (empty(parent::uninstall()) || empty($this->tabObject->delete())) {
            return false;
        }
        return true;
    }
    
    /**
     * @return bool|string
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitProductionshippingModule')) == true) {
            $this->saveFormData();
        }
        return $this->getOutput($this->teplateFolder.'admin/configure.tpl');
    }

    /**
     * @return bool
     */
    public function saveFormData()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            $this->updateConfigValue($key, Tools::getValue($key));
        }
        
        return true;
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function updateConfigValue($key, $value)
    {
        if ($this->testEnv == true) {
            $res = true;
        } else {
            if (!empty($value) && is_array($value)) {
                $value = serialize($value);
            }
            $res = Configuration::updateValue($key, $value);
        }
        return (bool) $res;
    }

    /**
     * @param string $pathToTeplate
     * @return bool|string
     * @throws Exception
     * @throws SmartyException
     */
    public function getOutput($pathToTeplate = '')
    {
        if (!is_file($pathToTeplate)) {
            return false;
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($pathToTeplate);
        return $output.$this->renderForm();
    }
    
    /**
     * Create the form that will be displayed in the configuration of your module.
     * 
     * @return mixed
     */
    public function renderForm()
    {
        $this->helperForm->show_toolbar = false;
        $this->helperForm->table = $this->table;
        $this->helperForm->module = $this;
        $this->helperForm->default_form_language = $this->context->language->id;
        $this->helperForm->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $this->helperForm->identifier = $this->identifier;
        $this->helperForm->submit_action = 'submitProductionshippingModule';
        $this->helperForm->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $this->helperForm->token = Tools::getAdminTokenLite('AdminModules');

        $this->helperForm->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        
        $configForm = $this->getConfigForm();
        return $this->helperForm->generateForm(array($configForm));
    }
    
    /**
     * Create the structure of your form.
     * 
     * @return array
     */
    public function getConfigForm()
    {        
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'label' => $this->l('Admin Email'),
                        'desc' => $this->l('Send copy of a "Manufacturer Report" to this email'),
                        'name' => 'ADMIN_EMAIL',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'label' => $this->l('Developer Email'),
                        'desc' => $this->l('Send cron.log to this email'),
                        'name' => 'DEV_EMAIL',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select  excluded order statuses'),
                        'desc' => $this->l('Orders with that statuses will be excluded from orders listing, by default'),
                        'name' => 'PRODUCTION_ORDER_STATES',
                        'class' => 'chosen',
                        'multiple' => true,
                        'options' => array(
                            'query' => OrderState::getOrderStates((int)$this->context->language->id),
                            'id' => 'id_order_state',
                            'name' => 'name'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
    
    /**
     * Set values for the inputs.
     * 
     * @return array
     */
    protected function getConfigFormValues()
    {
        $helperList = $this->container->get('helperList');
        $productionOrderStates = $helperList->getProductionOrderStates();
        
        return array(
            'ADMIN_EMAIL' => Configuration::get('ADMIN_EMAIL'),
            'DEV_EMAIL' => Configuration::get('DEV_EMAIL'),
            'PRODUCTION_ORDER_STATES' => $productionOrderStates,
        );
    }


    /**
     * @return bool
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }

    /**
     * @return bool
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}

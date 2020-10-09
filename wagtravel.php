<?php
/**
 * 2007-2020 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Wagtravel extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'wagtravel';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'WAG Travel';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('WAG Travel');
        $this->description = $this->l('Make your website smarter');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('WAGTRAVEL_CODE', null);
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayReassurance')
        ;
    }

    public function uninstall()
    {
        Configuration::deleteByName('WAGTRAVEL_CODE');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitWagtravelModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWagtravelModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
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
                        'desc' => $this->l('Enter a WAG Travel Code'),
                        'name' => 'WAGTRAVEL_CODE',
                        'label' => $this->l('Code'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'WAGTRAVEL_CODE' => Configuration::get('WAGTRAVEL_CODE', null)
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookDisplayHeader()
    {
        $this->context->smarty->assign([
                'wagtravelCode' => Configuration::get('WAGTRAVEL_CODE', null),
        ]);
        return $this->display(
            $this->getLocalPath(),
            'views/templates/tag.tpl'
        );
    }

    public function hookDisplayReassurance($product)
    {
        if ($id_product = (int)Tools::getValue('id_product')) {
            $product = new Product(
                $id_product,
                true,
                $this->context->language->id,
                $this->context->shop->id
            );

            $image = Product::getCover($id_product);
            $link = new Link();

            $this->context->smarty->assign([
                'product' => $product,
                'language' => $this->context->language,
                'url' => $this->context->link->getProductLink($product),
                'id_product_attribute' => (int)Tools::getValue('id_product_attribute'),
                'cover' => $link->getImageLink($product->link_rewrite, $image['id_image'], 'home_default')
            ]);
        }

        return $this->display(
            $this->getLocalPath(),
            'views/templates/datalayer.tpl'
        );
    }

    public function hookDisplayOrderConfirmation() {
        return $this->display(
            $this->getLocalPath(),
            'views/templates/orderConfirmation.tpl'
        );
    }
}

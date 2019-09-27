<?php
/**
 *  @author Yaroslav <yaroslav@wannabe.pro>
 *  @copyright  2019 QIWI
 *  @license    https://www.opensource.org/licenses/MIT  MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'load.php';

use Qiwi\ConfigManager;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Kassaqiwi extends PaymentModule
{
    private $configManager;

    public function __construct()
    {
        $this->name = 'kassaqiwi';
        $this->tab = 'payments_gateways';
        $this->version = '0.0.2';
        $this->module_key = 'f7b84666812c788ff4400fa49529f26d';
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->author = 'QIWI';
        $this->controllers = ['process', 'cancel', 'webhook'];
        $this->is_eu_compatible = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('QIWI Kassa');
        $this->description = $this->l('Payment over: VISA, MasterCard, MIR, Phone balance, QIWI Wallet.');

        // Since Prestashop do not use Dependency Injection, make sure that we can change which class that handle
        // certain behavior, so we can easily mock it in tests.
        $this->setConfigManager(new ConfigManager());
    }

    /**
     * Executes when installing module.
     * Validates that required hooks exists and initiate default values in the database.
     */
    public function install()
    {
        // If anything fails during installation, return false which will raise an error to the user.
        if (!parent::install() ||
            !$this->registerHook('paymentOptions') ||
            !$this->registerHook('paymentReturn') ||
            !$this->configManager->addFields([
                $this->l('QIWI Kassa: waiting'),
                $this->l('QIWI Kassa: paid'),
                $this->l('QIWI Kassa: rejected'),
                $this->l('QIWI Kassa: expired'),
                $this->l('QIWI Kassa: partial refunded'),
                $this->l('QIWI Kassa: full refunded'),
            ])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Executes when uninstalling the module.
     * Cleanup DB fields and raise error if something goes wrong.
     */
    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->configManager->deleteFields()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Hook in to the list of payment options on checkout page.
     *
     * @return PaymentOption[]
     */
    public function hookPaymentOptions()
    {
        if (!$this->active) {
            return;
        }

        $paymentOption = new PaymentOption();
        $paymentOption->setCallToActionText($this->l('Payment ower QIWI Kassa'))
            ->setAction($this->context->link->getModuleLink($this->name, 'process', [], true))
            ->setAdditionalInformation($this->context->smarty
                ->fetch('module:kassaqiwi/views/templates/front/payment_infos.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/payment.png'));
        $paymentOptions = [$paymentOption];

        return $paymentOptions;
    }

    public function hookDisplayPaymentEU()
    {
        $payment_options = [
            'cta_text' => $this->l('QIWI Kassa'),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/payment.png'),
            'action' => $this->context->link->getModuleLink($this->name, 'process', array(), true)
        ];

        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
    }

    /**
     * Module Configuration page controller.
     * Handle the form POST request and outputs the form.
     */
    public function getContent()
    {
        $output = null;
        if (Tools::isSubmit('update_settings_' . $this->name)) {
            Configuration::updateValue('QIWI_SECRET_KEY', (string) Tools::getValue('QIWI_SECRET_KEY'));
            Configuration::updateValue('QIWI_THEME_CODE', (string) Tools::getValue('QIWI_THEME_CODE'));
            Configuration::updateValue('QIWI_USE_POPUP', (bool) Tools::getValue('QIWI_USE_POPUP'));
            Configuration::updateValue('QIWI_LIVE_TIME', max(1, (int) Tools::getValue('QIWI_LIVE_TIME')));
            Configuration::updateValue('QIWI_USE_DEBUG', (bool) Tools::getValue('QIWI_USE_DEBUG'));
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Generates a HTML Form that is used on the module configuration page.
     */
    public function displayForm()
    {
        $this->context->smarty->assign([
            'qiwi_notification' => $this->context->link->getModuleLink($this->name, 'webhook', array(), true),
        ]);
        $description = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/description.tpl');
        $notification = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/notification.tpl');
        $fields_form = [0 => []];
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'description' => $description,
            'input' => [
                [
                    'type' => 'html',
                    'label' => $this->l('Notification address'),
                    'html_content' => $notification,
                    'desc' => $this->l('Set this value in the payment system store settings.'),
                    'name' => 'QIWI_NOTIFICATION',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Secret Key'),
                    'desc' => $this->l('The key to the payment system for your store.'),
                    'name' => 'QIWI_SECRET_KEY',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Theme style code'),
                    'desc' => $this->l('Personalization code of payment form style is presented in the payment system
                     store settings.'),
                    'name' => 'QIWI_THEME_CODE',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Live time'),
                    'desc' => $this->l('The live time for unpaid invoice in days.'),
                    'name' => 'QIWI_LIVE_TIME',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Debug mode'),
                    'desc' => $this->l('Log api requests.'),
                    'name' => 'QIWI_USE_DEBUG',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'debug_active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'debug_active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        $helper->submit_action = 'update_settings_' . $this->name;

        // Sets current value from DB to the form.
        $helper->fields_value['QIWI_SECRET_KEY'] = Configuration::get('QIWI_SECRET_KEY');
        $helper->fields_value['QIWI_THEME_CODE'] = Configuration::get('QIWI_THEME_CODE');
        $helper->fields_value['QIWI_USE_POPUP'] = Configuration::get('QIWI_USE_POPUP');
        $helper->fields_value['QIWI_LIVE_TIME'] = Configuration::get('QIWI_LIVE_TIME');
        $helper->fields_value['QIWI_USE_DEBUG'] = Configuration::get('QIWI_USE_DEBUG');

        return $helper->generateForm($fields_form);
    }

    public function setConfigManager($manager)
    {
        $this->configManager = $manager;
    }
}

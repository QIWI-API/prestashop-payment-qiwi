<?php
/**
 *  @author Yaroslav <yaroslav@wannabe.pro>
 *  @copyright  2019 QIWI
 *  @license    https://www.opensource.org/licenses/MIT  MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'load.php';

use Qiwi\Client;
use Qiwi\OrderManager;

/**
 * @property-read \Kassaqiwi $module
 */
class KassaqiwiProcessModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        // Check that payment module is active,
        // to prevent users from calling this controller when payment method is inactive.
        if (!$this->isModuleActive()) {
            die($this->module->l('This payment method is not available.', 'payment'));
        }

        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $total = OrderManager::getCartTotal($cart);

        $billId = $this->apiGetBillId();

        $this->module->validateOrder(
            $cart->id,
            Configuration::get('QIWI_STATUS_WAITING'),
            $total,
            $this->module->displayName,
            null,
            [
                'transaction_id' => $billId,
            ],
            (int)$cart->id_currency,
            false,
            $customer->secure_key
        );

        $bill = $this->apiCreateBill($billId, $cart);

        Tools::redirect($bill['payUrl']);
        die();
    }

    /**
     * Check if the current module is an active payment module.
     */
    public function isModuleActive()
    {
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'kassaqiwi') {
                $authorized = true;
                break;
            }
        }

        return $authorized;
    }

    public function apiGetBillId()
    {
        $client = new Client();
        return $client->generateId();
    }

    public function apiCreateBill($billId, $cart)
    {
        $products = array_map(function ($item) {
            return $item['cart_quantity'] . ' × ' . $item['name'];
        }, $cart->getProducts());

        $orderId = method_exists('Order', 'getOrderByCartId') ?
            Order::getOrderByCartId($cart->id) : Order::getIdByCartId($cart->id);

        $client = new Client(Configuration::get('QIWI_SECRET_KEY'));

        $params = [
            'amount'             => OrderManager::getCartTotal($cart),
            'currency'           => OrderManager::getCurrencyIsoById($cart->id_currency),
            'comment'            => join($products, ', '),
            'expirationDateTime' => $client->getLifetimeByDay((int) Configuration::get('QIWI_LIVE_TIME')),
            'account'            => $cart->id_customer,
            'successUrl'         => OrderManager::getOrderConfirmationUrl($this->context, $cart->id, $this->module->id),
            'customFields'       => [
                'themeCode' => Configuration::get('QIWI_THEME_CODE'),
                'orederId' => $orderId,
                'cartId' => $cart->id,
            ],
        ];

        $error = null;
        try {
            $bill = $client->createBill($billId, $params);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if (Configuration::get('QIWI_USE_DEBUG')) {
            PrestaShopLogger::addLog('QIWI create bill: ' . json_encode([
                    'billId' => $billId,
                    'params' => $params,
                    'result' => $bill,
                    'error' => $error
                ]));
        }

        return $bill;
    }
}

<?php
/**
 *  @author Yaroslav <yaroslav@wannabe.pro>
 *  @copyright  2019 QIWI
 *  @license    https://www.opensource.org/licenses/MIT  MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Qiwi\Client;

/**
 * @property-read \Kassaqiwi $module
 */
class KassaqiwiWebhookModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $sign = null;
        $body = null;
        $client = new Client();
        try {
            $sign = array_key_exists('HTTP_X_API_SIGNATURE_SHA256', $_SERVER)
                ? Tools::stripslashes($_SERVER['HTTP_X_API_SIGNATURE_SHA256'])
                : '';
            $body = Tools::file_get_contents('php://input');
            $notice = json_decode($body, true);
            if ($client->checkNotificationSignature($sign, $notice, Configuration::get('QIWI_SECRET_KEY'))) {
                $orderId = (int) $notice['bill']['customFields']['orederId'];
                $cartId = (int) $notice['bill']['customFields']['cartId'];
                $order = new Order($orderId);
                $status = $this->getStatusByTimeLine($notice['bill']['status']['value']);

                if (!$order || $order->id_cart != $cartId) {
                    throw new Exception('Order not exists');
                }

                if (is_null($status)) {
                    throw new Exception('Invalid status');
                }

                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->changeIdOrderState((int) Configuration::get($status), $order);
            } else {
                throw new Exception('Check notification signature fail.');
            }
        } catch (Exception $exception) {
            $this->error['warning'] = $exception->getMessage();
        }

        if (Configuration::get('QIWI_USE_DEBUG')) {
            $message = 'QIWI notification' . PHP_EOL;
            $message .= '- signature: ' . $sign . PHP_EOL;
            $message .= '- body: ' . $body . PHP_EOL;
            if (isset($this->error['warning'])) {
                $message .= '- error: ' . $this->error['warning'];
            } else {
                $message .= '- success';
            }
            PrestaShopLogger::addLog($message);
        }

        header('Content-Type: application/json');
        die(json_encode(['error' => count($this->error)]));
    }

    private function getStatusByTimeLine($timeline)
    {
        switch ($timeline) {
            case 'WAITING':
                return 'QIWI_STATUS_WAITING';
            case 'PAID':
                return 'QIWI_STATUS_PAID';
            case 'REJECTED':
                return 'QIWI_STATUS_REJECTED';
            case 'EXPIRED':
                return 'QIWI_STATUS_EXPIRED';
            case 'PARTIAL':
                return 'QIWI_STATUS_PARTIAL_REFUNDED';
            case 'FULL':
                return 'QIWI_STATUS_FULL_REFUNDED';
            default:
                return null;
        }
    }
}

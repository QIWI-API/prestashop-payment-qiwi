<?php
/*
*  @author Yaroslav <yaroslav@wannabe.pro>
*  @copyright  2019 QIWI
*  @license    https://www.opensource.org/licenses/MIT  MIT License
*/

namespace Qiwi;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Currency;
use Cart;
use Customer;

/**
 * Wrapper to interact with the order/cart data.
 */
class OrderManager
{
    public static function getCurrencyIsoById($currencyId)
    {
        $currency = new Currency($currencyId);
        return $currency->iso_code;
    }

    public static function getOrderConfirmationUrl($context, $cartId, $moduleId, $secureKey = null)
    {
        $secureKey = empty($secureKey) ? $context->customer->secure_key : $secureKey;

        return $context->shop->getBaseURL(true) . 'index.php?' . http_build_query([
                'controller' => 'order-confirmation',
                'id_cart' => $cartId,
                'id_module' => $moduleId,
                'key' => $secureKey
            ]);
    }

    public static function getOrderCancelUrl($context, $moduleName)
    {
        return $context->link->getModuleLink($moduleName, 'cancel');
    }

    public static function getCartById($cartId)
    {
        return new Cart($cartId);
    }

    public static function getCustomerById($customerId)
    {
        return new Customer($customerId);
    }

    public static function getCartTotal($cart)
    {
        return $cart->getOrderTotal(true, Cart::BOTH);
    }
}

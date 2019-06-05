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

use Configuration;
use OrderState;

/**
 * For testing purposes we wrap the Configuration in a wrapper class so that we can easily mock it.
 */
class ConfigManager
{
    public function addFields($names = [
        'QIWI Kassa: waiting',
        'QIWI Kassa: paid',
        'QIWI Kassa: rejected',
        'QIWI Kassa: expired',
        'QIWI Kassa: partial refunded',
        'QIWI Kassa: full refunded',
    ])
    {
        $orderWaiting = $this->createOrderStatus($names[0], '#0042FF');
        $orderPaid = $this->createOrderStatus($names[1], '#D3DE00');
        $orderRejected = $this->createOrderStatus($names[2], '#E64A0C');
        $orderExpired = $this->createOrderStatus($names[3], '#933008');
        $orderPartialRefunded = $this->createOrderStatus($names[4], '#533F2E');
        $orderFullRefunded = $this->createOrderStatus($names[5], '#756558');

        if (Configuration::updateValue('QIWI_SECRET_KEY', '')
            && Configuration::updateValue('QIWI_THEME_CODE', '')
            && Configuration::updateValue('QIWI_USE_POPUP', false)
            && Configuration::updateValue('QIWI_LIVE_TIME', 40)
            && Configuration::updateValue('QIWI_USE_DEBUG', false)
            && Configuration::updateValue('QIWI_STATUS_WAITING', $orderWaiting->id)
            && Configuration::updateValue('QIWI_STATUS_PAID', $orderPaid->id)
            && Configuration::updateValue('QIWI_STATUS_REJECTED', $orderRejected->id)
            && Configuration::updateValue('QIWI_STATUS_EXPIRED', $orderExpired->id)
            && Configuration::updateValue('QIWI_STATUS_PARTIAL_REFUNDED', $orderPartialRefunded->id)
            && Configuration::updateValue('QIWI_STATUS_FULL_REFUNDED', $orderFullRefunded->id)
        ) {
            return true;
        }

        return false;
    }

    public function createOrderStatus($name, $color)
    {
        $order = new OrderState();
        $order->name = array_fill(0, 10, $name);
        $order->send_email = 0;
        $order->invoice = 0;
        $order->color = $color;
        $order->unremovable = false;
        $order->logable = 1;
        $order->add();

        return $order;
    }

    public function deleteFields()
    {
        $orderWaiting = new OrderState(Configuration::get('QIWI_STATUS_WAITING'));
        $orderPaid = new OrderState(Configuration::get('QIWI_STATUS_PAID'));
        $orderRejected = new OrderState(Configuration::get('QIWI_STATUS_REJECTED'));
        $orderExpired = new OrderState(Configuration::get('QIWI_STATUS_EXPIRED'));
        $orderPartialRefunded = new OrderState(Configuration::get('QIWI_STATUS_PARTIAL_REFUNDED'));
        $orderFullRefunded = new OrderState(Configuration::get('QIWI_STATUS_FULL_REFUNDED'));

        if (Configuration::deleteByName('QIWI_SECRET_KEY')
            && Configuration::deleteByName('QIWI_THEME_CODE')
            && Configuration::deleteByName('QIWI_USE_POPUP')
            && Configuration::deleteByName('QIWI_LIVE_TIME')
            && Configuration::deleteByName('QIWI_USE_DEBUG')
            && Configuration::deleteByName('QIWI_STATUS_WAITING')
            && Configuration::deleteByName('QIWI_STATUS_PAID')
            && Configuration::deleteByName('QIWI_STATUS_REJECTED')
            && Configuration::deleteByName('QIWI_STATUS_EXPIRED')
            && Configuration::deleteByName('QIWI_STATUS_PARTIAL_REFUNDED')
            && Configuration::deleteByName('QIWI_STATUS_FULL_REFUNDED')
            && $orderWaiting->delete()
            && $orderPaid->delete()
            && $orderRejected->delete()
            && $orderExpired->delete()
            && $orderPartialRefunded->delete()
            && $orderFullRefunded->delete()
        ) {
            return true;
        }

        return false;
    }
}

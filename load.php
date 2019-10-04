<?php
/**
 *  @author Yaroslav <yaroslav@wannabe.pro>
 *  @copyright  2019 QIWI
 *  @license    https://www.opensource.org/licenses/MIT  MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('CLIENT_NAME')) {
    /**
     * The client name fingerprint.
     *
     * @const string
     */
    define('CLIENT_NAME', 'prestashop');
}

if (!defined('CLIENT_VERSION')) {
    /**
     * The client version fingerprint.
     *
     * @const string
     */
    define('CLIENT_VERSION', '0.0.3');
}

// Autoload for standalone composer build.
if (!class_exists('Curl\Curl')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'curl' . DIRECTORY_SEPARATOR . 'curl' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Curl' . DIRECTORY_SEPARATOR . 'Curl.php';
}

if (!class_exists('Qiwi\Api\BillPaymentsException')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'qiwi' . DIRECTORY_SEPARATOR . 'bill-payments-php-sdk' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'BillPaymentsException.php';
}

if (!class_exists('Qiwi\Api\BillPayments')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'qiwi' . DIRECTORY_SEPARATOR . 'bill-payments-php-sdk' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'BillPayments.php';
}

if (!class_exists('Qiwi\Client') ) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Client.php';
}

if (!class_exists('Qiwi\ConfigManager') ) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'ConfigManager.php';
}

if (!class_exists('Qiwi\OrderManager') ) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'OrderManager.php';
}

<?php
/**
 *  @author Yaroslav <yaroslav@wannabe.pro>
 *  @copyright  2019 QIWI
 *  @license    https://www.opensource.org/licenses/MIT  MIT License
 */

namespace Qiwi;

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
    define('CLIENT_VERSION', '0.0.1');
}

use Qiwi\Api\BillPayments;

/**
 * @inheritDoc
 */
class Client extends BillPayments
{
    /**
     * @inheritDoc
     */
    public function __construct($key = '', $options = [])
    {
        // Setup CURL options.
        $ca_path = realpath('../cacert.pem');
        if (is_file($ca_path)) {
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_SSL_VERIFYHOST] = 2;
            $options[CURLOPT_CAINFO] = $ca_path;
        }

        parent::__construct($key, $options);
    }
}

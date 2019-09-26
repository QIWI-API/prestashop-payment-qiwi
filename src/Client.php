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

<?php
/*
*  @author Yaroslav <yaroslav@wannabe.pro>
*  @copyright  2019 QIWI
*  @license    https://www.opensource.org/licenses/MIT  MIT License
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class KassaqiwiCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        Tools::redirect('index.php?controller=order&step=1');
    }
}

<style>
    p.payment_module a.qiwi:after {
        display: block;
        content: "\f054";
        position: absolute;
        right: 15px;
        margin-top: -11px;
        top: 50%;
        font-family: "FontAwesome";
        font-size: 25px;
        height: 22px;
        width: 14px;
        color: #777;
    }
</style>

<p class="payment_module">
    <a class="qiwi" href="{$link->getModuleLink('qiwi', 'process', [], true)|escape:'html'}" title="{l s='Pay by QIWI Kassa' mod='qiwi'}">
        {l s='Pay by QIWI Kassa' mod='qiwi'} <span>{l s='(Payment over: VISA, MasterCard, MIR, Phone balance, QIWI Wallet)' mod='qiwi'}</span>
    </a>
</p>

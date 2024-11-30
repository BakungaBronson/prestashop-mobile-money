{if $transaction}
<table class="product" width="100%" cellpadding="4" cellspacing="0">
    <tr>
        <td style="width: 50%">{l s='Mobile Money Payment' mod='mobilemoney'}</td>
        <td style="width: 50%">{$transaction}</td>
    </tr>
</table>
{/if}
{extends file='page.tpl'}
{block name='page_content'}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{l s='Mobile Money Payment' mod='mobilemoney'}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4>{l s='Total Amount:' mod='mobilemoney'} {$amount}</h4>
                <p>{l s='Please select your mobile money provider below:' mod='mobilemoney'}</p>
                
                <div class="payment-options">
                    <div class="payment-option">
                        <input type="radio" name="payment_option" id="mtn_money" value="mtn" checked>
                        <label for="mtn_money">MTN Mobile Money</label>
                    </div>
                    <div class="payment-option">
                        <input type="radio" name="payment_option" id="airtel_money" value="airtel">
                        <label for="airtel_money">Airtel Money</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div id="mtn_qr_container" class="qr-container">
                    {if $mtn_qr}
                        <img src="{$mtn_qr}" alt="MTN Mobile Money QR Code" class="img-fluid">
                    {else}
                        <p class="alert alert-warning">{l s='MTN QR Code not configured' mod='mobilemoney'}</p>
                    {/if}
                </div>
                <div id="airtel_qr_container" class="qr-container" style="display: none;">
                    {if $airtel_qr}
                        <img src="{$airtel_qr}" alt="Airtel Money QR Code" class="img-fluid">
                    {else}
                        <p class="alert alert-warning">{l s='Airtel QR Code not configured' mod='mobilemoney'}</p>
                    {/if}
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <p>{l s='Instructions:' mod='mobilemoney'}</p>
                <ol>
                    <li>{l s='Scan the QR code with your mobile money app' mod='mobilemoney'}</li>
                    <li>{l s='Complete the payment in your app' mod='mobilemoney'}</li>
                    <li>{l s='Click "I have paid" below once payment is complete' mod='mobilemoney'}</li>
                </ol>
                
                <form action="{$validation_url}" method="post" class="text-center">
                    <button type="submit" class="btn btn-primary">
                        {l s='I have paid' mod='mobilemoney'}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const mtnRadio = document.getElementById('mtn_money');
    const airtelRadio = document.getElementById('airtel_money');
    const mtnContainer = document.getElementById('mtn_qr_container');
    const airtelContainer = document.getElementById('airtel_qr_container');
    
    function updateQRDisplay() {
        if (mtnRadio.checked) {
            mtnContainer.style.display = 'block';
            airtelContainer.style.display = 'none';
        } else {
            mtnContainer.style.display = 'none';
            airtelContainer.style.display = 'block';
        }
    }
    
    mtnRadio.addEventListener('change', updateQRDisplay);
    airtelRadio.addEventListener('change', updateQRDisplay);
});
</script>

<style>
.payment-options {
    margin: 20px 0;
}
.payment-option {
    margin: 10px 0;
}
.qr-container {
    max-width: 300px;
    margin: 0 auto;
    text-align: center;
}
.qr-container img {
    max-width: 100%;
    height: auto;
}
</style>
{/block}
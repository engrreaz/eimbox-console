<?php
include_once("header.php");

echo '<pre>' . var_dump($_SESSION) . '</pre>';

$stid = '1031873512';
$payerReference = 'Payment for ' . $stid;
$amount = 10;
?>

<!-- <script id="myScript" src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script> -->
<script src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>


<button id="bKash_button">
    Bkash Pay
</button>

<?php include_once("footer.php"); ?>

<script type="text/javascript">

    var accessToken = '';
    $(document).ready(function () {
        $.ajax({
            url: "bkash/token.php",
            type: 'POST',
            contentType: 'application/json',
            success: function (data) {

                console.log(data);
                accessToken = JSON.stringify(data);
                console.log(accessToken);

            },
            error: function () {
                console.log('error');

            }
        });

        var paymentConfig = {
            createCheckoutURL: "bkash/createpayment.php",
        };

        var amount = <?= $amount; ?>;
        var payerReference = '<?= $payerReference; ?>';

        // alert(payerReference);

        var paymentRequest;
        var cBURL = '<?= BASEURL; ?>payment_confirm_check.php';
        paymentRequest = { mode: '0011', payerReference: payerReference, callbackURL: cBURL, amount: amount, currency: 'BDT', intent: 'sale' };

        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: paymentRequest,
            createRequest: function (request) {

                $.ajax({
                    url: paymentConfig.createCheckoutURL + "?amount=" + paymentRequest.amount + "&currency=" + paymentRequest.currency + "&intent=" + paymentRequest.intent + "&mode=" + paymentRequest.mode + "&payerReference=" + paymentRequest.payerReference + "&callbackURL=" + paymentRequest.callbackURL,
                    type: 'GET',
                    contentType: 'application/json',
                    success: function (data) {

                        var obj = JSON.parse(data);

                        console.log(obj);

                        if (data && obj.paymentID != null) {
                            paymentID = obj.paymentID;
                            bkashURL = obj.bkashURL;
                            window.location.href = bkashURL;

                        }
                        else {
                            console.log('error');
                            bKash.create().onError();
                        }
                    },
                    error: function () {
                        console.log('error');
                        bKash.create().onError();
                    }
                });
            }

        });
    });

    function callReconfigure(val) {
        bKash.reconfigure(val);
    }

    function clickPayButton() {
        $("#bKash_button").trigger('click');
    }


</script>

</body>

</html>
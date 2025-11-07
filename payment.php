<?php

// validate user session 
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment - <?php echo $website['site_name']; ?></title>
  <meta name="description" content="">
  <meta name="keywords" content="">
  <link rel="icon" type="image/png" sizes="120x120" href="">
  <link rel="stylesheet" href="">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/toastr.min.js"></script>
  <!-- <script id="myScript" src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script> -->
    <script src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>

<style>
    th, td {
        padding:5px;
    }
</style>
</head>

<body>



    <!-- Pay Now Button -->
      <button id="bKash_button">
       Bkash Pay
      </button>

  </div>


  <script type="text/javascript">

            var accessToken='';
            $(document).ready(function(){
                $.ajax({
                    url: "bkash/token.php",
                    type: 'POST',
                    contentType: 'application/json',
                    success: function (data) {

                        accessToken=JSON.stringify(data);
                        console.log(accessToken);
                        
                    },
                    error: function(){
                        console.log('error');

                    }
                });

                var paymentConfig={
                    createCheckoutURL:"bkash/createpayment.php",
                };

                var amount = 20;
                var payerReference = '8afisd923ufasopdh';

                alert(payerReference);

                var paymentRequest;
                var cBURL = 'http://localhost/bkashpgw/payment_confirm_check.php';
                paymentRequest = {mode:'0011', payerReference: payerReference, callbackURL:cBURL, amount:amount, currency:'BDT',intent:'sale'};

                bKash.init({
                    paymentMode: 'checkout',
                    paymentRequest: paymentRequest,
                    createRequest: function(request){

                        $.ajax({
                            url: paymentConfig.createCheckoutURL+"?amount="+paymentRequest.amount+"&currency="+paymentRequest.currency+"&intent="+paymentRequest.intent+"&mode="+paymentRequest.mode+"&payerReference="+paymentRequest.payerReference+"&callbackURL="+paymentRequest.callbackURL,
                            type:'GET',
                            contentType: 'application/json',
                            success: function(data) {
                                
                                var obj = JSON.parse(data);

                                console.log(obj);

                                if(data && obj.paymentID != null){
                                    paymentID = obj.paymentID;
                                    bkashURL = obj.bkashURL;
                                    window.location.href = bkashURL;
                                    
                                }
                                else {
                                    console.log('error');
                                    bKash.create().onError();
                                }
                            },
                            error: function(){
                                console.log('error');
                                bKash.create().onError();
                            }
                        });
                    }

                });
            });

            function callReconfigure(val){
                bKash.reconfigure(val);
            }

            function clickPayButton(){
                $("#bKash_button").trigger('click');
            }


        </script>

</body>

</html>
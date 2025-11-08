var version;
var createURL;
var executeagreementURL;
var executeURL;
var cancelagreementURL;
var searchTransactionURL;
var refundURL;
var queryagreementURL;
var authCaptureURL;
var authVoidURL;
var paymentStatusURL;
var tokenizedExecutePaymentURL;
var callbackUrlForTokenizedLegacy;
var callbackUrlForTokenizedNew;
var amount;

var tokenizedInitiatePayoutB2bURL;
var tokenizedB2bPayoutURL;
var tokenizedQueryPayoutURL;

var allApiEndpoints;


$(document).ready(function () {
    var currentUrl = new URL(window.location.href);
    var path = currentUrl.pathname.split('/');
    version = path[path.length - 1]
    console.log('version:: ');
    console.log(version);
    document.getElementById('apiVersion').innerHTML = version;

    //$('#agreementchkbox').checked(true)
    $("#dvcreateagreement").show("slow");
    $('#changeAmountDivCheckout').hide("slow");
    $('#changeAmountDivAgreement').hide("slow");
    $('#isAggregratorDiv').hide("slow");
    $("#redirectUrlForPayDiv").hide("slow");
    document.getElementById("merchantAssociationInfoCheckout").value = "";
    document.getElementById("merchantAssociationInfoAgreement").value = "";
    $("#dvAggregratorInfoCheckout").hide();
    $("#dvAggregratorInfoQueryAgreement").hide();
    $("#isAggregrator").prop('checked', false);
    $('#isAuthDiv').hide("slow");
    $("#isAuth").prop('checked', false);
    $("#intentDiv").hide("slow");
    $("#dvagreementpay").hide("slow");
    $("#dvqueryagreement").hide("slow");
    $("#dvcheckout").hide("slow");

    merchantBackendURLFetch()
        .done(function (merchantBackendObj) {
            for (var key in merchantBackendObj) {
                console.log(key, merchantBackendObj[key]);
                merchantBackendObj[key] = merchantBackendObj[key].replace("XXX", 'v' + version);
            }
            console.log('merchantBackendObj:: ');
            console.log(merchantBackendObj);
            executeagreementURL = merchantBackendObj.executeagreementURL;
            console.log('executeagreementURL: ' + executeagreementURL);
            cancelagreementURL = merchantBackendObj.cancelagreementURL;
            console.log('cancelagreementURL: ' + cancelagreementURL);
            searchTransactionURL = merchantBackendObj.searchTransactionURL;
            console.log('searchTransactionURL: ' + searchTransactionURL);
            refundURL = merchantBackendObj.refundURL;
            console.log('refundURL: ' + refundURL);
            authCaptureURL = merchantBackendObj.authCaptureURL;
            console.log('authCaptureURL: ' + authCaptureURL);
            authVoidURL = merchantBackendObj.authVoidURL;
            console.log('authVoidURL: ' + authVoidURL);
            queryagreementURL = merchantBackendObj.queryagreementURL;
            console.log('queryagreementURL: ' + queryagreementURL);
            callbackUrlForTokenizedLegacy = merchantBackendObj.pageURLForTokenizedStatus;
            callbackUrlForTokenizedNew = merchantBackendObj.pageURLForTokenizedCallback;
            createURL = merchantBackendObj.createURL;
            executePaymentURL = merchantBackendObj.executeTokenizedPaymentURL;
            executeURL = merchantBackendObj.executeURL;
            paymentStatusURL = merchantBackendObj.paymentStatusURL;
            console.log('paymentStatusURL: ' + paymentStatusURL);

            tokenizedInitiatePayoutB2bURL = merchantBackendObj.tokenizedInitiatePayoutB2bURL;
            console.log('tokenizedInitiatePayoutB2bURL: ' + tokenizedInitiatePayoutB2bURL);
            tokenizedB2bPayoutURL = merchantBackendObj.tokenizedB2bPayoutURL;
            console.log('tokenizedB2bPayoutURL: ' + tokenizedB2bPayoutURL);
            tokenizedQueryPayoutURL = merchantBackendObj.tokenizedQueryPayoutURL;
            console.log('tokenizedQueryPayoutURL: ' + tokenizedQueryPayoutURL);

            // get all endpoints
            commonAjaxGET(merchantBackendObj.apiPathFetchUrl)
                .done(function (response) {
                    allApiEndpoints = response;
                    console.log(allApiEndpoints);
                })
                .fail(function (error) {
                    console.log(error);
                });

        })
        .fail(function (error) {
            console.log('error:: ');
            console.log(error);
        });


    $('#agreementchkbox').click(function () {
        clearViewBoard();
        $("#dvcreateagreement").show("slow");
        $('#changeAmountDivCheckout').hide("slow");
        $('#changeAmountDivAgreement').hide("slow");
        $('#isAggregratorDiv').hide("slow");
        $("#redirectUrlForPayDiv").hide("slow");
        document.getElementById("merchantAssociationInfoCheckout").value = "";
        document.getElementById("merchantAssociationInfoAgreement").value = "";
        $("#dvAggregratorInfoQueryAgreement").hide();
        $("#dvAggregratorInfoCheckout").hide();
        $("#isAggregrator").prop('checked', false);
        $('#isAuthDiv').hide("slow");
        $("#intentDiv").hide("slow");
        $("#dvagreementpay").hide("slow");
        $("#dvqueryagreement").hide("slow");
        $("#dvcheckout").hide("slow");
    });

    $('#paymentchkbox').click(function () {
        clearViewBoard();
        amount = (Math.random() * 100).toFixed(2);
        console.log("Amount: " + amount);
        $('#new_amount_agreement').val(amount);
        $('#new_amount_checkout').val(amount);
        $('#changeAmountDivAgreement').show("slow");
        $('#changeAmountDivCheckout').show("slow");
        $("#dvcreateagreement").hide("slow");
        $('#isAggregratorDiv').show("slow");
        $('#isAuthDiv').show("slow");
        $("#redirectUrlForPayDiv").show("slow");
        $("#dvagreementpay").show("slow");
        $("#intentDiv").show("slow");
        $("#dvqueryagreement").hide("slow");
        $("#dvcheckout").hide("slow");

    });

    $('#checkoutchkbox').click(function () {
        clearViewBoard();
        amount = (Math.random() * 100).toFixed(2);
        console.log("Amount: " + amount);
        $('#new_amount_agreement').val(amount);
        $('#new_amount_checkout').val(amount);
        $('#changeAmountDivAgreement').show("slow");
        $('#changeAmountDivCheckout').show("slow");
        $("#dvcheckout").show("slow");
        $("#dvcreateagreement").hide("slow");
        $('#isAuthDiv').show("slow");
        $('#isAggregratorDiv').show("slow");
        $("#redirectUrlForPayDiv").hide("slow");
        $("#intentDiv").show("slow");
        $("#dvagreementpay").hide("slow");
        $("#dvqueryagreement").hide("slow");
    });

    $('#utilitychkbox').click(function () {
        clearViewBoard();
        $("#dvcreateagreement").hide("slow");
        $('#isAggregratorDiv').hide("slow");
        $('#changeAmountDivCheckout').hide("slow");
        $('#changeAmountDivAgreement').hide("slow");
        $("#redirectUrlForPayDiv").hide("slow");
        document.getElementById("merchantAssociationInfoCheckout").value = "";
        document.getElementById("merchantAssociationInfoAgreement").value = "";
        $("#dvAggregratorInfoQueryAgreement").hide();
        $("#dvAggregratorInfoCheckout").hide();
        $('#isAuthDiv').hide("slow");
        $("#isAggregrator").prop('checked', false);
        $("#intentDiv").hide("slow");
        $("#dvagreementpay").hide("slow");
        $("#dvqueryagreement").show("slow");
        $("#dvcheckout").hide("slow");
    });


    $("#isAggregrator").click(function () {
        clearViewBoard();
        if ($(this).is(":checked")) {
            $("#dvAggregratorInfoQueryAgreement").show();
            $("#dvAggregratorInfoCheckout").show();
        } else {
            document.getElementById("merchantAssociationInfoCheckout").value = "";
            document.getElementById("merchantAssociationInfoAgreement").value = "";
            $("#dvAggregratorInfoCheckout").hide();
            $("#dvAggregratorInfoQueryAgreement").hide();
        }
    });
    $("#isAuth").click(function () {
        clearViewBoard();
        if ($(this).is(":checked")) {
            $("#isSaleOrAuth").text("Intent: authorization");
        } else {
            $("#isSaleOrAuth").text("Intent: sale");
        }
    });


    $("#change_amount_agreement").click(function () {
        clearViewBoard();
        console.log('Change Amount Agreement Clicked');
        var amountRegex = /^\d+(\.\d{2})?$/;
        var new_amount = $("#new_amount_agreement").val();

        if (amountRegex.test(new_amount)) {
            amount = new_amount;
            $('#amount').text(new_amount);
        }
    });

    $("#change_amount_checkout").click(function () {
        clearViewBoard();
        console.log('Change Amount Checkout Clicked');
        var amountRegex = /^\d+(\.\d{2})?$/;
        var new_amount = $("#new_amount_checkout").val();

        if (amountRegex.test(new_amount)) {
            amount = new_amount;
            $('#amount').text(new_amount);
        }
    });

    $('#bKash_button').click(function () {
        clearViewBoard();
        var payerReference = document.getElementById('payerReference').value;
        payerReference = payerReference.replace(/</g, "&lt;").replace(/>/g, "&gt;");

        var callbackURL = callbackUrlForTokenizedNew;


        var mode = "0000";
        var agreementId = null;
        var amount = null;
        create(payerReference, agreementId, merchantAssociationInfo = null, intent = null, amount, callbackURL, mode, createURL)
            .done(function (response) {
                console.log('Create response under done:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                document.getElementById('redirectBkashTokenize').innerText = "";
                var redirectUrl = response.bkashURL;
                if (redirectUrl) {
                    $("#redirectUrlDiv").show("slow");
                    $('<a target="_blank" href="' + redirectUrl + '">' + redirectUrl + '</a>').appendTo($('#redirectBkashTokenize'));
                }
                alertify.success('Create response found');
                console.log('TokenizePay redirectURL:: ' + redirectUrl);
            })
            .fail(function (error) {
                console.log('create API error:: ');
                console.log(error);
                alertify.error('Failed Create');
            });
    });


    $('#cancel_agreement_btn').click(function () {
        clearViewBoard();
        var agreementId = document.getElementById('agreementIdForCancel').value;
        agreementId = agreementId.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        console.log('cancelAgreement response:: agreementId' + agreementId);
        cancelAgreementResponse(agreementId, cancelagreementURL)
            .done(function (response) {
                console.log('cancelAgreement response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Cancel Agreement response found');
            })
            .fail(function (error) {
                console.log('cancelAgreement error:: ');
                console.log(error);
                alertify.error('Failed Cancel Agreement');
            });

    });


    //Search Transaction Code Starts

    $('#searchTransaction_btn').click(function () {
        clearViewBoard();
        var transactionID = document.getElementById('searchTransaction').value;
        transactionID = transactionID.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        console.log('Search Transaction response:: transactionID' + transactionID + " Search Transaction URL: " + searchTransactionURL);
        searchTransactionResponse(transactionID, searchTransactionURL)
            .done(function (response) {
                console.log('Search Transaction response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Search transaction response found');
            })
            .fail(function (error) {
                console.log('Search Transaction error:: ');
                console.log(error);
                alertify.error('Failed Search Transaction');
            });

    });

    //Search Transaction Code ends


    //Refund Code Starts

    $('#refund_btn').click(function () {
        clearViewBoard();
        var transactionID = document.getElementById('transactionIdForRefund').value;
        transactionID = transactionID.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var paymentID = document.getElementById('paymentIdForRefund').value;
        paymentID = paymentID.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var amount = document.getElementById('amountForRefund').value;
        amount = amount.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var sku = document.getElementById('skuForRefund').value;
        sku = sku.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var reason = document.getElementById('reasonForRefund').value;
        reason = reason.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        console.log('Refund response:: transactionID' + transactionID + " Refund URL: " + refundURL);
        refundResponse(transactionID, paymentID, amount, sku, reason, refundURL)
            .done(function (response) {
                console.log('Refund response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Refund response found');
            })
            .fail(function (error) {
                console.log('Refund error:: ');
                console.log(error);
                alertify.error('Failed Refund');
            });

    });

    //Refund code ends

    $('#query_agreement_btn').click(function () {
        clearViewBoard();
        var agreementId = document.getElementById('agreementIdForquery').value;
        console.log('queryAgreement response:: agreementId' + agreementId);
        queryAgreementResponse(agreementId, queryagreementURL)
            .done(function (response) {
                console.log('queryAgreement response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Query Agreement response found');
            })
            .fail(function (error) {
                console.log('queryAgreement error:: ');
                console.log(error);
                alertify.error('Failed Query Agreement');
            });

    });

    $('#auth_capture_btn').click(function () {
        clearViewBoard();
        var paymentId = document.getElementById('authCaptureVoid').value;
        console.log('auth_capture_btn response:: paymentId' + paymentId);
        authCaptureVoidResponse(paymentId, authCaptureURL)
            .done(function (response) {
                console.log('auth Capture response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Auth Capture response found');
            })
            .fail(function (error) {
                console.log('authCapture error:: ');
                console.log(error);
                alertify.error('Failed Auth Capture');

            })
    })

    $('#auth_void_btn').click(function () {
        clearViewBoard();
        var paymentId = document.getElementById('authCaptureVoid').value;
        console.log('auth_void_btn response:: paymentId' + paymentId);
        authCaptureVoidResponse(paymentId, authVoidURL)
            .done(function (response) {
                console.log('auth Void response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Auth Void response found');
            })
            .fail(function (error) {
                console.log('authVoid error:: ');
                console.log(error);
                alertify.error('Failed Auth Void');

            })
    })

    $('#pay_agreement_btn').click(function () {
        clearViewBoard();
        var agreementId = document.getElementById('agreementIdForPayment').value;
        agreementId = agreementId.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        console.log('CreatePayment Request:: agreementId' + agreementId);
        var callbackURL = callbackUrlForTokenizedNew;

        var merchantAssociationInfo;
        var intent;

        if ($(isAggregrator).is(":checked")) {
            merchantAssociationInfo = document.getElementById('merchantAssociationInfoAgreement').value;
            merchantAssociationInfo = merchantAssociationInfo.replace(/</g, "&lt;").replace(/>/g, "&gt;");
            console.log('CreatePayment Request:: merchantAssociationInfo' + merchantAssociationInfo);
        } else merchantAssociationInfo = null;
        if ($(isAuth).is(":checked")) {
            intent = 'authorization';
        } else intent = 'sale';

        if (amount == null) {
            amount = 10;
        }
        console.log('changed amount:: ' + amount);
        var mode = "0001";
        var payerReference = null;
        create(payerReference, agreementId, merchantAssociationInfo, intent, amount, callbackURL, mode, createURL)
            .done(function (response) {
                console.log('Create response under done:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                document.getElementById('redirectTokPay').innerText = "";
                var redirectUrlForPay = response.bkashURL;
                if (redirectUrlForPay) {
                    $("#redirectUrlForPayDiv").show("slow");
                    $('<a target="_blank" href="' + redirectUrlForPay + '">' + redirectUrlForPay + '</a>').appendTo($('#redirectTokPay'));
                }
                alertify.success('Create response found');
                console.log('TokenizePay redirectURL:: ' + redirectUrlForPay);
            })
            .fail(function (error) {
                console.log('create API error:: ');
                console.log(error);
                alertify.error('Failed Create');
            });
    });

    $('#pay_checkout_btn').click(function () {
        clearViewBoard();
        var payerReference = document.getElementById('payerReferenceCheckout').value;
        payerReference = payerReference.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var callbackURL = callbackUrlForTokenizedNew;
        if (amount == null) {
            amount = 10;
        }
        console.log('changed amount :: ' + amount);
        var merchantAssociationInfo;

        if ($(isAggregrator).is(":checked")) {
            merchantAssociationInfo = document.getElementById('merchantAssociationInfoCheckout').value;
            merchantAssociationInfo = merchantAssociationInfo.replace(/</g, "&lt;").replace(/>/g, "&gt;");
            console.log('CreatePayment Request:: merchantAssociationInfo' + merchantAssociationInfo);
        } else merchantAssociationInfo = null;

        if ($(isAuth).is(":checked")) {
            intent = 'authorization';
        } else intent = 'sale';

        var mode = "0011";
        var agreementId = null;
        create(payerReference, agreementId, merchantAssociationInfo, intent, amount, callbackURL, mode, createURL)
            .done(function (response) {
                console.log('Create response under done:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                document.getElementById('redirectCheckout').innerText = "";
                var redirectUrlForPay = response.bkashURL;
                if (redirectUrlForPay) {
                    $("#redirectCheckoutDiv").show("slow");
                    $('<a target="_blank" href="' + redirectUrlForPay + '">' + redirectUrlForPay + '</a>').appendTo($('#redirectCheckout'));
                }
                alertify.success('Create response found');
                console.log('TokenizePay redirectURL:: ' + redirectUrlForPay);
            })
            .fail(function (error) {
                console.log('create API error:: ');
                console.log(error);
                alertify.error('Failed Create');
            });

    });


    // Payment Status
    $('#query_payment_btn').click(function () {
        clearViewBoard();
        var paymentId = document.getElementById('paymentIdForquery').value;
        paymentId = paymentId.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        console.log('queryPayment response:: paymentId' + paymentId);
        paymentStatusResponse(paymentId, paymentStatusURL)
            .done(function (response) {
                console.log('PaymentStatus response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Query Payment response found');
            })
            .fail(function (error) {
                console.log('PaymentStatus error:: ');
                console.log(error);
                alertify.error('Failed Query Payment Status');
            });

    });


});

$(document).ready(function () {
    $('#initiate_payout_b2b_button').click(function () {
        clearViewBoard();
        var type = document.getElementById('initiate_payout_type_b2b').value;
        type = type.replace(/</g, "&lt;").replace(/>/g, "&gt;");

        var reference = document.getElementById('reference_init_payout_b2b').value;
        reference = reference.replace(/</g, "&lt;").replace(/>/g, "&gt;");

        console.log('initiate payout response:: type: ' + type);
        initiatePayoutResponse(type, reference, tokenizedInitiatePayoutB2bURL)
            .done(function (response) {
                console.log('Initiate Payout response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Initiate payout response found');
            })
            .fail(function (error) {
                console.log('Initiate payout error:: ');
                console.log(error);
                alertify.error('Failed Initiation of Payout');
            });
    });

    $('#b2b_payment_btn').click(function () {
        clearViewBoard();
        console.log("in b2b button click");
        var paymentIdB2b = document.getElementById('paymentIdB2b').value;
        paymentIdB2b = paymentIdB2b.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var amountB2b = document.getElementById('amountB2b').value;
        amountB2b = amountB2b.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var currencyB2b = document.getElementById('currencyB2b').value;
        currencyB2b = currencyB2b.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var merchantInvoiceNumberB2b = document.getElementById('merchantInvoiceNumberB2b').value;
        merchantInvoiceNumberB2b = merchantInvoiceNumberB2b.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var receiverMSISDNB2b = document.getElementById('receiverMSISDNB2b').value;
        receiverMSISDNB2b = receiverMSISDNB2b.replace(/</g, "&lt;").replace(/>/g, "&gt;");

        var dataObject = {
            "payoutID": paymentIdB2b,
            "amount": amountB2b,
            "currency": currencyB2b,
            "merchantInvoiceNumber": merchantInvoiceNumberB2b,
            "receiverMSISDN": receiverMSISDNB2b
        };

        console.log('initiate b2b payment:: dataObject: ' + dataObject);


        b2bPayoutResponse(dataObject, tokenizedB2bPayoutURL)
            .done(function (response) {
                console.log('B2B Payout response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('B2B Payout response found');
            })
            .fail(function (error) {
                console.log('B2B Payout  error:: ');
                console.log(error);
                alertify.error('B2B Payout Failed');
            });
    });

    $('#payment_id_b2b_query_payout_btn').click(function () {
        clearViewBoard();
        var paymentID = document.getElementById('payment_id_b2b_query_payout').value;
        paymentID = paymentID.replace(/</g, "&lt;").replace(/>/g, "&gt;");

        console.log('query payout response:: paymentID: ' + paymentID);
        queryPaymentB2bResponse(paymentID, tokenizedQueryPayoutURL)
            .done(function (response) {
                console.log('Query Payment response:: ' + JSON.stringify(response));
                $('#response').html(JSON.stringify(response, undefined, 2));

                alertify.success('Query Payment response found');
            })
            .fail(function (error) {
                console.log('Query Payment error:: ');
                console.log(error);
                alertify.error('Failed Query Payment');
            });
    });
});


function queryPaymentB2bResponse(paymentID, url) {
    console.log("query payment url --> ", url + paymentID);
    $("#api_call").text(allApiEndpoints.tokenized_query_payout_b2b_url);
    $('#request').html(JSON.stringify({"payoutID": paymentID}, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url + paymentID,
        type: 'GET'
    });
}

function initiatePayoutResponse(type, reference, url) {
    console.log(url);

    $("#api_call").text(allApiEndpoints.tokenized_initiate_payout_url);
    $('#request').html(JSON.stringify({"type": type, "reference": reference}, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify({
            "type": type,
            "reference": reference
        }),
        contentType: 'application/json'
    });
}

function b2bPayoutResponse(dataObject, url) {
    console.log(dataObject, url);
    $("#api_call").text(allApiEndpoints.tokenized_b2b_payment_url);
    $('#request').html(JSON.stringify(dataObject, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify(dataObject),
        contentType: 'application/json'
    });
}

function merchantBackendURLFetch() {
    return $.ajax({
        url: '../../getTokenizedCheckoutBackendURL',
        type: 'GET'
    });
}

function cancelAgreementResponse(agreementId, url) {
    console.log(url);
    $("#api_call").text(allApiEndpoints.cancel_checkout_agreement_url);
    $('#request').html(JSON.stringify({"agreementID": agreementId}, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify({"agreementID": agreementId}),
        contentType: 'application/json'
    });
}

//Search Transaction


function searchTransactionResponse(transactionID, url) {
    console.log(url);
    $("#api_call").text(allApiEndpoints.tokenized_general_search_transaction);
    $('#request').html(JSON.stringify({"trxID": transactionID}, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify({"trxID": transactionID}),
        contentType: 'application/json'
    });
}

//Refund

function refundResponse(transactionID, paymentID, amount, sku, reason, url) {
    console.log(transactionID, paymentID, amount, sku, reason, url);
    $("#api_call").text(allApiEndpoints.tokenized_refund_payment_url);
    $('#request').html(JSON.stringify({
        "paymentID": paymentID,
        "amount": amount,
        "trxID": transactionID,
        "sku": sku,
        "reason": reason
    }, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify({
            "paymentID": paymentID,
            "amount": amount,
            "trxID": transactionID,
            "sku": sku,
            "reason": reason
        }),
        contentType: 'application/json'
    });
}

function queryAgreementResponse(agreementId, url) {
    console.log(url);
    $("#api_call").text(allApiEndpoints.query_checkout_agreement_url);
    $('#request').html(JSON.stringify({"agreementID": agreementId}, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify({"agreementID": agreementId}),
        contentType: 'application/json'
    });
}

function authCaptureVoidResponse(paymentId, url) {
    if (url === authCaptureURL) {
        $("#api_call").text(allApiEndpoints.tokenized_capture_payment_url);
    } else if (url === authVoidURL) {
        $("#api_call").text(allApiEndpoints.tokenized_void_payment_url);
    }
    console.log(url);
    $('#request').html(JSON.stringify({"paymentID": paymentId}, undefined, 2));
    alertify.message("Processing ...");
    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify({"paymentID": paymentId}),
        contentType: 'application/json'
    });
}

function create(payerReference, agreementId, merchantAssociationInfo, intent, amount, callbackURL, mode, url) {
    console.log('Create Payment Using Agreement Merchant Association Info :: ' + merchantAssociationInfo)
    console.log(url);

    if (mode == "0000") {
        var request = {
            "payerReference": payerReference,
            "callbackURL": callbackURL,
            "mode": mode
        };

        $("#api_call").text(allApiEndpoints.tokenized_create_checkout_url + ' - 0000');
        $('#request').html(JSON.stringify(request, undefined, 2));
        alertify.message("Processing ...");

        return $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(request),
            contentType: 'application/json'
        });
    } else if (mode == "0011") {
        var request = {
            "payerReference": payerReference,
            "callbackURL": callbackURL,
            "amount": amount,
            "intent": intent,
            "merchantAssociationInfo": merchantAssociationInfo,
            "mode": mode
        };

        $("#api_call").text(allApiEndpoints.tokenized_create_checkout_url + ' - 0011');
        $('#request').html(JSON.stringify(request, undefined, 2));
        alertify.message("Processing ...");

        return $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(request),
            contentType: 'application/json'
        });
    } else if (mode == "0001") {
        var request = {
            "agreementID": agreementId,
            "merchantAssociationInfo": merchantAssociationInfo,
            "callbackURL": callbackURL,
            "amount": amount,
            "intent": intent,
            "mode": mode
        };

        $("#api_call").text(allApiEndpoints.tokenized_create_checkout_url + ' - 0001');
        $('#request').html(JSON.stringify(request, undefined, 2));
        alertify.message("Processing ...");

        return $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(request),
            contentType: 'application/json'
        });
    }
}


function paymentStatusResponse(paymentID, url) {
    console.log(url);

    $("#api_call").text(allApiEndpoints.tokenized_payment_status_url);
    $('#request').html(JSON.stringify({"paymentID": paymentID}, undefined, 2));
    alertify.message("Processing ...");

    return $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify({"paymentID": paymentID}),
        contentType: 'application/json'
    });
}

function commonAjaxGET(url) {
    return $.ajax({
        url: url,
        type: 'GET'
    });
}

function clearViewBoard() {
    $("#api_call").empty();
    $('#request').empty();
    $('#response').empty();
}
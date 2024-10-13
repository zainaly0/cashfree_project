<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pay now</title>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
</head>
<body onload="payment()">

    <input type="hidden" id="paymentSessionId" value="{{$payment_session_id}}">

    <script type="text/javascript">

    function payment(){
        var cashFree= Cashfree({
            mode: 'sandbox' // or production

        })

        let checkoutoptions={
            paymentSessionId: document.getElementById('paymentSessionId').value,
            redirectTarget: "_self" //_self, _blank, _top
        }

        cashfree.checkout(checkoutoptions)
    }


    </script>
    
</body>
</html>
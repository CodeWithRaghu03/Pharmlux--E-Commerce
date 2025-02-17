<!DOCTYPE html>
<html>
<head>
    <title>Demo Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            color: #fff;
        }
        header {
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        header h1 {
            margin: 0;
        }
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            color: #333;
            text-align: center;
        }
        .payment-container h2 {
            margin-bottom: 20px;
        }
        .payment-container button {
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .payment-container button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to My E-commerce Site</h1>
    </header>
    <div class="payment-container">
        <h2>Demo Payment</h2>
        <button id="rzp-button1">Pay with Razorpay</button>
    </div>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "rzp_test_YOUR_KEY_ID", // Replace with your test Key ID
            "amount": "1000", // Amount in paise (₹10.00)
            "currency": "INR",
            "name": "My E-commerce Site",
            "description": "Test Transaction",
            "image": "path/to/logo.png",
            "handler": function (response){
                alert("Payment Successful! Payment ID: " + response.razorpay_payment_id);
            },
            "prefill": {
                "name": "John Doe",
                "email": "john.doe@example.com"
            },
            "theme": {
                "color": "#F37254"
            }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('rzp-button1').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }
    </script>
</body>
</html>

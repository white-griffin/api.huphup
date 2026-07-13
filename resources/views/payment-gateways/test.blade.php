<!doctype html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <title>Test Gateway</title>

    <style>
        body {
            font-family: tahoma;
            background: #f5f5f5;
        }

        .card {
            width: 420px;
            margin: 80px auto;
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, .08);
        }

        h2 {
            margin-top: 0;
        }

        .row {
            margin: 12px 0;
        }

        form {
            display: inline-block;
            width: 48%;
        }

        button {
            width: 100%;
            padding: 12px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Test Payment Gateway</h2>

    <div class="row">
        <strong>Amount:</strong>
        {{ number_format($payment->amount) }}
    </div>

    <div class="row">
        <strong>Transaction:</strong>
        {{ $payment->transaction_id }}
    </div>

    <hr>

    <form
        method="POST"
        action="{{ route('payments.callback',['gateway'=>'test']) }}"
    >

        <input
            type="hidden"
            name="transaction_id"
            value="{{ $payment->transaction_id }}"
        >

        <input
            type="hidden"
            name="status"
            value="OK"
        >

        <button type="submit">
            پرداخت موفق
        </button>

    </form>

    <form
        method="POST"
        action="{{ route('payments.callback',['gateway'=>'test']) }}"
    >

        <input
            type="hidden"
            name="transaction_id"
            value="{{ $payment->transaction_id }}"
        >

        <input
            type="hidden"
            name="status"
            value="NOK"
        >

        <button type="submit">
            پرداخت ناموفق
        </button>

    </form>

</div>

</body>
</html>

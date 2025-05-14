<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلب رقم: {{ $order->getOrderNumber() }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .store-info {
            margin-bottom: 20px;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .order-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-info table th, .order-info table td {
            padding: 8px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        .customer-info {
            margin-bottom: 20px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            padding: 8px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: left;
            font-weight: bold;
            font-size: 16px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>تفاصيل الطلب</h1>
        <h2>رقم الطلب: {{ $order->getOrderNumber() }}</h2>
    </div>
    
    @if($store)
    <div class="store-info">
        <h3>معلومات المتجر</h3>
        <p>{{ $store->name }}</p>
        <p>{{ $store->address }}</p>
        <p>{{ $store->phone_number }}</p>
    </div>
    @endif
    
    <div class="order-info">
        <h3>معلومات الطلب</h3>
        <table>
            <tr>
                <th>تاريخ الطلب:</th>
                <td>{{ $order->getCreatedAt()->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <th>حالة الطلب:</th>
                <td>{{ $order->getStatus() }}</td>
            </tr>
            <tr>
                <th>حالة الدفع:</th>
                <td>{{ $order->getPaymentStatus() }}</td>
            </tr>
            <tr>
                <th>طريقة الدفع:</th>
                <td>{{ $order->getPaymentMethod() }}</td>
            </tr>
        </table>
    </div>
    
    @if($user = $order->getUser())
    <div class="customer-info">
        <h3>معلومات العميل</h3>
        <p><strong>الاسم:</strong> {{ $user['name'] }}</p>
        <p><strong>البريد الإلكتروني:</strong> {{ $user['email'] }}</p>
    </div>
    @endif
    
    <h3>عنوان الشحن</h3>
    <p>{!! nl2br(e($order->getShippingAddress())) !!}</p>
    
    <h3>عنوان الفوترة</h3>
    <p>{!! nl2br(e($order->getBillingAddress())) !!}</p>
    
    <h3>المنتجات</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->getItems() as $item)
            <tr>
                <td>{{ $item->getProductName() }}</td>
                <td>{{ $item->getQuantity() }}</td>
                <td>{{ number_format($item->getPrice(), 2) }}</td>
                <td>{{ number_format($item->getQuantity() * $item->getPrice(), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total">
        المجموع الكلي: {{ number_format($order->getTotalAmount(), 2) }} ريال
    </div>
    
    <div class="footer">
        <p>شكراً لطلبك من متجرنا. نتمنى لك تجربة تسوق ممتعة!</p>
    </div>
</body>
</html>

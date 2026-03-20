<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توثيق البريد الإلكتروني - شركة المفتاح</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            text-align: right;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #2c3e50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .content {
            line-height: 1.8;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background-color: #4CAF50;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .footer {
            text-align: center;
            color: #888;
            font-size: 12px;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 شركة المفتاح للعقارات</h1>
        </div>
        
        <div class="content">
            <p>مرحباً <strong>{{ $userName }}</strong>،</p>
            
            <p>شكراً لتسجيلك في منصة المفتاح للعقارات. هذا البريد الإلكتروني للتحقق من صحة بريدك الإلكتروني.</p>
            
            <p>اضغط على الزر أدناه لتوثيق بريدك الإلكتروني:</p>
            
            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="btn">توثيق البريد الإلكتروني</a>
            </p>
            
            <p>أو انسخ الرابط التالي في متصفحك:</p>
            <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px;">{{ $verificationUrl }}</p>
            
            <p><strong>ملاحظة:</strong> هذا الرابط صالح لمدة 24 ساعة.</p>
        </div>
        
        <div class="footer">
            <p>شركة المفتاح للعقارات</p>
            <p>إذا لم تقم بالتسجيل في منصتنا، يمكنك تجاهل هذا البريد.</p>
        </div>
    </div>
</body>
</html>

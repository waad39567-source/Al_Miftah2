<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توثيق البريد الإلكتروني</title>
    <style>
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>شركة المفتاح</h1>
    <p>مرحباً {{ $userName }}،</p>
    <p>شكراً للتسجيل معنا. هذا البريد الإلكتروني للتحقق من صحة بريدك الإلكتروني.</p>
    <p>هذه صفحة توثيق الايميل لشركة المفتاح</p>
    
    <a href="{{ $verificationUrl }}" class="btn">توثيق البريد الإلكتروني</a>
    
    <p>أو يمكنك نسخ الرابط التالي ولصقه في المتصفح:</p>
    <p>{{ $verificationUrl }}</p>
</body>
</html>

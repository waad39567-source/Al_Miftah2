# توثيق API شركة المفتاح

## معلومات عامة

- **Base URL:** `http://127.0.0.1:8000/api`
- **نوع الاستجابة:** JSON
- **Authentication:** Bearer Token (Laravel Sanctum)

---

##جدول المحتويات

1. [المستخدمين والأدوار](#1-المستخدمين-والأدوار)
2. [Regions (المناطق)](#2-regions-المناطق)
3. [العقارات](#3-العقارات)
4. [طلبات التواصل](#4-طلبات-التواصل)
5. [الأدمن](#5-الأدمن)

---

## 1. المستخدمين والأدوار

### 1.1 تسجيل مستخدم جديد

**الوصف:** إنشاء حساب مستخدم جديد في النظام

**Method:** `POST`
**URL:** `{{base_url}}/auth/register`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "اسم المستخدم",
    "email": "email@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "966501234567",
    "role": "user"
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب | الوصف |
|-------|------|-------|-------|
| name | string | نعم | الاسم الكامل (max: 255) |
| email | email | نعم | البريد الإلكتروني (unique) |
| password | string | نعم | كلمة المرور (min: 8) |
| password_confirmation | string | نعم | تأكيد كلمة المرور |
| phone | string | نعم | رقم الهاتف (unique, max: 20) |
| role | string | لا | `user` أو `owner` |

---

**✅ استجابة ناجحة (201):**
```json
{
    "success": true,
    "message": "تم التسجيل بنجاح. لم يتم توثيق حسابك بعد يرجى التحقق من بريدك الالكتروني لتوثيق الحساب",
    "data": {
        "user": {
            "id": 1,
            "name": "اسم المستخدم",
            "email": "email@example.com",
            "phone": "966501234567",
            "role": "user",
            "is_active": true,
            "created_at": "2026-03-08T12:00:00"
        }
    }
}
```

**❌ استجابة خاطئة - بريد مكرر (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "email": ["البريد الإلكتروني مستخدم من قبل"]
    }
}
```

**❌ استجابة خاطئة - رقم هاتف مكرر (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "phone": ["رقم الهاتف مستخدم من قبل"]
    }
}
```

---

### 1.2 تسجيل الدخول

**الوصف:** تسجيل الدخول للحصول على token

**Method:** `POST`
**URL:** `{{base_url}}/auth/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "email": "email@example.com",
    "password": "password123"
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب |
|-------|------|-------|
| email | email | نعم |
| password | string | نعم |

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "message": "تم تسجيل الدخول بنجاح",
    "data": {
        "user": {
            "id": 1,
            "name": "اسم المستخدم",
            "email": "email@example.com",
            "phone": "966501234567",
            "role": "user",
            "is_active": true,
            "created_at": "2026-03-08T12:00:00"
        },
        "token": "1|abc123..."
    }
}
```

**❌ استجابة خاطئة - بيانات غير صحيحة (401):**
```json
{
    "success": false,
    "message": "بيانات الاعتماد غير صحيحة"
}
```

**❌ استجابة خاطئة - حساب غير نشط (403):**
```json
{
    "success": false,
    "message": "الحساب غير نشط"
}
```

**❌ استجابة خاطئة - حساب غير موثق (403):**
```json
{
    "success": false,
    "message": "لم يتم توثيق حسابك بعد يرجى التحقق من بريدك الالكتروني لتوثيق الحساب"
}
```

---

### 1.3 إرسال رابط توثيق البريد الإلكتروني

**الوصف:** إرسال بريد إلكتروني يحتوي على رابط التوثيق للمستخدم

> **ملاحظة مهمة:** هذا الـ API يضيف البريد إلى Queue (قائمة الانتظار)، ويجب تشغيل `php artisan queue:work` لإرسال البريد فعلياً. رابط التوثيق يُرسل للمستخدم عبر البريد الإلكتروني وليس عبر الواجهة.

**Method:** `POST`
**URL:** `{{base_url}}/auth/send-verification-email`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "email": "user@example.com"
}
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "message": "تم إضافة إرسال رابط توثيق البريد الإلكتروني إلى قائمة الانتظار",
    "data": null
}
```

**❌ استجابة خاطئة - البريد غير موجود (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "email": ["The selected email is invalid."]
    }
}
```

**❌ استجابة خاطئة - البريد موثق مسبقاً (400):**
```json
{
    "success": false,
    "message": "تم توثيق البريد الإلكتروني مسبقاً"
}
```

---

### 1.4 توثيق البريد الإلكتروني

**الوصف:** التحقق من بريد المستخدم الإلكتروني وتوثيق الحساب

**Method:** `POST`
**URL:** `{{base_url}}/auth/verify-email`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "email": "user@example.com"
}
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "message": "تم توثيق البريد الإلكتروني بنجاح",
    "data": {
        "email_verified_at": "2026-03-08T12:00:00"
    }
}
```

**❌ استجابة خاطئة - البريد موثق مسبقاً (400):**
```json
{
    "success": false,
    "message": "تم توثيق البريد الإلكتروني مسبقاً"
}
```

---

### 1.5 جلب بيانات المستخدم الحالي

**الوصف:** جلب معلومات المستخدم المسجل

**Method:** `GET`
**URL:** `{{base_url}}/auth/me`

**Headers:**
```
Authorization: Bearer {{token}}
Accept: application/json
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "اسم المستخدم",
        "email": "email@example.com",
        "phone": "966501234567",
        "role": "user",
        "is_active": true,
        "created_at": "2026-03-08T12:00:00"
    }
}
```

**❌ استجابة خاطئة - غير مصادق (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

### 1.6 تغيير كلمة المرور

**الوصف:** تغيير كلمة مرور المستخدم الحالي

**Method:** `POST`
**URL:** `{{base_url}}/auth/change-password`

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "current_password": "oldpassword123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب |
|-------|------|-------|
| current_password | string | نعم |
| password | string | نعم (min: 8) |
| password_confirmation | string | نعم |

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "message": "تم تغيير كلمة المرور بنجاح"
}
```

**❌ استجابة خاطئة - كلمة المرور الحالية خطأ (400):**
```json
{
    "success": false,
    "message": "كلمة المرور الحالية غير صحيحة"
}
```

---

### 1.7 تسجيل الخروج

**الوصف:** تسجيل خروج المستخدم وإلغاء token الحالي

**Method:** `POST`
**URL:** `{{base_url}}/auth/logout`

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{}
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "message": "تم تسجيل الخروج بنجاح"
}
```

---

### 1.8 ترقية مستخدم إلى أدمن

**الوصف:** ترقية مستخدم عادي إلى دور أدمن (أدمن فقط)

**Method:** `POST`
**URL:** `{{base_url}}/auth/promote-to-admin`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON) - باستخدام id:**
```json
{
    "id": 5
}
```

**أو باستخدام email:**
```json
{
    "email": "user@example.com"
}
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "message": "تم ترقية المستخدم إلى مسؤول بنجاح",
    "data": {
        "id": 5,
        "name": "اسم المستخدم",
        "email": "user@example.com",
        "phone": "966501234567",
        "role": "admin",
        "is_active": true,
        "created_at": "2026-03-08T12:00:00"
    }
}
```

**❌ استجابة خاطئة - مستخدم غير موجود (404):**
```json
{
    "success": false,
    "message": "المستخدم غير موجود"
}
```

**❌ استجابة خاطئة - غير مصرح (403):**
```json
{
    "success": false,
    "message": "غير مصرح لك بهذه العملية"
}
```

---

### 1.9 إنشاء مستخدم جديد (أدمن فقط)

**الوصف:** إنشاء مستخدم جديد من قبل الأدمن

**Method:** `POST`
**URL:** `{{base_url}}/auth/users`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "اسم المستخدم الجديد",
    "email": "newuser@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "966501234567",
    "role": "user",
    "is_active": true
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب | القيم الممكنة |
|-------|------|-------|---------------|
| name | string | نعم | |
| email | email | نعم | unique |
| password | string | نعم | min: 8 |
| phone | string | لا | unique |
| role | string | نعم | user, owner, admin |
| is_active | boolean | لا | true/false |

---

**✅ استجابة ناجحة (201):**
```json
{
    "success": true,
    "message": "تم إنشاء المستخدم بنجاح",
    "data": {
        "id": 6,
        "name": "اسم المستخدم الجديد",
        "email": "newuser@example.com",
        "phone": "966501234567",
        "role": "user",
        "is_active": true,
        "created_at": "2026-03-08T12:00:00"
    }
}
```

---

## 2. Regions (المناطق)

### 2.1 جلب جميع المناطق

**الوصف:** جلب قائمة المناطق مع دعم التصفية والبحث

**Method:** `GET`
**URL:** `{{base_url}}/regions`

**الـ Query Parameters (اختياري):**
| Parameter | الوصف | مثال |
|-----------|-------|------|
| type | فلترة حسب النوع | `?type=city` |
| parent_id | فلترة حسب المنطقة الأب | `?parent_id=1` |
| parent_id | المناطق الرئيسية | `?parent_id=null` |
| search | البحث في الاسم | `?search=رياض` |
| has_children | فلترة حسب وجود أبناء | `?has_children=true` |
| sort_by | ترتيب حسب الحقل | `?sort_by=name` |
| sort_order | اتجاه الترتيب (asc/desc) | `?sort_order=asc` |
| per_page | عدد النتائج في الصفحة | `?per_page=10` |

**ملاحظة:** القيم المتاحة لـ type: `country`, `governorate`, `city`, `neighborhood`

**Headers:**
```
Accept: application/json
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "السعودية",
            "type": "country",
            "parent_id": null,
            "created_at": "2026-03-08T12:00:00",
            "children": [
                {
                    "id": 2,
                    "name": "الرياض",
                    "type": "city",
                    "parent_id": 1,
                    "created_at": "2026-03-08T12:00:00"
                }
            ]
        }
    ]
}
```

---

### 2.2 جلب منطقة محددة

**الوصف:** جلب تفاصيل منطقة معينة مع مناطقها الفرعية

**Method:** `GET`
**URL:** `{{base_url}}/regions/{id}`

**Headers:**
```
Accept: application/json
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "الرياض",
        "type": "city",
        "parent_id": 1,
        "created_at": "2026-03-08T12:00:00",
        "children": []
    }
}
```

**❌ استجابة خاطئة - منطقة غير موجودة (404):**
```json
{
    "success": false,
    "message": "المنطقة غير موجودة"
}
```

---

### 2.3 جلب أنواع المناطق المتاحة

**الوصف:** جلب قائمة أنواع المناطق

**Method:** `GET`
**URL:** `{{base_url}}/regions/types/list`

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": {
        "country": "دولة",
        "governorate": "محافظة",
        "city": "مدينة",
        "neighborhood": "حي"
    }
}
```

---

### 2.4 جلب المناطق الرئيسية

**الوصف:** جلب المناطق الرئيسية (التي ليس لها أب)

**Method:** `GET`
**URL:** `{{base_url}}/regions/root/list`

---

### 2.5 جلب المناطق الفرعية

**الوصف:** جلب المناطق الفرعية لمنطقة معينة

**Method:** `GET`
**URL:** `{{base_url}}/regions/{id}/children`

---

### 2.6 إنشاء منطقة جديدة (أدمن فقط)

**الوصف:** إنشاء منطقة جديدة

**Method:** `POST`
**URL:** `{{base_url}}/admin/regions`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "الدمام",
    "type": "city",
    "parent_id": null
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب | القيم الممكنة |
|-------|------|-------|---------------|
| name | string | نعم | |
| type | string | نعم | country, governorate, city, neighborhood |
| parent_id | integer | لا | معرف المنطقة الأب |

---

**✅ استجابة ناجحة (201):**
```json
{
    "success": true,
    "message": "تم إنشاء المنطقة بنجاح",
    "data": {
        "id": 5,
        "name": "الدمام",
        "type": "city",
        "parent_id": null,
        "created_at": "2026-03-08T12:00:00"
    }
}
```

**❌ استجابة خاطئة - غير مصرح (403):**
```json
{
    "success": false,
    "message": "غير مصرح لك بهذه العملية"
}
```

---

### 2.7 تحديث منطقة (أدمن فقط)

**الوصف:** تحديث منطقة موجودة

**Method:** `PUT`
**URL:** `{{base_url}}/admin/regions/{id}`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "الدمام - تحديث",
    "type": "city"
}
```

---

### 2.8 حذف منطقة (أدمن فقط)

**الوصف:** حذف منطقة

**Method:** `DELETE`
**URL:** `{{base_url}}/admin/regions/{id}`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

---

**❌ استجابة خاطئة - منطقة لها أبناء (422):**
```json
{
    "success": false,
    "message": "لا يمكن حذف منطقة لها مناطق فرعية"
}
```

---

## 3. العقارات

### 3.1 جلب قائمة العقارات

**الوصف:** جلب قائمة العقارات المعتمدة مع إمكانية الفلترة

**Method:** `GET`
**URL:** `{{base_url}}/properties`

**الـ Query Parameters (اختياري):**
| Parameter | الوصف | مثال |
|-----------|-------|------|
| type | نوع العقار (sale/rent) | `?type=sale` |
| region_id | معرف المنطقة | `?region_id=2` |
| search | البحث في العنوان والوصف | `?search=شقة` |

**Headers:**
```
Accept: application/json
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "owner_id": 5,
                "title": "شقة فاخرة",
                "description": "شقة جديدة في وسط المدينة",
                "price": "500000.00",
                "type": "sale",
                "property_type": "apartment",
                "area": 150,
                "region_id": 2,
                "location": "الرياض",
                "status": "approved",
                "is_active": true,
                "owner": {
                    "id": 5,
                    "name": "مالك العقار",
                    "email": "owner@example.com"
                },
                "region": {
                    "id": 2,
                    "name": "الرياض"
                },
                "images": []
            }
        ],
        "first_page_url": "...",
        "last_page_url": "...",
        "next_page_url": null,
        "prev_page_url": null,
        "per_page": 15,
        "total": 1
    }
}
```

---

### 3.2 جلب عقار واحد

**الوصف:** جلب تفاصيل عقار معين

**Method:** `GET`
**URL:** `{{base_url}}/properties/{id}`

**Headers:**
```
Accept: application/json
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "owner_id": 5,
        "title": "شقة فاخرة",
        "description": "شقة جديدة في وسط المدينة",
        "price": "500000.00",
        "type": "sale",
        "property_type": "apartment",
        "area": 150,
        "region_id": 2,
        "location": "الرياض",
        "latitude": null,
        "longitude": null,
        "status": "approved",
        "rejection_reason": null,
        "is_active": true,
        "approved_by": 1,
        "approved_at": "2026-03-08T12:00:00",
        "owner": {...},
        "region": {...},
        "images": [...]
    }
}
```

**❌ استجابة خاطئة - عقار غير موجود (404):**
```json
{
    "success": false,
    "message": "العقار غير موجود"
}
```

---

## 4. طلبات التواصل

### 4.1 إرسال طلب تواصل

**الوصف:** إرسال طلب تواصل مع مالك العقار

**Method:** `POST`
**URL:** `{{base_url}}/contact`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "property_id": 1,
    "name": "اسم المرسل",
    "phone": "966501234567",
    "message": "رسالة اهتمام بالعقار"
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب |
|-------|------|-------|
| property_id | integer | نعم (must exist) |
| name | string | نعم |
| phone | string | نعم |
| message | string | لا |

---

**✅ استجابة ناجحة (201):**
```json
{
    "success": true,
    "message": "تم إرسال طلب التواصل بنجاح",
    "data": {
        "id": 1,
        "property_id": 1,
        "owner_id": 5,
        "name": "اسم المرسل",
        "phone": "966501234567",
        "message": "رسالة اهتمام بالعقار",
        "created_at": "2026-03-08T12:00:00"
    }
}
```

**❌ استجابة خاطئة (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "property_id": ["The selected property id is invalid."]
    }
}
```

---

## 5. الأدمن

### 5.1 جلب جميع المستخدمين (أدمن فقط)

**الوصف:** جلب قائمة بجميع المستخدمين

**Method:** `GET`
**URL:** `{{base_url}}/users`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "مدير النظام",
                "email": "admin@almiftah.com",
                "phone": "01234567890",
                "role": "admin",
                "is_active": true,
                "email_verified_at": "2026-03-08T12:00:00",
                "created_at": "2026-03-08T12:00:00"
            }
        ],
        "per_page": 15,
        "total": 6
    }
}
```

**❌ استجابة خاطئة - غير مصرح (403):**
```json
{
    "success": false,
    "message": "غير مصرح لك بهذه العملية"
}
```

---

### 5.2 جلب جميع العقارات (أدمن فقط)

**الوصف:** جلب قائمة بجميع العقارات مع الفلترة

**Method:** `GET`
**URL:** `{{base_url}}/admin/properties`

**الـ Query Parameters (اختياري):**
| Parameter | الوصف |
|-----------|-------|
| status | pending, approved, rejected |

**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

---

### 5.3 الموافقة على عقار (أدمن فقط)

**الوصف:** الموافقة على عقار معين

**Method:** `POST`
**URL:** `{{base_url}}/admin/properties/{id}/approve`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{}
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "message": "تم الموافقة على العقار بنجاح",
    "data": {
        "id": 1,
        "status": "approved",
        "approved_by": 1,
        "approved_at": "2026-03-08T12:00:00"
    }
}
```

---

### 5.4 رفض عقار (أدمن فقط)

**الوصف:** رفض عقار معين مع ذكر السبب

**Method:** `POST`
**URL:** `{{base_url}}/admin/properties/{id}/reject`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "reason": "العقار لا يستوفي الشروط المطلوبة"
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب |
|-------|------|-------|
| reason | string | نعم |

---

### 5.5 جلب طلبات التواصل (أدمن فقط)

**الوصف:** جلب جميع طلبات التواصل

**Method:** `GET`
**URL:** `{{base_url}}/admin/contact-requests`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

---

### 5.6 إنشاء منطقة (أدمن فقط)

**الوصف:** إنشاء منطقة جديدة

**Method:** `POST`
**URL:** `{{base_url}}/admin/regions`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "الدمام",
    "type": "city",
    "parent_id": null
}
```

**قواعد التحقق:**
| الحقل | النوع | مطلوب | القيم الممكنة |
|-------|------|-------|---------------|
| name | string | نعم | |
| type | string | نعم | country, governorate, city, neighborhood |
| parent_id | integer | لا | معرف المنطقة الأب |

---

### 5.7 تحديث منطقة (أدمن فقط)

**الوصف:** تحديث منطقة موجودة

**Method:** `PUT`
**URL:** `{{base_url}}/admin/regions/{id}`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "الدمام - تحديث"
}
```

---

### 5.8 حذف منطقة (أدمن فقط)

**الوصف:** حذف منطقة

**Method:** `DELETE`
**URL:** `{{base_url}}/admin/regions/{id}`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

---

### 5.9 جلب الإحصائيات (أدمن فقط)

**الوصف:** جلب إحصائيات النظام

**Method:** `GET`
**URL:** `{{base_url}}/admin/statistics`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Accept: application/json
```

---

**✅ استجابة ناجحة (200):**
```json
{
    "success": true,
    "data": {
        "users": 6,
        "properties": 10,
        "approved_properties": 8,
        "pending_properties": 2,
        "contact_requests": 5
    }
}
```

---

## أنواع المستخدمين (الأدوار)

| الدور | الوصف |
|-------|-------|
| `user` | مستخدم عادي - يمكنه تصفح العقارات والتواصل |
| `owner` | مالك عقار - يمكنه إضافة وإدارة عقاراته |
| `admin` | مدير النظام - يمكنه إدارة كل شيء |

---

## ملاحظات هامة

1. **توثيق البريد الإلكتروني:** رابط التوثيق يُرسل للمستخدم عبر البريد الإلكتروني وليس عبر الواجهة الأمامية. يجب استدعاء `send-verification-email` لإضافته للـ Queue، ثم تشغيل `php artisan queue:work` لإرساله.

2. **الـ Queue:** بعض العمليات مثل إرسال البريد تعمل بشكل غير متزامن عبر Queue.

3. **الـ Token:** يتم إنشاء token عند تسجيل الدخول، ويجب إرساله في كل طلب يتطلب مصادقة عبر Header `Authorization: Bearer {token}`.

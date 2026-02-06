# Certificate Generator API

موقع بسيط لتوليد الشهادات مع البيانات المخصصة

## المتطلبات
- PHP 7.4 أو أحدث
- GD Library (عادة مثبتة مع PHP)

## التثبيت

1. انسخ المجلد `certificate web` إلى السيرفر
2. ضع صور الشهادات في مجلد `templates/`:
   - `templates/ar.jpg` - الشهادة العربية
   - `templates/en.jpg` - الشهادة الإنجليزية
3. (اختياري) ضع خط Cairo في `fonts/Cairo-Bold.ttf`

## الاستخدام

### API Endpoint
```
POST /index.php
```

### Request Body (JSON)
```json
{
  "studentName": "أحمد محمد",
  "courseName": "تطوير تطبيقات Flutter",
  "instructorName": "د. محمد علي",
  "date": "15 يناير 2026",
  "language": "ar"
}
```

### Response (JSON)
```json
{
  "success": true,
  "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
}
```

## اختبار محلي

```bash
php -S localhost:8000
```

ثم افتح المتصفح على: http://localhost:8000/test.html

## رفع على استضافة

يمكنك رفع الملفات على أي استضافة تدعم PHP مثل:
- 000webhost (مجاني)
- InfinityFree (مجاني)
- Hostinger
- أي استضافة PHP أخرى

## ملاحظات
- تأكد من أن مجلد `templates` يحتوي على الصور
- الخط العربي اختياري لكن يحسن الشكل
- الصور ترجع بصيغة base64 جاهزة للعرض

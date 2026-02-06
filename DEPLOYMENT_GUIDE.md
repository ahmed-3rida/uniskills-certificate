# دليل رفع موقع الشهادات 🚀

## الخطوات السريعة

### 1. اختبار محلي
```bash
cd "certificate web"
php -S localhost:8000
```
ثم افتح: http://localhost:8000/test.html

### 2. تحميل الخط العربي (اختياري)
```bash
php download_font.php
```

### 3. رفع على استضافة مجانية

#### خيار 1: InfinityFree (موصى به)
1. سجل على: https://infinityfree.net
2. أنشئ موقع جديد
3. ارفع كل الملفات عبر FTP أو File Manager
4. الرابط سيكون: `http://yoursite.infinityfreeapp.com`

#### خيار 2: 000webhost
1. سجل على: https://www.000webhost.com
2. أنشئ موقع جديد
3. ارفع الملفات عبر File Manager
4. الرابط سيكون: `https://yoursite.000webhostapp.com`

#### خيار 3: استضافة مدفوعة
- Hostinger (رخيص وسريع)
- Namecheap
- أي استضافة PHP

### 4. التأكد من المتطلبات
تأكد أن الاستضافة تدعم:
- ✅ PHP 7.4+
- ✅ GD Library
- ✅ file_get_contents
- ✅ JSON functions

### 5. اختبار الموقع
بعد الرفع، افتح:
```
https://yoursite.com/test.html
```

## استخدام في التطبيق

### مثال Flutter
```dart
Future<String> generateCertificate({
  required String studentName,
  required String courseName,
  required String instructorName,
  required String date,
  required String language,
}) async {
  final response = await http.post(
    Uri.parse('https://yoursite.com/index.php'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'studentName': studentName,
      'courseName': courseName,
      'instructorName': instructorName,
      'date': date,
      'language': language,
    }),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return data['image']; // base64 image
  }
  
  throw Exception('Failed to generate certificate');
}
```

## حل المشاكل الشائعة

### المشكلة: "GD Library not found"
**الحل:** تواصل مع الدعم الفني للاستضافة لتفعيل GD Library

### المشكلة: "CORS Error"
**الحل:** تأكد من رفع ملف `.htaccess`

### المشكلة: "Template not found"
**الحل:** تأكد من رفع مجلد `templates` مع الصور

### المشكلة: النص لا يظهر بشكل صحيح
**الحل:** 
1. حمل الخط العربي: `php download_font.php`
2. أو عدل المواضع في `index.php`

## تخصيص المواضع

إذا كانت النصوص في مكان خاطئ، عدل في `index.php`:

```php
// غير هذه القيم حسب تصميم شهادتك
$studentY = 105 * $scale;  // موضع اسم الطالب
$courseY = 148 * $scale;   // موضع اسم الدورة
$dateX = 75 * $scale;      // موضع التاريخ (X)
$dateY = $height - (35 * $scale); // موضع التاريخ (Y)
```

## الأمان

الموقع آمن لأنه:
- ✅ لا يحفظ أي بيانات
- ✅ يعالج الصور في الذاكرة فقط
- ✅ يرجع النتيجة مباشرة
- ✅ لا يوجد قاعدة بيانات

## الأداء

- معالجة كل شهادة: ~0.5 ثانية
- حجم الصورة المرجعة: ~200-500 KB
- يدعم معالجة متزامنة

## الدعم

إذا واجهت مشاكل:
1. تحقق من ملف `test.html` محلياً
2. تأكد من رفع كل الملفات
3. راجع error logs في لوحة التحكم
4. تواصل مع دعم الاستضافة

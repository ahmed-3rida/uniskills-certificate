# 📚 توثيق API مولد الشهادات

## 📋 نظرة عامة

API بسيط لتوليد شهادات مخصصة بناءً على قالب صورة. يستقبل بيانات المستخدم ويرجع صورة الشهادة جاهزة بصيغة base64.

---

## 🔗 Endpoint

```
POST /index.php
```

---

## 📥 Request

### Headers
```
Content-Type: application/json
```

### Body (JSON)
```json
{
  "studentName": "أحمد محمد علي",
  "courseName": "تطوير تطبيقات Flutter",
  "instructorName": "د. محمد علي",
  "date": "15 يناير 2026",
  "language": "ar"
}
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `studentName` | string | ✅ Yes | اسم الطالب |
| `courseName` | string | ✅ Yes | اسم الدورة |
| `instructorName` | string | ✅ Yes | اسم المدرب |
| `date` | string | ✅ Yes | تاريخ الإكمال |
| `language` | string | ✅ Yes | اللغة (`ar` أو `en`) |

---

## 📤 Response

### Success Response (200 OK)
```json
{
  "success": true,
  "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
}
```

### Error Response (400 Bad Request)
```json
{
  "error": "Missing required field: studentName"
}
```

### Error Response (404 Not Found)
```json
{
  "error": "Certificate template not found"
}
```

### Error Response (500 Internal Server Error)
```json
{
  "error": "Failed to load certificate template"
}
```

---

## 💻 أمثلة الاستخدام

### Flutter (Dart)
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

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
    if (data['success']) {
      return data['image']; // base64 string
    }
  }
  
  throw Exception('Failed to generate certificate');
}

// استخدام
void main() async {
  try {
    final certificateBase64 = await generateCertificate(
      studentName: 'أحمد محمد',
      courseName: 'Flutter Development',
      instructorName: 'د. محمد',
      date: '15 يناير 2026',
      language: 'ar',
    );
    
    // عرض الصورة
    final bytes = base64Decode(certificateBase64.split(',').last);
    // استخدم bytes لعرض أو حفظ الصورة
  } catch (e) {
    print('Error: $e');
  }
}
```

### JavaScript (Fetch API)
```javascript
async function generateCertificate(data) {
  try {
    const response = await fetch('https://yoursite.com/index.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        studentName: data.studentName,
        courseName: data.courseName,
        instructorName: data.instructorName,
        date: data.date,
        language: data.language
      })
    });

    const result = await response.json();
    
    if (result.success) {
      return result.image; // base64 string
    } else {
      throw new Error(result.error);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// استخدام
generateCertificate({
  studentName: 'أحمد محمد',
  courseName: 'Flutter Development',
  instructorName: 'د. محمد',
  date: '15 يناير 2026',
  language: 'ar'
}).then(base64Image => {
  // عرض الصورة
  document.getElementById('certificate').src = base64Image;
});
```

### Python (Requests)
```python
import requests
import json
import base64

def generate_certificate(student_name, course_name, instructor_name, date, language):
    url = 'https://yoursite.com/index.php'
    
    data = {
        'studentName': student_name,
        'courseName': course_name,
        'instructorName': instructor_name,
        'date': date,
        'language': language
    }
    
    response = requests.post(url, json=data)
    
    if response.status_code == 200:
        result = response.json()
        if result['success']:
            return result['image']
    
    raise Exception('Failed to generate certificate')

# استخدام
certificate_base64 = generate_certificate(
    student_name='أحمد محمد',
    course_name='Flutter Development',
    instructor_name='د. محمد',
    date='15 يناير 2026',
    language='ar'
)

# حفظ الصورة
image_data = certificate_base64.split(',')[1]
with open('certificate.jpg', 'wb') as f:
    f.write(base64.b64decode(image_data))
```

### cURL
```bash
curl -X POST https://yoursite.com/index.php \
  -H "Content-Type: application/json" \
  -d '{
    "studentName": "أحمد محمد",
    "courseName": "Flutter Development",
    "instructorName": "د. محمد",
    "date": "15 يناير 2026",
    "language": "ar"
  }'
```

---

## ⚙️ التخصيص

### تعديل مواضع النصوص

افتح `config.php` وعدل:

```php
'positions' => [
    'student_name' => [
        'y' => 105,           // موضع Y من الأعلى
        'font_size' => 30,    // حجم الخط
        'centered' => true,   // توسيط أفقي
    ],
    // ... باقي المواضع
],
```

### تعديل جودة الصورة

```php
'image' => [
    'quality' => 95,  // 1-100 (أعلى = جودة أفضل، حجم أكبر)
],
```

---

## 🔒 الأمان

### CORS
- مفعل افتراضياً للسماح بالطلبات من أي مصدر
- يمكن تقييده في `config.php`:

```php
'allowed_origins' => ['https://yourapp.com'],
```

### الخصوصية
- ✅ لا يتم حفظ أي بيانات
- ✅ المعالجة في الذاكرة فقط
- ✅ لا توجد قاعدة بيانات
- ✅ الصورة ترجع مباشرة ثم تحذف

---

## 📊 الأداء

| Metric | Value |
|--------|-------|
| وقت المعالجة | ~0.3-0.5 ثانية |
| حجم الاستجابة | ~200-500 KB |
| الطلبات المتزامنة | مدعومة |
| الحد الأقصى للطلبات | يعتمد على الاستضافة |

---

## 🐛 استكشاف الأخطاء

### خطأ: "GD Library not found"
```
الحل: تأكد من تثبيت PHP GD extension
```

### خطأ: "Template not found"
```
الحل: تأكد من وجود الملفات:
- templates/ar.jpg
- templates/en.jpg
```

### خطأ: "CORS blocked"
```
الحل: تأكد من رفع ملف .htaccess
```

### النص لا يظهر بشكل صحيح
```
الحل: 
1. حمل الخط العربي: php download_font.php
2. أو عدل المواضع في config.php
```

---

## 📝 ملاحظات

- الصور المرجعة بصيغة base64 جاهزة للعرض مباشرة
- يدعم اللغتين العربية والإنجليزية
- يمكن استخدامه مع أي لغة برمجة تدعم HTTP
- لا يحتاج قاعدة بيانات
- سريع وخفيف

---

## 📞 الدعم

للمساعدة:
1. راجع `QUICK_START_AR.md` للبدء السريع
2. راجع `DEPLOYMENT_GUIDE.md` للنشر
3. جرب `test.html` للاختبار المحلي

---

## 📄 الترخيص

مفتوح المصدر - استخدمه كما تشاء! 🎉

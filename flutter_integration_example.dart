// مثال على كيفية استخدام API الشهادات في Flutter
// ضع هذا الكود في ملف منفصل مثل: lib/services/certificate_api_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;

class CertificateApiService {
  // غير هذا الرابط إلى رابط موقعك بعد الرفع
  static const String baseUrl = 'https://yoursite.com/index.php';
  
  /// توليد شهادة من السيرفر
  /// يرجع الصورة بصيغة base64
  static Future<String> generateCertificate({
    required String studentName,
    required String courseName,
    required String instructorName,
    required String date,
    required String language, // 'ar' or 'en'
  }) async {
    try {
      final response = await http.post(
        Uri.parse(baseUrl),
        headers: {
          'Content-Type': 'application/json',
        },
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
        
        if (data['success'] == true) {
          return data['image']; // base64 image string
        } else {
          throw Exception(data['error'] ?? 'Unknown error');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to generate certificate: $e');
    }
  }
  
  /// حفظ الصورة من base64
  static Future<File> saveBase64Image(String base64String, String fileName) async {
    // إزالة البادئة data:image/jpeg;base64,
    final base64Data = base64String.split(',').last;
    final bytes = base64Decode(base64Data);
    
    final directory = await getTemporaryDirectory();
    final file = File('${directory.path}/$fileName');
    await file.writeAsBytes(bytes);
    
    return file;
  }
}

// مثال على الاستخدام في CertificateScreen
class CertificateScreenWithApi extends StatefulWidget {
  final Course course;
  final String instructorName;

  const CertificateScreenWithApi({
    super.key,
    required this.course,
    required this.instructorName,
  });

  @override
  State<CertificateScreenWithApi> createState() => _CertificateScreenWithApiState();
}

class _CertificateScreenWithApiState extends State<CertificateScreenWithApi> {
  String? _certificateBase64;
  bool _isGenerating = false;
  String _studentName = '';
  String _completionDate = '';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final userData = await AuthService.getUserData();
    setState(() {
      _studentName = userData?.name ?? 'الطالب';
      _completionDate = DateFormat('dd MMMM yyyy', 'ar').format(DateTime.now());
    });
  }

  Future<void> _generateCertificate() async {
    setState(() => _isGenerating = true);

    try {
      final languageService = Provider.of<LanguageService>(context, listen: false);
      final isArabic = languageService.isArabic;

      final certificateImage = await CertificateApiService.generateCertificate(
        studentName: _studentName,
        courseName: widget.course.title,
        instructorName: widget.instructorName,
        date: _completionDate,
        language: isArabic ? 'ar' : 'en',
      );

      setState(() {
        _certificateBase64 = certificateImage;
        _isGenerating = false;
      });
    } catch (e) {
      setState(() => _isGenerating = false);
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('خطأ في توليد الشهادة: $e')),
        );
      }
    }
  }

  Future<void> _downloadCertificate() async {
    if (_certificateBase64 == null) return;

    try {
      final fileName = 'Certificate_${widget.course.title.replaceAll(' ', '_')}.jpg';
      final file = await CertificateApiService.saveBase64Image(
        _certificateBase64!,
        fileName,
      );

      // حفظ في المعرض
      Directory? directory;
      if (Platform.isAndroid) {
        directory = Directory('/storage/emulated/0/Download');
      } else {
        directory = await getApplicationDocumentsDirectory();
      }

      final savedFile = File('${directory.path}/$fileName');
      await file.copy(savedFile.path);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم حفظ الشهادة بنجاح!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('خطأ في حفظ الشهادة: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('الشهادة')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (_certificateBase64 != null)
              Image.memory(
                base64Decode(_certificateBase64!.split(',').last),
                width: 300,
              )
            else
              const Text('لم يتم توليد الشهادة بعد'),
            
            const SizedBox(height: 20),
            
            ElevatedButton(
              onPressed: _isGenerating ? null : _generateCertificate,
              child: _isGenerating
                  ? const CircularProgressIndicator()
                  : const Text('توليد الشهادة'),
            ),
            
            if (_certificateBase64 != null)
              ElevatedButton(
                onPressed: _downloadCertificate,
                child: const Text('تحميل الشهادة'),
              ),
          ],
        ),
      ),
    );
  }
}

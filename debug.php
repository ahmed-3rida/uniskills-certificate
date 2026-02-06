<?php
/**
 * Debug script to check server configuration
 * Open this file in browser to see if everything is working
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فحص النظام</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .check {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .success {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }
        .error {
            background: #ffebee;
            border-left-color: #f44336;
        }
        .warning {
            background: #fff3e0;
            border-left-color: #ff9800;
        }
        .info {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .icon {
            font-size: 20px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 فحص نظام مولد الشهادات</h1>
        
        <?php
        // Check PHP version
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '7.4.0', '>=');
        ?>
        <div class="check <?php echo $phpOk ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $phpOk ? '✅' : '❌'; ?></span>
            <strong>إصدار PHP:</strong> <?php echo $phpVersion; ?>
            <?php if (!$phpOk): ?>
                <br><small>يجب أن يكون PHP 7.4 أو أحدث</small>
            <?php endif; ?>
        </div>

        <?php
        // Check GD Library
        $gdInstalled = extension_loaded('gd');
        ?>
        <div class="check <?php echo $gdInstalled ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $gdInstalled ? '✅' : '❌'; ?></span>
            <strong>GD Library:</strong> <?php echo $gdInstalled ? 'مثبتة' : 'غير مثبتة'; ?>
            <?php if (!$gdInstalled): ?>
                <br><small>GD Library مطلوبة لمعالجة الصور</small>
            <?php endif; ?>
        </div>

        <?php
        // Check JSON support
        $jsonInstalled = function_exists('json_encode');
        ?>
        <div class="check <?php echo $jsonInstalled ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $jsonInstalled ? '✅' : '❌'; ?></span>
            <strong>JSON Support:</strong> <?php echo $jsonInstalled ? 'متوفر' : 'غير متوفر'; ?>
        </div>

        <?php
        // Check config file
        $configExists = file_exists(__DIR__ . '/config.php');
        ?>
        <div class="check <?php echo $configExists ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $configExists ? '✅' : '❌'; ?></span>
            <strong>ملف الإعدادات:</strong> <?php echo $configExists ? 'موجود' : 'غير موجود'; ?>
            <br><small><code>config.php</code></small>
        </div>

        <?php
        // Check templates directory
        $templatesDir = __DIR__ . '/templates';
        $templatesDirExists = is_dir($templatesDir);
        ?>
        <div class="check <?php echo $templatesDirExists ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $templatesDirExists ? '✅' : '❌'; ?></span>
            <strong>مجلد القوالب:</strong> <?php echo $templatesDirExists ? 'موجود' : 'غير موجود'; ?>
            <br><small><code>templates/</code></small>
        </div>

        <?php
        // Check Arabic template
        $arTemplate = $templatesDir . '/ar.jpg';
        $arTemplateExists = file_exists($arTemplate);
        ?>
        <div class="check <?php echo $arTemplateExists ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $arTemplateExists ? '✅' : '❌'; ?></span>
            <strong>الشهادة العربية:</strong> <?php echo $arTemplateExists ? 'موجودة' : 'غير موجودة'; ?>
            <br><small><code>templates/ar.jpg</code></small>
            <?php if ($arTemplateExists): ?>
                <br><small>الحجم: <?php echo number_format(filesize($arTemplate) / 1024, 2); ?> KB</small>
            <?php endif; ?>
        </div>

        <?php
        // Check English template
        $enTemplate = $templatesDir . '/en.jpg';
        $enTemplateExists = file_exists($enTemplate);
        ?>
        <div class="check <?php echo $enTemplateExists ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $enTemplateExists ? '✅' : '❌'; ?></span>
            <strong>الشهادة الإنجليزية:</strong> <?php echo $enTemplateExists ? 'موجودة' : 'غير موجودة'; ?>
            <br><small><code>templates/en.jpg</code></small>
            <?php if ($enTemplateExists): ?>
                <br><small>الحجم: <?php echo number_format(filesize($enTemplate) / 1024, 2); ?> KB</small>
            <?php endif; ?>
        </div>

        <?php
        // Check font file
        $fontPath = __DIR__ . '/fonts/Cairo-Bold.ttf';
        $fontExists = file_exists($fontPath);
        ?>
        <div class="check <?php echo $fontExists ? 'success' : 'warning'; ?>">
            <span class="icon"><?php echo $fontExists ? '✅' : '⚠️'; ?></span>
            <strong>الخط العربي:</strong> <?php echo $fontExists ? 'موجود' : 'غير موجود (اختياري)'; ?>
            <br><small><code>fonts/Cairo-Bold.ttf</code></small>
            <?php if (!$fontExists): ?>
                <br><small>يمكنك تحميله بتشغيل: <code>php download_font.php</code></small>
            <?php endif; ?>
        </div>

        <?php
        // Check write permissions
        $canWrite = is_writable(__DIR__);
        ?>
        <div class="check <?php echo $canWrite ? 'success' : 'warning'; ?>">
            <span class="icon"><?php echo $canWrite ? '✅' : '⚠️'; ?></span>
            <strong>صلاحيات الكتابة:</strong> <?php echo $canWrite ? 'متوفرة' : 'محدودة'; ?>
        </div>

        <?php
        // Overall status
        $allOk = $phpOk && $gdInstalled && $jsonInstalled && $configExists && 
                 $templatesDirExists && $arTemplateExists && $enTemplateExists;
        ?>
        <div class="check <?php echo $allOk ? 'success' : 'error'; ?>" style="margin-top: 30px; font-size: 18px;">
            <span class="icon"><?php echo $allOk ? '🎉' : '⚠️'; ?></span>
            <strong>الحالة العامة:</strong> 
            <?php if ($allOk): ?>
                كل شيء جاهز! يمكنك استخدام النظام الآن.
                <br><br>
                <a href="test.html" style="display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;">
                    جرب الآن →
                </a>
            <?php else: ?>
                يوجد مشاكل يجب حلها أولاً
            <?php endif; ?>
        </div>

        <div class="check info" style="margin-top: 20px;">
            <span class="icon">ℹ️</span>
            <strong>معلومات إضافية:</strong>
            <br>• مسار المجلد: <code><?php echo __DIR__; ?></code>
            <br>• نظام التشغيل: <code><?php echo PHP_OS; ?></code>
            <br>• الوقت الحالي: <code><?php echo date('Y-m-d H:i:s'); ?></code>
        </div>
    </div>
</body>
</html>

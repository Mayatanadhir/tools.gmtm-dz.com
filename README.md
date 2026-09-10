<p align="center">
  <img src="public/build/assets/logo.svg" width="120" height="120" alt="Core Kernel Logo" onerror="this.src='https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg'">
</p>

<h1 align="center">ENGI-MATE Core Kernel — Point Zero Master Template</h1>

<p align="center">
  <strong>Production-Ready Enterprise SaaS & Application Foundation for Laravel 13 & PHP 8.4+</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.4%2B-blue?style=flat-square" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-13.x-red?style=flat-square" alt="Laravel Version">
  <img src="https://img.shields.io/badge/Tests-261%20Passed%20(100%25)-emerald?style=flat-square" alt="Tests">
  <img src="https://img.shields.io/badge/Localization-AR%20%7C%20EN%20%7C%20FR%20(559%20Keys)-orange?style=flat-square" alt="Localization">
  <img src="https://img.shields.io/badge/Code%20Style-Laravel%20Pint-purple?style=flat-square" alt="Pint Style">
  <img src="https://img.shields.io/badge/Template%20Tag-v1.0.0--core--kernel-indigo?style=flat-square" alt="Core Tag">
</p>

---

## 🌟 Overview (نظرة عامة)

**ENGI-MATE Core Kernel (Point Zero)** هو القالب المعماري الأساسي والنواة الأم المجهزة للانطلاق في تطوير أي مشروع أو نظام برمجي ضخم (Enterprise Application / Multi-Tenant SaaS / ERP / CRM / eCommerce).

بدلاً من قضاء أسابيع في بناء أنظمة المصادقة، الصلاحيات، دعم اللغات، الوضع الليلي، والاتصال بقواعد البيانات لكل مشروع جديد، توفر هذه النواة بنية تحتية برمجية ذاتية الشفاء والتهيئة (`Zero-Touch & Self-Healing`) تتيح لك إطلاق أي تطبيق جديد خلال **5 دقائق فقط**.

---

## 🚀 Key Architectural Pillars (الميزات المعمارية الجاهزة)

### 1. Zero-Touch Database Auto-Creation & Auto-Migration (التهجير والإنشاء الذاتي لقاعدة البيانات)
- **إنشاء تلقائي صامت للقواعد الجديدة:** بمجرد تحديد اسم قاعدة بيانات جديدة في `.env`، يقوم وسيط `EnsureDatabaseIsMigrated` بالاتصال بالخادم وإنشاء القاعدة تلقائياً (`CREATE DATABASE IF NOT EXISTS`) دون الحاجة لأي أوامر يدوية.
- **محرك التهجير التلقائي:** يفحص الجداول المفقودة ويشغل ملفات التهجير وتغذية الأدوار والصلاحيات تلقائياً وبشكل صامت.
- **شاشة خطأ احتياطية فاخرة (`503 Fallback View`):** في حال تعذر الاتصال بالسيرفر أو كانت الصلاحيات مقيدة، يتم عرض صفحة تشخيصية راقية توضح حالة الاتصال وإرشادات الحل بدلاً من انهيار التطبيق.

### 2. Zero-State Super Admin Onboarding Gate (بوابة الإعداد الأولي التلقائي)
- **اعتراض حالة الصفر:** عند تشغيل النظام على قاعدة جديدة وفارغة (`User::count() === 0`)، يتم اعتراض جميع الطلبات وتحويل الزائر إلى معالج إعداد حساب السوبر أدمن الأول (`/system-tables/setup`).
- **الإغلاق المحكم التلقائي (Anti-Hijacking Lockdown):** بمجرد إنشاء السوبر أدمن، يتم إغلاق مسار الإعداد نهائياً وإرجاع `404 Not Found` لأي محاولة وصول لاحقة لحماية النظام من التلاعب.

### 3. Enterprise RBAC & Security Quarantine (إدارة الصلاحيات وسجل التدقيق)
- مبني على **Spatie Laravel-Permission** مع حماية خاصة للدور الأساسي `Super-Admin` والدور التلقائي للمستخدمين `User`.
- **التوليد الذاتي للصلاحيات (`PermissionDiscoveryService`):** يقوم بفحص جميع جداول التطبيق وتوليد صلاحيات CRUD تلقائياً لكل جدول وتعيينها للسوبر أدمن.
- **سياسة حماية النفس (Anti-Self-Action Policy):** منع المسؤولين برمجياً من تخفيض رتبهم، قفل حساباتهم، أو حذف أنفسهم.
- **قسم الجداول الأمنية السري المعزول (`system-tables.*`):** عزل تام لأدوات التحقيق الجنائي، الجلسات النشطة، النسخ الاحتياطية، وسجل النشاطات.

### 4. Native Trilingual Localization (المحرك ثلاثي اللغة AR / EN / FR)
- دعم كامل ومتزامن لثلاث لغات: **العربية (افتراضية RTL)، الإنجليزية (LTR)، والفرنسية (LTR)**.
- تطابق 1-إلى-1 لكافة المفاتيح بنسبة 100% عبر `lang/ar.json`, `lang/en.json`, `lang/fr.json` (أكثر من 559 مفتاحاً دون أي مفتاح مفقود).
- حزم أصول منفصلة كلياً لـ RTL و LTR مجمعة عبر Vite لمنع أي تضارب أو وميض بصري (Zero-FOUC).

### 5. Unified Design System (نظام التصميم الموحد الخالي من الستايلات المدمجة)
- التزام صارم بالقاعدة 12: **منع الستايلات المدمجة (`style="..."`) نهائياً**.
- مصفوفة الأزرار الموحدة الدلالية (`<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-success-button>`, `<x-warning-button>`, `<x-info-button>`).
- معمارية الجداول الموحدة (`<x-table>`, `<x-table.th>`, `<x-table.tr>`, `<x-table.td>`, `<x-table.actions>`, `<x-table.empty>`).
- دعم متكامل للوضع الليلي والنهاري (Dark & Light Mode) وحفظ التفضيل عبر التخزين المحلي.

### 6. Dynamic System Settings & Cached Shields (محرك الإعدادات الديناميكي)
- إدارة معلمات التشغيل من قاعدة البيانات مع نظام تخزين مؤقت فائق السرعة (`Cache TTL: 86400s`).
- درع إيقاف وتفعيل التسجيل الفوري مع زر تبديل ثنائي اللغة تفاعلي بـ Alpine.js.

### 7. Living Documentation Protocol (بروتوكول الذاكرة الحية والتوثيق المستمر)
- وثائق هندسية حية ومحدثة باستمرار داخل مجلد `docs/`:
  - `docs/changelog.md`: الأرشيف التاريخي الزمني لجميع التغييرات.
  - `docs/project_state.md`: لقطة شاملة للحالة الراهنة للجداول، المسارات، والاختبارات.
  - `docs/ARCHITECTURE_LOG.md`: سجل القرارات المعمارية وتبريراتها الهندسية (ADRs).

---

## ⚡ How to Spin Up a New Project (كيف تنطلق لإنشاء مشروع جديد في دقائق)

### الخطوة 1: استنساخ النواة الأساسية
```bash
git clone <repository-url> my-new-app
cd my-new-app
composer install
npm install
```

### الخطوة 2: تهيئة ملف البيئة (`.env`)
قم بنسخ ملف `.env.example` وتحديد اسم مشروعك وقاعدة البيانات الجديدة:
```env
APP_NAME="My New System"
DB_DATABASE=my_new_system_db
DB_USERNAME=root
DB_PASSWORD=
```

### الخطوة 3: تخصيص الهوية البصرية (اختياري)
- استبدل الشعار في `resources/views/components/application-logo.blade.php`.
- عدّل الألوان الأساسية في `tailwind.config.js` إذا رغبت في هوية لونية مختلفة.
- قم ببناء الأصول:
```bash
npm run build
```

### الخطوة 4: افتح المتصفح وانطلق!
قم بزيارة رابط المشروع محلياً (مثلاً عبر Laravel Herd):
```
http://my-new-app.test
```
- **سيتولى النظام فوراً وبشكل تلقائي:**
  1. إنشاء قاعدة البيانات `my_new_system_db` في MySQL.
  2. تشغيل كافة ملفات التهجير وتغذية الأدوار والصلاحيات.
  3. تحويلك مباشرة لصفحة إعداد أول حساب سوبر أدمن (`/system-tables/setup`).
  4. بمجرد إدخال البيانات، يتم تفعيل حسابك وتوجيهك إلى لوحة التحكم (`/dashboard`)، ويتم قفل صفحة الإعداد نهائياً!

---

## 🧪 Testing & Code Standards (الاختبارات والمعايير)

تشمل النواة حزمة اختبارات تغطي 100% من الوظائف الأساسية والأمنية:
```bash
# تشغيل حزمة الاختبارات الكاملة (261 اختباراً)
php artisan test

# فحص وتنسيق الكود وفق معايير Laravel Pint
vendor/bin/pint --format agent
```

---

## 📄 License

هذا القالب المعماري متاح ومخصص للاستخدام الداخلي ومشاريع المؤسسة وفق ترخيص [MIT License](LICENSE).

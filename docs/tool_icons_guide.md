# دليل معمارية وتصميم أيقونات الخدمات (Tool Icons Architecture & Design Guide)

هذا الدليل مخصص للمساعدين الذكيين (AI Agents) والمطورين لتوضيح كيفية إنشاء وتضمين أيقونات الخدمات والأدوات (Tool & Service Icons) داخل تطبيقات Laravel بنظام معياري مستقل ومحترف.

---

## 1. الفلسفة المعمارية (Architectural Philosophy)

1. **العزل الكامل (Modular Isolation):** كل أيقونة لأداة أو خدمة يجب أن تكون في ملف Blade مستقل تماماً، دون جمع الأيقونات في ملف ضخم أو استخدام جمل `@switch` عملاقة.
2. **التحميل الديناميكي (Dynamic Component Proxy):** الاعتماد على مكوّن وسيط (`<x-tool-icon>`) يستدعي الأيقونة ديناميكياً باستخدام ميزة `<x-dynamic-component>` في Laravel.
3. **الأمان واحتواء الأخطاء (Fail-Safe Fallback):** في حال عدم وجود ملف الأيقونة لأي سبب، يجب ألا يتعطل التطبيق، بل يتم إظهار شكل افتراضي أنيق تلقائياً.
4. **أيقونات غنية بصرياً (Rich Visual Metaphors):** الأيقونات ليست مجرد خطوط باهتة أحادية اللون (Monochrome strokes)، بل رسومات توضيحية مصغرة تعبر عن وظيفة الأداة بتدرجات وظلال وتفاصيل تفاعلية (Micro-Illustrations).

---

## 2. الهيكل الشجري للملفات (File Structure)

```text
resources/
└── views/
    └── components/
        ├── tool-icon.blade.php                 <-- المكون الوسيط الديناميكي
        └── icons/
            └── tools/                          <-- مجلد الأيقونات الفردية
                ├── {tool-slug}.blade.php
                ├── task-tracker.blade.php
                ├── budget-manager.blade.php
                └── ai-writing-assistant.blade.php
```

---

## 3. المكوّن الوسيط: `resources/views/components/tool-icon.blade.php`

يتم إنشاء هذا المكون ليعمل كمحول ديناميكي:

```blade
@props([
    'name',
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

@php
    $component = 'icons.tools.' . $name;
@endphp

@if(view()->exists('components.' . $component))
    <x-dynamic-component :component="$component" :class="$class" {{ $attributes }} />
@else
    {{-- Generic Tool Fallback Icon --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect x="10" y="10" width="44" height="44" rx="12" fill="#F1F5F9" stroke="#94A3B8" stroke-width="1.5" />
        <path d="M26 32 H38 M32 26 V38" stroke="#0284C7" stroke-width="2.5" stroke-linecap="round" />
    </svg>
@endif
```

---

## 4. مواصفات وقواعد تصميم الـ SVG الفردي (Design System Guidelines)

عند إنشاء أيقونة لأي أداة جديدة داخل `resources/views/components/icons/tools/{tool-slug}.blade.php`:

### أ. الرأس والإعدادات القياسية
```blade
@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    ...
</svg>
```

### ب. قواعد الأبعاد والرسم:
- **نظام الإحداثيات (Canvas):** `viewBox="0 0 64 64"`.
- **معرّفات فريدة للتدرجات والظلال (Unique IDs):**
  - كل تدرج لوني `<linearGradient>` أو فلتر ظل `<filter>` يجب أن يحتوي على بادئة فريدة مستخرجة من اسم الأداة (مثال: `tt-board` لأداة task-tracker أو `bm-bg` لأداة budget-manager) لمنع تضارب المعرفات (ID collisions) في المتصفح عند ظهور أكثر من أيقونة في نفس الصفحة.
- **التدرجات والظلال (Gradients & Shadows):**
  - استخدم لوحات ألوان حديثة ومتناسقة مستوحاة من Tailwind (Slate, Indigo, Emerald, Amber, Violet).
  - استخدم `<feDropShadow>` ناعم لإعطاء عمق ثلاثي الأبعاد مصغر (Micro-depth).
- **التفاصيل المصغرة (Rich Metaphors):**
  - صمم عناصر توحي بالتطبيق الفعلي (مثلاً: أزرار نافذة نظام، بطاقات مصغرة، خطوط بيانية، شارات).

---

## 5. مثال عملي متكامل (Template Example)

ملف: `resources/views/components/icons/tools/example-metric.blade.php`

```blade
@props([
    'class' => 'w-14 h-14 sm:w-16 sm:h-16 shrink-0',
])

{{-- Example Metric: Premium Analytics Window with Bar Chart --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <!-- تدرج فريد يبدأ ببادئة خاصة بالأداة -->
        <linearGradient id="em-card-bg" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
            <stop stop-color="#1E293B" />
            <stop offset="1" stop-color="#0F172A" />
        </linearGradient>
        <filter id="em-shadow" x="4" y="6" width="56" height="54" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="3" stdDeviation="2.5" flood-opacity="0.18" />
        </filter>
    </defs>

    <g filter="url(#em-shadow)">
        <!-- إطار البطاقة / النافذة الأساسية -->
        <rect x="8" y="10" width="48" height="44" rx="8" fill="url(#em-card-bg)" stroke="#334155" stroke-width="1.2" />
        
        <!-- شريط الرأس ونقاط النوافذ -->
        <path d="M8 17 C8 13.1 11.1 10 15 10 H49 C52.9 10 56 13.1 56 17 V19 H8 Z" fill="#334155" />
        <circle cx="13" cy="14.5" r="1.5" fill="#EF4444" />
        <circle cx="18" cy="14.5" r="1.5" fill="#F59E0B" />
        <circle cx="23" cy="14.5" r="1.5" fill="#10B981" />

        <!-- الأعمدة البيانية التوضيحية -->
        <rect x="15" y="36" width="6" height="12" rx="2" fill="#38BDF8" />
        <rect x="25" y="28" width="6" height="20" rx="2" fill="#818CF8" />
        <rect x="35" y="22" width="6" height="26" rx="2" fill="#F43F5E" />
        <rect x="45" y="32" width="6" height="16" rx="2" fill="#34D399" />
    </g>
</svg>
```

---

## 6. كيفية الاستدعاء في القوالب (Usage in Blade)

```blade
{{-- 1. استدعاء مباشر وثابت باسم الأداة --}}
<x-tool-icon name="task-tracker" />

{{-- 2. استدعاء مخصص مع تغيير الحجم أو المظهر --}}
<x-tool-icon name="budget-manager" class="w-20 h-20 hover:scale-105 transition-transform" />

{{-- 3. استدعاء ديناميكي داخل حلقات تكرار البيانات --}}
@foreach($tools as $tool)
    <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 flex items-center gap-4">
        <x-tool-icon :name="$tool->slug" class="w-16 h-16 shrink-0" />
        <div>
            <h3 class="font-bold text-gray-900 dark:text-white">{{ $tool->name }}</h3>
            <p class="text-sm text-gray-500">{{ $tool->description }}</p>
        </div>
    </div>
@endforeach
```

---

## 7. تعليمات للمساعد الذكي عند طلب إضافة أداة جديدة (AI Instructions):
عندما يُطلب منك إضافة أداة جديدة باسم `xyz`:
1. تأكد من تحديد معرّف الأداة بالـ kebab-case: `xyz`.
2. أنشئ ملفاً مستقلاً حصرياً في: `resources/views/components/icons/tools/xyz.blade.php`.
3. لا تقم أبداً بتعديل ملف `tool-icon.blade.php` ولا تضيف أي عبارات `@switch` داخله.
4. استخدم لوحة ألوان وظلال وتفاصيل تتماشى مع نمط باقي الأدوات.

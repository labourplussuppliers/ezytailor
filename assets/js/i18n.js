/**
 * ezyTailor Multi-Language & RTL Engine
 * Supported: English (en), Urdu (ur), Hindi (hi), Arabic (ar)
 */

const TRANSLATIONS = {
    en: {
        dashboard: "Dashboard",
        customers: "Customers",
        orders: "Orders",
        payments: "Payments",
        ledger: "Ledger Book",
        measurements: "Measurements",
        analytics: "Analytics",
        settings: "Settings",
        new_customer: "+ Customer",
        new_order: "+ Order",
        search_placeholder: "Search customers, orders, phone...",
        unpaid_amount: "Unpaid Amount",
        unpaid_orders: "Unpaid Orders",
        total_orders: "Total Orders",
        ready_orders: "Ready for Delivery",
        net_balance: "Net Balance",
        total_income: "Total Income",
        total_expense: "Total Expenses",
        recent_orders: "Recent Orders",
        view_all: "View All",
        order_no: "Order #",
        customer: "Customer",
        garment: "Garment",
        delivery_date: "Delivery Date",
        total: "Total",
        advance: "Advance",
        balance: "Balance",
        status: "Status",
        action: "Actions",
        paid: "Paid",
        partial: "Partial",
        unpaid: "Unpaid",
        pending: "Pending",
        cutting: "Cutting",
        stitching: "Stitching",
        ready: "Ready",
        delivered: "Delivered",
        cancelled: "Cancelled",
        urgent: "Urgent",
        phone: "Phone",
        city: "City",
        date: "Date",
        receipt: "Receipt",
        print_receipt: "Print Receipt",
        save: "Save",
        cancel: "Cancel",
        delete: "Delete",
        edit: "Edit",
        add_payment: "Collect Payment",
        voice_note: "Voice Instruction",
        record_voice: "Record Voice Note",
        recording: "Recording... Click to Stop",
        play_audio: "Play Audio",
        fabric_photo: "Fabric / Style Photo",
        special_instructions: "Special Instructions",
        theme: "Theme",
        language: "Language",
        call: "Call",
        whatsapp: "WhatsApp",
        success: "Success",
        error: "Error"
    },
    ur: {
        dashboard: "ڈیش بورڈ",
        customers: "گاہک / کسٹمرز",
        orders: "آرڈرز",
        payments: "ادائیگیاں",
        ledger: "کھاتہ بک / لیجر",
        measurements: "ناپ اور پیمائش",
        analytics: "کاروباری تجزیہ",
        settings: "ترتیبات / سیٹنگز",
        new_customer: "+ نیا گاہک",
        new_order: "+ نیا آرڈر",
        search_placeholder: "گاہک، فون یا آرڈر تلاش کریں...",
        unpaid_amount: "باقی رقم (غیر ادا شدہ)",
        unpaid_orders: "باقی آرڈرز",
        total_orders: "کل آرڈرز",
        ready_orders: "تیار آرڈرز",
        net_balance: "خالص کیش بیلنس",
        total_income: "کل آمدن",
        total_expense: "کل اخراجات",
        recent_orders: "حالیہ آرڈرز",
        view_all: "تمام دیکھیں",
        order_no: "آرڈر نمبر",
        customer: "گاہک کا نام",
        garment: "لباس کی قسم",
        delivery_date: "واپسی تاریخ",
        total: "کل رقم",
        advance: "ایڈوانس",
        balance: "بقایا رقم",
        status: "حالت",
        action: "ایکشن",
        paid: "مکمل ادا",
        partial: "نامکمل ادا",
        unpaid: "غیر ادا شدہ",
        pending: "زیر التواء",
        cutting: "کٹنگ جاری",
        stitching: "سلائی جاری",
        ready: "تیار ہے",
        delivered: "حوالے کر دیا",
        cancelled: "منسوخ",
        urgent: "ضروری / ارجنٹ",
        phone: "فون نمبر",
        city: "شہر",
        date: "تاریخ",
        receipt: "رسید",
        print_receipt: "رسید پرنٹ کریں",
        save: "محفوظ کریں",
        cancel: "منسوخ",
        delete: "حذف کریں",
        edit: "تبدیل کریں",
        add_payment: "رقم وصول کریں",
        voice_note: "آواز کا نوٹ",
        record_voice: "آواز ریکارڈ کریں",
        recording: "ریکارڈنگ جاری ہے...",
        play_audio: "آواز سنیں",
        fabric_photo: "کپڑے / اسٹائل کی تصویر",
        special_instructions: "خاص ہدایات",
        theme: "تھیم",
        language: "زبان",
        call: "کال کریں",
        whatsapp: "واٹس ایپ",
        success: "کامیابی",
        error: "خرابی"
    },
    hi: {
        dashboard: "डैशबोर्ड",
        customers: "ग्राहक",
        orders: "ऑर्डर्स",
        payments: "भुगतान",
        ledger: "खाता बही (लेजर)",
        measurements: "नाप",
        analytics: "व्यापार विश्लेषण",
        settings: "सेटिंग्स",
        new_customer: "+ नया ग्राहक",
        new_order: "+ नया ऑर्डर",
        search_placeholder: "ग्राहक, फ़ोन या ऑर्डर खोजें...",
        unpaid_amount: "बकाया राशि",
        unpaid_orders: "बकाया ऑर्डर्स",
        total_orders: "कुल ऑर्डर्स",
        ready_orders: "तैयार ऑर्डर्स",
        net_balance: "शुद्ध बैलेंस",
        total_income: "कुल आय",
        total_expense: "कुल खर्च",
        recent_orders: "हालिया ऑर्डर्स",
        view_all: "सभी देखें",
        order_no: "ऑर्डर सं.",
        customer: "ग्राहक",
        garment: "पोशाक",
        delivery_date: "वितरण तिथि",
        total: "कुल",
        advance: "अग्रिम",
        balance: "शेष",
        status: "स्थिति",
        action: "कार्रवाई",
        paid: "भुगतान पूर्ण",
        partial: "आंशिक",
        unpaid: "अदत्त",
        pending: "लंबित",
        cutting: "कटाई",
        stitching: "सिलाई",
        ready: "तैयार",
        delivered: "वितरित",
        cancelled: "रद्द",
        urgent: "अति आवश्यक",
        phone: "फ़ोन",
        city: "शहर",
        date: "दिनांक",
        receipt: "रसीद",
        print_receipt: "रसीद प्रिंट करें",
        save: "सहेजें",
        cancel: "रद्द करें",
        delete: "हटाएं",
        edit: "संपादित करें",
        add_payment: "भुगतान प्राप्त करें",
        voice_note: "वॉइस निर्देश",
        record_voice: "आवाज़ रिकॉर्ड करें",
        recording: "रिकॉर्डिंग जारी...",
        play_audio: "ऑडियो सुनें",
        fabric_photo: "कपड़े की तस्वीर",
        special_instructions: "विशेष निर्देश",
        theme: "थीम",
        language: "भाषा",
        call: "कॉल करें",
        whatsapp: "व्हाट्सएप",
        success: "सफल",
        error: "त्रुटि"
    },
    ar: {
        dashboard: "لوحة التحكم",
        customers: "العملاء",
        orders: "الطلبات",
        payments: "المدفوعات",
        ledger: "دفتر الحسابات",
        measurements: "المقاسات",
        analytics: "التقارير والإحصائيات",
        settings: "الإعدادات",
        new_customer: "+ عميل جديد",
        new_order: "+ طلب جديد",
        search_placeholder: "بحث عن عميل، هاتف أو طلب...",
        unpaid_amount: "المبلغ المتبقي غير المسدد",
        unpaid_orders: "الطلبات غير المسددة",
        total_orders: "إجمالي الطلبات",
        ready_orders: "جاهز للتسليم",
        net_balance: "صافي الرصيد",
        total_income: "إجمالي المقبوضات",
        total_expense: "إجمالي المصروفات",
        recent_orders: "الطلبات الأخيرة",
        view_all: "عرض الكل",
        order_no: "رقم الطلب",
        customer: "العميل",
        garment: "نوع الثوب",
        delivery_date: "موعد الاستلام",
        total: "الإجمالي",
        advance: "الدفعة المقدمة",
        balance: "المتبقي",
        status: "الحالة",
        action: "الإجراءات",
        paid: "مسدد بالكامل",
        partial: "مسدد جزئياً",
        unpaid: "غير مسدد",
        pending: "قيد الانتظار",
        cutting: "التفصيل والقص",
        stitching: "الخياطة",
        ready: "جاهز",
        delivered: "تم التسليم",
        cancelled: "ملغي",
        urgent: "مستعجل جداً",
        phone: "رقم الهاتف",
        city: "المدينة",
        date: "التاريخ",
        receipt: "الفاتورة",
        print_receipt: "طباعة الفاتورة",
        save: "حفظ",
        cancel: "إلغاء",
        delete: "حذف",
        edit: "تعديل",
        add_payment: "قبض دفعة",
        voice_note: "ملاحظة صوتية",
        record_voice: "تسجيل صوتي",
        recording: "جاري التسجيل...",
        play_audio: "تشغيل الصوت",
        fabric_photo: "صورة القماش والموديل",
        special_instructions: "تعليمات خاصة بالخياطة",
        theme: "المظهر",
        language: "اللغة",
        call: "اتصال",
        whatsapp: "واتساب",
        success: "نجاح",
        error: "خطأ"
    }
};

let currentLang = localStorage.getItem('ezytailor_lang') || 'en';

function setLanguage(lang) {
    if (!TRANSLATIONS[lang]) lang = 'en';
    currentLang = lang;
    localStorage.setItem('ezytailor_lang', lang);

    const isRtl = (lang === 'ur' || lang === 'ar');
    document.documentElement.setAttribute('dir', isRtl ? 'rtl' : 'ltr');
    document.documentElement.setAttribute('lang', lang);

    if (lang === 'ur') {
        document.body.classList.add('urdu-font');
    } else {
        document.body.classList.remove('urdu-font');
    }

    // Apply translations to all DOM elements with data-i18n attribute
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (TRANSLATIONS[lang] && TRANSLATIONS[lang][key]) {
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                el.placeholder = TRANSLATIONS[lang][key];
            } else {
                el.textContent = TRANSLATIONS[lang][key];
            }
        }
    });

    // Update active state in language dropdown if exists
    document.querySelectorAll('.lang-item').forEach(item => {
        item.classList.toggle('active', item.getAttribute('data-lang') === lang);
    });

    // Notify backend silently
    fetch((window.APP_BASE_URL || '') + '/api/settings.php?action=set_lang&lang=' + lang).catch(() => {});
}

function __t(key, fallback = '') {
    if (TRANSLATIONS[currentLang] && TRANSLATIONS[currentLang][key]) {
        return TRANSLATIONS[currentLang][key];
    }
    return fallback || key;
}

// Auto init on load
document.addEventListener('DOMContentLoaded', () => {
    setLanguage(currentLang);
});

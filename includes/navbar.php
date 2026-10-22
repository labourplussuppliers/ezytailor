<?php
/**
 * ezyTailor - Top App Bar / Navbar
 */
?>
<header class="app-topbar">
    <div class="topbar-left">
        <button type="button" class="mobile-menu-btn" aria-label="Toggle Menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title">
            <i class="bi bi-scissors"></i>
            <span class="full-name"><?= e($settings['shop_name']) ?></span>
        </div>
    </div>

    <!-- Global Quick Search -->
    <div class="search-box-wrap d-none d-md-block">
        <i class="bi bi-search search-box-icon"></i>
        <form action="orders.php" method="GET">
            <input type="text" name="q" class="search-box-input" placeholder="Search orders, phone, customer..." data-i18n="search_placeholder">
        </form>
    </div>

    <div class="topbar-right">
        <!-- Theme Switcher Dropdown -->
        <div class="dropdown">
            <button class="theme-pill-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Switch Theme">
                <i class="bi bi-palette-fill"></i>
                <span class="d-none d-sm-inline" data-i18n="theme">Theme</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 200px;">
                <li><h6 class="dropdown-header">Choose Color Theme</h6></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-set-theme="olive">
                        <span style="width:14px;height:14px;border-radius:50%;background:#5C6B50;display:inline-block;"></span>
                        Olive / Sage Green (Default)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-set-theme="navy">
                        <span style="width:14px;height:14px;border-radius:50%;background:#1E3A8A;display:inline-block;"></span>
                        Navy Blue
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-set-theme="dark">
                        <span style="width:14px;height:14px;border-radius:50%;background:#0F172A;display:inline-block;"></span>
                        Deep Charcoal / Dark Mode
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-set-theme="maroon">
                        <span style="width:14px;height:14px;border-radius:50%;background:#7B1113;display:inline-block;"></span>
                        Royal Maroon
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-set-theme="teal">
                        <span style="width:14px;height:14px;border-radius:50%;background:#0D5C75;display:inline-block;"></span>
                        Classic Teal
                    </a>
                </li>
            </ul>
        </div>

        <!-- Language Switcher Dropdown -->
        <div class="dropdown">
            <button class="lang-pill-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Change Language">
                <i class="bi bi-translate"></i>
                <span class="d-none d-sm-inline" data-i18n="language">Language</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item lang-item" href="#" onclick="setLanguage('en'); return false;">🇺🇸 English (EN)</a></li>
                <li><a class="dropdown-item lang-item" href="#" onclick="setLanguage('ur'); return false;">🇵🇰 اردو (Urdu - RTL)</a></li>
                <li><a class="dropdown-item lang-item" href="#" onclick="setLanguage('hi'); return false;">🇮🇳 हिन्दी (Hindi)</a></li>
                <li><a class="dropdown-item lang-item" href="#" onclick="setLanguage('ar'); return false;">🇸🇦 العربية (Arabic - RTL)</a></li>
            </ul>
        </div>

        <!-- Quick Book Button -->
        <a href="order_create.php" class="btn btn-sm btn-light fw-bold text-success d-none d-sm-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i>
            <span data-i18n="new_order">+ Order</span>
        </a>
    </div>
</header>
<main class="app-main">

<?php
/**
 * ezyTailor - System Settings, Themes, Languages & Shop Profile
 */

$pageTitle = 'Settings & Themes';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();
$settings = getSettings();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-gear-fill text-primary me-2"></i><span data-i18n="settings">Shop Settings & Multi-Theme</span></h4>
            <p class="text-muted small mb-0">Configure store branding, color themes, thermal receipt formatting, and language preferences.</p>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Left: Shop Profile & Receipt Settings -->
        <div class="col-lg-7">
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-shop text-primary"></i> Boutique & Store Information</span>
                </div>
                <div class="app-card-body p-4">
                    <form id="settings-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Shop / Boutique Name *</label>
                                <input type="text" name="shop_name" class="form-control" value="<?= e($settings['shop_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Master Tailor / Owner Name</label>
                                <input type="text" name="owner_name" class="form-control" value="<?= e($settings['owner_name']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Slogan / Tagline</label>
                                <input type="text" name="tagline" class="form-control" value="<?= e($settings['tagline']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Primary Phone *</label>
                                <input type="text" name="phone" class="form-control" value="<?= e($settings['phone']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">WhatsApp Number</label>
                                <input type="text" name="whatsapp" class="form-control" value="<?= e($settings['whatsapp']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?= e($settings['email']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">City</label>
                                <input type="text" name="city" class="form-control" value="<?= e($settings['city']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Shop Physical Address</label>
                                <input type="text" name="address" class="form-control" value="<?= e($settings['address']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Currency Symbol</label>
                                <input type="text" name="currency" class="form-control" value="<?= e($settings['currency']) ?>" style="max-width:120px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Default Receipt Paper</label>
                                <select name="receipt_paper_size" class="form-select">
                                    <option value="80mm" <?= $settings['receipt_paper_size'] === '80mm' ? 'selected' : '' ?>>80mm Roll (Standard)</option>
                                    <option value="58mm" <?= $settings['receipt_paper_size'] === '58mm' ? 'selected' : '' ?>>58mm Roll (Mini POS)</option>
                                </select>
                            </div>

                            <!-- Thermal Print Customizations -->
                            <div class="col-12 pt-3 border-top">
                                <h6 class="fw-bold mb-3 text-secondary small text-uppercase">Thermal Receipt Texts</h6>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Receipt Header Message</label>
                                <textarea name="receipt_header" class="form-control" rows="2"><?= e($settings['receipt_header']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Receipt Footer Message</label>
                                <textarea name="receipt_footer" class="form-control" rows="2"><?= e($settings['receipt_footer']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Terms & Conditions</label>
                                <textarea name="terms" class="form-control" rows="3"><?= e($settings['terms']) ?></textarea>
                            </div>

                            <div class="col-12 text-end pt-3">
                                <button type="submit" class="btn btn-primary-custom px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Save Store Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Multi-Theme & Multi-Language Settings -->
        <div class="col-lg-5">
            
            <!-- 1. Multi-Theme Switcher (Requirement 1) -->
            <div class="app-card mb-4">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-palette-fill text-primary"></i> 1. Color Themes</span>
                </div>
                <div class="app-card-body p-3">
                    <p class="text-muted small mb-3">Choose a theme palette to style the app. Switches instantly and persists across refreshes and devices.</p>
                    
                    <div class="d-flex flex-column gap-2">
                        <!-- Olive Green (Default - Screenshot Match) -->
                        <div class="p-3 rounded border d-flex justify-content-between align-items-center theme-option cursor-pointer" 
                             data-theme="olive" onclick="Themes.apply('olive', true)">
                            <div class="d-flex align-items-center gap-3">
                                <span style="width:24px;height:24px;border-radius:50%;background:#5C6B50;display:inline-block;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,0.3);"></span>
                                <div>
                                    <div class="fw-bold">Elegant Olive / Sage Green</div>
                                    <small class="text-muted">Primary #5C6B50, Off-White #F7F6F2 (Screenshot Match)</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Select</button>
                        </div>

                        <!-- Navy Blue -->
                        <div class="p-3 rounded border d-flex justify-content-between align-items-center theme-option cursor-pointer" 
                             data-theme="navy" onclick="Themes.apply('navy', true)">
                            <div class="d-flex align-items-center gap-3">
                                <span style="width:24px;height:24px;border-radius:50%;background:#1E3A8A;display:inline-block;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,0.3);"></span>
                                <div>
                                    <div class="fw-bold">Navy Blue</div>
                                    <small class="text-muted">Corporate deep navy with clean blue accents</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Select</button>
                        </div>

                        <!-- Deep Charcoal / Dark Mode -->
                        <div class="p-3 rounded border d-flex justify-content-between align-items-center theme-option cursor-pointer" 
                             data-theme="dark" onclick="Themes.apply('dark', true)">
                            <div class="d-flex align-items-center gap-3">
                                <span style="width:24px;height:24px;border-radius:50%;background:#0F172A;display:inline-block;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,0.3);"></span>
                                <div>
                                    <div class="fw-bold">Deep Charcoal / Dark Mode</div>
                                    <small class="text-muted">Modern dark theme for night usage and low eye strain</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Select</button>
                        </div>

                        <!-- Royal Maroon -->
                        <div class="p-3 rounded border d-flex justify-content-between align-items-center theme-option cursor-pointer" 
                             data-theme="maroon" onclick="Themes.apply('maroon', true)">
                            <div class="d-flex align-items-center gap-3">
                                <span style="width:24px;height:24px;border-radius:50%;background:#7B1113;display:inline-block;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,0.3);"></span>
                                <div>
                                    <div class="fw-bold">Royal Maroon</div>
                                    <small class="text-muted">Rich luxury maroon for high-end boutique stores</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Select</button>
                        </div>

                        <!-- Classic Teal -->
                        <div class="p-3 rounded border d-flex justify-content-between align-items-center theme-option cursor-pointer" 
                             data-theme="teal" onclick="Themes.apply('teal', true)">
                            <div class="d-flex align-items-center gap-3">
                                <span style="width:24px;height:24px;border-radius:50%;background:#0D5C75;display:inline-block;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,0.3);"></span>
                                <div>
                                    <div class="fw-bold">Classic Teal</div>
                                    <small class="text-muted">Sophisticated ocean teal palette</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Select</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Multi-Language Switcher (Requirement 8) -->
            <div class="app-card mb-4">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-translate text-primary"></i> 2. Language & RTL Layout</span>
                </div>
                <div class="app-card-body p-3">
                    <p class="text-muted small mb-3">Switch between English, Urdu, Hindi, and Arabic. Urdu and Arabic automatically activate right-to-left (RTL) typography.</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100 py-2 text-start lang-option-btn" onclick="setLanguage('en')">
                                🇺🇸 <strong>English</strong>
                                <small class="text-muted d-block">Default LTR</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100 py-2 text-end lang-option-btn" onclick="setLanguage('ur')">
                                🇵🇰 <strong>اردو</strong>
                                <small class="text-muted d-block">Nastaliq RTL</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100 py-2 text-start lang-option-btn" onclick="setLanguage('hi')">
                                🇮🇳 <strong>हिन्दी</strong>
                                <small class="text-muted d-block">Hindi LTR</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100 py-2 text-end lang-option-btn" onclick="setLanguage('ar')">
                                🇸🇦 <strong>العربية</strong>
                                <small class="text-muted d-block">Arabic RTL</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Sample Data Reset -->
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-title text-danger"><i class="bi bi-arrow-repeat text-danger"></i> Reset Sample Data</span>
                </div>
                <div class="app-card-body p-3">
                    <p class="small text-muted mb-3">Re-import demo customers, orders, measurement templates, and ledger transactions.</p>
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold" onclick="resetDemoData()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reload Sample Database
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
document.getElementById('settings-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch((window.APP_BASE_URL || '') + '/api/settings.php?action=save', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Toast.show(data.message, 'success');
        } else {
            Toast.show(data.message || 'Error saving settings', 'error');
        }
    })
    .catch(err => Toast.show('Error saving settings', 'error'));
});

function resetDemoData() {
    if (confirm('Are you sure you want to reload sample demonstration data? Existing test entries will be reset to factory defaults.')) {
        fetch((window.APP_BASE_URL || '') + '/api/settings.php?action=reset_demo')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Toast.show(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Toast.show(data.message || 'Error reloading data', 'error');
                }
            });
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

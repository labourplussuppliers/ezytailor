<?php
/**
 * ezyTailor - Smart Measurements & Dynamic Template Form Builder
 */

$pageTitle = 'Measurements & Templates';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

$selectedCustomerId = (int)($_GET['customer_id'] ?? 0);
$editId = (int)($_GET['edit_id'] ?? 0);

// Fetch active customers
$customers = $pdo->query("SELECT id, name, phone, customer_code FROM customers ORDER BY name ASC")->fetchAll();

// Fetch templates
$templates = $pdo->query("SELECT * FROM measurement_templates WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

// Existing measurement if edit mode
$editMeasurement = null;
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM customer_measurements WHERE id = ?");
    $stmt->execute([$editId]);
    $editMeasurement = $stmt->fetch();
    if ($editMeasurement) {
        $selectedCustomerId = $editMeasurement['customer_id'];
    }
}

// Fetch all saved measurements for listing/history
$allMeasurementsStmt = $pdo->query("
    SELECT cm.*, c.name as customer_name, c.phone as customer_phone, c.customer_code, mt.title as template_title, mt.icon as template_icon
    FROM customer_measurements cm
    JOIN customers c ON cm.customer_id = c.id
    LEFT JOIN measurement_templates mt ON cm.template_id = mt.id
    ORDER BY cm.id DESC
    LIMIT 100
");
$allMeasurements = $allMeasurementsStmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-rulers text-primary me-2"></i><span data-i18n="measurements">Smart Measurements & Templates</span></h4>
            <p class="text-muted small mb-0">Record and customize exact client body dimensions, visual guides, style options, and voice instructions.</p>
        </div>
        <button type="button" class="btn btn-outline-custom btn-sm" id="btn-add-custom-field">
            <i class="bi bi-plus-lg me-1"></i> Add Custom Measurement Field
        </button>
    </div>

    <!-- Main Builder Row -->
    <div class="row g-4">
        
        <!-- Form Builder (Left Column) -->
        <div class="col-lg-8">
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-title">
                        <i class="bi bi-pencil-square text-primary"></i> 
                        <?= $editMeasurement ? 'Edit Measurement Fit' : 'Record New Customer Measurement' ?>
                    </span>
                </div>
                <div class="app-card-body">
                    <form id="measurement-form">
                        <input type="hidden" name="id" value="<?= $editMeasurement['id'] ?? 0 ?>">
                        <input type="hidden" name="template_id" id="template_id_input" value="<?= $editMeasurement['template_id'] ?? 1 ?>">
                        <input type="hidden" name="garment_type" id="garment_type_input" value="<?= $editMeasurement['garment_type'] ?? 'male_shalwar_kameez' ?>">
                        <input type="hidden" name="fabric_photo" id="m_hidden_fabric_photo" value="<?= $editMeasurement['fabric_photo'] ?? '' ?>">
                        <input type="hidden" name="audio_note" id="m_hidden_audio_note" value="<?= $editMeasurement['audio_note'] ?? '' ?>">

                        <!-- 1. Customer & Template Pickers -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Customer *</label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">-- Choose Customer --</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $selectedCustomerId === (int)$c['id'] ? 'selected' : '' ?>>
                                            <?= e($c['name']) ?> (<?= e($c['phone']) ?>) - <?= e($c['customer_code']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Garment Template *</label>
                                <select id="measurement_template_select" class="form-select" required>
                                    <!-- Rendered dynamically by measurements.js -->
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Measurement Profile Title</label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?= e($editMeasurement['title'] ?? 'Standard Fit') ?>" 
                                       placeholder="e.g. Eid Special Fit, Office Slim Fit, Loose Comfort Fit">
                            </div>
                        </div>

                        <!-- 2. Dynamic Measurement Fields Grid -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0 text-secondary small text-uppercase">
                                    <i class="bi bi-aspect-ratio text-primary me-1"></i> Body Dimensions (Inches)
                                </label>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none" onclick="Measurements.addCustomFieldPrompt()">
                                    <i class="bi bi-plus-circle"></i> + Add Field
                                </button>
                            </div>
                            <div class="measurement-fields-grid" id="measurement_fields_container">
                                <!-- Populated dynamically from template fields -->
                            </div>
                        </div>

                        <!-- 3. Style Preferences & Cuts -->
                        <div class="mb-4 pt-3 border-top">
                            <label class="form-label fw-bold mb-3 text-secondary small text-uppercase">
                                <i class="bi bi-palette text-primary me-1"></i> Style Preferences & Cuts
                            </label>
                            <div class="row g-2" id="style_options_container">
                                <!-- Populated dynamically -->
                            </div>
                        </div>

                        <!-- 4. Photo & Audio Instructions -->
                        <div class="mb-4 pt-3 border-top">
                            <label class="form-label fw-bold mb-3 text-secondary small text-uppercase">
                                <i class="bi bi-mic text-primary me-1"></i> Voice Recording & Fabric Swatch
                            </label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 voice-record-toggle-btn mb-2"
                                                data-target-input="#m_hidden_audio_note" 
                                                data-target-player="#m_voice_player">
                                            <i class="bi bi-mic-fill"></i> Record Voice Instructions
                                        </button>
                                        <audio id="m_voice_player" controls class="w-100" style="display:none;height:35px;"></audio>
                                        <?php if (!empty($editMeasurement['audio_note'])): ?>
                                            <div class="mt-2 text-start small">
                                                <span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Existing Voice Note:</span>
                                                <audio controls class="w-100 mt-1" style="height:35px;">
                                                    <source src="<?= UPLOAD_URL ?>/audio/<?= e($editMeasurement['audio_note']) ?>">
                                                </audio>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Sample Swatch / Style Photo</label>
                                    <input type="file" id="m_fabric_upload" class="form-control form-control-sm" accept="image/*">
                                    <div class="mt-2">
                                        <img id="m_fabric_preview" src="" alt="Preview" class="img-thumbnail" style="display:none;max-height:80px;">
                                        <?php if (!empty($editMeasurement['fabric_photo'])): ?>
                                            <img src="<?= UPLOAD_URL ?>/fabrics/<?= e($editMeasurement['fabric_photo']) ?>" class="img-thumbnail mt-1" style="max-height:80px;">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. General Notes -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Master Tailor Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Keep extra margin on side seams for future alterations"><?= e($editMeasurement['notes'] ?? '') ?></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="reset" class="btn btn-light">Reset</button>
                            <button type="submit" class="btn btn-primary-custom px-4 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Save Measurement Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Visual Body Measurement Guide & Guide Card (Right Column) -->
        <div class="col-lg-4">
            <!-- Visual Body Guide Card -->
            <div class="app-card mb-4">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-info-circle text-primary"></i> Body Measurement Guide</span>
                </div>
                <div class="app-card-body p-3">
                    <div class="text-center p-3 mb-3 bg-light rounded border">
                        <!-- Visual Tailoring Mannequin SVG Diagram -->
                        <svg viewBox="0 0 200 300" style="max-height:220px;width:100%;" xmlns="http://www.w3.org/2000/svg">
                            <!-- Head & Neck -->
                            <circle cx="100" cy="35" r="22" fill="#E2E8F0" stroke="#5C6B50" stroke-width="2"/>
                            <!-- Shoulders line -->
                            <path d="M 50 70 L 150 70" stroke="#DC2626" stroke-width="2.5" stroke-dasharray="4,3"/>
                            <text x="100" y="65" font-size="9" fill="#DC2626" font-weight="bold" text-anchor="middle">Shoulder / Tira</text>
                            <!-- Torso -->
                            <path d="M 60 70 L 140 70 L 130 170 L 70 170 Z" fill="#F1F5F9" stroke="#5C6B50" stroke-width="2"/>
                            <!-- Chest line -->
                            <path d="M 63 100 L 137 100" stroke="#2563EB" stroke-width="2" stroke-dasharray="3,3"/>
                            <text x="100" y="97" font-size="9" fill="#2563EB" font-weight="bold" text-anchor="middle">Chest / Chaati</text>
                            <!-- Waist line -->
                            <path d="M 68 135 L 132 135" stroke="#059669" stroke-width="2" stroke-dasharray="3,3"/>
                            <text x="100" y="132" font-size="9" fill="#059669" font-weight="bold" text-anchor="middle">Waist / Kamar</text>
                            <!-- Arms -->
                            <path d="M 55 75 L 35 150 L 30 185" stroke="#5C6B50" stroke-width="6" stroke-linecap="round"/>
                            <path d="M 145 75 L 165 150 L 170 185" stroke="#5C6B50" stroke-width="6" stroke-linecap="round"/>
                            <!-- Arm Length indicator -->
                            <text x="180" y="130" font-size="9" fill="#D97706" font-weight="bold">Sleeves</text>
                            <!-- Bottom / Legs -->
                            <path d="M 75 170 L 70 280 M 125 170 L 130 280" stroke="#5C6B50" stroke-width="8" stroke-linecap="round"/>
                            <text x="100" y="240" font-size="9" fill="#4B5563" text-anchor="middle">Length / Lambai</text>
                        </svg>
                    </div>

                    <div class="small text-muted">
                        <p class="mb-2"><strong>Standard Measurement Guidelines:</strong></p>
                        <ul class="ps-3 mb-0">
                            <li><strong>Tera (Shoulder):</strong> Measure from tip of left shoulder bone across nape to right shoulder.</li>
                            <li><strong>Chaati (Chest):</strong> Measure around fullest part of chest keeping tape level.</li>
                            <li><strong>Loosing:</strong> Standard comfort looseness is 3.5 to 5.0 inches.</li>
                            <li><strong>Bazu (Sleeve):</strong> From shoulder point down along elbow to wrist joint.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Built-in Templates Summary -->
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-collection text-primary"></i> 7 Built-in Templates</span>
                </div>
                <div class="list-group list-group-flush small">
                    <?php foreach ($templates as $t): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi <?= e($t['icon'] ?: 'bi-scissors') ?> text-primary me-2"></i>
                                <strong><?= e($t['title']) ?></strong>
                                <span class="text-muted d-block" style="font-size:0.75rem;"><?= e($t['title_ur'] ?: '') ?></span>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('measurement_template_select').value='<?= $t['template_key'] ?>'; Measurements.selectTemplate('<?= $t['template_key'] ?>');">
                                Use
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Saved Measurements Listing Card -->
    <div class="app-card mt-4">
        <div class="app-card-header">
            <span class="app-card-title"><i class="bi bi-clock-history text-primary"></i> Recently Saved Customer Measurements</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Profile Title</th>
                        <th>Garment</th>
                        <th>Dimensions Snapshot</th>
                        <th>Voice Note</th>
                        <th>Last Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allMeasurements)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No measurements recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allMeasurements as $am): 
                            $dim = json_decode($am['measurements_json'], true) ?: [];
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><a href="customer_view.php?id=<?= $am['customer_id'] ?>" class="text-decoration-none text-dark"><?= e($am['customer_name']) ?></a></div>
                                    <small class="text-muted"><i class="bi bi-telephone"></i> <?= e($am['customer_phone']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($am['title']) ?></span></td>
                                <td class="text-capitalize"><?= ucwords(str_replace('_', ' ', $am['garment_type'])) ?></td>
                                <td>
                                    <small class="text-muted">
                                        L: <?= $dim['lambai'] ?? $dim['kameez_length'] ?? $dim['length'] ?? '-' ?>" | 
                                        T: <?= $dim['tera'] ?? $dim['shoulder'] ?? '-' ?>" | 
                                        C: <?= $dim['chaati'] ?? $dim['chest'] ?? '-' ?>"
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($am['audio_note'])): ?>
                                        <span class="badge bg-danger"><i class="bi bi-mic-fill"></i> Audio Note</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= format_date($am['updated_at']) ?></small></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="measurements.php?edit_id=<?= $am['id'] ?>" class="btn btn-outline-primary" title="Edit Fit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="order_create.php?customer_id=<?= $am['customer_id'] ?>&measurement_id=<?= $am['id'] ?>" class="btn btn-outline-success" title="Book Order">
                                            <i class="bi bi-scissors"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Upload preview hook -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const uploadInput = document.getElementById('m_fabric_upload');
    if (uploadInput) {
        uploadInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('photo', file);
                fetch((window.APP_BASE_URL || '') + '/api/upload.php?type=fabric', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success && d.filename) {
                            document.getElementById('m_hidden_fabric_photo').value = d.filename;
                            const prev = document.getElementById('m_fabric_preview');
                            prev.src = d.url;
                            prev.style.display = 'block';
                            Toast.show('Fabric photo attached!', 'success');
                        }
                    });
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

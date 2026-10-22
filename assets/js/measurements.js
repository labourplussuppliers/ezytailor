/**
 * ezyTailor - Smart Measurements & Dynamic Template Builder JS
 */

const Measurements = {
    templates: [],
    currentTemplate: null,

    init() {
        this.loadTemplates();
        this.bindEvents();
    },

    loadTemplates() {
        fetch((window.APP_BASE_URL || '') + '/api/measurements.php?action=templates')
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    this.templates = res.data;
                    this.renderTemplateSelector();
                }
            })
            .catch(err => console.error(err));
    },

    bindEvents() {
        // Template dropdown change
        const select = document.getElementById('measurement_template_select');
        if (select) {
            select.addEventListener('change', (e) => {
                this.selectTemplate(e.target.value);
            });
        }

        // Add custom field button
        const addFieldBtn = document.getElementById('btn-add-custom-field');
        if (addFieldBtn) {
            addFieldBtn.addEventListener('click', () => {
                this.addCustomFieldPrompt();
            });
        }

        // Measurement form submission
        const form = document.getElementById('measurement-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.save(form);
            });
        }
    },

    renderTemplateSelector() {
        const select = document.getElementById('measurement_template_select');
        if (!select) return;

        const currentVal = select.value;
        select.innerHTML = '<option value="">-- Select Garment Template --</option>' + 
            this.templates.map(t => `<option value="${t.template_key}">${t.title} (${t.title_ur || ''})</option>`).join('');

        if (currentVal) {
            select.value = currentVal;
            this.selectTemplate(currentVal);
        } else if (this.templates.length > 0) {
            select.value = this.templates[0].template_key;
            this.selectTemplate(this.templates[0].template_key);
        }
    },

    selectTemplate(templateKey, existingData = null, existingStyles = null) {
        const t = this.templates.find(item => item.template_key === templateKey);
        if (!t) return;

        this.currentTemplate = t;

        // Set hidden garment_type
        const gTypeInput = document.getElementById('garment_type_input');
        if (gTypeInput) gTypeInput.value = t.template_key;

        const tIdInput = document.getElementById('template_id_input');
        if (tIdInput) tIdInput.value = t.id;

        // Render measurement input fields
        const fieldsContainer = document.getElementById('measurement_fields_container');
        if (fieldsContainer && t.fields) {
            fieldsContainer.innerHTML = t.fields.map(f => {
                const val = (existingData && existingData[f.key]) ? existingData[f.key] : (f.default || '');
                const labelUr = f.label_ur ? ` / ${f.label_ur}` : '';
                return `
                    <div class="measurement-field-card" data-key="${f.key}">
                        <div class="measurement-field-label">
                            <span>${f.label} <small class="text-muted font-monospace">${labelUr}</small></span>
                            <span class="unit">${f.unit || 'in'}</span>
                        </div>
                        <div class="measurement-input-wrap">
                            <input type="number" step="0.125" name="measurements[${f.key}]" 
                                   class="form-control measurement-input" 
                                   value="${val}" placeholder="0.0">
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Render style options
        const stylesContainer = document.getElementById('style_options_container');
        if (stylesContainer) {
            if (t.styles && t.styles.length > 0) {
                stylesContainer.innerHTML = t.styles.map(s => {
                    const selVal = (existingStyles && existingStyles[s.key]) ? existingStyles[s.key] : (s.options[0] || '');
                    return `
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold small text-secondary">${s.label}</label>
                            <select name="styles[${s.key}]" class="form-select form-select-sm">
                                ${s.options.map(opt => `<option value="${opt}" ${opt === selVal ? 'selected' : ''}>${opt}</option>`).join('')}
                            </select>
                        </div>
                    `;
                }).join('');
            } else {
                stylesContainer.innerHTML = `<div class="col-12 text-muted small py-2">No predefined styling options for this garment.</div>`;
            }
        }
    },

    addCustomFieldPrompt() {
        const fieldName = prompt("Enter Custom Measurement Field Name (e.g., 'Wrist Loosing' or 'Pocket Depth'):");
        if (!fieldName || !fieldName.trim()) return;

        const key = 'custom_' + fieldName.trim().toLowerCase().replace(/[^a-z0-9]/g, '_');
        const defaultVal = prompt("Enter Default Value in inches (optional):", "0") || "0";

        const fieldsContainer = document.getElementById('measurement_fields_container');
        if (fieldsContainer) {
            const div = document.createElement('div');
            div.className = 'measurement-field-card border-primary';
            div.innerHTML = `
                <div class="measurement-field-label text-primary">
                    <span>${fieldName.trim()}</span>
                    <span class="unit">in</span>
                </div>
                <div class="measurement-input-wrap">
                    <input type="number" step="0.125" name="measurements[${key}]" 
                           class="form-control measurement-input" 
                           value="${defaultVal}">
                </div>
            `;
            fieldsContainer.appendChild(div);
            Toast.show(`Added custom field "${fieldName.trim()}"`, 'success');
        }
    },

    save(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch((window.APP_BASE_URL || '') + '/api/measurements.php?action=save', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success) {
                Toast.show(data.message, 'success');
                // redirect or reload if needed
                setTimeout(() => {
                    const custId = form.querySelector('[name="customer_id"]').value;
                    if (custId) {
                        window.location.href = `customer_view.php?id=${custId}&tab=measurements`;
                    } else {
                        window.location.reload();
                    }
                }, 1000);
            } else {
                Toast.show(data.message || 'Error saving measurement', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error(err);
            Toast.show('An unexpected error occurred', 'error');
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Measurements.init();
});

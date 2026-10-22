/**
 * ezyTailor - Order Booking & Thermal Receipt JS
 */

const Orders = {
    init() {
        this.bindEvents();
        this.calculateFinancials();
    },

    bindEvents() {
        // Customer change in order booking form -> load their measurements
        const custSelect = document.getElementById('order_customer_select');
        if (custSelect) {
            custSelect.addEventListener('change', (e) => {
                this.loadCustomerMeasurements(e.target.value);
            });
        }

        // Quantity +/- buttons
        const qtyMinus = document.getElementById('btn-qty-minus');
        const qtyPlus = document.getElementById('btn-qty-plus');
        const qtyInput = document.getElementById('order_quantity_input');

        if (qtyMinus && qtyInput) {
            qtyMinus.addEventListener('click', () => {
                let current = parseInt(qtyInput.value) || 1;
                if (current > 1) {
                    qtyInput.value = current - 1;
                    this.calculateFinancials();
                }
            });
        }

        if (qtyPlus && qtyInput) {
            qtyPlus.addEventListener('click', () => {
                let current = parseInt(qtyInput.value) || 1;
                qtyInput.value = current + 1;
                this.calculateFinancials();
            });
        }

        // Unit Price and Advance Paid input recalculations
        const priceInput = document.getElementById('order_unit_price');
        const advInput = document.getElementById('order_advance_paid');

        if (priceInput) {
            priceInput.addEventListener('input', () => this.calculateFinancials());
        }
        if (advInput) {
            advInput.addEventListener('input', () => this.calculateFinancials());
        }

        // Delivery date shortcut presets (+1 day, +3 days, +7 days)
        document.querySelectorAll('[data-delivery-preset]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const days = parseInt(btn.getAttribute('data-delivery-preset')) || 7;
                const d = new Date();
                d.setDate(d.getDate() + days);
                const dateStr = d.toISOString().split('T')[0];
                const dateInput = document.getElementById('order_delivery_date');
                if (dateInput) dateInput.value = dateStr;
                Toast.show(`Delivery date set to ${dateStr}`, 'info', 1800);
            });
        });

        // Booking form submit
        const form = document.getElementById('order-booking-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createOrder(form);
            });
        }

        // Order status change dropdown
        document.addEventListener('change', (e) => {
            const statusSelect = e.target.closest('.order-status-select');
            if (statusSelect) {
                const orderId = statusSelect.getAttribute('data-order-id');
                const newStatus = statusSelect.value;
                this.updateStatus(orderId, newStatus, statusSelect);
            }
        });

        // Fabric image preview on upload
        const fabricInput = document.getElementById('fabric_photo_input');
        if (fabricInput) {
            fabricInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    this.uploadFabricImage(file);
                }
            });
        }
    },

    calculateFinancials() {
        const qtyInput = document.getElementById('order_quantity_input');
        const priceInput = document.getElementById('order_unit_price');
        const advInput = document.getElementById('order_advance_paid');
        const totalEl = document.getElementById('order_total_display');
        const totalInput = document.getElementById('order_total_amount');
        const balanceEl = document.getElementById('order_balance_display');
        const balanceInput = document.getElementById('order_balance_due');

        const qty = parseInt(qtyInput?.value) || 1;
        const price = parseFloat(priceInput?.value) || 0;
        const advance = parseFloat(advInput?.value) || 0;

        const total = qty * price;
        const balance = Math.max(0, total - advance);

        if (totalEl) totalEl.textContent = 'Rs ' + total.toLocaleString();
        if (totalInput) totalInput.value = total;
        if (balanceEl) balanceEl.textContent = 'Rs ' + balance.toLocaleString();
        if (balanceInput) balanceInput.value = balance;
    },

    loadCustomerMeasurements(customerId) {
        const select = document.getElementById('order_measurement_select');
        if (!select) return;

        if (!customerId) {
            select.innerHTML = '<option value="">-- Select Customer First --</option>';
            return;
        }

        fetch((window.APP_BASE_URL || '') + `/api/measurements.php?action=customer_measurements&customer_id=${customerId}`)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    if (res.data.length === 0) {
                        select.innerHTML = '<option value="">No saved measurements (Click "+ Add Measurement")</option>';
                    } else {
                        select.innerHTML = '<option value="">-- Choose Customer Fit --</option>' + 
                            res.data.map(m => `<option value="${m.id}" data-garment="${m.garment_type}">${m.title} (${m.template_name || m.garment_type})</option>`).join('');
                    }
                }
            });
    },

    uploadFabricImage(file) {
        const formData = new FormData();
        formData.append('photo', file);

        fetch((window.APP_BASE_URL || '') + '/api/upload.php?type=fabric', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.filename) {
                const hidden = document.getElementById('hidden_fabric_photo');
                if (hidden) hidden.value = data.filename;

                const preview = document.getElementById('fabric_preview_img');
                if (preview) {
                    preview.src = data.url;
                    preview.style.display = 'block';
                }
                Toast.show('Fabric swatch uploaded!', 'success');
            } else {
                Toast.show(data.message || 'Image upload failed', 'error');
            }
        })
        .catch(err => console.error(err));
    },

    createOrder(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch((window.APP_BASE_URL || '') + '/api/orders.php?action=create', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success) {
                Toast.show(data.message, 'success');
                // Open Receipt immediately
                if (confirm('Order Created! Would you like to print the Thermal POS Receipt now?')) {
                    window.open(`receipt.php?id=${data.order_id}`, '_blank');
                }
                window.location.href = 'orders.php';
            } else {
                Toast.show(data.message || 'Error booking order', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error(err);
            Toast.show('An unexpected error occurred', 'error');
        });
    },

    updateStatus(orderId, newStatus, selectEl) {
        const formData = new FormData();
        formData.append('id', orderId);
        formData.append('status', newStatus);

        fetch((window.APP_BASE_URL || '') + '/api/orders.php?action=update_status', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Toast.show(data.message, 'success');
            } else {
                Toast.show(data.message || 'Failed to update status', 'error');
            }
        })
        .catch(err => console.error(err));
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Orders.init();
});

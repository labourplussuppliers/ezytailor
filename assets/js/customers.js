/**
 * ezyTailor - Customers Management JS
 */

const Customers = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        // Real-time search with debounce
        let searchTimer = null;
        const searchInput = document.getElementById('customer-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    this.loadList(e.target.value);
                }, 300);
            });
        }

        // Customer form submit
        const form = document.getElementById('customer-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.save(form);
            });
        }

        // Direct WhatsApp click handler
        document.addEventListener('click', (e) => {
            const waBtn = e.target.closest('.btn-whatsapp');
            if (waBtn) {
                const phone = waBtn.getAttribute('data-phone');
                const name = waBtn.getAttribute('data-name');
                const balance = waBtn.getAttribute('data-balance') || '0';
                this.openWhatsApp(phone, name, balance);
            }
        });
    },

    loadList(query = '') {
        const tableBody = document.getElementById('customers-table-body');
        const cardsGrid = document.getElementById('customers-cards-grid');
        if (!tableBody && !cardsGrid) return;

        fetch((window.APP_BASE_URL || '') + '/api/customers.php?action=list&q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const customers = res.data;

                if (tableBody) {
                    if (customers.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No customers found.</td></tr>`;
                    } else {
                        tableBody.innerHTML = customers.map(c => `
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace">${c.customer_code}</span></td>
                                <td>
                                    <div class="fw-bold">${c.name}</div>
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> ${c.city || 'Lahore'}</small>
                                </td>
                                <td>
                                    <div><a href="tel:${c.phone}" class="text-decoration-none text-dark fw-semibold"><i class="bi bi-telephone-fill text-success me-1"></i> ${c.phone}</a></div>
                                </td>
                                <td>${c.total_orders} orders</td>
                                <td>
                                    ${parseFloat(c.total_balance_due) > 0 
                                        ? `<span class="badge badge-unpaid">Rs ${Number(c.total_balance_due).toLocaleString()} Due</span>` 
                                        : `<span class="badge badge-paid">Clear</span>`}
                                </td>
                                <td><small class="text-muted">${c.created_at ? c.created_at.substring(0, 10) : '-'}</small></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="customer_view.php?id=${c.id}" class="btn btn-outline-secondary" title="View Profile"><i class="bi bi-eye"></i></a>
                                        <button type="button" class="btn btn-outline-success btn-whatsapp" data-phone="${c.phone}" data-name="${c.name}" data-balance="${c.total_balance_due}" title="WhatsApp"><i class="bi bi-whatsapp"></i></button>
                                        <button type="button" class="btn btn-outline-primary" onclick="Customers.edit(${c.id})" title="Edit"><i class="bi bi-pencil"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                    }
                }
            })
            .catch(err => console.error(err));
    },

    save(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch((window.APP_BASE_URL || '') + '/api/customers.php?action=save', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success) {
                Toast.show(data.message, 'success');
                // Close modal
                const modalEl = document.getElementById('modal-customer-add');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                form.reset();
                this.loadList();
            } else {
                Toast.show(data.message || 'Error saving customer', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            Toast.show('An unexpected error occurred', 'error');
        });
    },

    edit(id) {
        fetch((window.APP_BASE_URL || '') + '/api/customers.php?action=get&id=' + id)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const c = res.data;
                    const form = document.getElementById('customer-form');
                    if (!form) return;

                    form.querySelector('[name="id"]').value = c.id;
                    form.querySelector('[name="customer_code"]').value = c.customer_code;
                    form.querySelector('[name="name"]').value = c.name;
                    form.querySelector('[name="phone"]').value = c.phone;
                    form.querySelector('[name="alt_phone"]').value = c.alt_phone || '';
                    form.querySelector('[name="city"]').value = c.city || 'Lahore';
                    form.querySelector('[name="gender"]').value = c.gender || 'male';
                    form.querySelector('[name="address"]').value = c.address || '';
                    form.querySelector('[name="notes"]').value = c.notes || '';

                    const modalEl = document.getElementById('modal-customer-add');
                    if (modalEl) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            });
    },

    openWhatsApp(phone, name, balanceDue) {
        let cleanPhone = phone.replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '92' + cleanPhone.substring(1);
        }

        let message = `Assalam-o-Alaikum ${name}! Thank you for choosing ezyTailor.`;
        if (parseFloat(balanceDue) > 0) {
            message += ` Your pending balance is Rs ${Number(balanceDue).toLocaleString()}. Please feel free to contact us for your order updates.`;
        } else {
            message += ` Your orders with us are in process. Let us know if you need any adjustments.`;
        }

        const url = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
        window.open(url, '_blank');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Customers.init();
});

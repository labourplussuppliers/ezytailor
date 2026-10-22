/**
 * ezyTailor - Payments & Ledger Book JS
 */

const Payments = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        // Collect Payment Form Submit
        const payForm = document.getElementById('collect-payment-form');
        if (payForm) {
            payForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitPayment(payForm);
            });
        }

        // Ledger Entry Form Submit
        const ledgerForm = document.getElementById('ledger-entry-form');
        if (ledgerForm) {
            ledgerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitLedgerEntry(ledgerForm);
            });
        }

        // Ledger Filter change
        const filterType = document.getElementById('ledger-filter-type');
        const filterCat = document.getElementById('ledger-filter-category');
        if (filterType) {
            filterType.addEventListener('change', () => this.filterLedger());
        }
        if (filterCat) {
            filterCat.addEventListener('change', () => this.filterLedger());
        }
    },

    openCollectModal(orderId, orderNo, customerName, balanceDue) {
        const modalEl = document.getElementById('modal-collect-payment');
        if (!modalEl) return;

        document.getElementById('pay_modal_order_id').value = orderId;
        document.getElementById('pay_modal_order_no').textContent = orderNo;
        document.getElementById('pay_modal_customer').textContent = customerName;
        document.getElementById('pay_modal_balance_display').textContent = 'Rs ' + Number(balanceDue).toLocaleString();
        
        const amountInput = document.getElementById('pay_modal_amount');
        amountInput.value = balanceDue;
        amountInput.max = balanceDue;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    },

    submitPayment(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch((window.APP_BASE_URL || '') + '/api/payments.php?action=collect', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success) {
                Toast.show(data.message, 'success');
                const modalEl = document.getElementById('modal-collect-payment');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                form.reset();
                setTimeout(() => window.location.reload(), 800);
            } else {
                Toast.show(data.message || 'Payment collection failed', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error(err);
            Toast.show('An unexpected error occurred', 'error');
        });
    },

    submitLedgerEntry(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch((window.APP_BASE_URL || '') + '/api/ledger.php?action=save', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success) {
                Toast.show(data.message, 'success');
                const modalEl = document.getElementById('modal-add-ledger');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                form.reset();
                setTimeout(() => window.location.reload(), 800);
            } else {
                Toast.show(data.message || 'Error adding entry', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error(err);
            Toast.show('An unexpected error occurred', 'error');
        });
    },

    filterLedger() {
        const type = document.getElementById('ledger-filter-type')?.value || 'all';
        const cat = document.getElementById('ledger-filter-category')?.value || 'all';
        const url = `ledger.php?type=${type}&category=${cat}`;
        window.location.href = url;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Payments.init();
});

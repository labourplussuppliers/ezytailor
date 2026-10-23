# ezyTailor - Smart Tailor POS & Shop Management Web Application

A full-stack, production-ready web application clone of **ezyTailor** engineered for tailor shops, bespoke boutiques, and garment crafting ateliers.

---

## 🚀 Live Access URLs (Localhost XAMPP)
- **Primary Workspace URL**: [http://localhost/esytailor/](http://localhost/esytailor/)
- **Alternate Alias URL**: [http://localhost/ezytailor/](http://localhost/ezytailor/)

---

## 🛠 Tech Stack
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), jQuery 3.7+
- **Styling & Icons**: Bootstrap 5.3, Bootstrap Icons 1.11, FontAwesome 6, Custom Mobile-First CSS
- **Backend**: Vanilla PHP (8.2+ with clean modular REST API architecture)
- **Database**: MySQL (PDO with prepared statements for security against SQL injection)
- **Extra Libraries**:
  - [Chart.js 4.4](https://www.chartjs.org/) for business growth and revenue analytics
  - **HTML5 Web Audio API & MediaRecorder** for voice instruction recording & playback
  - [JsBarcode](https://lindell.me/JsBarcode/) for thermal receipt barcodes
  - **CSS `@media print`** optimized for 58mm and 80mm ESC/POS thermal roll printers

---

## 🌟 Key Features & Capabilities

### 1. Dynamic Multi-Theme System
- **5 Pre-configured Color Palettes**:
  - **Olive / Sage Green (Default)**: `#5C6B50` / `#4A5840` with off-white background `#F7F6F2` (exact match to UI mockups)
  - **Navy Blue**: Corporate deep blue `#1E3A8A`
  - **Deep Charcoal / Dark Mode**: `#0F172A` / `#1E293B` for night usage
  - **Royal Maroon**: Luxury boutique maroon `#7B1113`
  - **Classic Teal**: Modern teal `#0D5C75`
- Seamless switching via CSS custom variables (`--primary-color`, `--bg-color`, `--card-bg`, etc.).
- Persists across refreshes in `localStorage` and automatically syncs to the MySQL `settings` table.

### 2. Dashboard & Home Screen
- **Monthly Business Summary Cards**:
  - **Unpaid Amount**: e.g., `Rs 19,200 / 46,600` with visual progress percentage recovery bar.
  - **Unpaid Orders Count**: e.g., `7 / 10 orders` pending balance collection.
  - **Ready for Delivery**: Count of finished suits ready for customer pickup.
  - **Net Cash Balance**: Real-time store cash (Total In minus Total Out).
- **Quick Navigation Grid**: One-tap access to Customers, Orders, Payments, Ledger Book, Measurements, and Analytics.
- **Floating Action Button (FAB)**: Speed-dial for quick `+ Customer` and `+ Order`.
- **Recent Orders List**: With urgent priority tags, delivery dates, and quick action buttons (Thermal Print, Collect Payment, WhatsApp).

### 3. Customer Management
- Real-time instant search by Name, Phone, City, or Customer Code.
- Auto-generated customer codes (e.g., `CUST-101`, `CUST-102`).
- Balance due badge indicator (e.g., `Rs 2,400 Due` vs `Fully Cleared`).
- **One-click Direct Actions**:
  - **Call Button**: `tel:+92300...`
  - **WhatsApp Direct**: `https://wa.me/...` with pre-filled greeting and balance notice.
- **Customer Profile View with Tabs**:
  - **Measurements Tab**: All saved fits, specs, fabric swatches, and audio voice notes.
  - **Orders Tab**: History of suits ordered, status, delivery date, and balance.
  - **Payments Tab**: Itemized log of advances and balance clearances.
  - **Profile Info**: Full address, city, and tailoring fitting preferences.

### 4. Smart Measurements & Templates
- **7 Built-in Garment Templates**:
  1. **Male Shalwar Kameez** (لمبائی, تیرا, چھاتی, لوزنگ, کمر, گھیرا, بازو, کالر, کف, موڈھا, شلوار لمبائی, پانچہ, آسن, شلوار گھیرا)
  2. **Female Shalwar Kameez / Ladies Suit** (قمیض لمبائی, تیرا, چھاتی, کمر, کولہے, دامن, بازو, اگلا گلا, پچھلا گلا, ٹراؤزر لمبائی, پانچہ)
  3. **Men Shirt** (Length, Shoulder, Chest, Waist, Sleeves, Collar, Cuff, Armhole)
  4. **Trousers / Dress Pant** (Length, Waist, Hip, Thigh, Knee, Bottom/Mori, Fly/Rise)
  5. **Waistcoat / Waskat** (Length, Shoulder, Chest, Waist, Neck, Armhole)
  6. **Suit Coat / Blazer** (Coat Length, Shoulder, Chest, Waist, Hip, Sleeves, Half Back)
  7. **Thobe / Jubba** (عربی ثوب - Length, Shoulder, Chest, Loosing, Sleeves, Neck, Daman)
- **Dynamic Form Builder**: Add custom measurement fields on the fly.
- **Visual Mannequin Guide**: Anatomical measurement diagram indicating shoulder, chest, waist, and length measurement points.
- **Fabric Swatch Photo Upload**: Attach cloth sample images directly to client measurements.
- **HTML5 Web Audio Recording**: Record voice notes (up to 5 minutes) via `MediaRecorder` API with live timer and in-browser audio playback.

### 5. Order Booking & POS Thermal Receipt Printing
- Step-by-step booking form with Customer search, Fit selection, Quantity increment `+` / decrement `-` buttons, Unit rate, and Delivery date picker.
- Delivery shortcuts: `+1 Day (Urgent)`, `+3 Days`, `+1 Week`.
- Auto-calculation: `Total = Quantity * Rate`, `Balance = Total - Advance`.
- Automatic ledger integration: Advances paid automatically create income entries.
- **Instant POS Thermal Receipt (`receipt.php`)**:
  - Switchable between **80mm Roll (Standard POS)** and **58mm Roll (Mini POS)**.
  - Store header, Customer code, Delivery date highlight box, Item breakdown, Fit measurement specs, Barcode (CODE128), Terms & Conditions, and Customer Signature line.

### 6. Payments & Ledger Book
- **Ledger Overview**: Net Cash Balance, Total In (Income), Total Out (Expense).
- Categorized accounting: `Shop Order`, `Rent`, `Utility`, `Salaries`, `Fabric/Material`, `Sale`, `General`.
- Filter by Type (Income / Expense), Category, and Date Range.
- Order balance collection modal: Accepts Cash, JazzCash, EasyPaisa, Bank Transfer, updates order status, and logs to ledger.

### 7. Business Analytics (Chart.js)
- Metric cards: Gross Sales, Cash Collected, Outstanding Unpaid, Total Booked Orders.
- Time filter buttons: `Today`, `This Week`, `This Month`, `This Year`, `Lifetime`.
- Interactive Charts:
  1. **Revenue Trend & Order Growth**: Multi-axis line/bar chart.
  2. **Garment Distribution**: Donut chart showing breakdown across suits.
  3. **Payment Status**: Donut chart (Paid in Full vs Partial vs Unpaid).

### 8. Multi-Language & RTL Layout
- Supported Languages: **English (EN)**, **Urdu (اردو)**, **Hindi (हिन्दी)**, **Arabic (العربية)**.
- Right-to-Left (RTL) typography automatically applied for Urdu and Arabic with Noto Nastaliq font styling.

---

## 📁 Database Schema (`database/schema.sql`)
1. `users`: System administrative credentials (default: `admin` / `admin123`).
2. `settings`: Store configuration, receipt headers/footers, theme, currency.
3. `customers`: Customer directory, phone, address, notes.
4. `measurement_templates`: Predefined garment templates with JSON specs.
5. `customer_measurements`: Client saved fits, style selections, photos, and voice notes.
6. `workers`: Tailors / karigars, piece rates, contact.
7. `orders`: Order booking, delivery dates, totals, advances, balances, statuses.
8. `order_items`: Multi-item breakdown per order.
9. `payments`: Payment transaction logs and payment methods.
10. `ledger`: Cash-in and cash-out daily journal.

---

## 💻 Local Setup Instructions

1. **Place in Webroot**: Ensure folder is inside `xampp/htdocs/esytailor` (or `ezytailor`).
2. **Start Services**: Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Database Import**:
   - Access phpMyAdmin or MySQL CLI.
   - Run the script at `database/schema.sql` (Database name: `ezytailor_db`).
4. **Open in Browser**:
   - Navigate to `http://localhost/esytailor/` (or `http://localhost/ezytailor/`).

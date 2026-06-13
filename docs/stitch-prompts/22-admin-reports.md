# Admin — Reports & Analytics Screen — Google Stitch Prompt

## Purpose
Comprehensive platform analytics — revenue reports, user growth, product performance, doctor performance, and exportable PDFs/CSVs.

## Layout
Dashboard-style with charts, KPI cards, and data tables. All charts interactive.

## Sections & Components

### 1. Report Type Tabs
- Overview | Revenue | Users | Products | Doctors | Orders | Returns

### 2. Date Range Selector
- Preset pills: Today | This Week | This Month | Last Month | This Quarter | This Year | All Time
- Custom range: date picker (start - end)
- "Compare to previous period" toggle

### 3. Overview Tab
- **KPI Cards**: Total Revenue, Total Orders, Total Users, Average Order Value, Conversion Rate, Repeat Purchase Rate
- **Revenue Trend**: area chart (monthly with previous year overlay)
- **Orders Fulfillment**: doughnut chart (delivered / cancelled / returned / processing)
- **Top Categories**: horizontal bar chart
- **Platform Growth**: line chart (cumulative users + orders)

### 4. Revenue Tab
- Daily/Weekly/Monthly revenue breakdown
- Revenue by payment method (pie: UPI 45%, COD 30%, Card 15%, Wallet 10%)
- Revenue by product category (bar chart)
- Tax report: GST collected per month
- Refund analysis: total refunded amount vs revenue

### 5. Users Tab
- Signup trend (line chart)
- User type breakdown (customer 80%, retailer 15%, distributor 5%)
- Active vs inactive users
- Top cities by user count
- User retention cohort chart (weekly)

### 6. Products Tab
- Top 10 selling products
- Low-performing products
- Stock alerts summary
- Category-wise sales
- Revenue per product (sorted descending)

### 7. Doctors Tab
- Top doctors by consultations
- Average rating trend
- Earnings report: total paid, pending, per doctor
- Consultation type breakdown

### 8. Orders Tab
- Orders by status (donut chart)
- Orders by hour (heatmap — peak ordering times)
- Average delivery time (in days)
- Order value distribution (histogram)

### 9. Returns Tab
- Return rate % (daily trend)
- Top reasons for returns (bar chart)
- Refund processing time (avg days)

### 10. Export Section
- "Download Report" dropdown: PDF | CSV | Excel
- "Schedule Report": frequency (daily/weekly/monthly), recipients (email list), format
- "Saved Reports": list of previously generated reports with date

## Interactions
- Date range: all charts update with animation
- Chart click: drill-down to detail
- Export: loading animation → file download
- Compare toggle: overlay previous period as dashed line
- Hover tooltips on all data points

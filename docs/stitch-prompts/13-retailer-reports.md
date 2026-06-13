# Retailer — Sales Reports Screen — Google Stitch Prompt

## Purpose
Analytics dashboard for retailer — sales trends, top products, revenue, profit margins, and exportable reports.

## Layout
Dashboard-style with charts, KPI row, and data tables below.

## Sections & Components

### 1. Time Period Selector
- Pill buttons: Today | This Week | This Month | This Quarter | This Year | Custom Range
- Date picker: start - end inputs

### 2. KPI Cards Row (4 cards)
- **Total Sales**: ₹1,25,000 (↑8.5% vs previous period)
- **Total Orders**: 84 (↑12%)
- **Avg Order Value**: ₹1,488 (↓2%)
- **Profit Margin**: 22.4% (↑1.2%)

### 3. Sales Trend Chart
- Line chart: X = dates, Y = revenue
- Two lines: "Sales" (green, #005221) vs "Previous Period" (grey, dashed)
- Interactive: hover shows tooltip with exact value + date

### 4. Top Products
- Horizontal bar chart (or table): Product name | Qty Sold | Revenue | % of Total
- Top 10 products sorted by revenue
- "View All" link

### 5. Category Breakdown
- Pie/donut chart showing sales by category:
  - Churnas 30% | Oils 25% | Tablets 20% | Syrups 15% | Others 10%
- Legend with category names + percentages

### 6. Orders Over Time
- Mini area chart showing order count per day for the selected period

### 7. Recent Transactions Table
- Date | Order ID | Customer/Distributor | Items | Amount | Payment Method | Status
- Pagination at bottom
- "View All Orders" link

### 8. Export Section
- Buttons: Export as PDF | Export as CSV | Export as Excel
- Schedule report: "Send monthly report to my email" toggle

## Color Palette
- #005221 (sales line, positive KPI)
- #D4AF37 (accent, profit)
- #10B981 (growth indicators)
- #EF4444 (decline indicators)
- #F8FAF5 (chart bg)

## Typography
- Chart labels: DM Sans regular, small
- KPI numbers: Plus Jakarta Sans bold, 36px
- Table: DM Sans regular

## Interactions
- Time period: pills switch with data reload animation
- Chart hover: tooltip with precise values
- KPI click: filters rest of page to that metric detail
- Export: file download with loading spinner

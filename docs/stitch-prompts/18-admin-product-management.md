# Admin — Product Management Screen — Google Stitch Prompt

## Purpose
Full CRUD for product catalog — add/edit medicines, manage inventory, set pricing, categories, tags, and bulk upload.

## Layout
Filter + table view, product form modal/sidebar, category management panel.

## Sections & Components

### 1. Top Bar
- Search: "Search by name, SKU, or category..."
- Category filter dropdown
- Status filter: All | Active | Inactive | Out of Stock
- "Add Product" button (#005221)
- "Bulk Upload" button (outline)
- "Export" button

### 2. Product Table
- Columns: Image (thumb) | Name | SKU | Category | Price | Stock | Status | Actions
- Status: Active (green dot), Inactive (grey dot), Out of Stock (red dot)
- Price: current price bold, MRP strikethrough
- Actions: Edit | Duplicate | Toggle Status | Delete

### 3. Add/Edit Product Form (Full Page or Large Modal)
- **Basic**: Name, Description (rich text), Category (dropdown), Tags
- **Pricing**: MRP, Selling Price, GST%, Unit (e.g., 100g, 500ml, 30 tablets)
- **Stock**: Current Stock, Low Stock Threshold
- **Images**: Upload (multiple), drag to reorder, set primary
- **Attributes**: Brand, Manufacturer, Shelf Life, Storage Instructions, Usage Instructions, Ingredients
- **SEO**: Meta title, Meta description, URL slug (auto-generated, editable)
- **Status**: Active / Inactive toggle
- **Featured**: "Show on homepage" checkbox
- "Save" / "Save & Add Another" buttons

### 4. Bulk Upload Modal
- Download CSV template link
- Drag-and-drop CSV file upload
- Field mapping preview after upload
- Validation report: "3 errors, 47 products ready to import"
- "Import" button
- "Download error log" if errors exist

### 5. Category Management (Side Panel)
- Tree view of categories (expandable)
- Add category: name, slug, parent category, image, description
- Edit / Delete category (with product count)
- Drag to reorder

### 6. Stock Alerts Section
- Products where stock ≤ threshold
- Product | Current Stock | Threshold | "Add Stock" quick action

### 7. Empty State
- Package icon
- "No products yet"
- "Add your first Ayurvedic product to start selling"

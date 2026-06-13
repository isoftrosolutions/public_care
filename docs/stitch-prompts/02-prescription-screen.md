# Prescription Screen — Google Stitch Prompt

## Purpose
Digital prescription viewer with upload, generate, print, and share functionality. Supports doctor-signed prescriptions, patient download, and pharmacy reorder.

## Layout
Two-panel: left = prescription list (stacked), right = prescription preview (detail view on mobile after tap).

## Sections & Components

### 1. Page Header
- Left: back arrow + "Prescriptions" heading + count badge
- Right: "Upload Prescription" button (outline) + "Order on Prescription" button (filled #005221)

### 2. Tab Filters
- All | Pending | Active | Expired (pill tabs, active tab has #005221 bg with white text)

### 3. Prescription List (Left Panel)
- Each item is a card with:
  - Doctor name + specialization (e.g., "Dr. Priya Sharma — Ayurvedacharya")
  - Date of issue (e.g., "Issued: 12 Jun 2026")
  - Expiry date (e.g., "Valid until: 12 Sep 2026") — red text if expired
  - Medicine count (e.g., "5 medicines")
  - Status badge: Active (green), Pending (yellow), Expired (red)
  - Action icons: view, download PDF, reorder
- Tap opens in right preview panel

### 4. Prescription Preview (Right Panel / Full on Mobile)
- Top bar: doctor name + clinic/hospital name + registration number + logo/blank
- Date of issue + valid until + "Digital Signature Verified" green badge
- **Patient details**: name, age, gender, weight (small row)
- **Medicine table**: columns = S.No | Medicine Name | Dosage | Frequency | Duration | Instructions
  - E.g., "Triphala Churna 1 tsp Bedtime with warm water 30 days Take after meal"
  - E.g., "Ashwagandha 500mg 1-0-1 After breakfast & dinner 60 days With milk"
- Doctor's digital signature at bottom (stylized cursive + "(Signed digitally)" text)
- **Action buttons row**: Download PDF | Print | Share | Order All Medicines (#005221 CTA)

### 5. Upload Prescription Modal
- Drag-and-drop zone: dashed border, upload icon, "Drop prescription image or click to browse"
- Supported formats: JPG, PNG, PDF (max 5MB)
- File preview after selection + "Upload" button
- Success toast: "Prescription uploaded successfully"

### 6. Empty State
- Illustration: clipboard with prescription icon
- "No prescriptions yet"
- "Your doctor's prescriptions will appear here. You can also upload a physical prescription."
- "Upload Prescription" ghost button

## Typography
- Headings: Plus Jakarta Sans
- Medicine names: DM Sans Medium
- Dosage instructions: DM Sans Regular, small

## States
- Loading: skeleton list (5 shimmer cards)
- Empty: illustration + upload CTA
- Detail: open with right panel showing selected preset
- Expired: red overlay note + "Consult Again" CTA

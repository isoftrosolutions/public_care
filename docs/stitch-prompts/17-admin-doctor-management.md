# Admin — Doctor Management Screen — Google Stitch Prompt

## Purpose
Manage Ayurvedic doctors — profiles, consultation types, schedules, approval, verification, and performance metrics.

## Layout
Filter + table view with detail slide-in panel.

## Sections & Components

### 1. Top Bar
- Search: "Search by name, specialisation, or ID..."
- Status filter: All | Active | Inactive | Pending Verification | Rejected
- Specialisation filter dropdown (Ayurvedacharya, Panchakarma Specialist, etc.)
- "Add New Doctor" button
- "Export" button

### 2. Doctor Table
- Columns: Avatar + Name | Specialisation | Experience | Consultation Fee | Rating | Patients Treated | Status | Actions
- Rating: star display (e.g., ★4.5)
- Status: Active (green), Inactive (grey), Pending (yellow), Rejected (red)
- Actions: View | Edit | Approve | Suspend | Delete

### 3. Doctor Detail Panel
- **Profile**: photo, name, degrees, specialisation, experience (years), bio
- **Contact**: email, phone, clinic address
- **Consultation**: video fee, clinic fee, available slots, consultation types
- **Schedule**: weekly calendar view (Mon-Sun, available time blocks)
- **Ratings**: star breakdown + recent reviews list
- **Documents**: degree certificates, license, ID proof — uploaded files with verification status
- **Earnings**: total earned, this month, pending payout
- **Actions**: Verify Documents | Approve Profile | Suspend | Remove Doctor

### 4. Doctor Verification Modal
- Document list: each with status (Pending/Verified/Rejected) + view button
- "Verify All" button
- Rejection requires reason

### 5. Add/Edit Doctor Form
- Personal info: name, email, phone, DOB, gender
- Professional: degrees (multiple), specialisation, experience, registration number
- Consultation: video fee, clinic fee (or free), consultation types (checkboxes)
- Bio textarea (rich text)
- Profile image upload
- Documents upload (degree, license, ID)
- Schedule builder (day-by-day time slot picker)

### 6. Empty State
- Medical cross icon
- "No doctors found"
- "Invite your first Ayurvedic doctor to join the platform"

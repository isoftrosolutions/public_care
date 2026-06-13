# Profile / Settings Screen — Google Stitch Prompt

## Purpose
User account management, personal details, saved addresses, family members, notification preferences, language, privacy, and app settings.

## Layout
Two-column on desktop: left = settings navigation (stacked links), right = content panel. Single scrollable page on mobile with grouped sections.

## Sections & Components

### 1. Profile Card (Hero Section)
- Avatar (editable) + name + email + phone
- "Edit Profile" pencil icon overlay on avatar
- Member since date + "Ayurveda Wellness" tag
- Account type indicator: "Gold Member" or "Standard"

### 2. Settings Categories (Accordion Grouped)

#### 2a. Account
- Personal Information: name, email, phone, DOB, gender — inline editable
- Change Password: old pass | new pass | confirm pass | "Update" button
- Delete Account: red text + "Deactivate my account" — confirmation modal

#### 2b. Addresses
- "Saved Addresses" heading + "Add New Address" button
- Each address card: name, address, phone, type badge (Home/Work/Other)
- Default badge on one address
- Actions: edit, delete, set as default

#### 2c. Family Members (see Family Management section separately)
- Summary showing count + avatars + "Manage" link

#### 2d. Notifications
- Toggle list with switches:
  - Order updates (on/off)
  - Offers & discounts
  - Health reminders
  - Appointment reminders
  - Lab report ready
  - AI health tips
- "Time to remind" dropdown: 8:00 AM | 12:00 PM | 6:00 PM | 9:00 PM

#### 2e. Payment Methods
- Saved cards list (masked number, expiry, card type icon)
- Saved UPI IDs list
- Wallet balance display
- "Add Payment Method" button

#### 2f. Language
- Language selection page (see Language Selection screen)
- Current language badge + "Change" link

#### 2g. Privacy & Security
- Manage sessions (show active sessions, revoke)
- Two-factor authentication toggle
- Data export button ("Download my data")
- Privacy policy link
- Terms of service link

#### 2h. Support
- FAQ link
- Contact support
- Rate the app
- Share the app
- About Ayurviro + version (v1.0.0)

### 3. Logout Button
- Full-width red outline button at bottom
- "Logout" with right arrow
- Confirmation modal: "Are you sure you want to logout?"

## Typography
- Section headings: Plus Jakarta Sans semibold, uppercase, small, grey
- Item labels: DM Sans medium
- Values: DM Sans regular

## Color Palette
- #005221 (accent buttons, active toggles)
- #EF4444 (danger, logout, delete)
- #F3F4F6 (section background)
- #1F2937 (text)

## Interactions
- Toggle switches: smooth slide animation with green fill
- Avatar: tap opens photo picker (camera / gallery / remove)
- Accordion: smooth expand/collapse with chevron rotation
- Logout: confirmation → session clear → redirect to home

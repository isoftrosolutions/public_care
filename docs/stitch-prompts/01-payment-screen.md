# Payment Screen — Google Stitch Prompt

## Purpose
Full-page payment gateway with multiple methods, coupon input, order summary, and animated success state. Includes wallet balance, saved cards, UPI, net banking, Pay Later, and COD options.

## Layout
Vertical single-column layout with sticky order summary sidebar on desktop (collapses to top on mobile).

## Sections & Components

### 1. Page Header
- Left: "Payment" heading + secure lock icon + "Secured by 256-bit SSL"
- Right: step indicator "Address → Payment → Confirmation" (step 2 active)

### 2. Order Summary (Sticky Sidebar on Desktop)
- Product thumbnail list (3 items shown + "and N more" if >3)
- Subtotal, delivery charge, discount, GST
- **Coupon section**: input field + "Apply" button, shows applied coupon name + "Remove"
- **Wallet toggle**: "Pay ₹XXX from your Wallet" — toggle switch with balance display
- Total amount in large bold (#005221)
- "Place Order" CTA button (full-width, #005221 bg, white text, rounded-lg, py-3)
- UPI auto-pay badge: "Save 5 min — Pay via UPI" small incentive text

### 3. Payment Methods (Main Content Area)
- Each method is a large radio-card with icon + label + right chevron

#### 3a. Wallet
- Icon: wallet outline
- Shows balance: "₹1250 available"
- Expandable section: enter amount, "Pay with Wallet" button

#### 3b. UPI (expanded by default)
- Icon: UPI logo
- **AutoPay section**: "Pay via UPI" input field (enter UPI ID) + "Verify & Pay"
- **QR section**: "Scan any UPI app" — placeholder QR box (grey dashed border, centered)
- **Saved UPI IDs**: pill buttons below input
- **Popular UPI Apps**: Google Pay (green), PhonePe (purple), Paytm (blue), BHIM (orange) — icon + name in tappable cards

#### 3c. Credit / Debit Card
- Icon: card chip
- Card number input (16 digits with spacing)
- Expiry (MM/YY) + CVV (inline)
- Cardholder name
- "Save card for future" checkbox
- Lock icon + "Your card data is PCI-DSS compliant"

#### 3d. Net Banking
- Icon: bank building
- Popular banks row: SBI, HDFC, ICICI, Axis, Kotak (icon pills)
- "Other Banks" dropdown below

#### 3e. Pay Later
- Icon: clock + rupee
- Options: Simpl, LazyPay, Amazon Pay Later, ZestMoney
- Each shows "Pay in 3" or "No cost EMI" badge

#### 3f. Cash on Delivery (COD)
- Icon: cash + delivery truck
- Amount to pay on delivery
- "COD available for orders up to ₹5000" note
- "I confirm I will accept delivery" checkbox

### 4. Bottom Security Bar
- Small icons: SSL lock, PCI badge, 3D Secure
- "Your payment info is encrypted and never stored" text

## Color Palette
- Primary: #005221
- Background: #FFFFFF (cards), #F8FAF5 (page)
- Text: #1A1A1A (primary), #6B7280 (secondary)
- Success: #10B981 (payment success)
- Borders: #E5E7EB

## Typography
- Headings: Plus Jakarta Sans, semibold
- Body: DM Sans, regular
- Prices: DM Sans, bold

## Interactions
- Method selection: smooth radio-card flip animation
- Coupon applied: green check + amount deduction animated
- UPI verify: shimmer loading state on button
- Card input: auto-format spaces every 4 digits
- Place Order: button → spinner → success animation (green check + confetti)
- Error: red border on field + inline error message
- COD confirmation: checkbox must be checked to enable Place Order

## States
- Loading: skeleton cards for payment methods
- Empty: no saved cards/UPI — show helper text
- Error: payment failed — retry button + support link
- Success: green check, order number, "Continue Shopping" + "View Order" buttons

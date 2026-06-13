# Wishlist Screen — Google Stitch Prompt

## Purpose
Saved products collection for future purchase. Includes move-to-cart, price alerts, share wishlist, and social features.

## Layout
Grid layout (3 cols desktop, 2 cols tablet, 1 col mobile) with list/switch toggle.

## Sections & Components

### 1. Page Header
- "My Wishlist" heading + product count badge: "12 items"
- Right: Sort dropdown (Newest | Price: Low-High | Price: High-Low | Discount)
- Right: Grid/List view toggle icon

### 2. Wishlist Product Card
- **Image**: 1:1 aspect ratio, object-cover
- **Badges**: Discount % (red), "Low Stock" (yellow) if applicable
- **Product name**: DM Sans medium, 2-line clamp
- **Price**: current price (#005221, bold) + original price (strikethrough, grey) + discount %
- **Rating**: stars (colored #D4AF37) + count
- **In stock / Out of stock** indicator
- **Actions row:**
  - "Add to Cart" CTA (#005221 filled button) — disabled if OOS
  - Heart icon (filled red) — tap to remove from wishlist
  - Secondary: "Notify when price drops" bell icon

### 3. Empty State
- Heart icon with hands holding it
- "Your wishlist is empty"
- "Save your favourite products here and buy them later!"
- "Start Shopping" CTA

### 4. Wishlist Features
- **Share Wishlist**: button generates shareable link
- **Clear All**: danger link at bottom (with confirmation)
- **Move All to Cart**: "Add all items to cart" — checks stock, shows quantity picker (if multiple quantities needed)
- **Notify All**: "Turn on price alerts for all items"

### 5. Price Drop Alert Modal
- "Get notified when price drops"
- Select discount threshold: 10% | 20% | 30% | 50%
- "Set Alert" button
- Active alerts shown as "🔔 Alert set for 20% drop"

## Interactions
- Grid/List toggle: smooth transition animation
- Add to Cart: item slides to cart icon with badge increment
- Remove from Wishlist: heart fills/unfills with scale animation
- Share: native share sheet on mobile, copy link on desktop
- Stock check: green dot "In Stock" / red dot "Out of Stock"

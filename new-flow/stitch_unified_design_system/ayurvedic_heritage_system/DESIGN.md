---
name: Ayurvedic Heritage System
colors:
  surface: '#fbf9f8'
  surface-dim: '#dbd9d9'
  surface-bright: '#fbf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3f3'
  surface-container: '#efeded'
  surface-container-high: '#eae8e7'
  surface-container-highest: '#e4e2e2'
  on-surface: '#1b1c1c'
  on-surface-variant: '#40493f'
  inverse-surface: '#303030'
  inverse-on-surface: '#f2f0f0'
  outline: '#707a6e'
  outline-variant: '#bfc9bc'
  surface-tint: '#1f6c35'
  primary: '#005221'
  on-primary: '#ffffff'
  primary-container: '#1e6b34'
  on-primary-container: '#9be9a4'
  inverse-primary: '#8bd895'
  secondary: '#5a605a'
  on-secondary: '#ffffff'
  secondary-container: '#dfe4dc'
  on-secondary-container: '#606660'
  tertiary: '#5a4200'
  on-tertiary: '#ffffff'
  tertiary-container: '#785800'
  on-tertiary-container: '#ffd06c'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#a6f5af'
  primary-fixed-dim: '#8bd895'
  on-primary-fixed: '#002109'
  on-primary-fixed-variant: '#005321'
  secondary-fixed: '#dfe4dc'
  secondary-fixed-dim: '#c3c8c1'
  on-secondary-fixed: '#181d18'
  on-secondary-fixed-variant: '#434843'
  tertiary-fixed: '#ffdf9f'
  tertiary-fixed-dim: '#f6be35'
  on-tertiary-fixed: '#261a00'
  on-tertiary-fixed-variant: '#5c4300'
  background: '#fbf9f8'
  on-background: '#1b1c1c'
  surface-variant: '#e4e2e2'
typography:
  display-lg:
    fontFamily: DM Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: DM Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: DM Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: DM Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-max: 1280px
  gutter: 24px
  margin-desktop: 64px
  margin-mobile: 16px
---

## Brand & Style

This design system is rooted in the "Modern Ayurvedic" aesthetic—a fusion of traditional herbal wisdom and contemporary digital efficiency. The visual language is designed to evoke feelings of trust, tranquility, and natural vitality. It targets health-conscious individuals seeking holistic wellness through a professional, clean interface.

The style is characterized by **Corporate Modern** sensibilities with a **Nature-Inspired** influence. It utilizes a spacious layout, soft shadows for depth, and a high-contrast primary color that reinforces the herbal brand identity. The interface avoids unnecessary clutter, prioritizing high-quality product photography and clear typography to build credibility in the health and wellness space.

## Colors

The palette is anchored by a deep **Forest Green** (Primary), symbolizing nature, health, and growth. This color is used for key actions, brand elements, and success states. 

A **Mint Mist** (Secondary) serves as a subtle background tint for sections and cards to differentiate them from the pure white canvas without adding visual noise. **Golden Harvest** (Tertiary) is used sparingly for star ratings, badges, and high-priority secondary information to provide warmth. The **Neutral** scale focuses on soft charcoals and warm greys, avoiding pure black to maintain a softer, more organic feel.

## Typography

The typography system uses a pairing of two highly legible sans-serifs to maintain a modern, clean look. **DM Sans** is utilized for headlines and display text, offering a geometric but friendly structure that feels authoritative. **Plus Jakarta Sans** is used for all UI labels and body copy, chosen for its soft terminals and excellent readability at small sizes.

On desktop, the hierarchy expands to utilize larger display styles for hero sections. Headlines should maintain a tight line height for a compact, professional appearance. Body text should always use a slightly increased line height (1.5x) to ensure the educational content remains accessible and easy to digest.

## Layout & Spacing

The layout follows a **Fluid Grid** system on a 12-column structure for desktop. 

- **Grid:** Use a 12-column grid for desktop (1280px max width) with 24px gutters. Elements should snap to column boundaries.
- **Rhythm:** An 8px base grid governs all padding and margins. Use multiples of 8 (16, 24, 32, 48, 64) to maintain vertical rhythm.
- **Responsive Behavior:** On tablet, the grid shifts to 8 columns with 20px gutters. On mobile, it adopts a 4-column structure with 16px margins. 
- **Content Density:** Maintain generous white space around product images and text blocks to allow the "herbal" aesthetic to breathe. Avoid dense "data-table" styles in favor of spacious list items and cards.

## Elevation & Depth

This system uses **Tonal Layers** combined with **Ambient Shadows** to create a sense of organized depth.

1.  **Level 0 (Canvas):** Pure white (#FFFFFF) or the Secondary tint (#F4F9F1) for background sections.
2.  **Level 1 (Cards/Surfaces):** White surfaces with a very soft, diffused shadow (0px 4px 20px rgba(0, 0, 0, 0.05)). This is the primary container for product listings and category cards.
3.  **Level 2 (Interaction/Floating):** Used for hover states on cards or sticky navigation bars. The shadow becomes slightly more pronounced (0px 8px 30px rgba(0, 0, 0, 0.08)).
4.  **Overlays:** Modals and menus use a backdrop blur (10px) with a semi-transparent dark overlay to focus the user’s attention on the active task.

## Shapes

The shape language is consistently **Rounded**, avoiding sharp corners to reflect the organic nature of herbal products. 

- **Standard Elements:** Buttons, input fields, and small cards use a 0.5rem (8px) radius.
- **Large Containers:** Product cards and main content areas use a 1rem (16px) radius for a softer, more premium appearance.
- **Icons:** Icons should be contained within circular or highly rounded containers when used as category entry points. 
- **Interactive States:** High-priority "Buy Now" buttons may use pill-shaped (full-round) corners to distinguish them from secondary "Add to Cart" actions.

## Components

### Buttons
- **Primary:** Solid Forest Green background with white text. High emphasis.
- **Secondary:** Forest Green border with transparent background and green text.
- **Ghost:** No border, green or neutral text, used for less frequent actions like "Forgot Password".

### Input Fields
- Use a light grey border (1px) with a 0.5rem radius. 
- Labels should be placed above the field in `label-lg` style.
- Active states use a 2px Primary green border.

### Cards
- **Product Card:** White background, soft shadow, 1rem radius. Image at the top, followed by product name, price (with strikethrough for discounts in a lighter grey), and a star rating.
- **Category Card:** Circular icon container with text centered underneath.

### Chips & Badges
- **Bestseller/Discount:** Small, rounded rectangles with a light tint of green or yellow background and dark text.
- **Status Pills:** Fully rounded (pill) shapes for order status (e.g., "Shipped", "Delivered") using low-saturation versions of the status color.

### Icons
- Use thin-stroke, linear icons with rounded ends. 
- Primary icons should be Forest Green. 
- Navigation icons on the desktop header should be neutral grey, shifting to green on hover/active states.
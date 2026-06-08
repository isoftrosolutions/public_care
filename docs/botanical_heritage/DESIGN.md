---
name: Botanical Heritage
colors:
  surface: '#f4fafd'
  surface-dim: '#d4dbdd'
  surface-bright: '#f4fafd'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eef5f7'
  surface-container: '#e8eff1'
  surface-container-high: '#e2e9ec'
  surface-container-highest: '#dde4e6'
  on-surface: '#161d1f'
  on-surface-variant: '#414844'
  inverse-surface: '#2b3234'
  inverse-on-surface: '#ebf2f4'
  outline: '#717973'
  outline-variant: '#c1c8c2'
  surface-tint: '#3f6653'
  primary: '#012d1d'
  on-primary: '#ffffff'
  primary-container: '#1b4332'
  on-primary-container: '#86af99'
  inverse-primary: '#a5d0b9'
  secondary: '#735c00'
  on-secondary: '#ffffff'
  secondary-container: '#fed65b'
  on-secondary-container: '#745c00'
  tertiary: '#002d1a'
  on-tertiary: '#ffffff'
  tertiary-container: '#1a432e'
  on-tertiary-container: '#84b095'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c1ecd4'
  primary-fixed-dim: '#a5d0b9'
  on-primary-fixed: '#002114'
  on-primary-fixed-variant: '#274e3d'
  secondary-fixed: '#ffe088'
  secondary-fixed-dim: '#e9c349'
  on-secondary-fixed: '#241a00'
  on-secondary-fixed-variant: '#574500'
  tertiary-fixed: '#c0edd0'
  tertiary-fixed-dim: '#a4d1b4'
  on-tertiary-fixed: '#002112'
  on-tertiary-fixed-variant: '#264f39'
  background: '#f4fafd'
  on-background: '#161d1f'
  surface-variant: '#dde4e6'
typography:
  display-lg:
    fontFamily: Source Serif 4
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Source Serif 4
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Source Serif 4
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
  headline-md:
    fontFamily: Source Serif 4
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Manrope
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Manrope
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Manrope
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Manrope
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
  container-max: 1200px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 48px
  section-gap: 80px
---

## Brand & Style

This design system establishes a premium Ayurvedic healthcare identity that bridges ancient holistic wisdom with clinical precision. The visual narrative focuses on **Modern Traditionalism**—utilizing generous whitespace to signify high-end clinical hygiene, while grounding the experience in deep, earthy botanical tones.

The target audience seeks trust, longevity, and professional care. To evoke this, the system employs a **Corporate / Modern** style influenced by editorial minimalism. It prioritizes clarity and breathable layouts, ensuring the user feels a sense of calm and "wellness through order." The aesthetic is high-contrast but soft, avoiding the aggressive saturation of digital-first startups in favor of a timeless, authoritative presence.

## Colors

The palette is rooted in the natural world. **Forest Green (#1B4332)** serves as the primary anchor, used for headers, primary actions, and brand-heavy moments to establish authority. **Warm Gold (#D4AF37)** is used sparingly as a premium accent—ideal for high-value call-to-actions, member tiers, or decorative flourishes that signify quality.

**Sage Green (#B7E4C7)** provides a softer touch for backgrounds, secondary buttons, and success states. The neutral palette is strictly cool-toned to maintain a clinical edge, utilizing **Pure White** for primary surfaces to maximize readability and **Off-White (#F8F9FA)** to define distinct content sections without creating jarring visual breaks.

## Typography

The typographic strategy pairs the authoritative, literary grace of **Source Serif 4** with the technical efficiency of **Manrope**. 

- **Headlines:** Use the Serif for all titles to communicate tradition and clinical expertise. Tighten letter spacing slightly on larger display sizes to maintain a premium "magazine" feel.
- **Body & UI:** Manrope provides the modern, accessible counter-balance. Its geometric nature ensures clarity in medical data, dosages, and instructional text.
- **Labels:** Use uppercase Manrope with increased letter spacing for category tags and small identifiers to mimic high-end packaging design.

## Layout & Spacing

The system follows a **Fixed Grid** approach for desktop environments to maintain a "contained" and premium feel, while transitioning to a fluid model for mobile devices. 

- **Desktop:** A 12-column grid with a 1200px max-width centered in the viewport. 
- **Rhythm:** A strict 8px baseline grid governs all vertical movement. Large "Section Gaps" (80px+) are encouraged between major content blocks to prevent visual clutter and honor the "breathable" brand pillar.
- **Safe Areas:** Generous inner-padding (inset) within cards and containers (minimum 24px) ensures that text never feels cramped against borders.

## Elevation & Depth

Depth is achieved through **Tonal Layers** and extremely **Ambient Shadows**. 

Avoid heavy, dark drop shadows. Instead, use soft, multi-layered shadows with a hint of the Primary Color (#1B4332) in the shadow's tint to make them feel integrated rather than "floating." Surfaces should feel like high-quality paper or matte medical equipment.

- **Level 0 (Base):** White (#FFFFFF) or Off-White (#F8F9FA).
- **Level 1 (Cards):** White background with a 1px border (#E9ECEF) or a 4px blur shadow (alpha 0.05).
- **Level 2 (Modals/Popovers):** Defined by a 12px blur shadow (alpha 0.08) to create clear separation for user focus.

## Shapes

The shape language is organic yet disciplined. A **Rounded (0.5rem)** base is applied to most standard components (inputs, buttons) to evoke friendliness and comfort. 

Larger containers and cards should utilize **rounded-lg (1rem)** or **rounded-xl (1.5rem)** to emphasize the "soft" nature of holistic wellness. Circles are reserved specifically for profile avatars and secondary action icons to provide a visual break from the predominantly rectangular grid.

## Components

- **Buttons:** Primary buttons use the Forest Green background with white text. Secondary buttons use a Forest Green ghost style (border only) or a Sage Green soft background. High-value "Consultation" buttons use Gold (#D4AF37) with dark text.
- **Input Fields:** Use a subtle border (#DEE2E6) that thickens and changes to Forest Green on focus. Labels should always be visible above the field in Manrope Semibold.
- **Cards:** White surfaces with a very light border. Use larger corner radii (16px) for cards containing lifestyle photography to create an "editorial" look.
- **Chips/Badges:** For Ayurvedic Doshas (Vata, Pitta, Kapha) or medical categories, use pill-shaped chips with Sage Green backgrounds and dark green text.
- **Lists:** Use custom iconography (leaf or botanical dots) instead of standard bullet points to reinforce the nature-based theme.
- **Navigation:** A clean top-bar with plenty of height (minimum 80px) to allow the logo and primary navigation links to "breathe."
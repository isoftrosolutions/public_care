<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= htmlspecialchars($page_title ?? 'Admin') ?> | <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: { primary: "#012d1d", "on-primary": "#ffffff", "primary-container": "#1b4332", "on-primary-container": "#86af99", "primary-fixed": "#c1ecd4", "primary-fixed-dim": "#a5d0b9", secondary: "#735c00", "on-secondary": "#ffffff", "secondary-container": "#fed65b", "secondary-fixed": "#ffe088", "secondary-fixed-dim": "#e9c349", "tertiary-fixed-dim": "#a4d1b4", background: "#f4fafd", surface: "#f4fafd", "surface-dim": "#d4dbdd", "surface-bright": "#f4fafd", "surface-container-lowest": "#ffffff", "surface-container-low": "#eef5f7", "surface-container": "#e8eff1", "surface-container-high": "#e2e9ec", "surface-container-highest": "#dde4e6", "on-surface": "#161d1f", "on-surface-variant": "#414844", "surface-variant": "#dde4e6", outline: "#717973", "outline-variant": "#c1c8c2", error: "#ba1a1a", "on-error": "#ffffff", "error-container": "#ffdad6", "on-error-container": "#93000a", tertiary: "#002d1a", "on-tertiary": "#ffffff", "tertiary-container": "#1a432e", "on-tertiary-container": "#84b095", "tertiary-fixed": "#c0edd0", "on-secondary-container": "#745c00", "on-secondary-fixed-variant": "#574500", "on-primary-fixed": "#002114", "on-primary-fixed-variant": "#274e3d", "on-tertiary-fixed": "#002112", "on-tertiary-fixed-variant": "#264f39", "inverse-surface": "#2b3234", "inverse-on-surface": "#ebf2f4", "inverse-primary": "#a5d0b9", "surface-tint": "#3f6653", "on-background": "#161d1f" },
      borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
      spacing: { base: "8px", gutter: "24px", "section-gap": "80px", "margin-mobile": "16px", "margin-desktop": "48px", "container-max": "1200px" },
      fontFamily: { "display-lg": ["Source Serif 4"], "headline-lg": ["Source Serif 4"], "headline-md": ["Source Serif 4"], "body-lg": ["Manrope"], "body-md": ["Manrope"], "label-md": ["Manrope"], "label-sm": ["Manrope"] },
      fontSize: { "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }], "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "600" }], "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }], "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }], "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }], "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }], "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }] }
    }
  }
};
</script>
<style>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
.bento-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.bento-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
</style>
</head>

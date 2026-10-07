# DESIGN TOKENS & STYLES (Stitch Logistics Reference)

## Typography
- **Font-Family**: "Inter", sans-serif
- **Tracking Numbers**: Use tabular-nums (`[font-variant-numeric:tabular-nums]`) for tracking numbers (e.g. COLIS-2026-00125).

## Colors
- **Primary**: #2563EB (Bleu royal)
- **Primary Hover**: #1D4ED8
- **Accent/Status**:
  - Shipped (Expédié): #EAB308 (Jaune - text-yellow-600, bg-yellow-100)
  - Out for Delivery (En cours): #F97316 (Orange - text-orange-600, bg-orange-100)
  - Delivered (Livré): #22C55E (Vert - text-green-600, bg-green-100)
  - Delayed (Retardé): #EF4444 (Rouge - text-red-600, bg-red-100)
- **Background**: #F8FAFC (Gris très clair)
- **Cards**: #FFFFFF avec shadow-sm (border border-slate-200)
- **Text**: 
  - Main: #0F172A (slate-900)
  - Muted: #64748B (slate-500)

## Elements
- **Buttons**: Rounded-md, px-4 py-2, font-medium, transition-colors.
- **Inputs**: Rounded-md, border-slate-300, focus:ring-2 focus:ring-primary.
- **Timeline**: 
  - Ligne verticale grise (slate-200)
  - Points de statut (ronds de 12px, pleins si passés/actifs, vides/gris si futurs)
  - Affichage de la date en gris, texte en noir.
- **Badges**: Rounded-full, px-2.5 py-0.5, text-xs font-semibold.

## Layout
- **Public**: Centered card max-w-2xl, logo at top.
- **Admin**: Sidebar (w-64, hidden on mobile) + Main Content area. Mobile uses hamburger menu.

# Chez Guillaume — Boucherie à Montbeton
## Fichier guide du projet (context.md)

---

### 1. Présentation du commerce

**Nom :** Chez Guillaume
**Type :** Boucherie · Charcutier · Traiteur · Épicerie Fine
**Adresse :** 496 Route de Montauban, 82290 Montbeton (Tarn-et-Garonne, 82)
**Téléphone :** 05 63 27 29 98
**Email :** Chezguillaume82290@gmail.com
**Site :** https://boucheriechezguillaume.fr
**SIRET :** 97789531700021
**Facebook :** https://www.facebook.com/people/Boucherie-Chez-Guillaume/61559543176738/

**Horaires :**
- Lundi : Fermé
- Mardi – Samedi : 08h00–12h30 / 15h30–19h00
- Dimanche : 09h00–12h30

---

### 2. Stack Technique

| Outil              | Usage                                         |
|--------------------|-----------------------------------------------|
| Astro 4.x          | Framework SSG (fichiers `.astro`)             |
| Tailwind CSS 3.x   | Stylisation utilitaire                        |
| Alpine.js          | Interactivité légère (island architecture)    |
| Web3Forms          | Formulaires de contact & réservation          |
| astro:content      | Content Collections pour les menus traiteur  |
| TypeScript         | Typage des données et composants              |

---

### 3. Design System

Inspiré du site **Haverick Meats** — esthétique premium sombre et éditoriale.

**Couleurs :**
```
dark-950 : #080808  — fond le plus profond
dark-900 : #111111  — fond principal
dark-800 : #1A1A1A  — cartes / surfaces
dark-700 : #242424  — surfaces élevées
dark-600 : #2E2E2E  — bordures

cream-100 : #F5F0E8 — texte principal
cream-200 : #EAE3D6 — texte secondaire
cream-300 : #D4CBBA — texte atténué
cream-400 : #B8AD9A — très atténué
cream-500 : #9A9485 — muted

rouge-600 : #C41818 — accent principal CTA
rouge-500 : #D42020 — hover
rouge-700 : #A01212 — pressed
```

**Typographie :**
- **Titre** : Playfair Display — italic, font-weight 500–700 — grands effets édito
- **Label** : DM Sans — uppercase, letter-spacing 0.35em — catégories / sections
- **Corps** : DM Sans — 400/300 — texte courant, descriptions

**Composants clés :**
- `.dark-card` — fond sombre semi-transparent, border subtile
- `.section-label` — uppercase ultra-espacé (0.35em)
- `.hero-title` — serif italic, très grand
- `.btn-rouge` — CTA rouge (#C41818)
- `.btn-outline` — outline crème
- `.link-serif` — lien italique discret

---

### 4. Architecture du projet

```
src/
├── assets/images/          ← Images optimisées (WebP via astro:assets)
├── components/
│   ├── Navigation.astro    ← Nav fixe, logo centré, Alpine.js scroll
│   ├── Footer.astro        ← Barre contact + liens + horaires
│   ├── SectionLabel.astro  ← Label uppercase réutilisable
│   ├── ProductCard.astro   ← Carte produit boucherie/charcuterie
│   ├── MenuCard.astro      ← Carte menu traiteur
│   └── HoursTable.astro    ← Tableau horaires avec "Aujourd'hui"
├── content/
│   ├── config.ts           ← Schema Zod Content Collections
│   └── menus/              ← Fichiers .md des menus (gérés par Guillaume)
│       ├── brazero-festif.md
│       ├── cocktail-dinatoire.md
│       └── repas-traditionnel.md
├── data/
│   ├── contact.ts          ← Coordonnées centralisées
│   ├── hours.ts            ← Horaires + Schema.org
│   └── products.ts         ← Catalogue produits
├── layouts/
│   └── Layout.astro        ← SEO global, Schema.org JSON-LD, fonts
├── pages/
│   ├── index.astro         ← / — Hero 3 panneaux (style Haverick)
│   ├── boucherie.astro     ← /boucherie
│   ├── charcuterie.astro   ← /charcuterie
│   ├── a-propos.astro      ← /a-propos
│   ├── contact.astro       ← /contact
│   ├── merci.astro         ← /merci (redirect après formulaire)
│   └── traiteur/
│       ├── index.astro     ← /traiteur — liste menus
│       ├── [slug].astro    ← /traiteur/[slug] — détail + réservation
│       └── sur-mesure.astro ← /traiteur/sur-mesure — builder 4 étapes
└── styles/
    └── global.css          ← Tailwind + Google Fonts + composants CSS
```

---

### 5. Gestion des menus traiteur (pour Guillaume)

**Ajouter un menu :** Créer un fichier `.md` dans `src/content/menus/`
**Champs obligatoires :** `title`, `tag`, `description`, `pricePerPerson`, `minPeople`, `starters[]`, `mains[]`
**Champs optionnels :** `featured`, `image`, `maxPeople`, `duration`, `sides[]`, `desserts[]`, `includes[]`, `note`

Après ajout : lancer `npm run build` pour régénérer le site.

---

### 6. Formulaires — Web3Forms

**Clé d'accès :** `src/data/contact.ts` → champ `web3forms.accessKey`
**À remplacer avant mise en prod** par la vraie clé obtenue sur https://web3forms.com

Formulaires présents :
- `/contact` — message général
- `/traiteur/[slug]#reserver` — réservation menu précis
- `/traiteur/sur-mesure` — commande menu sur mesure (4 étapes, Alpine.js)

---

### 7. SEO Local

- **Title pattern :** `[Page] | Chez Guillaume — Boucherie Montbeton`
- **Schema.org :** `LocalBusiness + FoodEstablishment` dans `Layout.astro`
- **Geo :** `geo.region: FR-82`, `geo.placename: Montbeton`
- **Canonical :** systématique via `Astro.url.href`
- **Open Graph + Twitter Card :** présents dans le Layout

---

### 8. Images — À fournir

Placer dans `public/images/` :
```
hero-boucherie.jpg       ← Photo ambiance boucherie (fond hero accueil)
viande-coupe.jpg         ← Coupe de viande (médaillon hero)
viande-boeuf.jpg         ← Rayon bœuf
charcuterie.jpg          ← Plateau charcuterie
traiteur-brazero.jpg     ← Brazéro en action
guillaume-portrait.jpg   ← Photo de Guillaume
guillaumet-atelier.jpg   ← Guillaume en train de travailler
boutique-devanture.jpg   ← Façade de la boucherie
menus/brazero.jpg        ← Image menu brazéro
menus/cocktail.jpg       ← Image menu cocktail
menus/repas.jpg          ← Image menu repas
```

---

### 9. Commandes utiles

```bash
npm run dev      # Démarrage développement (localhost:4321)
npm run build    # Build de production (dossier dist/)
npm run preview  # Prévisualisation du build
```

---

### 10. Checklist avant mise en production

- [ ] Remplacer la clé Web3Forms dans `src/data/contact.ts`
- [ ] Ajouter toutes les photos dans `public/images/`
- [ ] Intégrer le vrai logo dans `Navigation.astro` et `Footer.astro`
- [ ] Vérifier les coordonnées GPS dans `Layout.astro` (schema.org geo)
- [ ] Activer le logo SVG/PNG dans `public/favicon.svg`
- [ ] Tester tous les formulaires (mode test Web3Forms d'abord)
- [ ] Vérifier le score Lighthouse (cible : 95+)
- [ ] Déployer sur Netlify / Vercel / Cloudflare Pages

# Context : Boucherie chez guillaume

## 1. Vue d'ensemble
**But du projet :** Site vitrine premium et minimaliste pour la "Boucherie chez guillaume".
**Stack technique :** Astro (SSG), Tailwind CSS, Alpine.js.
**Design Source :** Esthétique "App-like" Neumorphic Glassmorphism (Meat Club Theme: Gold/Dark) pour un positionnement haut de gamme.

## 2. État actuel
- [x] **CONSOLIDATION :** Fusion des sections Brazéro, Traiteur (Planches) et Boucherie (La Carte) en une seule section interactive.
- [x] **NAVIGATION :** Système d'onglets (Tabs) en Alpine.js pour naviguer entre les 3 univers de manière fluide.
- [x] **DESIGN :** Cartes glassmorphismes avec images circulaires et boutons "pill" pour une expérience utilisateur premium.
- [x] **INTERACTION :** Modales split-view pour les menus Brazéro avec hiérarchie de données claire (listes à puces).
- [x] **DATA :** Centralisation des données dans le frontmatter pour une modification facile.

## 3. Architecture
```text
/src
  /layouts/Layout.astro (Header & Footer Premium)
  /pages/index.astro    (Landing Page consolidée avec onglets et modales)
  /styles/global.css    (Core Design System)
```

## 4. Règles SEO en place
- **Structure Sémantique :** H1 stratégique sur le secteur Tarn-et-Garonne.
- **Accessibilité :** Balises Alt descriptives et structure d'onglets sémantique.
- **Vitesse :** Chargement SSR des données via Astro, interactivité légère avec Alpine.js.

## 5. Backlog / To-Do
- [ ] Finaliser l'intégration des prix réels pour "La Carte".
- [ ] Connecter le bouton de contact à un formulaire fonctionnel.
- [ ] Remplacer les images Unsplash par les photos de la boutique.

## 6. Journal des modifications
- **Modification Récente :** Fusion des sections en onglets pour un design ultra-épuré et intuitif.
- **Précédent :** Refonte des modales Brazéro en format split-view pour une meilleure lisibilité.

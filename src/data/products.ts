export type Product = {
  id: string;
  name: string;
  description: string;
  category: 'boucherie' | 'charcuterie' | 'epicerie';
  featured?: boolean;
  image?: string;
};

export const products: Product[] = [
  // Boucherie
  { id: 'boeuf', name: 'Bœuf', description: 'Pièces nobles et morceaux du boucher, sélectionnés chez des éleveurs locaux.', category: 'boucherie', featured: true },
  { id: 'veau', name: 'Veau', description: 'Veau de qualité, tendre et savoureux, en provenance de producteurs régionaux.', category: 'boucherie', featured: true },
  { id: 'agneau', name: 'Agneau', description: 'Agneau de pays, idéal pour vos grillades et plats mijotés.', category: 'boucherie', featured: true },
  { id: 'porc', name: 'Porc', description: 'Toutes les découpes du cochon, du rôti à la côtelette.', category: 'boucherie' },
  { id: 'volaille', name: 'Volaille', description: 'Poulet, pintade, canard — élevage soigné, qualité garantie.', category: 'boucherie' },
  // Charcuterie
  { id: 'saucisse-maison', name: 'Saucisses Maison', description: 'Saucisses fraîches préparées artisanalement selon la tradition.', category: 'charcuterie', featured: true },
  { id: 'pates-terrines', name: 'Pâtés & Terrines', description: 'Recettes maison, préparées avec soin dans notre laboratoire.', category: 'charcuterie', featured: true },
  { id: 'plats-cuisines', name: 'Plats Cuisinés', description: 'Plats du jour prêts à réchauffer — le goût de la maison chaque soir.', category: 'charcuterie' },
  { id: 'plateaux-charcuterie', name: 'Plateaux Charcuterie', description: 'Plateaux garnis sur commande pour apéritifs et événements.', category: 'charcuterie' },
  // Épicerie fine
  { id: 'fromages', name: 'Fromages', description: 'Sélection de fromages affinés pour accompagner vos viandes.', category: 'epicerie' },
  { id: 'epicerie', name: 'Épicerie Fine', description: 'Condiments, sauces et produits régionaux soigneusement sélectionnés.', category: 'epicerie' },
];

export const categories = {
  boucherie:  { label: 'Boucherie',     description: 'Viandes fraîches de qualité, issues d\'éleveurs locaux et régionaux.' },
  charcuterie: { label: 'Charcuterie',  description: 'Préparations artisanales réalisées chaque jour dans notre laboratoire.' },
  epicerie:   { label: 'Épicerie Fine', description: 'Une sélection de produits du terroir pour sublimer vos repas.' },
};

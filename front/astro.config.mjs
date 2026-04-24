// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwindcss from '@tailwindcss/vite';

// https://astro.build/config
export default defineConfig({
  site: 'https://boucheriechezguillaume.fr',
  integrations: [
    sitemap({
      filter: (page) =>
        !page.includes('/espace-boucher') &&
        !page.includes('/send-devis'),
    }),
  ],
  vite: {
    plugins: [tailwindcss()]
  }
});
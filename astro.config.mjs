import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';
import alpinejs from '@astrojs/alpinejs';

export default defineConfig({
  site: 'https://boucheriechezguillaume.fr',
  integrations: [
    tailwind({ applyBaseStyles: false }),
    alpinejs(),
  ],
  image: {
    domains: [],
  },
});

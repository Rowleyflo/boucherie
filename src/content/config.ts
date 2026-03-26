import { defineCollection, z } from 'astro:content';

const menus = defineCollection({
  type: 'content',
  schema: z.object({
    title: z.string(),
    tag: z.string(),                          // ex: 'Brazéro', 'Cocktail', 'Repas Assis'
    description: z.string(),
    pricePerPerson: z.number(),
    minPeople: z.number().default(10),
    maxPeople: z.number().optional(),
    duration: z.string().optional(),          // ex: '3h'
    featured: z.boolean().default(false),
    image: z.string().optional(),
    starters: z.array(z.string()),
    mains: z.array(z.string()),
    sides: z.array(z.string()).optional(),
    desserts: z.array(z.string()).optional(),
    includes: z.array(z.string()).optional(), // ex: 'Pain', 'Service', 'Couverts'
    note: z.string().optional(),
  }),
});

export const collections = { menus };

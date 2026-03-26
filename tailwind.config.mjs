import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,ts,tsx}'],
  theme: {
    extend: {
      colors: {
        dark: {
          950: '#080808',
          900: '#111111',
          800: '#1A1A1A',
          700: '#242424',
          600: '#2E2E2E',
          500: '#3A3A3A',
        },
        cream: {
          50:  '#FDFAF5',
          100: '#F5F0E8',
          200: '#EAE3D6',
          300: '#D4CBBA',
          400: '#B8AD9A',
          500: '#9A9485',
          600: '#7A7268',
        },
        rouge: {
          400: '#E83535',
          500: '#D42020',
          600: '#C41818',
          700: '#A01212',
          800: '#7A0D0D',
        },
      },
      fontFamily: {
        serif: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
        sans:  ['"DM Sans"', ...defaultTheme.fontFamily.sans],
      },
      letterSpacing: {
        'ultra': '0.35em',
        'max':   '0.5em',
      },
      backgroundImage: {
        'gradient-dark': 'linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.8) 100%)',
        'gradient-card': 'linear-gradient(135deg, rgba(26,26,26,0.92) 0%, rgba(17,17,17,0.95) 100%)',
      },
    },
  },
  plugins: [],
};

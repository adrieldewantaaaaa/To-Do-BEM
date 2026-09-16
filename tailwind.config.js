/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],
  theme: {
    extend: {
      colors: {
        paper: '#FFFFFF', surface: '#FFFFFF', ink: '#37352F', cobalt: '#2383E2',
        primary: '#2383E2', accent: '#D9730D',
        success: '#448361', warning: '#CB912F', danger: '#D9544D', charcoal: '#191919',
      },
      boxShadow: { card: '0 1px 0 rgba(23,23,23,.08), 0 8px 24px rgba(23,23,23,.06)' },
    },
  },
  plugins: [],
};

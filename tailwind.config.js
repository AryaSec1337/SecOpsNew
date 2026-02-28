/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'secops-dark': '#0f172a',
        'secops-accent': '#3b82f6',
        'secops-danger': '#ef4444',
      },
    },
  },
  plugins: [],
}

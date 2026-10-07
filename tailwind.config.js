/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./**/*.html",
    "./api/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        primary: "var(--color-primary, #0B1F3A)",
        accent: "var(--color-accent, #F59E0B)",
        tertiary: "#3B82F6",
        surface: {
          DEFAULT: "#F8FAFC",
          dim: "#F1F5F9",
        },
        status: {
          shipped: { bg: "#EFF6FF", text: "#1D4ED8", dot: "#3B82F6" },
          delivery: { bg: "#FEF3C7", text: "#B45309", dot: "#F59E0B" },
          delivered: { bg: "#ECFDF5", text: "#047857", dot: "#10B981" },
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      }
    },
  },
  plugins: [],
}

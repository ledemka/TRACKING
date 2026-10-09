/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./templates/**/*.php",
    "./api/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        primary: "var(--color-primary, #0B1F3A)",
        accent: "var(--color-accent, #F59E0B)",
        action: "#1D4ED8",
        danger: "#ba1a1a",
        surface: {
          DEFAULT: "#F8FAFC",
          dim: "#F1F5F9",
        },
        status: {
          shipped: { bg: "#EFF6FF", text: "#2563EB", border: "#BFDBFE" },
          delivery: { bg: "#FFF7ED", text: "#D97706", border: "#FED7AA" },
          delivered: { bg: "#F0FDF4", text: "#16A34A", border: "#BBF7D0" },
          delayed: { bg: "#FEF2F2", text: "#DC2626", border: "#FECACA" },
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      boxShadow: {
        'elevation-1': '0 1px 3px 0 rgba(11, 31, 58, 0.04), 0 1px 2px -1px rgba(11, 31, 58, 0.04)',
        'elevation-2': '0 4px 12px -2px rgba(11, 31, 58, 0.08), 0 2px 6px -2px rgba(11, 31, 58, 0.04)',
        'elevation-3': '0 12px 24px -4px rgba(11, 31, 58, 0.12), 0 4px 8px -2px rgba(11, 31, 58, 0.04)',
        'elevation-4': '0 20px 32px -8px rgba(11, 31, 58, 0.16)',
      },
      borderRadius: {
        'xl': '1.5rem',
      }
    },
  },
  plugins: [],
}

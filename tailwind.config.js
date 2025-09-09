module.exports = {
  content: [
    "./*.php",                // scan root
    "./public/**/*.php",      // scan semua php di public
    "./includes/**/*.php",    // scan php di includes
    "./public/**/*.html",
    "./includes/**/*.html",
    "./assets/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#3B82F6',
        secondary: '#10B981'
      }
    },
  },
  safelist: [
    // (biarin tetap ada)
  ],
  plugins: [],
}

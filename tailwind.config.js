module.exports = {
  mode: 'jit',
  content: [
      '/*.php',
    './libs/*.php',  // Scan all PHP files in the libs directory
    './partials/*.php',  // Scan all PHP files in the partials directory
    './view/*.php',  // Scan all PHP files in the view directory
  ],
  theme: {
    extend: {
      spacing: {
        '24': '6rem',  // Adding 6rem (96px) as a custom spacing value
      },
      width: {
        '24': '6rem',  // Adding 6rem (96px) as a custom width value
      },
      height: {
        '24': '6rem',  // Adding 6rem (96px) as a custom height value
      },
    },
  },
  plugins: [],
}

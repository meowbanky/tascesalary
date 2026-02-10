module.exports = {
  mode: "jit",
  content: [
    "./*.php",
    "./libs/*.php",
    "./partials/*.php",
    "./view/*.php",
    "./auth_api/**/*.php",
    "./payroll-portal___/**/*.{html,js,tsx,jsx}",
    "./assets/js/*.js",
  ],
  theme: {
    extend: {
      spacing: {
        24: "6rem",
      },
      width: {
        24: "6rem",
      },
      height: {
        24: "6rem",
      },
    },
  },
  plugins: [],
};

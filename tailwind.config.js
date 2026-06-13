/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/**/*.{js,jsx}",
    "./components/**/*.{js,jsx}",
  ],
  theme: {
    extend: {
      colors: {
        ink: "#050511",
        night: "#090B1D",
        electric: "#11C8FF",
        neon: "#9A3BFF",
        punch: "#FF2EA6",
        orangeFizz: "#FF8A1D",
      },
      fontFamily: {
        display: ["var(--font-display)", "Inter", "sans-serif"],
        body: ["var(--font-body)", "Inter", "sans-serif"],
      },
      boxShadow: {
        glow: "0 0 34px rgba(255, 46, 166, 0.34)",
        cyan: "0 0 44px rgba(17, 200, 255, 0.3)",
      },
      backgroundImage: {
        "fizz-radial": "radial-gradient(circle at 20% 20%, rgba(17, 200, 255, .24), transparent 34%), radial-gradient(circle at 78% 14%, rgba(255, 46, 166, .22), transparent 28%), linear-gradient(135deg, #050511 0%, #0B1235 52%, #120621 100%)",
      },
    },
  },
  plugins: [],
};

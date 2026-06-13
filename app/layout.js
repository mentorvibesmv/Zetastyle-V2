import { Inter, Space_Grotesk } from "next/font/google";
import "./globals.css";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-body",
  display: "swap",
});

const spaceGrotesk = Space_Grotesk({
  subsets: ["latin"],
  variable: "--font-display",
  display: "swap",
});

export const metadata = {
  title: "TheFizzology | Premium Mocktail Fizz Bombs",
  description:
    "Turn every drink into a party with premium mocktail fizz bombs, cocktail mixers, and party beverage products.",
  openGraph: {
    title: "TheFizzology | Turn Every Drink Into A Party",
    description: "Premium mocktail fizz bombs and cocktail mixers for unforgettable nights.",
    type: "website",
  },
};

export default function RootLayout({ children }) {
  return (
    <html lang="en" className={`${inter.variable} ${spaceGrotesk.variable}`}>
      <body>{children}</body>
    </html>
  );
}

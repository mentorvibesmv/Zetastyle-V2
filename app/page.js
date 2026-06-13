"use client";

import { motion } from "framer-motion";
import {
  ArrowRight,
  Gift,
  Instagram,
  Menu,
  MessageCircle,
  PartyPopper,
  ShoppingBag,
  Sparkles,
  Star,
  X,
  Zap,
} from "lucide-react";
import { useState } from "react";
import { CursorGlow, ParallaxCan, Reveal, ScrollProgress } from "@/components/SiteMotion";

const products = [
  {
    name: "Berry Blast",
    price: "Rs. 349",
    desc: "Raspberry, blueberry, neon fizz, zero boring energy.",
    colors: "from-[#ff2ea6] via-[#9a3bff] to-[#11c8ff]",
  },
  {
    name: "Citrus Spark",
    price: "Rs. 329",
    desc: "Orange zest, lime pop, and a crisp party finish.",
    colors: "from-[#ff8a1d] via-[#ffd166] to-[#11c8ff]",
  },
  {
    name: "Tropical Rush",
    price: "Rs. 379",
    desc: "Pineapple, passionfruit, sunset glow in every drop.",
    colors: "from-[#00f5a0] via-[#11c8ff] to-[#ff8a1d]",
  },
  {
    name: "Midnight Mojito",
    price: "Rs. 399",
    desc: "Mint, lime, dark sparkle, made for after-hours pours.",
    colors: "from-[#0b1235] via-[#9a3bff] to-[#00f5d4]",
  },
];

const reels = [
  ["POV: house party", "Fizz the moment", "from-punch to-electric"],
  ["Mocktail bar", "Drop. Spark. Repeat.", "from-neon to-orangeFizz"],
  ["Unboxing night", "Gifting with glow", "from-electric to-punch"],
  ["College fest", "Party starts here", "from-orangeFizz to-neon"],
  ["Wedding hamper", "Crafted for nights", "from-[#00f5a0] to-punch"],
];

const testimonials = [
  ["Aanya R.", "Berry Blast literally made my mocktail table look like a reel setup.", "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=240&q=80"],
  ["Kabir S.", "Used these for a birthday pre-game. Everyone asked where we ordered from.", "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=240&q=80"],
  ["Mira K.", "Premium enough for hampers, fun enough for house parties. That combo is rare.", "https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=240&q=80"],
];

const faqs = [
  ["How to order?", "Tap Shop Now, choose your flavors, and complete checkout. Bulk and gifting orders can also start on WhatsApp."],
  ["Delivery availability?", "We deliver across major Indian cities with launch-week express slots in select metro areas."],
  ["Shelf life?", "Fizz bombs stay party-ready for up to 9 months when stored cool, dry, and sealed."],
  ["Bulk order support?", "Yes. We support birthdays, weddings, college events, corporate gifting, and custom hampers."],
  ["Mocktail compatibility?", "Drop one into soda, lemonade, tonic, sparkling water, juice, or your favorite cocktail base."],
];

function Nav() {
  const [open, setOpen] = useState(false);
  const links = ["Shop", "How", "Reels", "Events", "FAQ"];

  return (
    <header className="fixed inset-x-0 top-0 z-50 px-4 py-4">
      <nav className="glass mx-auto flex max-w-7xl items-center justify-between rounded-full px-4 py-3 md:px-6">
        <a href="#hero" className="font-display text-lg font-black tracking-tight">
          The<span className="text-gradient">Fizzology</span>
        </a>
        <div className="hidden items-center gap-7 text-sm font-semibold text-white/72 md:flex">
          {links.map((link) => (
            <a key={link} href={`#${link.toLowerCase()}`} className="transition hover:text-white">
              {link}
            </a>
          ))}
        </div>
        <div className="hidden items-center gap-3 md:flex">
          <button aria-label="Open cart" className="relative rounded-full border border-white/15 bg-white/10 p-3 transition hover:bg-white/18">
            <ShoppingBag size={18} />
            <span className="absolute -right-1 -top-1 grid h-5 w-5 place-items-center rounded-full bg-punch text-[10px] font-black">2</span>
          </button>
          <a href="#shop" className="rounded-full bg-white px-5 py-3 text-sm font-black text-ink transition hover:scale-105">
            Shop Now
          </a>
        </div>
        <button className="rounded-full border border-white/15 bg-white/10 p-3 md:hidden" onClick={() => setOpen(!open)} aria-label="Toggle navigation">
          {open ? <X size={18} /> : <Menu size={18} />}
        </button>
      </nav>
      {open && (
        <div className="glass mx-4 mt-3 rounded-3xl p-4 md:hidden">
          {links.map((link) => (
            <a key={link} href={`#${link.toLowerCase()}`} onClick={() => setOpen(false)} className="block rounded-2xl px-4 py-3 font-semibold text-white/78">
              {link}
            </a>
          ))}
        </div>
      )}
    </header>
  );
}

function ProductMini({ item, index }) {
  return (
    <motion.article
      whileHover={{ y: -10, rotateX: 5, rotateY: index % 2 ? -5 : 5 }}
      transition={{ type: "spring", stiffness: 220, damping: 18 }}
      className="glass group rounded-[2rem] p-5"
    >
      <div className={`relative mb-5 grid aspect-[4/3] place-items-center overflow-hidden rounded-3xl bg-gradient-to-br ${item.colors}`}>
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,.5),transparent_18%),radial-gradient(circle_at_70%_80%,rgba(255,255,255,.22),transparent_24%)]" />
        <div className="relative h-36 w-28 rounded-[2rem] bg-white/18 shadow-2xl backdrop-blur-md transition duration-500 group-hover:scale-110">
          <div className="absolute inset-x-3 top-4 h-5 rounded-full bg-white/35" />
          <div className="absolute inset-x-4 bottom-6 text-center font-display text-lg font-black leading-none">{item.name.split(" ")[0]}</div>
        </div>
      </div>
      <div className="flex items-start justify-between gap-3">
        <div>
          <h3 className="font-display text-2xl font-black">{item.name}</h3>
          <p className="mt-2 text-sm leading-6 text-white/62">{item.desc}</p>
        </div>
        <p className="rounded-full bg-white/10 px-3 py-2 text-sm font-black">{item.price}</p>
      </div>
      <button className="mt-5 flex w-full items-center justify-center gap-2 rounded-full bg-white px-5 py-3 font-black text-ink transition hover:bg-electric">
        Add to Cart <ShoppingBag size={17} />
      </button>
    </motion.article>
  );
}

function Hero() {
  return (
    <section id="hero" className="relative min-h-screen overflow-hidden bg-fizz-radial px-4 pt-32">
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_110%,rgba(255,138,29,.2),transparent_35%)]" />
      <div className="mx-auto grid min-h-[calc(100vh-8rem)] max-w-7xl items-center gap-10 lg:grid-cols-[1.05fr_.95fr]">
        <motion.div initial={{ opacity: 0, y: 36 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.85 }} className="relative z-10">
          <p className="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[.22em] text-white/76 backdrop-blur">
            <Sparkles size={15} className="text-orangeFizz" /> Fizz the moment
          </p>
          <h1 className="max-w-5xl font-display text-[clamp(3.35rem,12vw,8.8rem)] font-black leading-[.82] tracking-tight">
            Turn Every Drink Into A <span className="text-gradient">Party</span>
          </h1>
          <p className="mt-7 max-w-2xl text-lg font-medium leading-8 text-white/68 md:text-2xl">
            Premium Mocktail Fizz Bombs & Cocktail Mixers.
          </p>
          <div className="mt-9 flex flex-col gap-4 sm:flex-row">
            <a href="#shop" className="group inline-flex items-center justify-center gap-3 rounded-full bg-white px-7 py-4 font-black text-ink shadow-cyan transition hover:scale-105">
              Shop Now <ArrowRight className="transition group-hover:translate-x-1" size={19} />
            </a>
            <a href="#shop" className="inline-flex items-center justify-center rounded-full border border-white/18 bg-white/10 px-7 py-4 font-black text-white backdrop-blur transition hover:bg-white/18">
              Explore Flavors
            </a>
          </div>
        </motion.div>
        <ParallaxCan />
      </div>
      <div className="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 text-center text-xs font-bold uppercase tracking-[.28em] text-white/50">
        <div className="mx-auto mb-3 h-12 w-7 rounded-full border border-white/28 p-1">
          <motion.div className="h-2 w-2 rounded-full bg-white" animate={{ y: [0, 20, 0] }} transition={{ repeat: Infinity, duration: 1.6 }} />
        </div>
        Scroll
      </div>
    </section>
  );
}

function HowItWorks() {
  const steps = [
    ["Drop", "Place one fizz bomb into your glass.", Zap],
    ["Fizz", "Watch color, bubbles, and aroma wake up.", Sparkles],
    ["Enjoy", "Serve cold, film fast, repeat often.", PartyPopper],
  ];

  return (
    <section id="how" className="px-4 py-24">
      <Reveal className="mx-auto max-w-3xl text-center">
        <p className="font-black uppercase tracking-[.28em] text-electric">Drop. Spark. Repeat.</p>
        <h2 className="mt-4 font-display text-4xl font-black md:text-6xl">Three steps to instant celebration</h2>
      </Reveal>
      <div className="mx-auto mt-14 grid max-w-6xl gap-5 md:grid-cols-3">
        {steps.map(([title, copy, Icon], index) => (
          <Reveal key={title} delay={index * 0.12} className="glass rounded-[2rem] p-7 text-center">
            <motion.div whileHover={{ rotate: 8, scale: 1.08 }} className="mx-auto grid h-20 w-20 place-items-center rounded-3xl bg-gradient-to-br from-electric via-neon to-punch shadow-glow">
              <Icon size={34} />
            </motion.div>
            <h3 className="mt-6 font-display text-3xl font-black">{title}</h3>
            <p className="mt-3 text-white/62">{copy}</p>
          </Reveal>
        ))}
      </div>
    </section>
  );
}

function Reels() {
  return (
    <section id="reels" className="overflow-hidden py-24">
      <Reveal className="px-4 text-center">
        <p className="font-black uppercase tracking-[.28em] text-punch">Social first</p>
        <h2 className="mt-4 font-display text-4xl font-black md:text-6xl">Built for the group chat</h2>
      </Reveal>
      <div className="no-scrollbar mt-12 flex gap-5 overflow-x-auto px-4 md:px-[max(1rem,calc((100vw-72rem)/2))]">
        {reels.map(([title, tag, colors]) => (
          <motion.article key={title} whileHover={{ y: -12 }} className="glass relative h-[520px] min-w-[285px] overflow-hidden rounded-[2rem] p-5">
            <div className={`absolute inset-0 bg-gradient-to-br ${colors} opacity-75`} />
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_25%,rgba(255,255,255,.42),transparent_18%),linear-gradient(180deg,transparent,rgba(0,0,0,.68))]" />
            <div className="relative flex h-full flex-col justify-between">
              <div className="flex items-center justify-between">
                <Instagram size={22} />
                <span className="rounded-full bg-black/24 px-3 py-1 text-xs font-black backdrop-blur">LIVE</span>
              </div>
              <div>
                <div className="mb-4 h-52 rounded-[2rem] border border-white/18 bg-white/12 shadow-2xl backdrop-blur-md">
                  <div className="marquee flex h-full w-[200%] items-center gap-5 px-5">
                    {Array.from({ length: 8 }).map((_, index) => (
                      <span key={index} className="h-10 w-10 rounded-full bg-white/55 shadow-cyan" />
                    ))}
                  </div>
                </div>
                <h3 className="font-display text-3xl font-black">{title}</h3>
                <p className="mt-2 font-semibold text-white/72">{tag}</p>
              </div>
            </div>
          </motion.article>
        ))}
      </div>
    </section>
  );
}

function Events() {
  const events = ["Birthday parties", "Wedding hampers", "Corporate gifting", "College events"];
  return (
    <section id="events" className="px-4 py-24">
      <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[.9fr_1.1fr]">
        <Reveal>
          <p className="font-black uppercase tracking-[.28em] text-orangeFizz">Events & gifting</p>
          <h2 className="mt-4 font-display text-4xl font-black md:text-6xl">Crafted for unforgettable nights</h2>
          <p className="mt-6 max-w-xl text-lg leading-8 text-white/62">Custom packs, premium hampers, and bulk-ready fizz for hosts who want the drink table to steal the show.</p>
        </Reveal>
        <div className="grid gap-4 sm:grid-cols-2">
          {events.map((event, index) => (
            <Reveal key={event} delay={index * 0.08} className="glass rounded-[2rem] p-6">
              <div className="mb-8 grid h-16 w-16 place-items-center rounded-3xl bg-white/12">
                {index === 1 ? <Gift /> : <PartyPopper />}
              </div>
              <h3 className="font-display text-2xl font-black">{event}</h3>
              <p className="mt-3 text-sm leading-6 text-white/60">Premium presentation, bold flavors, and a camera-ready pour.</p>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}

function FAQ() {
  const [active, setActive] = useState(0);

  return (
    <section id="faq" className="px-4 py-24">
      <Reveal className="mx-auto max-w-3xl text-center">
        <p className="font-black uppercase tracking-[.28em] text-electric">FAQ</p>
        <h2 className="mt-4 font-display text-4xl font-black md:text-6xl">Questions before the first fizz?</h2>
      </Reveal>
      <div className="mx-auto mt-12 max-w-3xl space-y-3">
        {faqs.map(([q, a], index) => (
          <div key={q} className="glass overflow-hidden rounded-3xl">
            <button onClick={() => setActive(active === index ? -1 : index)} className="flex w-full items-center justify-between gap-5 px-6 py-5 text-left font-display text-xl font-black">
              {q}
              <span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/10">{active === index ? "-" : "+"}</span>
            </button>
            <motion.div initial={false} animate={{ height: active === index ? "auto" : 0 }} className="overflow-hidden">
              <p className="px-6 pb-6 leading-7 text-white/62">{a}</p>
            </motion.div>
          </div>
        ))}
      </div>
    </section>
  );
}

export default function Home() {
  return (
    <main className="relative bg-ink">
      <ScrollProgress />
      <CursorGlow />
      <div className="noise" />
      <Nav />
      <Hero />
      <section id="shop" className="relative px-4 py-24">
        <Reveal className="mx-auto max-w-3xl text-center">
          <p className="font-black uppercase tracking-[.28em] text-punch">Party starts here</p>
          <h2 className="mt-4 font-display text-4xl font-black md:text-6xl">Flavor drops with main-character energy</h2>
        </Reveal>
        <div className="mx-auto mt-12 grid max-w-7xl gap-5 md:grid-cols-2 xl:grid-cols-4">
          {products.map((item, index) => <ProductMini key={item.name} item={item} index={index} />)}
        </div>
      </section>
      <HowItWorks />
      <Reels />
      <section className="px-4 py-24">
        <Reveal className="mx-auto max-w-3xl text-center">
          <p className="font-black uppercase tracking-[.28em] text-orangeFizz">Loved already</p>
          <h2 className="mt-4 font-display text-4xl font-black md:text-6xl">Reviews with real sparkle</h2>
        </Reveal>
        <div className="mx-auto mt-12 grid max-w-6xl gap-5 md:grid-cols-3">
          {testimonials.map(([name, quote, img], index) => (
            <Reveal key={name} delay={index * 0.1} className="glass rounded-[2rem] p-6">
              <div className="flex items-center gap-4">
                <img src={img} alt={name} className="h-14 w-14 rounded-full object-cover" />
                <div>
                  <h3 className="font-black">{name}</h3>
                  <div className="mt-1 flex text-orangeFizz">{Array.from({ length: 5 }).map((_, i) => <Star key={i} size={15} fill="currentColor" />)}</div>
                </div>
              </div>
              <p className="mt-6 leading-7 text-white/68">"{quote}"</p>
            </Reveal>
          ))}
        </div>
      </section>
      <Events />
      <FAQ />
      <footer className="border-t border-white/10 px-4 py-12">
        <div className="mx-auto flex max-w-7xl flex-col gap-8 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 className="font-display text-3xl font-black">The<span className="text-gradient">Fizzology</span></h2>
            <p className="mt-2 text-white/58">Drop. Spark. Repeat.</p>
          </div>
          <div className="flex flex-wrap gap-3 text-sm font-bold text-white/70">
            <a href="#shop" className="rounded-full bg-white/10 px-4 py-2">Shop</a>
            <a href="#events" className="rounded-full bg-white/10 px-4 py-2">Events</a>
            <a href="https://instagram.com" className="rounded-full bg-white/10 px-4 py-2">Instagram</a>
            <a href="mailto:hello@thefizzology.com" className="rounded-full bg-white/10 px-4 py-2">hello@thefizzology.com</a>
          </div>
        </div>
      </footer>
      <a href="https://wa.me/919999999999" className="fixed bottom-5 right-5 z-50 grid h-14 w-14 place-items-center rounded-full bg-[#25D366] text-white shadow-glow transition hover:scale-110" aria-label="Contact TheFizzology on WhatsApp">
        <MessageCircle />
      </a>
    </main>
  );
}

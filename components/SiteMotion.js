"use client";

import { motion, useMotionValue, useSpring, useScroll, useTransform } from "framer-motion";
import { useEffect, useState } from "react";

export function ScrollProgress() {
  const { scrollYProgress } = useScroll();
  const scaleX = useSpring(scrollYProgress, { stiffness: 120, damping: 24 });

  return (
    <motion.div
      className="fixed left-0 top-0 z-[80] h-1 origin-left bg-gradient-to-r from-electric via-punch to-orangeFizz"
      style={{ scaleX, width: "100%" }}
    />
  );
}

export function CursorGlow() {
  const x = useMotionValue(-200);
  const y = useMotionValue(-200);
  const smoothX = useSpring(x, { damping: 35, stiffness: 180 });
  const smoothY = useSpring(y, { damping: 35, stiffness: 180 });

  useEffect(() => {
    const onMove = (event) => {
      x.set(event.clientX - 190);
      y.set(event.clientY - 190);
    };
    window.addEventListener("pointermove", onMove);
    return () => window.removeEventListener("pointermove", onMove);
  }, [x, y]);

  return (
    <motion.div
      className="pointer-events-none fixed z-[3] hidden h-[380px] w-[380px] rounded-full bg-[radial-gradient(circle,rgba(17,200,255,.18),rgba(255,46,166,.12)_36%,transparent_68%)] blur-2xl lg:block"
      style={{ x: smoothX, y: smoothY }}
    />
  );
}

export function Reveal({ children, className = "", delay = 0 }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 34 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-80px" }}
      transition={{ duration: 0.75, delay, ease: [0.22, 1, 0.36, 1] }}
      className={className}
    >
      {children}
    </motion.div>
  );
}

export function ParallaxCan() {
  const { scrollYProgress } = useScroll();
  const y = useTransform(scrollYProgress, [0, 0.4], [0, 90]);
  const rotate = useTransform(scrollYProgress, [0, 0.4], [-8, 8]);
  const [tilt, setTilt] = useState({ rx: 0, ry: 0 });

  return (
    <motion.div
      style={{ y, rotate }}
      onMouseMove={(event) => {
        const rect = event.currentTarget.getBoundingClientRect();
        setTilt({
          rx: ((event.clientY - rect.top) / rect.height - 0.5) * -10,
          ry: ((event.clientX - rect.left) / rect.width - 0.5) * 12,
        });
      }}
      onMouseLeave={() => setTilt({ rx: 0, ry: 0 })}
      animate={{ rotateX: tilt.rx, rotateY: tilt.ry }}
      transition={{ type: "spring", stiffness: 120, damping: 16 }}
      className="relative mx-auto grid place-items-center perspective-1000"
    >
      <div className="product-can">
        <div className="can-label">
          <div className="text-center">
            <p className="font-display text-[clamp(1.35rem,4vw,2.5rem)] font-black leading-none">The</p>
            <p className="font-display text-[clamp(1.1rem,3vw,2rem)] font-black leading-none text-gradient">Fizzology</p>
            <p className="mt-3 text-xs font-bold uppercase tracking-[.35em] text-white/70">Fizz Bombs</p>
          </div>
        </div>
      </div>
      {Array.from({ length: 18 }).map((_, index) => (
        <span
          key={index}
          className="fizz-dot text-electric"
          style={{
            "--s": `${6 + (index % 5) * 3}px`,
            "--d": `${2.4 + (index % 6) * 0.4}s`,
            left: `${-10 + (index * 17) % 116}%`,
            top: `${-12 + (index * 23) % 104}%`,
          }}
        />
      ))}
    </motion.div>
  );
}

<?php
/**
 * Landing Page — CV Builder (Motion UI Redesign)
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// If logged in, go to dashboard
if (is_logged_in()) {
    redirect(BASE_URL . '/dashboard.php');
}

$pageTitle = 'Build Your Professional CV';
require_once __DIR__ . '/includes/header.php';
?>

<!-- =============================================
     SCOPED LANDING PAGE STYLES — does not affect 
     any other page in the application
     ============================================= -->
<style>
/* ─── IMPORT ─────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap');

/* ─── RESET / SCOPE ───────────────────────────── */
.lp-root *,
.lp-root *::before,
.lp-root *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

/* ─── TOKENS ──────────────────────────────────── */
.lp-root {
  --lp-bg:        #03020a;
  --lp-surface:   #0c0b18;
  --lp-cyan:      #00ffe0;
  --lp-violet:    #7b2fff;
  --lp-pink:      #ff2d7a;
  --lp-gold:      #ffc15e;
  --lp-text:      #e8e6f0;
  --lp-muted:     #7a7890;
  --lp-border:    rgba(123,47,255,.18);
  --lp-glass:     rgba(12,11,24,.72);
  --ease-out-expo: cubic-bezier(.16,1,.3,1);
  font-family: 'DM Sans', sans-serif;
  background: var(--lp-bg);
  color: var(--lp-text);
  overflow-x: hidden;
  position: relative;
}

/* ─── CANVAS LAYER ────────────────────────────── */
#lp-canvas {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
}

/* ─── NOISE OVERLAY ───────────────────────────── */
.lp-noise {
  position: fixed;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  opacity: .03;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* ─── AURORA BLOBS ────────────────────────────── */
.lp-aurora {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.lp-aurora__blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: .28;
  animation: lp-drift 18s ease-in-out infinite alternate;
}
.lp-aurora__blob:nth-child(1) {
  width: 700px; height: 700px;
  background: var(--lp-violet);
  top: -20%; left: -10%;
  animation-duration: 22s;
}
.lp-aurora__blob:nth-child(2) {
  width: 500px; height: 500px;
  background: var(--lp-cyan);
  top: 30%; right: -8%;
  animation-duration: 17s;
  animation-delay: -6s;
}
.lp-aurora__blob:nth-child(3) {
  width: 400px; height: 400px;
  background: var(--lp-pink);
  bottom: 10%; left: 30%;
  animation-duration: 25s;
  animation-delay: -12s;
}
@keyframes lp-drift {
  0%   { transform: translate(0,0) scale(1); }
  33%  { transform: translate(60px,-80px) scale(1.08); }
  66%  { transform: translate(-40px,60px) scale(.94); }
  100% { transform: translate(30px,30px) scale(1.05); }
}

/* ─── WRAP ────────────────────────────────────── */
.lp-wrap {
  position: relative;
  z-index: 2;
}

/* ─── HERO ────────────────────────────────────── */
.lp-hero {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 120px 0 80px;
  position: relative;
}
.lp-hero__inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 40px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}
@media (max-width: 900px) {
  .lp-hero__inner { grid-template-columns: 1fr; }
  .lp-hero__visual { display: none !important; }
}

/* badge */
.lp-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  background: rgba(0,255,224,.08);
  border: 1px solid rgba(0,255,224,.25);
  border-radius: 100px;
  font-size: 12px;
  font-weight: 500;
  color: var(--lp-cyan);
  letter-spacing: .06em;
  text-transform: uppercase;
  margin-bottom: 28px;
  opacity: 0;
  animation: lp-fadeUp .8s var(--ease-out-expo) .1s forwards;
}
.lp-badge::before {
  content:'';
  width: 6px; height: 6px;
  background: var(--lp-cyan);
  border-radius: 50%;
  animation: lp-pulse 2s ease-in-out infinite;
}
@keyframes lp-pulse {
  0%,100%{ opacity:1; transform:scale(1); }
  50%{ opacity:.4; transform:scale(.6); }
}

/* headline */
.lp-headline {
  font-family: 'Syne', sans-serif;
  font-size: clamp(3rem, 6vw, 5.2rem);
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: -.03em;
  margin-bottom: 24px;
  opacity: 0;
  animation: lp-fadeUp .9s var(--ease-out-expo) .25s forwards;
}
.lp-headline em {
  font-style: normal;
  position: relative;
  display: inline-block;
}
.lp-headline em span {
  background: linear-gradient(135deg, var(--lp-cyan), var(--lp-violet), var(--lp-pink));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  background-size: 200%;
  animation: lp-gradShift 4s ease-in-out infinite alternate;
}
@keyframes lp-gradShift {
  0%  { background-position: 0% 50%; }
  100%{ background-position: 100% 50%; }
}
.lp-headline em::after {
  content:'';
  position: absolute;
  left: 0; right: 0;
  bottom: 2px;
  height: 3px;
  background: linear-gradient(90deg, var(--lp-cyan), var(--lp-violet));
  border-radius: 2px;
  transform: scaleX(0);
  transform-origin: left;
  animation: lp-underline 1s var(--ease-out-expo) 1.1s forwards;
}
@keyframes lp-underline {
  to { transform: scaleX(1); }
}

/* description */
.lp-description {
  font-size: 1.1rem;
  line-height: 1.75;
  color: var(--lp-muted);
  max-width: 480px;
  margin-bottom: 40px;
  opacity: 0;
  animation: lp-fadeUp .9s var(--ease-out-expo) .4s forwards;
}

/* CTA row */
.lp-cta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  align-items: center;
  opacity: 0;
  animation: lp-fadeUp .9s var(--ease-out-expo) .55s forwards;
}
.lp-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 36px;
  background: linear-gradient(135deg, var(--lp-violet), var(--lp-cyan));
  color: #fff;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: .95rem;
  letter-spacing: .02em;
  border-radius: 100px;
  text-decoration: none;
  position: relative;
  overflow: hidden;
  transition: transform .3s var(--ease-out-expo), box-shadow .3s;
  box-shadow: 0 0 40px rgba(123,47,255,.4);
}
.lp-btn-primary::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--lp-cyan), var(--lp-violet));
  opacity: 0;
  transition: opacity .4s;
}
.lp-btn-primary:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 12px 60px rgba(123,47,255,.6), 0 0 0 1px rgba(0,255,224,.3);
  color: #fff;
  text-decoration: none;
}
.lp-btn-primary:hover::before { opacity: 1; }
.lp-btn-primary > * { position: relative; z-index: 1; }
.lp-btn-primary .lp-arrow {
  width: 28px; height: 28px;
  background: rgba(255,255,255,.2);
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: transform .3s var(--ease-out-expo);
}
.lp-btn-primary:hover .lp-arrow { transform: translateX(4px); }

.lp-btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 28px;
  border: 1px solid var(--lp-border);
  border-radius: 100px;
  color: var(--lp-muted);
  font-size: .9rem;
  text-decoration: none;
  background: var(--lp-glass);
  backdrop-filter: blur(12px);
  transition: all .3s;
}
.lp-btn-ghost:hover {
  border-color: rgba(0,255,224,.4);
  color: var(--lp-cyan);
  text-decoration: none;
  box-shadow: 0 0 20px rgba(0,255,224,.1);
}

/* stats strip */
.lp-stats {
  display: flex;
  gap: 32px;
  margin-top: 56px;
  flex-wrap: wrap;
  opacity: 0;
  animation: lp-fadeUp .9s var(--ease-out-expo) .7s forwards;
}
.lp-stat {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.lp-stat__num {
  font-family: 'Syne', sans-serif;
  font-size: 1.6rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--lp-text), var(--lp-cyan));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.lp-stat__label {
  font-size: .78rem;
  color: var(--lp-muted);
  letter-spacing: .04em;
  text-transform: uppercase;
}
.lp-stat-divider {
  width: 1px;
  background: var(--lp-border);
  align-self: stretch;
}

/* scroll hint */
.lp-scroll-hint {
  position: absolute;
  bottom: 32px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: var(--lp-muted);
  font-size: 11px;
  letter-spacing: .1em;
  text-transform: uppercase;
  opacity: 0;
  animation: lp-fadeUp .8s var(--ease-out-expo) 1.2s forwards;
}
.lp-scroll-mouse {
  width: 22px; height: 36px;
  border: 1.5px solid rgba(255,255,255,.15);
  border-radius: 12px;
  display: flex;
  justify-content: center;
  padding-top: 6px;
}
.lp-scroll-dot {
  width: 3px; height: 7px;
  background: var(--lp-cyan);
  border-radius: 2px;
  animation: lp-scrollDot 1.8s ease-in-out infinite;
}
@keyframes lp-scrollDot {
  0%  { transform: translateY(0); opacity:1; }
  80% { transform: translateY(10px); opacity:0; }
  100%{ transform: translateY(0); opacity:0; }
}

/* ─── HERO VISUAL ─────────────────────────────── */
.lp-hero__visual {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  opacity: 0;
  animation: lp-fadeIn 1.2s var(--ease-out-expo) .6s forwards;
}
.lp-mockup {
  width: 100%;
  max-width: 460px;
  background: var(--lp-glass);
  backdrop-filter: blur(24px);
  border: 1px solid var(--lp-border);
  border-radius: 24px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 40px 120px rgba(0,0,0,.6), 0 0 0 1px rgba(123,47,255,.1);
  animation: lp-float 6s ease-in-out infinite;
}
@keyframes lp-float {
  0%,100%{ transform: translateY(0) rotateY(-4deg) rotateX(2deg); }
  50%    { transform: translateY(-18px) rotateY(2deg) rotateX(-1deg); }
}
.lp-mockup-bar {
  height: 40px;
  background: rgba(255,255,255,.03);
  border-bottom: 1px solid var(--lp-border);
  display: flex;
  align-items: center;
  padding: 0 16px;
  gap: 7px;
}
.lp-dot { width:10px; height:10px; border-radius:50%; }
.lp-dot:nth-child(1){ background:#ff5f57; }
.lp-dot:nth-child(2){ background:#febc2e; }
.lp-dot:nth-child(3){ background:#28c840; }
.lp-mockup-body { padding: 28px; }
.lp-mockup-row { display: flex; gap: 20px; }
.lp-mockup-left { width: 38%; border-right: 1px solid var(--lp-border); padding-right: 18px; }
.lp-mockup-right { flex: 1; }

/* skeleton lines */
.sk {
  height: 8px;
  border-radius: 4px;
  margin-bottom: 8px;
  position: relative;
  overflow: hidden;
}
.sk::after {
  content:'';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.08) 50%, transparent 100%);
  animation: lp-shimmer 2.2s ease-in-out infinite;
}
@keyframes lp-shimmer {
  0%  { transform: translateX(-100%); }
  100%{ transform: translateX(100%); }
}
.sk-avatar {
  width: 64px; height: 64px;
  border-radius: 50%;
  margin: 0 auto 16px;
  background: linear-gradient(135deg, var(--lp-violet), var(--lp-cyan));
  position: relative;
  overflow: hidden;
}
.sk-avatar::after {
  content:'';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,.15));
}
.sk-title  { background: rgba(123,47,255,.35); width: 75%; }
.sk-sub    { background: rgba(255,255,255,.07); width: 55%; }
.sk-head   { background: rgba(0,255,224,.2); width: 40%; height: 10px; margin-bottom: 10px; }
.sk-line   { background: rgba(255,255,255,.07); }
.sk-tag {
  display: inline-block;
  height: 20px;
  border-radius: 10px;
  margin-right: 6px;
  margin-bottom: 6px;
  background: rgba(0,255,224,.12);
  border: 1px solid rgba(0,255,224,.2);
}

/* glow ring behind card */
.lp-glow-ring {
  position: absolute;
  width: 500px; height: 500px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(123,47,255,.3) 0%, transparent 70%);
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
  animation: lp-ringPulse 4s ease-in-out infinite;
}
@keyframes lp-ringPulse {
  0%,100%{ transform:translate(-50%,-50%) scale(1); opacity:.6; }
  50%    { transform:translate(-50%,-50%) scale(1.15); opacity:.3; }
}

/* orbiting dot */
.lp-orbit {
  position: absolute;
  width: 380px; height: 380px;
  border: 1px dashed rgba(123,47,255,.2);
  border-radius: 50%;
  top: 50%; left: 50%;
  transform: translate(-50%,-50%);
  animation: lp-spin 20s linear infinite;
  pointer-events: none;
}
.lp-orbit::after {
  content:'';
  position: absolute;
  width: 10px; height: 10px;
  background: var(--lp-cyan);
  border-radius: 50%;
  top: -5px; left: calc(50% - 5px);
  box-shadow: 0 0 12px var(--lp-cyan), 0 0 30px var(--lp-cyan);
}
@keyframes lp-spin { to{ transform: translate(-50%,-50%) rotate(360deg); } }

/* ─── FEATURES ────────────────────────────────── */
.lp-features {
  padding: 120px 0;
  position: relative;
}
.lp-features-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 40px;
}
.lp-section-eyebrow {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--lp-cyan);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.lp-section-eyebrow::before {
  content:'';
  width: 32px; height: 1px;
  background: var(--lp-cyan);
  flex-shrink: 0;
}
.lp-section-title {
  font-family: 'Syne', sans-serif;
  font-size: clamp(2rem, 4vw, 3.2rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -.02em;
  margin-bottom: 16px;
}
.lp-section-sub {
  font-size: 1.05rem;
  color: var(--lp-muted);
  max-width: 500px;
  line-height: 1.7;
  margin-bottom: 72px;
}

/* feature grid */
.lp-feat-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2px;
}
@media(max-width: 900px) { .lp-feat-grid { grid-template-columns: 1fr 1fr; } }
@media(max-width: 580px) { .lp-feat-grid { grid-template-columns: 1fr; } }

.lp-feat-card {
  background: var(--lp-surface);
  padding: 40px 36px;
  position: relative;
  overflow: hidden;
  cursor: default;
  transition: background .3s, transform .4s var(--ease-out-expo);
  --cx: 50%;
  --cy: 50%;
}
.lp-feat-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at var(--cx) var(--cy), rgba(123,47,255,.12), transparent 60%);
  opacity: 0;
  transition: opacity .4s;
}
.lp-feat-card:hover::before { opacity: 1; }
.lp-feat-card:hover { background: rgba(12,11,24,.9); }
/* top accent line that appears on hover */
.lp-feat-card::after {
  content:'';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--lp-violet), var(--lp-cyan));
  transform: scaleX(0);
  transform-origin: left;
  transition: transform .5s var(--ease-out-expo);
}
.lp-feat-card:hover::after { transform: scaleX(1); }

/* reveal on scroll */
.lp-feat-card {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity .6s var(--ease-out-expo), transform .6s var(--ease-out-expo), background .3s;
}
.lp-feat-card.lp-visible {
  opacity: 1;
  transform: translateY(0);
}

.lp-feat-icon {
  width: 52px; height: 52px;
  border-radius: 16px;
  background: rgba(123,47,255,.12);
  border: 1px solid rgba(123,47,255,.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  margin-bottom: 24px;
  position: relative;
  transition: all .3s;
}
.lp-feat-card:hover .lp-feat-icon {
  background: rgba(123,47,255,.25);
  box-shadow: 0 0 24px rgba(123,47,255,.35);
  transform: scale(1.1) rotate(-5deg);
}
.lp-feat-icon i { color: var(--lp-cyan); }

.lp-feat-title {
  font-family: 'Syne', sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 12px;
  color: var(--lp-text);
}
.lp-feat-body {
  font-size: .9rem;
  line-height: 1.7;
  color: var(--lp-muted);
}

/* ─── PROCESS / HOW ───────────────────────────── */
.lp-process {
  padding: 100px 0;
  position: relative;
}
.lp-process-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 40px;
}
.lp-steps {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  gap: 48px;
  position: relative;
  margin-top: 72px;
}
@media(max-width:900px){ .lp-steps { grid-template-columns: 1fr; gap: 32px; } }
/* connector line */
.lp-steps::before {
  content:'';
  position: absolute;
  top: 28px;
  left: 16.6%;
  right: 16.6%;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--lp-border), var(--lp-border), transparent);
}
@media(max-width:900px){ .lp-steps::before { display:none; } }

.lp-step {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity .6s var(--ease-out-expo), transform .6s var(--ease-out-expo);
}
.lp-step.lp-visible { opacity: 1; transform: none; }

.lp-step__num {
  font-family: 'Syne', sans-serif;
  font-size: 3rem;
  font-weight: 800;
  background: linear-gradient(135deg, rgba(123,47,255,.3), rgba(0,255,224,.15));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1;
  margin-bottom: 8px;
}
.lp-step__dot {
  width: 10px; height: 10px;
  background: var(--lp-cyan);
  border-radius: 50%;
  margin-bottom: 20px;
  box-shadow: 0 0 10px var(--lp-cyan);
}
.lp-step__title {
  font-family: 'Syne', sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 10px;
}
.lp-step__body {
  font-size: .9rem;
  color: var(--lp-muted);
  line-height: 1.7;
}

/* ─── CTA BAND ────────────────────────────────── */
.lp-cta-band {
  margin: 60px 40px;
  border-radius: 28px;
  overflow: hidden;
  position: relative;
  padding: 80px 60px;
  text-align: center;
  background: var(--lp-surface);
  border: 1px solid var(--lp-border);
  opacity: 0;
  transform: translateY(30px);
  transition: opacity .8s var(--ease-out-expo), transform .8s var(--ease-out-expo);
}
.lp-cta-band.lp-visible { opacity: 1; transform: none; }
.lp-cta-band__bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 20% 50%, rgba(123,47,255,.22) 0%, transparent 55%),
    radial-gradient(ellipse at 80% 50%, rgba(0,255,224,.1) 0%, transparent 55%);
  pointer-events: none;
}
.lp-cta-band h2 {
  font-family: 'Syne', sans-serif;
  font-size: clamp(1.8rem, 4vw, 3rem);
  font-weight: 800;
  letter-spacing: -.02em;
  margin-bottom: 16px;
  position: relative;
}
.lp-cta-band p {
  color: var(--lp-muted);
  font-size: 1rem;
  max-width: 440px;
  margin: 0 auto 40px;
  line-height: 1.7;
  position: relative;
}
.lp-cta-band .lp-btn-primary { position: relative; }

/* grid lines decoration */
.lp-grid-deco {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(123,47,255,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(123,47,255,.04) 1px, transparent 1px);
  background-size: 60px 60px;
  pointer-events: none;
}

/* ─── KEYFRAMES ───────────────────────────────── */
@keyframes lp-fadeUp {
  from { opacity:0; transform: translateY(24px); }
  to   { opacity:1; transform: translateY(0); }
}
@keyframes lp-fadeIn {
  from { opacity:0; }
  to   { opacity:1; }
}

/* ─── CUSTOM CURSOR (desktop only) ───────────── */
@media(pointer: fine) {
  .lp-root { cursor: none; }
  .lp-root a, .lp-root button { cursor: none; }
}
.lp-cursor {
  position: fixed;
  pointer-events: none;
  z-index: 9999;
  mix-blend-mode: screen;
}
.lp-cursor__dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--lp-cyan);
  position: absolute;
  top: -4px; left: -4px;
  transition: transform .1s;
  box-shadow: 0 0 8px var(--lp-cyan);
}
.lp-cursor__ring {
  width: 36px; height: 36px;
  border-radius: 50%;
  border: 1.5px solid rgba(0,255,224,.5);
  position: absolute;
  top: -18px; left: -18px;
  transition: transform .18s var(--ease-out-expo), width .3s, height .3s, top .3s, left .3s;
}
.lp-cursor.lp-hover .lp-cursor__ring {
  width: 60px; height: 60px;
  top: -30px; left: -30px;
  background: rgba(0,255,224,.05);
}
</style>

<!-- ── MARKUP ───────────────────────────────────── -->
<div class="lp-root" id="lp-root">

  <!-- Background layers -->
  <canvas id="lp-canvas"></canvas>
  <div class="lp-noise"></div>
  <div class="lp-aurora">
    <div class="lp-aurora__blob"></div>
    <div class="lp-aurora__blob"></div>
    <div class="lp-aurora__blob"></div>
  </div>

  <!-- Custom cursor -->
  <div class="lp-cursor" id="lp-cursor">
    <div class="lp-cursor__ring"></div>
    <div class="lp-cursor__dot"></div>
  </div>

  <div class="lp-wrap">

    <!-- ── HERO ── -->
    <section class="lp-hero">
      <div class="lp-hero__inner">
        <!-- Left -->
        <div class="lp-hero__left">
          <div class="lp-badge">✦ Free &amp; Professional CV Builder</div>

          <h1 class="lp-headline">
            Build a CV that<br>
            <em><span id="lp-typed"></span></em>
          </h1>

          <p class="lp-description">
            Create stunning, professional resumes in minutes. Choose from beautiful templates, 
            fill in your details, and download a polished PDF — completely free.
          </p>

          <div class="lp-cta">
            <a href="<?= BASE_URL ?>/register.php" class="lp-btn-primary" id="heroCtaRegister">
              <span>Start Building</span>
              <span class="lp-arrow"><i class="fas fa-arrow-right"></i></span>
            </a>
            <a href="#lp-features" class="lp-btn-ghost" id="heroCtaFeatures">
              <i class="fas fa-play-circle"></i>
              <span>See How It Works</span>
            </a>
          </div>

          <div class="lp-stats">
            <div class="lp-stat">
              <span class="lp-stat__num" data-count="10">0</span>
              <span class="lp-stat__label">Min to build</span>
            </div>
            <div class="lp-stat-divider"></div>
            <div class="lp-stat">
              <span class="lp-stat__num" data-count="3">0</span>
              <span class="lp-stat__label">Templates</span>
            </div>
            <div class="lp-stat-divider"></div>
            <div class="lp-stat">
              <span class="lp-stat__num" data-count="100" data-suffix="%">0</span>
              <span class="lp-stat__label">Free forever</span>
            </div>
          </div>
        </div>

        <!-- Right: animated CV mockup -->
        <div class="lp-hero__visual">
          <div class="lp-glow-ring"></div>
          <div class="lp-orbit"></div>
          <div class="lp-mockup">
            <div class="lp-mockup-bar">
              <div class="lp-dot"></div>
              <div class="lp-dot"></div>
              <div class="lp-dot"></div>
            </div>
            <div class="lp-mockup-body">
              <div class="lp-mockup-row">
                <div class="lp-mockup-left">
                  <div class="sk-avatar"></div>
                  <div class="sk sk-title"></div>
                  <div class="sk sk-sub"></div>
                  <div style="height:20px"></div>
                  <div class="sk" style="background:rgba(0,255,224,.15);width:60%"></div>
                  <div class="sk sk-line" style="width:90%"></div>
                  <div class="sk sk-line" style="width:70%"></div>
                  <div class="sk sk-line" style="width:80%"></div>
                </div>
                <div class="lp-mockup-right">
                  <div class="sk sk-head"></div>
                  <div class="sk sk-line"></div>
                  <div class="sk sk-line" style="width:88%"></div>
                  <div class="sk sk-line" style="width:65%"></div>
                  <div style="height:20px"></div>
                  <div class="sk sk-head" style="width:35%"></div>
                  <div class="sk sk-line"></div>
                  <div class="sk sk-line" style="width:82%"></div>
                  <div style="height:20px"></div>
                  <div class="sk sk-head" style="width:30%"></div>
                  <div style="margin-top:6px">
                    <span class="sk-tag" style="width:52px"></span>
                    <span class="sk-tag" style="width:44px"></span>
                    <span class="sk-tag" style="width:64px"></span>
                    <span class="sk-tag" style="width:48px"></span>
                    <span class="sk-tag" style="width:56px"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll hint -->
      <div class="lp-scroll-hint">
        <div class="lp-scroll-mouse"><div class="lp-scroll-dot"></div></div>
        <span>Scroll</span>
      </div>
    </section>

    <!-- ── FEATURES ── -->
    <section class="lp-features" id="lp-features">
      <div class="lp-features-inner">
        <div class="lp-section-eyebrow">What's inside</div>
        <h2 class="lp-section-title">Everything you need to<br>land your dream job</h2>
        <p class="lp-section-sub">Powerful features. Beautiful design. Zero cost.</p>

        <div class="lp-feat-grid">
          <?php
          $feats = [
            ['fas fa-palette',   'Beautiful Templates',  'Choose from 3 professionally designed templates — Classic, Modern, and Creative — each with dark mode variants.'],
            ['fas fa-bolt',      'Live Preview',         'See your CV update in real-time as you type. No guesswork — what you see is exactly what you get.'],
            ['fas fa-file-pdf',  'PDF Download',         'Download your finished CV as a crisp, print-ready PDF with one click. Professional quality guaranteed.'],
            ['fas fa-edit',      'Easy Editing',         'Our step-by-step wizard makes it simple. Add education, experience, skills and more with dynamic forms.'],
            ['fas fa-moon',      'Dark Mode',            'Easy on the eyes. Toggle dark mode for comfortable editing, day or night. Your preference is remembered.'],
            ['fas fa-shield-alt','Secure & Private',     'Your data is protected with industry-standard encryption. We never share your information — ever.'],
          ];
          foreach ($feats as $f): ?>
          <div class="lp-feat-card" data-scroll>
            <div class="lp-feat-icon"><i class="<?= $f[0] ?>"></i></div>
            <div class="lp-feat-title"><?= $f[1] ?></div>
            <p class="lp-feat-body"><?= $f[2] ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ── HOW IT WORKS ── -->
    <section class="lp-process">
      <div class="lp-process-inner">
        <div class="lp-section-eyebrow">The process</div>
        <h2 class="lp-section-title">Three steps to your<br>perfect resume</h2>

        <div class="lp-steps">
          <?php
          $steps = [
            ['01', 'Sign up free',      'Create your account in seconds — no credit card, no strings attached. Just your email and you\'re in.'],
            ['02', 'Fill your details', 'Walk through our intuitive wizard. Add experience, education, skills, and let the design happen automatically.'],
            ['03', 'Download & apply',  'Pick your favourite template, hit download, and get your polished PDF. Ready to send to employers.'],
          ];
          foreach ($steps as $s): ?>
          <div class="lp-step" data-scroll data-delay="<?= array_search($s, $steps) * 120 ?>">
            <div class="lp-step__num"><?= $s[0] ?></div>
            <div class="lp-step__dot"></div>
            <div class="lp-step__title"><?= $s[1] ?></div>
            <p class="lp-step__body"><?= $s[2] ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ── CTA BAND ── -->
    <div class="lp-cta-band" data-scroll>
      <div class="lp-grid-deco"></div>
      <div class="lp-cta-band__bg"></div>
      <h2>Ready to build your CV?</h2>
      <p>Join thousands of professionals. Create your perfect resume in under 10 minutes.</p>
      <a href="<?= BASE_URL ?>/register.php" class="lp-btn-primary" id="ctaBottomRegister">
        <span>Create My CV — It's Free</span>
        <span class="lp-arrow"><i class="fas fa-arrow-right"></i></span>
      </a>
    </div>

  </div><!-- /.lp-wrap -->
</div><!-- /#lp-root -->

<!-- ── SCRIPTS ─────────────────────────────────── -->
<script>
(function () {
  /* ── CUSTOM CURSOR ─── */
  const cursor = document.getElementById('lp-cursor');
  if (cursor && window.matchMedia('(pointer: fine)').matches) {
    let mx = -200, my = -200, rx = -200, ry = -200;
    document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });
    const moveCursor = () => {
      rx += (mx - rx) * .14;
      ry += (my - ry) * .14;
      cursor.style.transform = `translate(${mx}px,${my}px)`;
      cursor.querySelector('.lp-cursor__ring').style.transform = `translate(${rx - mx}px,${ry - my}px)`;
      requestAnimationFrame(moveCursor);
    };
    moveCursor();
    document.querySelectorAll('#lp-root a, #lp-root button').forEach(el => {
      el.addEventListener('mouseenter', () => cursor.classList.add('lp-hover'));
      el.addEventListener('mouseleave', () => cursor.classList.remove('lp-hover'));
    });
  }

  /* ── PARTICLE CANVAS ── */
  const canvas = document.getElementById('lp-canvas');
  const ctx = canvas.getContext('2d');
  let W, H, particles = [], mouse = { x: -999, y: -999 };

  const resize = () => {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
  };
  resize();
  window.addEventListener('resize', resize);
  window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });

  const COLORS = ['rgba(0,255,224,', 'rgba(123,47,255,', 'rgba(255,45,122,'];

  class Particle {
    constructor() { this.reset(); }
    reset() {
      this.x = Math.random() * W;
      this.y = Math.random() * H;
      this.vx = (Math.random() - .5) * .4;
      this.vy = (Math.random() - .5) * .4;
      this.r  = Math.random() * 1.5 + .3;
      this.a  = Math.random() * .5 + .1;
      this.color = COLORS[Math.floor(Math.random() * COLORS.length)];
    }
    update() {
      // drift toward mouse slightly
      const dx = mouse.x - this.x, dy = mouse.y - this.y;
      const dist = Math.sqrt(dx*dx + dy*dy);
      if (dist < 200) {
        this.vx += dx / dist * .012;
        this.vy += dy / dist * .012;
      }
      this.vx *= .98; this.vy *= .98;
      this.x += this.vx; this.y += this.vy;
      if (this.x < 0 || this.x > W || this.y < 0 || this.y > H) this.reset();
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.fillStyle = this.color + this.a + ')';
      ctx.fill();
    }
  }

  const N = Math.min(Math.floor(W * H / 12000), 100);
  for (let i = 0; i < N; i++) particles.push(new Particle());

  // draw connection lines
  const drawConnections = () => {
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const d = Math.sqrt(dx*dx + dy*dy);
        if (d < 120) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = `rgba(123,47,255,${(1 - d/120) * .12})`;
          ctx.lineWidth = .5;
          ctx.stroke();
        }
      }
    }
  };

  const loop = () => {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => { p.update(); p.draw(); });
    drawConnections();
    requestAnimationFrame(loop);
  };
  loop();

  /* ── TYPED HEADLINE ── */
  const words = ['stands out', 'gets noticed', 'opens doors', 'lands jobs'];
  let wi = 0, ci = 0, deleting = false, pause = false;
  const el = document.getElementById('lp-typed');
  if (el) {
    setInterval(() => {
      if (pause) return;
      const word = words[wi];
      if (!deleting) {
        el.textContent = word.slice(0, ++ci);
        if (ci === word.length) { pause = true; setTimeout(() => { pause = false; deleting = true; }, 2200); }
      } else {
        el.textContent = word.slice(0, --ci);
        if (ci === 0) { deleting = false; wi = (wi + 1) % words.length; }
      }
    }, 80);
  }

  /* ── SCROLL REVEAL ── */
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const delay = entry.target.dataset.delay || 0;
        setTimeout(() => entry.target.classList.add('lp-visible'), +delay);
        io.unobserve(entry.target);
      }
    });
  }, { threshold: .15 });
  document.querySelectorAll('[data-scroll]').forEach(el => io.observe(el));

  /* ── COUNTER ANIMATION ── */
  const countEls = document.querySelectorAll('[data-count]');
  const countIO = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const target = +el.dataset.count;
      const suffix = el.dataset.suffix || '';
      let start = 0;
      const duration = 1400;
      const startTime = performance.now();
      const tick = (now) => {
        const t = Math.min((now - startTime) / duration, 1);
        const ease = 1 - Math.pow(1 - t, 4);
        const val = Math.round(ease * target);
        el.textContent = val + suffix;
        if (t < 1) requestAnimationFrame(tick);
        else el.textContent = target + suffix;
      };
      requestAnimationFrame(tick);
      countIO.unobserve(el);
    });
  }, { threshold: .5 });
  countEls.forEach(el => countIO.observe(el));

  /* ── MAGNETIC CURSOR ON CARDS ── */
  document.querySelectorAll('.lp-feat-card').forEach(card => {
    card.addEventListener('mousemove', e => {
      const r = card.getBoundingClientRect();
      const x = ((e.clientX - r.left) / r.width  * 100).toFixed(1) + '%';
      const y = ((e.clientY - r.top)  / r.height * 100).toFixed(1) + '%';
      card.style.setProperty('--cx', x);
      card.style.setProperty('--cy', y);
    });
  });

  /* ── SMOOTH ANCHOR ── */
  document.querySelectorAll('a[href="#lp-features"]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      document.getElementById('lp-features')?.scrollIntoView({ behavior: 'smooth' });
    });
  });

})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
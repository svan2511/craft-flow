<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Craft Flow — Workshop Manager | अपनी वर्कशॉप आपकी हथेली पर</title>
<meta name="description" content="Craft Flow — हिंदी भाषा में वर्कशॉप, जॉब कार्ड, कारीगर लेजर, उधार खाता और रिपोर्ट मैनेज करने वाला एक ही ऐप। ऑर्डर बनाएँ, पेमेंट वसूलें, एडवांस और सेटलमेंट ट्रैक करें — सब एक जगह।">
<meta name="theme-color" content="#F8F6F3">
<link rel="icon" href="/img/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">

<style>
:root {
  --bg: #F8F6F3;
  --surface: #FFFFFF;
  --surface-soft: #F2EFEA;
  --surface-hi: #EBE7E0;
  --ink: #1C1B1A;
  --muted: #5F5B54;
  --faint: #8A857C;
  --line: #CFC9BF;
  --line-soft: #E4DFD7;
  --primary: #8A6D3B;
  --primary-deep: #6E552A;
  --primary-soft: #EFE4C8;
  --secondary: #7A6A4F;
  --success: #3E6B4F;
  --success-soft: #E3EEE6;
  --warning: #B7791F;
  --warning-soft: #F8F0DC;
  --error: #B3463E;
  --whatsapp: #25D366;
  --radius: 14px;
  --radius-sm: 8px;
  --shadow: 0 1px 2px rgba(28,27,26,.04), 0 8px 24px -8px rgba(28,27,26,.10);
  --shadow-lg: 0 2px 4px rgba(28,27,26,.05), 0 20px 48px -16px rgba(28,27,26,.18);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
  font-family: 'Inter', 'Noto Sans Devanagari', sans-serif;
  background: var(--bg);
  color: var(--ink);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}

h1, h2, h3, h4 { font-family: 'Poppins', 'Noto Sans Devanagari', sans-serif; line-height: 1.25; }
.en-font { font-family: 'Inter', sans-serif; }

.container { max-width: 1120px; margin: 0 auto; padding: 0 20px; }

/* ---------- Language toggle ---------- */
.lang-toggle {
  position: fixed; top: 84px; right: 16px; z-index: 60;
  display: inline-flex; align-items: center; gap: 4px;
  background: var(--surface); border: 1.5px solid var(--line);
  border-radius: 999px; padding: 4px; box-shadow: var(--shadow);
}
.lang-btn {
  border: 0; background: transparent; cursor: pointer;
  font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600;
  padding: 6px 10px; border-radius: 999px; color: var(--muted);
}
.lang-btn.active { background: var(--ink); color: #fff; }

/* ---------- Nav ---------- */
.nav {
  position: sticky; top: 0; z-index: 50;
  background: rgba(248,246,243,.9);
  backdrop-filter: saturate(180%) blur(14px);
  border-bottom: 1px solid var(--line-soft);
}
.nav-inner {
  display: flex; align-items: center; justify-content: space-between;
  height: 72px;
}
.brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.brand-logo { width: 44px; height: 44px; border-radius: 10px; overflow: hidden; display: grid; place-items: center; }
.brand-logo img { width: 100%; height: 100%; object-fit: contain; }
.brand-name { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 19px; letter-spacing: -.3px; color: var(--ink); }
.brand-name em { font-style: normal; color: var(--primary); }
.nav-links { display: flex; align-items: center; gap: 28px; }
.nav-links a { text-decoration: none; color: var(--muted); font-size: 14.5px; font-weight: 500; transition: color .15s; }
.nav-links a:hover { color: var(--ink); }
.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  font-family: 'Poppins', 'Noto Sans Devanagari', sans-serif; font-weight: 600;
  border-radius: 12px; cursor: pointer; text-decoration: none; white-space: nowrap;
  transition: transform .12s, box-shadow .15s, background .15s;
  border: 2px solid transparent;
}
.btn:active { transform: translateY(1px); }
.btn-primary { background: var(--primary); color: #fff; box-shadow: 0 8px 20px -6px rgba(138,109,59,.5); }
.btn-primary:hover { background: var(--primary-deep); }
.btn-outline { background: var(--surface); color: var(--ink); border-color: var(--line); }
.btn-outline:hover { border-color: var(--ink); }
.btn-whatsapp { background: var(--whatsapp); color: #fff; }
.btn-dark { background: var(--ink); color: #fff; }
.btn-dark:hover { background: #000; }
.btn-md { padding: 13px 20px; font-size: 14.5px; }
.btn-lg { padding: 16px 28px; font-size: 16px; }
.btn-sm { padding: 9px 14px; font-size: 13px; }
.material-symbols-outlined { font-size: 20px; vertical-align: -4px; user-select: none; }

.burger { display: none; background: none; border: 0; cursor: pointer; color: var(--ink); }
.burger .material-symbols-outlined { font-size: 30px; }

/* ---------- Hero ---------- */
.hero { position: relative; padding: 72px 0 40px; overflow: hidden; }
.hero::before {
  content: ""; position: absolute; inset: 0; pointer-events: none;
  background:
    radial-gradient(900px 500px at 85% -10%, rgba(138,109,59,.16), transparent 60%),
    radial-gradient(700px 420px at -5% 20%, rgba(62,107,79,.10), transparent 55%);
}
.hero-grid { position: relative; display: grid; grid-template-columns: 1.08fr .92fr; gap: 56px; align-items: center; }

.hero-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--primary-soft); color: var(--primary-deep);
  border: 1.5px solid var(--primary); font-weight: 600; font-size: 13px;
  padding: 8px 14px; border-radius: 999px; margin-bottom: 22px;
}
.hero-badge .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); }
.hero h1 { font-size: clamp(34px, 5vw, 56px); font-weight: 800; letter-spacing: -1.6px; margin-bottom: 18px; }
.hero h1 .accent { color: var(--primary); }
.hero .sub { font-size: 17.5px; color: var(--muted); max-width: 560px; margin-bottom: 8px; }
.hero .sub-2 { font-size: 15.5px; color: var(--faint); max-width: 540px; margin-top: 16px; }

.hero-ctas { display: flex; flex-wrap: wrap; gap: 14px; margin: 30px 0 22px; }
.download-hint { font-size: 13px; color: var(--faint); }
.download-hint .tag { display: inline-flex; align-items: center; gap: 5px; background: var(--surface); border: 1px solid var(--line); padding: 4px 10px; border-radius: 999px; margin-right: 8px; }
.download-hint .tag b { color: var(--success); }
.download-hint .android { font-weight: 600; color: var(--muted); letter-spacing: .3px; }

/* Hero phone mockup */
.phone-wrap { display: flex; justify-content: center; }
.phone {
  width: 320px; border-radius: 42px; border: 12px solid var(--ink);
  background: var(--surface); box-shadow: var(--shadow-lg); overflow: hidden;
  position: relative;
}
.phone-notch { position: absolute; top: 8px; left: 50%; transform: translateX(-50%); width: 110px; height: 24px; background: var(--ink); border-radius: 999px; z-index: 5; }
.phone-screen { padding: 34px 14px 16px; background: var(--bg); }
.phone-status { display: flex; justify-content: space-between; align-items: center; font-size: 10px; font-weight: 600; color: var(--muted); font-family: 'Inter', sans-serif; padding: 0 4px 10px; }
.phone-status .right { display: flex; gap: 6px; }
.phone-card { background: var(--surface); border: 1.5px solid var(--line-soft); border-radius: var(--radius-sm); padding: 12px; }
.phone-title { font-family: 'Poppins', sans-serif; font-size: 8.5px; font-weight: 600; color: var(--muted); margin-bottom: 8px; }
.phone-totals { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.phone-total { background: var(--surface-soft); border-radius: 6px; padding: 8px 10px; }
.phone-total .lbl { font-size: 7.5px; color: var(--muted); }
.phone-total .val { font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 700; margin-top: 2px; }
.phone-total .val.green { color: var(--success); }
.phone-total .val.bronze { color: var(--primary); }
.phone-card-green { background: linear-gradient(135deg, var(--success), #2C5E41); color: #fff; border: 0; }
.phone-card-green .phone-title { color: rgba(255,255,255,.75); }
.phone-card-green .phone-total { background: rgba(255,255,255,.14); }
.phone-card-green .lbl { color: rgba(255,255,255,.8); }
.phone-card-green .val { color: #fff; }
.phone-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; margin-top: 6px; }
.phone-action { background: var(--surface); border: 1.5px solid var(--line-soft); border-radius: 6px; padding: 8px 4px; text-align: center; font-size: 7.5px; font-weight: 600; color: var(--ink); }
.phone-action .icon { font-size: 16px; color: var(--primary); display: block; margin-bottom: 3px; }
.phone-list { display: grid; gap: 6px; margin-top: 6px; }
.phone-row { display: flex; align-items: center; justify-content: space-between; background: var(--surface); border: 1.5px solid var(--line-soft); border-radius: 6px; padding: 8px 10px; }
.phone-row .name { font-size: 8px; font-weight: 600; }
.phone-row .meta { font-size: 7px; color: var(--muted); }
.pill { font-size: 7px; font-weight: 700; padding: 3px 8px; border-radius: 999px; white-space: nowrap; }
.pill.amber { background: var(--warning-soft); color: var(--warning); border: 1px solid var(--warning); }
.pill.green { background: var(--success-soft); color: var(--success); border: 1px solid var(--success); }
.pill.red { background: #F9E4E0; color: var(--error); border: 1px solid var(--error); }
.phone-bottom-nav { display: flex; justify-content: space-around; padding: 10px 4px 2px; border-top: 1px solid var(--line-soft); margin-top: 10px; }
.phone-nav-item { text-align: center; font-size: 7px; color: var(--faint); }
.phone-nav-item .icon { font-size: 15px; display: block; margin-bottom: 2px; }
.phone-nav-item.active .icon { color: var(--primary); }
.phone-nav-item.active { color: var(--primary); font-weight: 600; }

/* Phone app slider */
.phone-slider { position: relative; overflow: hidden; border-radius: 10px; }
.phone-slides { display: flex; transition: transform .5s cubic-bezier(.4,0,.2,1); }
.phone-slide { min-width: 100%; padding: 2px; }
.phone-dots { display: flex; justify-content: center; gap: 5px; padding: 8px 0 2px; }
.phone-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--line); border: 0; padding: 0; cursor: pointer; transition: all .25s; }
.phone-dot.active { width: 16px; background: var(--primary); }
.phone-search { display: flex; align-items: center; gap: 6px; background: var(--surface-soft); border: 1.5px solid var(--line-soft); border-radius: 999px; padding: 7px 10px; font-size: 8px; color: var(--muted); }
.phone-search .material-symbols-outlined { font-size: 13px; }
.phone-filters { display: flex; gap: 4px; margin-top: 8px; }
.phone-filter { font-size: 7.5px; font-weight: 600; padding: 4px 8px; border-radius: 999px; border: 1px solid var(--line-soft); color: var(--muted); }
.phone-filter.active { background: var(--ink); color: #fff; border-color: var(--ink); }
.phone-orders { display: grid; gap: 6px; margin-top: 8px; }
.phone-order { background: var(--surface); border: 1.5px solid var(--line-soft); border-radius: 6px; padding: 8px 10px; }
.phone-order .top { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.phone-order .fin { display: flex; justify-content: space-between; margin-top: 6px; padding-top: 6px; border-top: 1px solid var(--line-soft); font-size: 7.5px; font-weight: 600; }
.phone-order .fin .g { color: var(--success); }
.phone-order .fin .r { color: var(--error); }
.phone-order .fin .w { color: var(--warning); }
.phone-kh { display: grid; gap: 6px; margin-top: 8px; }
.phone-kh-row { display: flex; align-items: center; justify-content: space-between; background: var(--surface); border: 1.5px solid var(--line-soft); border-radius: 6px; padding: 8px 10px; }
.phone-kh-row .name { font-size: 8px; font-weight: 600; }
.phone-kh-row .meta { font-size: 7px; color: var(--muted); }
.phone-kh-row .amt.pos { color: var(--success); font-size: 8.5px; font-weight: 700; }
.phone-kh-row .amt.neg { color: var(--error); font-size: 8.5px; font-weight: 700; }
.phone-bars { display: grid; gap: 5px; margin-top: 8px; }
.phone-bar { display: grid; grid-template-columns: 56px 1fr; gap: 6px; align-items: center; font-size: 7.5px; font-weight: 600; color: var(--muted); }
.phone-bar .track { height: 6px; border-radius: 999px; background: var(--surface-soft); overflow: hidden; }
.phone-bar .fill { height: 100%; border-radius: 999px; }

.float-chip {
  position: absolute; display: flex; align-items: center; gap: 10px;
  background: var(--surface); border: 1.5px solid var(--line-soft);
  border-radius: 12px; padding: 10px 14px; box-shadow: var(--shadow);
  font-size: 12px; font-weight: 600; animation: floaty 5s ease-in-out infinite;
}
.float-chip .icon { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; }
.float-chip .icon.green { background: var(--success-soft); color: var(--success); }
.float-chip .icon.amber { background: var(--warning-soft); color: var(--warning); }
.float-chip .icon .material-symbols-outlined { font-size: 18px; }
.float-chip small { display: block; font-weight: 400; font-size: 10.5px; color: var(--faint); }
.chip-1 { top: 18%; left: -34px; animation-delay: .3s; }
.chip-2 { bottom: 16%; left: -22px; animation-delay: 1.2s; }
@keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

/* ---------- Stats ---------- */
.stats { border-top: 1px solid var(--line-soft); border-bottom: 1px solid var(--line-soft); background: var(--surface); }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); }
.stat { padding: 30px 20px; text-align: center; border-right: 1px solid var(--line-soft); }
.stat:last-child { border-right: 0; }
.stat .num { font-family: 'Poppins', sans-serif; font-size: 32px; font-weight: 800; color: var(--ink); letter-spacing: -1px; }
.stat .num em { font-style: normal; color: var(--primary); }
.stat .lbl { font-size: 13.5px; color: var(--muted); margin-top: 4px; }

/* ---------- Section base ---------- */
.section { padding: 88px 0; }
.section-head { max-width: 640px; margin: 0 auto 54px; text-align: center; }
.eyebrow { display: inline-block; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 12.5px; letter-spacing: 2.5px; text-transform: uppercase; color: var(--primary); margin-bottom: 14px; }
.section-head h2 { font-size: clamp(27px, 4vw, 38px); font-weight: 700; letter-spacing: -1px; margin-bottom: 14px; }
.section-head p { color: var(--muted); font-size: 16.5px; }

/* ---------- Features ---------- */
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.feature {
  background: var(--surface); border: 1.5px solid var(--line-soft);
  border-radius: var(--radius); padding: 26px; transition: transform .18s, box-shadow .18s, border-color .18s;
}
.feature:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: var(--line); }
.feature .icon {
  width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; margin-bottom: 18px;
  background: var(--primary-soft); color: var(--primary); border: 2px solid var(--primary);
}
.feature .icon.green { background: var(--success-soft); color: var(--success); border-color: var(--success); }
.feature .icon.amber { background: var(--warning-soft); color: var(--warning); border-color: var(--warning); }
.feature .icon.red { background: #F9E4E0; color: var(--error); border-color: var(--error); }
.feature h3 { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
.feature p { font-size: 14.5px; color: var(--muted); }

/* ---------- Showcase (workflow strip) ---------- */
.workflow { background: var(--ink); color: #EBE4D8; }
.workflow .eyebrow { color: #D8C49A; }
.workflow .section-head h2 { color: #fff; }
.workflow .section-head p { color: #A8A096; }
.workflow-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.workflow-step { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.12); border-radius: var(--radius); padding: 26px 22px; }
.step-num { font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 700; color: #D8C49A; display: flex; align-items: center; justify-content: space-between; }
.step-num .material-symbols-outlined { font-size: 24px; color: #D8C49A; }
.workflow-step h3 { font-size: 16.5px; font-weight: 600; margin: 12px 0 8px; color: #fff; }
.workflow-step p { font-size: 13.5px; color: #A8A096; }

/* ---------- Versions / Download ---------- */
.download-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; max-width: 900px; margin: 0 auto; }
.dl-card {
  background: var(--surface); border: 2px solid var(--line); border-radius: var(--radius);
  padding: 30px; display: flex; flex-direction: column; position: relative; transition: transform .18s, box-shadow .18s;
}
.dl-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.dl-card .ribbon { position: absolute; top: 18px; right: -10px; background: var(--success); color: #fff; font-size: 11.5px; font-weight: 700; padding: 6px 14px; border-radius: 999px 0 0 999px; box-shadow: var(--shadow); }
.dl-card .ribbon.new { background: var(--primary); }
.dl-icon { width: 56px; height: 56px; border-radius: 14px; display: grid; place-items: center; margin-bottom: 18px; background: var(--ink); color: #fff; }
.dl-icon .material-symbols-outlined { font-size: 28px; }
.dl-card h3 { font-size: 24px; font-weight: 700; letter-spacing: -.5px; }
.dl-card .ver { font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 700; color: var(--primary); margin: 4px 0 12px; text-transform: uppercase; letter-spacing: 1px; }
.dl-card .desc { font-size: 14.5px; color: var(--muted); flex: 1; margin-bottom: 20px; }
.dl-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
.dl-meta .m { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; background: var(--surface-soft); border: 1px solid var(--line-soft); padding: 5px 10px; border-radius: 999px; color: var(--muted); }
.dl-meta .m .material-symbols-outlined { font-size: 15px; }
.dl-btn { width: 100%; }

.install-note {
  text-align: center; margin-top: 26px; font-size: 13.5px; color: var(--faint);
  display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;
}
.install-note .tag { display: inline-flex; align-items: center; gap: 6px; background: var(--surface); border: 1px solid var(--line); padding: 6px 12px; border-radius: 999px; font-weight: 500; }
.install-note .tag .material-symbols-outlined { font-size: 16px; color: var(--success); }

/* ---------- Install steps ---------- */
.install-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 900px; margin: 0 auto; }
.install-step { text-align: center; padding: 28px 20px; }
.install-step .ico { width: 60px; height: 60px; margin: 0 auto 16px; border-radius: 16px; display: grid; place-items: center; background: var(--primary-soft); color: var(--primary); border: 2px solid var(--primary); }
.install-step .ico .material-symbols-outlined { font-size: 30px; }
.install-step h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
.install-step p { font-size: 14px; color: var(--muted); }

/* ---------- FAQ ---------- */
.faq-list { max-width: 760px; margin: 0 auto; display: grid; gap: 12px; }
.faq-item { background: var(--surface); border: 1.5px solid var(--line-soft); border-radius: var(--radius-sm); overflow: hidden; }
.faq-q { width: 100%; text-align: left; background: none; border: 0; cursor: pointer; padding: 18px 20px; font-family: 'Poppins', 'Noto Sans Devanagari', sans-serif; font-size: 15.5px; font-weight: 600; color: var(--ink); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.faq-q .material-symbols-outlined { transition: transform .2s; color: var(--primary); }
.faq-item.open .faq-q .material-symbols-outlined { transform: rotate(180deg); }
.faq-a { max-height: 0; overflow: hidden; transition: max-height .25s ease; }
.faq-a p { padding: 0 20px 18px; font-size: 14.5px; color: var(--muted); }

/* ---------- CTA band ---------- */
.cta-band { background: linear-gradient(135deg, var(--primary), var(--primary-deep)); text-align: center; padding: 64px 0; color: #fff; }
.cta-band h2 { font-size: clamp(26px, 4vw, 36px); font-weight: 700; margin-bottom: 12px; letter-spacing: -1px; }
.cta-band p { font-size: 16px; color: rgba(255,255,255,.85); margin-bottom: 26px; }
.cta-band .btn-dark { background: #fff; color: var(--primary-deep); }
.cta-band .btn-dark:hover { background: #F6F1EA; }

/* ---------- Footer ---------- */
.footer { background: var(--ink); color: #A8A096; padding: 56px 0 32px; }
.footer-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 40px; margin-bottom: 40px; }
.footer h4 { color: #fff; font-size: 15px; font-weight: 600; margin-bottom: 16px; }
.footer a { color: #A8A096; text-decoration: none; display: inline-block; margin-bottom: 9px; font-size: 14px; transition: color .15s; }
.footer a:hover { color: #fff; }
.footer .brand { margin-bottom: 16px; }
.footer .brand-name { color: #fff; }
.footer .brand-name em { color: #D8C49A; }
.footer .about-text { font-size: 14px; max-width: 300px; }
.footer .bar { border-top: 1px solid rgba(255,255,255,.1); padding-top: 24px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; font-size: 13px; }
.footer .bar .heart { color: var(--error); }

/* ---------- Responsive ---------- */
@media (max-width: 960px) {
  .hero-grid { grid-template-columns: 1fr; gap: 48px; }
  .phone-wrap { order: -1; }
  .features-grid { grid-template-columns: 1fr 1fr; }
  .workflow-steps { grid-template-columns: 1fr 1fr; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .stat { border-bottom: 1px solid var(--line-soft); }
  .stat:nth-child(odd) { border-right: 1px solid var(--line-soft); }
  .stat:nth-child(even) { border-right: 0; }
  .stat:nth-last-child(-n+2) { border-bottom: 0; }
  .footer-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
  .nav-links { display: none; }
  .burger { display: block; }
  .nav-links.open {
    display: flex; flex-direction: column; align-items: stretch; gap: 4px;
    position: absolute; top: 72px; left: 0; right: 0;
    background: var(--surface); border-bottom: 1px solid var(--line-soft);
    padding: 12px 20px 18px; box-shadow: var(--shadow);
  }
  .nav-links.open a { padding: 10px 4px; font-size: 16px; }
  .features-grid, .workflow-steps, .install-steps, .download-cards { grid-template-columns: 1fr; }
  .section { padding: 64px 0; }
  .hero { padding: 40px 0 32px; }
  .hero h1 { font-size: 34px; }
  .chip-1 { left: -6px; }
  .chip-2 { left: 4px; }
  .lang-toggle { top: 82px; right: 12px; }
}
</style>
</head>
<body>

<!-- Language Toggle -->
<div class="lang-toggle" role="group" aria-label="Language">
  <button class="lang-btn active" data-lang="en">English</button>
  <button class="lang-btn" data-lang="hi">हिन्दी</button>
</div>

<!-- NAV -->
<nav class="nav">
  <div class="container nav-inner">
    <a href="#" class="brand">
      <span class="brand-logo"><img src="/img/logo.png" alt="Craft Flow"></span>
      <span class="brand-name">Craft <em>Flow</em></span>
    </a>
    <div class="nav-links" id="navLinks">
      <a href="#features" data-i18n="navFeatures">विशेषताएँ</a>
      <a href="#workflow" data-i18n="navHow">कैसे काम करता है</a>
      <a href="#download" data-i18n="navDownload">डाउनलोड</a>
      <a href="#faq" data-i18n="navFaq">सवाल</a>
      <a class="btn btn-primary btn-md" href="#download" data-i18n="navGetApp">ऐप पाएँ</a>
    </div>
    <button class="burger" id="burger" aria-label="Menu"><span class="material-symbols-outlined">menu</span></button>
  </div>
</nav>

<!-- HERO -->
<header class="hero">
  <div class="container hero-grid">
    <div>
      <span class="hero-badge"><span class="dot"></span><span data-i18n="heroBadge">अब हिन्दी में पूरा वर्कशॉप प्रबंधन</span></span>
      <h1><span id="heroTitleA">अपनी वर्कशॉप का </span><br><span class="accent" id="heroTitleB">डिजिटल खाता-बही,</span> <span id="heroTitleC">एक क्लिक में</span></h1>
      <p class="sub" data-i18n="heroSub">ऑर्डर बनाएँ, कारीगर को काम दें, एडवांस और सेटलमेंट ट्रैक करें, और ग्राहकों का उधार खाता संभालें — सब कुछ आपके मोबाइल पर, बिना कोई पेपर खाता-बही के।</p>
      <div class="hero-ctas">
        <a href="https://github.com/svan2511/craft-flow-app/releases/download/v2.0.0/app.apk" class="btn btn-primary btn-lg" id="dlLatest">
          <span class="material-symbols-outlined">download</span><span data-i18n="ctaLatest">नवीनतम संस्करण डाउनलोड करें</span>
        </a>
        <a href="https://github.com/svan2511/craft-flow-app/releases/download/v1.0.0/app.apk" class="btn btn-outline btn-lg" id="dlPrevious">
          <span class="material-symbols-outlined">widgets</span><span data-i18n="ctaPrevious">पिछला संस्करण (v1)</span>
        </a>
      </div>
      <div class="download-hint">
        <span class="tag"><span class="material-symbols-outlined" style="font-size:16px">verified</span><b data-i18n="safeHint">100% safe</b></span>
        <span class="tag"><span class="material-symbols-outlined" style="font-size:16px">android</span><span class="android">ANDROID APK</span></span>
      </div>
      <p class="sub-2" data-i18n="heroSub2">Craft Flow फर्नीचर, नक्काशी और फैब्रिकेशन वर्कशॉप मालिकों के लिए बना है — सहारनपुर, रुड़की, हरिद्वार और देहरादून के कारीगरों के लिए।</p>
    </div>

    <div class="phone-wrap">
      <div class="float-chip chip-1">
        <span class="icon green"><span class="material-symbols-outlined">payments</span></span>
        <span><span data-i18n="chipPaid">₹10,000 प्राप्त</span><small data-i18n="chipPaidSub">सुरेश जी से वसूली</small></span>
      </div>
      <div class="phone">
        <div class="phone-notch"></div>
        <div class="phone-screen">
          <div class="phone-status">
            <span>9:41</span>
            <span class="right"><span>●●●●</span> <span class="material-symbols-outlined" style="font-size:11px">signal_cellular_alt</span> <span class="material-symbols-outlined" style="font-size:11px">battery_full</span></span>
          </div>
          <div class="phone-slider">
            <div class="phone-slides" id="phoneSlides">
              <!-- Slide 1: Dashboard -->
              <div class="phone-slide">
                <div class="phone-card">
                  <div class="phone-title">Verma Furniture Workshop</div>
                  <div class="phone-totals">
                    <div class="phone-total"><div class="lbl" data-i18n="lblActiveOrders">सक्रिय ऑर्डर</div><div class="val bronze">8</div></div>
                    <div class="phone-total"><div class="lbl" data-i18n="lblMonthCollect">इस महीने वसूली</div><div class="val green">₹1,20,000</div></div>
                    <div class="phone-total"><div class="lbl" data-i18n="lblMarketDues">बाज़ार बकाया</div><div class="val bronze">₹45,000</div></div>
                    <div class="phone-total"><div class="lbl" data-i18n="lblAdvances">कारीगर एडवांस</div><div class="val" style="color:var(--warning)">₹8,500</div></div>
                  </div>
                </div>
                <div class="phone-card" style="margin-top:8px">
                  <div class="phone-title" data-i18n="lblTodayProfit">आज का लाभ</div>
                  <div class="phone-total" style="background:rgba(255,255,255,0);padding:2px 0">
                    <div class="val green" style="font-size:22px">₹14,500</div>
                  </div>
                </div>
                <div class="phone-actions">
                  <div class="phone-action"><span class="icon material-symbols-outlined">add_box</span><span data-i18n="actOrder">नया ऑर्डर</span></div>
                  <div class="phone-action"><span class="icon material-symbols-outlined" style="color:var(--success)">payments</span><span data-i18n="actPayment">भुगतान</span></div>
                  <div class="phone-action"><span class="icon material-symbols-outlined" style="color:var(--warning)">account_balance_wallet</span><span data-i18n="actAdvance">एडवांस</span></div>
                </div>
                <div class="phone-list">
                  <div class="phone-row">
                    <div><div class="name">सुरेश जी — बेड 6x6</div><div class="meta">#ORD-104 · कारीगर: असरफ</div></div>
                    <span class="pill amber" data-i18n="pillPolish">पॉलिश में</span>
                  </div>
                  <div class="phone-row">
                    <div><div class="name">रमेश जी — वॉर्डरोब</div><div class="meta">#ORD-98 · कारीगर: इश्तियाक</div></div>
                    <span class="pill green" data-i18n="pillReady">तैयार</span>
                  </div>
                  <div class="phone-row">
                    <div><div class="name">कार्तिक — डाइनिंग टेबल</div><div class="meta">#ORD-86 · कारीगर: मोहम्मद</div></div>
                    <span class="pill red" data-i18n="pillOverdue">अतिदेय</span>
                  </div>
                </div>
              </div>
              <!-- Slide 2: Job Cards -->
              <div class="phone-slide">
                <div class="phone-card">
                  <div class="phone-search"><span class="material-symbols-outlined">search</span><span data-i18n="lblSearchOrders">ऑर्डर ID या नाम खोजें…</span></div>
                  <div class="phone-filters">
                    <span class="phone-filter active" data-i18n="lblAll">सभी</span>
                    <span class="phone-filter" data-i18n="lblStructure">ढांचे में</span>
                    <span class="phone-filter" data-i18n="lblPolish">पॉलिश में</span>
                    <span class="phone-filter" data-i18n="lblReady">तैयार</span>
                  </div>
                </div>
                <div class="phone-orders">
                  <div class="phone-order">
                    <div class="top"><div><div class="name">#ORD-104 · सुरेश जी</div><div class="meta">बेड 6x6 · कारीगर: असरफ</div></div><span class="pill amber" data-i18n="pillPolish">पॉलिश में</span></div>
                    <div class="fin"><span data-i18n="finTotal">कुल ₹40,000</span><span class="g" data-i18n="finPaid">₹10,000 भुगतान</span><span class="r" data-i18n="finDue">₹30,000 बकाया</span></div>
                  </div>
                  <div class="phone-order">
                    <div class="top"><div><div class="name">#ORD-98 · रमेश जी</div><div class="meta">वॉर्डरोब · कारीगर: इश्तियाक</div></div><span class="pill green" data-i18n="pillReady">तैयार</span></div>
                    <div class="fin"><span data-i18n="finTotal2">कुल ₹25,000</span><span class="g" data-i18n="finPaid2">₹25,000 भुगतान</span><span class="g" data-i18n="finDue2">₹0 बकाया</span></div>
                  </div>
                  <div class="phone-order">
                    <div class="top"><div><div class="name">#ORD-86 · कार्तिक</div><div class="meta">डाइनिंग टेबल · कारीगर: मोहम्मद</div></div><span class="pill red" data-i18n="pillOverdue">अतिदेय</span></div>
                    <div class="fin"><span data-i18n="finTotal3">कुल ₹60,000</span><span class="g" data-i18n="finPaid3">₹15,000 भुगतान</span><span class="r" data-i18n="finDue3">₹45,000 बकाया</span></div>
                  </div>
                </div>
              </div>
              <!-- Slide 3: Karigar Ledger -->
              <div class="phone-slide">
                <div class="phone-card phone-card-green">
                  <div class="phone-title">असरफ कारीगर · नक्काशी मास्टर</div>
                  <div class="phone-totals">
                    <div class="phone-total"><div class="lbl" data-i18n="lblEarned">कुल कमाई</div><div class="val">₹12,500</div></div>
                    <div class="phone-total"><div class="lbl" data-i18n="lblAdvance">एडवांस</div><div class="val">₹3,000</div></div>
                    <div class="phone-total"><div class="lbl" data-i18n="lblBalance">बकाया</div><div class="val">₹9,500</div></div>
                  </div>
                </div>
                <div class="phone-kh" style="margin-top:8px">
                  <div class="phone-kh-row"><div><div class="name" data-i18n="khEarnBed">बेड 6x6 — कमाई</div><div class="meta">02 जून 2026</div></div><span class="amt pos">+₹5,000</span></div>
                  <div class="phone-kh-row"><div><div class="name" data-i18n="khAdvance">एडवांस — नकद</div><div class="meta">01 जून 2026</div></div><span class="amt neg">−₹3,000</span></div>
                  <div class="phone-kh-row"><div><div class="name" data-i18n="khEarnWardrobe">वॉर्डरोब — कमाई</div><div class="meta">28 मई 2026</div></div><span class="amt pos">+₹4,000</span></div>
                </div>
                <div class="phone-actions">
                  <div class="phone-action"><span class="icon material-symbols-outlined" style="color:var(--warning)">account_balance_wallet</span><span data-i18n="actGiveAdvance">एडवांस दें</span></div>
                  <div class="phone-action" style="border-color:var(--success)"><span class="icon material-symbols-outlined" style="color:var(--success)">payments</span><span data-i18n="actSettle">सेटल करें</span></div>
                </div>
              </div>
              <!-- Slide 4: Reports -->
              <div class="phone-slide">
                <div class="phone-card">
                  <div class="phone-filters">
                    <span class="phone-filter" data-i18n="lblToday">आज</span>
                    <span class="phone-filter" data-i18n="lblWeek">सप्ताह</span>
                    <span class="phone-filter active" data-i18n="lblMonth">महीना</span>
                    <span class="phone-filter" data-i18n="lblYear">साल</span>
                  </div>
                </div>
                <div class="phone-totals" style="margin-top:8px;grid-template-columns:1fr">
                  <div class="phone-total"><div class="lbl" data-i18n="lblRepCollect">इस महीने वसूली</div><div class="val green" style="font-size:18px">₹1,20,000</div></div>
                  <div class="phone-total"><div class="lbl" data-i18n="lblRepKarigar">कारीगर को भुगतान</div><div class="val bronze">₹8,500</div></div>
                </div>
                <div class="phone-card" style="margin-top:8px">
                  <div class="phone-title" data-i18n="lblRepNetProfit">शुद्ध लाभ — इस महीने</div>
                  <div class="phone-total" style="background:rgba(255,255,255,0);padding:2px 0">
                    <div class="val green" style="font-size:18px">₹42,000</div>
                  </div>
                  <div class="phone-bars">
                    <div class="phone-bar"><span data-i18n="lblSales">बिक्री</span><div class="track"><div class="fill" style="width:100%;background:var(--primary)"></div></div></div>
                    <div class="phone-bar"><span data-i18n="lblMaterial">सामग्री</span><div class="track"><div class="fill" style="width:58%;background:var(--warning)"></div></div></div>
                    <div class="phone-bar"><span data-i18n="lblLabor">मज़दूरी</span><div class="track"><div class="fill" style="width:30%;background:var(--secondary)"></div></div></div>
                    <div class="phone-bar"><span data-i18n="lblProfit">लाभ</span><div class="track"><div class="fill" style="width:35%;background:var(--success)"></div></div></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="phone-dots" id="phoneDots"></div>
          <div class="phone-bottom-nav">
            <span class="phone-nav-item active"><span class="icon material-symbols-outlined">space_dashboard</span><span data-i18n="tabDash">डैशबोर्ड</span></span>
            <span class="phone-nav-item"><span class="icon material-symbols-outlined">assignment</span><span data-i18n="tabJobs">ऑर्डर</span></span>
            <span class="phone-nav-item"><span class="icon material-symbols-outlined">groups</span><span data-i18n="tabKarigar">कारीगर</span></span>
            <span class="phone-nav-item"><span class="icon material-symbols-outlined">persons</span><span data-i18n="tabCustomers">ग्राहक</span></span>
            <span class="phone-nav-item"><span class="icon material-symbols-outlined">monitoring</span><span data-i18n="tabReports">रिपोर्ट</span></span>
          </div>
        </div>
      </div>
      <div class="float-chip chip-2">
        <span class="icon amber"><span class="material-symbols-outlined">account_balance_wallet</span></span>
        <span><span data-i18n="chipAdvance">₹1,000 एडवांस</span><small data-i18n="chipAdvanceSub">असरफ कारीगर को दिया गया</small></span>
      </div>
    </div>
  </div>
</header>

<!-- STATS -->
<div class="stats">
  <div class="container">
    <div class="stats-grid">
      <div class="stat"><div class="num"><span data-i18n="stat1">8+</span><em>%</em></div><div class="lbl" data-i18n="stat1lbl">ऑर्डर में बढ़ोतरी</div></div>
      <div class="stat"><div class="num" data-i18n="stat2">₹1L+</div><div class="lbl" data-i18n="stat2lbl">मासिक वसूली ट्रैक</div></div>
      <div class="stat"><div class="num"><span data-i18n="stat3">100</span><em>%</em></div><div class="lbl" data-i18n="stat3lbl">हिन्दी + English</div></div>
      <div class="stat"><div class="num"><span data-i18n="stat4">1</span><em></em></div><div class="lbl" data-i18n="stat4lbl">ऐप में पूरा खाता-बही</div></div>
    </div>
  </div>
</div>

<!-- FEATURES -->
<section class="section" id="features">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow" data-i18n="featEyebrow">विशेषताएँ</span>
      <h2 data-i18n="featTitle">वर्कशॉप का हर काम, एक ही ऐप में</h2>
      <p data-i18n="featSub">खाता-बही, ऑर्डर बुक, कारीगर लेजर और रिपोर्ट — सब कुछ डिजिटल, आसान और आपकी जेब में।</p>
    </div>
    <div class="features-grid">
      <div class="feature">
        <div class="icon"><span class="material-symbols-outlined">space_dashboard</span></div>
        <h3 data-i18n="f1Title">स्मार्ट डैशबोर्ड</h3>
        <p data-i18n="f1Desc">दिन की शुरुआत में पूरी जानकारी — कितने ऑर्डर चल रहे हैं, कितना बकाया है, एडवांस और महीने की कुल वसूली।</p>
      </div>
      <div class="feature">
        <div class="icon green"><span class="material-symbols-outlined">assignment</span></div>
        <h3 data-i18n="f2Title">जॉब कार्ड व ऑर्डर ट्रैकिंग</h3>
        <p data-i18n="f2Desc">हर ऑर्डर का पूरा सफ़र — नया → ढांचा → नक्काशी → पॉलिश → तैयार → डिलीवर। स्थिति एक नज़र में, रंगीन बैज के साथ।</p>
      </div>
      <div class="feature">
        <div class="icon amber"><span class="material-symbols-outlined">groups</span></div>
        <h3 data-i18n="f3Title">कारीगर लेजर</h3>
        <p data-i18n="f3Desc">हर कारीगर का हिसाब अलग — कमाई, एडवांस और सेटलमेंट। साप्ताहिक पेआउट दें और बकाया तुरंत समझें।</p>
      </div>
      <div class="feature">
        <div class="icon red"><span class="material-symbols-outlined">person_book</span></div>
        <h3 data-i18n="f4Title">उधार खाता (Udhaar Khata)</h3>
        <p data-i18n="f4Desc">ग्राहकों के बकाया पैसे कभी न भूलें। किसका कितना बकाया है, कितने ऑर्डर पूरे हुए — सब कुछ साफ़-सुथरा।</p>
      </div>
      <div class="feature">
        <div class="icon green"><span class="material-symbols-outlined">monitoring</span></div>
        <h3 data-i18n="f5Title">रिपोर्ट और लाभ</h3>
        <p data-i18n="f5Desc">आज, सप्ताह, महीने या साल का हिसाब — बिक्री, मज़दूरी लागत और शुद्ध लाभ। एक क्लिक में PDF/रिपोर्ट शेयर करें।</p>
      </div>
      <div class="feature">
        <div class="icon"><span class="material-symbols-outlined" style="color:var(--whatsapp)">chat</span></div>
        <h3 data-i18n="f6Title">WhatsApp इंटीग्रेशन</h3>
        <p data-i18n="f6Desc">ऑर्डर कन्फर्मेशन, इनवॉइस और पेमेंट रिमाइंडर सीधे WhatsApp पर ग्राहक को भेजें — कागज़ी झंझट नहीं।</p>
      </div>
    </div>
  </div>
</section>

<!-- WORKFLOW -->
<section class="section workflow" id="workflow">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow" data-i18n="howEyebrow">कैसे काम करता है</span>
      <h2 data-i18n="howTitle">4 आसान कदमों में पूरा हिसाब</h2>
      <p data-i18n="howSub">कोई तकनीकी ज्ञान नहीं चाहिए। बस अपना नंबर दर्ज करें और शुरू करें।</p>
    </div>
    <div class="workflow-steps">
      <div class="workflow-step">
        <div class="step-num">01<span class="material-symbols-outlined">smartphone</span></div>
        <h3 data-i18n="s1Title">APK डाउनलोड करें</h3>
        <p data-i18n="s1Desc">नीचे दिए बटन से नवीनतम संस्करण का APK फ़ाइल डाउनलोड करें। (प्ले स्टोर की ज़रूरत नहीं।)</p>
      </div>
      <div class="workflow-step">
        <div class="step-num">02<span class="material-symbols-outlined">install_mobile</span></div>
        <h3 data-i18n="s2Title">इंस्टॉल करें</h3>
        <p data-i18n="s2Desc">फ़ाइल खोलें और "अज्ञात स्रोत से इंस्टॉल करें" की अनुमति दें। एक बार की सेटिंग।</p>
      </div>
      <div class="workflow-step">
        <div class="step-num">03<span class="material-symbols-outlined">phone_iphone</span></div>
        <h3 data-i18n="s3Title">अपना नंबर सत्यापित करें</h3>
        <p data-i18n="s3Desc">OTP से लॉगिन करें, अपना वर्कशॉप विवरण भरें और तुरंत चलाएँ। कुछ ही मिनटों में शुरू।</p>
      </div>
      <div class="workflow-step">
        <div class="step-num">04<span class="material-symbols-outlined">verified</span></div>
        <h3 data-i18n="s4Title">हर दिन ऑटो-हिसाब</h3>
        <p data-i18n="s4Desc">ऑर्डर जोड़ें, पेमेंट दर्ज करें — बाकी का हिसाब Craft Flow खुद रखता है।</p>
      </div>
    </div>
  </div>
</section>

<!-- DOWNLOAD -->
<section class="section" id="download">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow" data-i18n="dlEyebrow">डाउनलोड</span>
      <h2 data-i18n="dlTitle">संस्करण चुनें और डाउनलोड करें</h2>
      <p data-i18n="dlSub">दोनों संस्करण एक जैसे फीचर्स के साथ आते हैं। नवीनतम (v2) में सारे सुधार और नई सुविधाएँ हैं — हमेशा v2 चुनें।</p>
    </div>
    <div class="download-cards">
      <div class="dl-card">
        <span class="ribbon" data-i18n="ribbonLatest">नवीनतम</span>
        <div class="dl-icon"><span class="material-symbols-outlined">rocket_launch</span></div>
        <h3 data-i18n="v2Name">Craft Flow v2</h3>
        <div class="ver" data-i18n="v2Ver">नवीनतम स्थिर संस्करण</div>
        <p class="desc" data-i18n="v2Desc">All the new features, bug fixes and a full Hindi + English experience. If you have not installed yet, start here.</p>
        <div class="dl-meta">
          <span class="m"><span class="material-symbols-outlined">android</span> Android</span>
          <span class="m"><span class="material-symbols-outlined">auto_awesome</span><span data-i18n="v2Meta">All features</span></span>
          <span class="m"><span class="material-symbols-outlined">translate</span><span data-i18n="hindiMeta">हिन्दी + English</span></span>
        </div>
        <a href="https://github.com/svan2511/craft-flow-app/releases/download/v2.0.0/app.apk" class="btn btn-primary btn-md dl-btn" data-i18n="v2Btn">v2 डाउनलोड करें (APK)</a>
      </div>
      <div class="dl-card">
        <span class="ribbon new" data-i18n="ribbonPrev">पिछला</span>
        <div class="dl-icon"><span class="material-symbols-outlined">archive</span></div>
        <h3 data-i18n="v1Name">Craft Flow v1</h3>
        <div class="ver" data-i18n="v1Ver">बेस / स्थिर संस्करण</div>
        <p class="desc" data-i18n="v1Desc">The first public release with all core features. This version supports English only — if you want Hindi, install Craft Flow v2.</p>
        <div class="dl-meta">
          <span class="m"><span class="material-symbols-outlined">android</span> Android</span>
          <span class="m"><span class="material-symbols-outlined">check</span><span data-i18n="v1Meta">Core features</span></span>
          <span class="m"><span class="material-symbols-outlined">translate</span><span data-i18n="hindiMeta2">English only</span></span>
        </div>
        <a href="https://github.com/svan2511/craft-flow-app/releases/download/v1.0.0/app.apk" class="btn btn-outline btn-md dl-btn" data-i18n="v1Btn">v1 डाउनलोड करें (APK)</a>
      </div>
    </div>
    <div class="install-note">
      <span class="tag"><span class="material-symbols-outlined">shield_check</span><span data-i18n="noteVirus">बिल्कुल सुरक्षित — कोई वायरस नहीं</span></span>
      <span class="tag"><span class="material-symbols-outlined">memory</span><span data-i18n="noteSize">हल्का फ़ाइल साइज़</span></span>

      <span class="tag"><span class="material-symbols-outlined">update</span><span data-i18n="noteUpdate">नया आता है तो यहीं सबसे पहले</span></span>
    </div>
  </div>
</section>

<!-- INSTALL STEPS -->
<section class="section" style="padding-top:0" id="install">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow" data-i18n="instEyebrow">इंस्टॉलेशन</span>
      <h2 data-i18n="instTitle">Android में इंस्टॉल कैसे करें</h2>
      <p data-i18n="instSub">पहली बार APK इंस्टॉल करने के आसान स्टेप्स।</p>
    </div>
    <div class="install-steps">
      <div class="install-step">
        <div class="ico"><span class="material-symbols-outlined">download</span></div>
        <h3 data-i18n="i1Title">APK फ़ाइल डाउनलोड करें</h3>
        <p data-i18n="i1Desc">ऊपर दिए डाउनलोड बटन दबाकर APK फ़ाइल सेव करें। फ़ाइल आपके "डाउनलोड" फोल्डर में मिलेगी।</p>
      </div>
      <div class="install-step">
        <div class="ico"><span class="material-symbols-outlined">settings</span></div>
        <h3 data-i18n="i2Title">अज्ञात स्रोत की अनुमति दें</h3>
        <p data-i18n="i2Desc">फ़ाइल खोलने पर Android पूछेगा — "इस स्रोत से इंस्टॉल की अनुमति दें" चुनें और इंस्टॉल दबाएँ।</p>
      </div>
      <div class="install-step">
        <div class="ico"><span class="material-symbols-outlined">check_circle</span></div>
        <h3 data-i18n="i3Title">खोलें और लॉगिन करें</h3>
        <p data-i18n="i3Desc">इंस्टॉल होते ही ऐप खोलें, अपना मोबाइल नंबर व OTP डालें और अपना वर्कशॉप सेट करें।</p>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="section" style="padding-top:0" id="faq">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow" data-i18n="faqEyebrow">सवाल-जवाब</span>
      <h2 data-i18n="faqTitle">अक्सर पूछे जाने वाले सवाल</h2>
      <p data-i18n="faqSub">कोई और सवाल है? हमें हमेशा सुनना पसंद है।</p>
    </div>
    <div class="faq-list">
      <div class="faq-item">
        <button class="faq-q"><span data-i18n="faq1q">Is Craft Flow on the Play Store?</span><span class="material-symbols-outlined">expand_more</span></button>
        <div class="faq-a"><p data-i18n="faq1a">We just launched, so for now the APK is downloaded directly from this page. It will soon be available on Google Play and the App Store.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span data-i18n="faq2q">Does this app work in Hindi?</span><span class="material-symbols-outlined">expand_more</span></button>
        <div class="faq-a"><p data-i18n="faq2a">Yes, absolutely! You can choose Hindi or English from the settings. The entire app is available in both languages — and Hindi support arrives in Craft Flow v2.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span data-i18n="faq3q">Is my data safe?</span><span class="material-symbols-outlined">expand_more</span></button>
        <div class="faq-a"><p data-i18n="faq3a">Yes. Your business data stays on secure servers and is only ever shared with your permission. It is never given to anyone else.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span data-i18n="faq4q">How many people can use this app?</span><span class="material-symbols-outlined">expand_more</span></button>
        <div class="faq-a"><p data-i18n="faq4a">Craft Flow is built for a single workshop owner (admin). You can manage all your karigars, customers and orders from one place.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span data-i18n="faq5q">Do I need internet?</span><span class="material-symbols-outlined">expand_more</span></button>
        <div class="faq-a"><p data-i18n="faq5a">Mobile data or Wi-Fi is needed to sync data, but the app is lightweight and works fine even on a weak network.</p></div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-band">
  <div class="container">
    <h2 data-i18n="ctaTitle">आज ही अपने हिसाब को डिजिटल बनाएँ</h2>
    <p data-i18n="ctaSub">पेपर खाता-बही अब ऐप में। मुफ़्त में शुरू करें, जब चाहे अपग्रेड करें।</p>
    <a href="#download" class="btn btn-dark btn-lg"><span class="material-symbols-outlined">download</span><span data-i18n="ctaBtn">अभी डाउनलोड करें</span></a>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="#" class="brand">
          <span class="brand-logo"><img src="/img/logo.png" alt="Craft Flow"></span>
          <span class="brand-name">Craft <em>Flow</em></span>
        </a>
        <p class="about-text" data-i18n="footerAbout">वर्कशॉप मालिकों, फर्नीचर मास्टर्स और फैब्रिकेशन शॉप्स के लिए एक पूरा व्यवसाय प्रबंधन साथी — हिन्दी में। सहारनपुर, रुड़की, हरिद्वार, देहरादून।</p>
      </div>
      <div>
        <h4 data-i18n="footerProd">उत्पाद</h4>
        <a href="#features" data-i18n="footerFeatures">विशेषताएँ</a>
        <a href="#workflow" data-i18n="footerHow">कैसे काम करता है</a>
        <a href="#download" data-i18n="footerDownload">डाउनलोड</a>
        <a href="#faq" data-i18n="footerFaq">सवाल-जवाब</a>
      </div>
      <div>
        <h4 data-i18n="footerContact">संपर्क व सहायता</h4>
        <a href="#">support@craftflow.app</a>
        <a href="#" data-i18n="footerWhatsapp">WhatsApp सहायता</a>
        <a href="#" data-i18n="footerFeedback">फीडबैक दें</a>
      </div>
    </div>
    <div class="bar">
      <span>© 2026 Craft Flow · <span data-i18n="footerRights">सर्वाधिकार सुरक्षित</span></span>
      <span>Made with <span class="heart">♥</span> <span data-i18n="footerFor">वर्कशॉप मालिकों के लिए</span></span>
    </div>
  </div>
</footer>

<script>
(function () {
  'use strict';

  var nav = document.getElementById('navLinks');
  var burger = document.getElementById('burger');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      burger.querySelector('.material-symbols-outlined').textContent = open ? 'menu_open' : 'menu';
    });
    nav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { nav.classList.remove('open'); });
    });
  }

  document.querySelectorAll('.faq-item').forEach(function (item) {
    var q = item.querySelector('.faq-q');
    var a = item.querySelector('.faq-a');
    q.addEventListener('click', function () {
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(function (o) {
        o.classList.remove('open');
        o.querySelector('.faq-a').style.maxHeight = null;
      });
      if (!isOpen) {
        item.classList.add('open');
        a.style.maxHeight = a.scrollHeight + 'px';
      }
    });
  });

  // ---------- Phone app slider ----------
  var slidesWrap = document.getElementById('phoneSlides');
  var dotsWrap = document.getElementById('phoneDots');
  if (slidesWrap && dotsWrap) {
    var slides = Array.prototype.slice.call(slidesWrap.children);
    var current = 0;
    var timer;
    slides.forEach(function (s, i) {
      var dot = document.createElement('button');
      dot.className = 'phone-dot' + (i === 0 ? ' active' : '');
      dot.setAttribute('aria-label', 'Slide ' + (i + 1));
      dot.addEventListener('click', function () { goTo(i); restart(); });
      dotsWrap.appendChild(dot);
    });
    function goTo(i) {
      current = (i + slides.length) % slides.length;
      slidesWrap.style.transform = 'translateX(-' + (current * 100) + '%)';
      Array.prototype.forEach.call(dotsWrap.children, function (d, j) {
        d.classList.toggle('active', j === current);
      });
    }
    function restart() {
      clearInterval(timer);
      timer = setInterval(function () { goTo(current + 1); }, 3500);
    }
    var touchX = null;
    slidesWrap.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; }, { passive: true });
    slidesWrap.addEventListener('touchend', function (e) {
      if (touchX === null) { return; }
      var dx = e.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) > 30) { goTo(dx < 0 ? current + 1 : current - 1); restart(); }
      touchX = null;
    }, { passive: true });
    restart();
  }

  // ---------- i18n ----------
  var dict = {
    navFeatures: ['विशेषताएँ', 'Features'],
    navHow: ['कैसे काम करता है', 'How it works'],
    navDownload: ['डाउनलोड', 'Download'],
    navFaq: ['सवाल', 'FAQ'],
    navGetApp: ['ऐप पाएँ', 'Get the app'],
    heroBadge: ['अब हिन्दी में पूरा वर्कशॉप प्रबंधन', 'Full workshop management, now in Hindi'],
    heroTitleA: ['अपनी वर्कशॉप का ', 'Your workshop, '],
    heroTitleB: ['डिजिटल खाता-बही,', 'one digital khata,'],
    heroTitleC: ['एक क्लिक में', 'simplified'],
    heroSub: ['ऑर्डर बनाएँ, कारीगर को काम दें, एडवांस और सेटलमेंट ट्रैक करें, और ग्राहकों का उधार खाता संभालें — सब कुछ आपके मोबाइल पर, बिना कोई पेपर खाता-बही के।', 'Create orders, assign karigar work, track advances & settlements, and manage customer udhaar — all on your phone, no paper khata needed.'],
    ctaLatest: ['नवीनतम संस्करण डाउनलोड करें', 'Download latest version'],
    ctaPrevious: ['पिछला संस्करण (v1)', 'Previous version (v1)'],
    safeHint: ['100% सुरक्षित', '100% safe'],
    heroSub2: ['Craft Flow फर्नीचर, नक्काशी और फैब्रिकेशन वर्कशॉप मालिकों के लिए बना है — सहारनपुर, रुड़की, हरिद्वार और देहरादून के कारीगरों के लिए।', 'Craft Flow is built for furniture, carving and fabrication workshop owners — for the craftsmen of Saharanpur, Roorkee, Haridwar and Dehradun.'],
    chipPaid: ['₹10,000 प्राप्त', '₹10,000 received'],
    chipPaidSub: ['सुरेश जी से वसूली', 'collected from Suresh Ji'],
    chipAdvance: ['₹1,000 एडवांस', '₹1,000 advance'],
    chipAdvanceSub: ['असरफ कारीगर को दिया गया', 'given to Asraf karigar'],
    actOrder: ['नया ऑर्डर', 'New Order'],
    actPayment: ['भुगतान', 'Payment'],
    actAdvance: ['एडवांस', 'Advance'],
    actGiveAdvance: ['एडवांस दें', 'Give Advance'],
    actSettle: ['सेटल करें', 'Settle'],
    lblActiveOrders: ['सक्रिय ऑर्डर', 'Active Orders'],
    lblMonthCollect: ['इस महीने वसूली', 'Monthly Collection'],
    lblMarketDues: ['बाज़ार बकाया', 'Market Dues'],
    lblAdvances: ['कारीगर एडवांस', 'Karigar Advances'],
    lblTodayProfit: ['आज का लाभ', "Today's Profit"],
    lblSearchOrders: ['ऑर्डर ID या नाम खोजें…', 'Search Order ID or Name…'],
    lblAll: ['सभी', 'All'],
    lblStructure: ['ढांचे में', 'In Structure'],
    lblPolish: ['पॉलिश में', 'In Polish'],
    lblReady: ['तैयार', 'Ready'],
    finTotal: ['कुल ₹40,000', 'Total ₹40,000'],
    finPaid: ['₹10,000 भुगतान', '₹10,000 Paid'],
    finDue: ['₹30,000 बकाया', '₹30,000 Due'],
    finTotal2: ['कुल ₹25,000', 'Total ₹25,000'],
    finPaid2: ['₹25,000 भुगतान', '₹25,000 Paid'],
    finDue2: ['₹0 बकाया', '₹0 Due'],
    finTotal3: ['कुल ₹60,000', 'Total ₹60,000'],
    finPaid3: ['₹15,000 भुगतान', '₹15,000 Paid'],
    finDue3: ['₹45,000 बकाया', '₹45,000 Due'],
    lblEarned: ['कुल कमाई', 'Total Earned'],
    lblAdvance: ['एडवांस', 'Advance'],
    lblBalance: ['बकाया', 'Balance'],
    khEarnBed: ['बेड 6x6 — कमाई', 'Bed 6x6 — Earning'],
    khAdvance: ['एडवांस — नकद', 'Advance — Cash'],
    khEarnWardrobe: ['वॉर्डरोब — कमाई', 'Wardrobe — Earning'],
    lblToday: ['आज', 'Today'],
    lblWeek: ['सप्ताह', 'Week'],
    lblMonth: ['महीना', 'Month'],
    lblYear: ['साल', 'Year'],
    lblRepCollect: ['इस महीने वसूली', 'Monthly Collection'],
    lblRepKarigar: ['कारीगर को भुगतान', 'Paid to Karigar'],
    lblRepNetProfit: ['शुद्ध लाभ — इस महीने', 'Net Profit — This Month'],
    lblSales: ['बिक्री', 'Sales'],
    lblMaterial: ['सामग्री', 'Material'],
    lblLabor: ['मज़दूरी', 'Labor'],
    lblProfit: ['लाभ', 'Profit'],
    pillPolish: ['पॉलिश में', 'In Polish'],
    pillReady: ['तैयार', 'Ready'],
    pillOverdue: ['अतिदेय', 'Overdue'],
    tabDash: ['डैशबोर्ड', 'Dashboard'],
    tabJobs: ['ऑर्डर', 'Orders'],
    tabKarigar: ['कारीगर', 'Karigars'],
    tabCustomers: ['ग्राहक', 'Customers'],
    tabReports: ['रिपोर्ट', 'Reports'],
    stat1: ['8+', '8+'],
    stat1lbl: ['ऑर्डर में बढ़ोतरी', 'order tracking made easy'],
    stat2: ['₹1L+', '₹1L+'],
    stat2lbl: ['मासिक वसूली ट्रैक', 'monthly collection tracked'],
    stat3: ['100', '100'],
    stat3lbl: ['हिन्दी + English', 'Hindi + English'],
    stat4: ['1', '1'],
    stat4lbl: ['ऐप में पूरा खाता-बही', 'app that keeps every khata'],
    featEyebrow: ['विशेषताएँ', 'Features'],
    featTitle: ['वर्कशॉप का हर काम, एक ही ऐप में', 'Everything your workshop needs, in one app'],
    featSub: ['खाता-बही, ऑर्डर बुक, कारीगर लेजर और रिपोर्ट — सब कुछ डिजिटल, आसान और आपकी जेब में।', 'Khata, order book, karigar ledger and reports — all digital, easy and in your pocket.'],
    f1Title: ['स्मार्ट डैशबोर्ड', 'Smart Dashboard'],
    f1Desc: ['दिन की शुरुआत में पूरी जानकारी — कितने ऑर्डर चल रहे हैं, कितना बकाया है, एडवांस और महीने की कुल वसूली।', 'Start your day knowing it all — active orders, market dues, advances and monthly collection at a glance.'],
    f2Title: ['जॉब कार्ड व ऑर्डर ट्रैकिंग', 'Job Cards & Order Tracking'],
    f2Desc: ['हर ऑर्डर का पूरा सफ़र — नया → ढांचा → नक्काशी → पॉलिश → तैयार → डिलीवर। स्थिति एक नज़र में, रंगीन बैज के साथ।', 'Every order journeys from new → structure → carving → polish → ready → delivered, with clear color badges.'],
    f3Title: ['कारीगर लेजर', 'Karigar Ledger'],
    f3Desc: ['हर कारीगर का हिसाब अलग — कमाई, एडवांस और सेटलमेंट। साप्ताहिक पेआउट दें और बकाया तुरंत समझें।', 'Separate books for every karigar — earnings, advances and settlements, with easy weekly payouts.'],
    f4Title: ['उधार खाता (Udhaar Khata)', 'Udhaar Khata'],
    f4Desc: ['ग्राहकों के बकाया पैसे कभी न भूलें। किसका कितना बकाया है, कितने ऑर्डर पूरे हुए — सब कुछ साफ़-सुथरा।', 'Never forget a customer balance again. See exactly who owes what, anytime.'],
    f5Title: ['रिपोर्ट और लाभ', 'Reports & Profit'],
    f5Desc: ['आज, सप्ताह, महीने या साल का हिसाब — बिक्री, मज़दूरी लागत और शुद्ध लाभ। एक क्लिक में PDF/रिपोर्ट शेयर करें।', 'Reports for today, week, month or year — sales, labor cost and net profit. Share as PDF in one tap.'],
    f6Title: ['WhatsApp इंटीग्रेशन', 'WhatsApp Integration'],
    f6Desc: ['ऑर्डर कन्फर्मेशन, इनवॉइस और पेमेंट रिमाइंडर सीधे WhatsApp पर ग्राहक को भेजें — कागज़ी झंझट नहीं।', 'Send order confirmations, invoices and payment reminders directly on WhatsApp. No paper hassle.'],
    howEyebrow: ['कैसे काम करता है', 'How it works'],
    howTitle: ['4 आसान कदमों में पूरा हिसाब', 'Start in 4 simple steps'],
    howSub: ['कोई तकनीकी ज्ञान नहीं चाहिए। बस अपना नंबर दर्ज करें और शुरू करें।', 'No technical skill needed. Just enter your number and get going.'],
    s1Title: ['APK डाउनलोड करें', 'Download the APK'],
    s1Desc: ['नीचे दिए बटन से नवीनतम संस्करण का APK फ़ाइल डाउनलोड करें। (प्ले स्टोर की ज़रूरत नहीं।)', 'Download the latest APK from the button below. (No Play Store needed.)'],
    s2Title: ['इंस्टॉल करें', 'Install it'],
    s2Desc: ['फ़ाइल खोलें और "अज्ञात स्रोत से इंस्टॉल करें" की अनुमति दें। एक बार की सेटिंग।', 'Open the file and allow "install from unknown sources". One-time setting.'],
    s3Title: ['अपना नंबर सत्यापित करें', 'Verify your number'],
    s3Desc: ['OTP से लॉगिन करें, अपना वर्कशॉप विवरण भरें और तुरंत चलाएँ। कुछ ही मिनटों में शुरू।', 'Login with OTP, add your workshop details, and you are ready in minutes.'],
    s4Title: ['हर दिन ऑटो-हिसाब', 'Auto books, every day'],
    s4Desc: ['ऑर्डर जोड़ें, पेमेंट दर्ज करें — बाकी का हिसाब Craft Flow खुद रखता है।', 'Add orders and payments — Craft Flow keeps the rest of the books for you.'],
    dlEyebrow: ['डाउनलोड', 'Download'],
    dlTitle: ['संस्करण चुनें और डाउनलोड करें', 'Choose a version & download'],
    dlSub: ['दोनों संस्करण एक जैसे फीचर्स के साथ आते हैं। नवीनतम (v2) में सारे सुधार और नई सुविधाएँ हैं — हमेशा v2 चुनें।', 'Both versions share the same core. Latest (v2) has all improvements and new features — always pick v2.'],
    ribbonLatest: ['नवीनतम', 'Latest'],
    v2Name: ['Craft Flow v2', 'Craft Flow v2'],
    v2Ver: ['नवीनतम स्थिर संस्करण', 'Latest stable release'],
    v2Desc: ['सभी नई सुविधाएँ, बग फिक्स और हिन्दी में पूरा अनुभव। नहीं इंस्टॉल किया तो यहीं से करें।', 'All the new features, bug fixes and a full Hindi experience. If you have not installed yet, start here.'],
    v2Meta: ['सभी फीचर्स', 'All features'],
    hindiMeta: ['हिन्दी + English', 'Hindi + English'],
    v2Btn: ['v2 डाउनलोड करें (APK)', 'Download v2 (APK)'],
    ribbonPrev: ['पिछला', 'Previous'],
    v1Name: ['Craft Flow v1', 'Craft Flow v1'],
    v1Ver: ['बेस / स्थिर संस्करण', 'Base / stable release'],
    v1Desc: ['पहला पब्लिक संस्करण — केवल English में। बुनियादी सभी सुविधाएँ मौजूद। हिन्दी चाहिए तो v2 इंस्टॉल करें।', 'The first public release with all core features. This version supports English only — if you want Hindi, install Craft Flow v2.'],
    v1Meta: ['बुनियादी फीचर्स', 'Core features'],
    hindiMeta2: ['केवल English', 'English only'],
    v1Btn: ['v1 डाउनलोड करें (APK)', 'Download v1 (APK)'],
    noteVirus: ['बिल्कुल सुरक्षित — कोई वायरस नहीं', '100% safe — no virus'],
    noteSize: ['हल्का फ़ाइल साइज़', 'Light file size'],
    noteUpdate: ['नया आता है तो यहीं सबसे पहले', 'New updates land here first'],
    instEyebrow: ['इंस्टॉलेशन', 'Installation'],
    instTitle: ['Android में इंस्टॉल कैसे करें', 'How to install on Android'],
    instSub: ['पहली बार APK इंस्टॉल करने के आसान स्टेप्स।', 'Easy steps for first-time APK installs.'],
    i1Title: ['APK फ़ाइल डाउनलोड करें', 'Download the APK file'],
    i1Desc: ['ऊपर दिए डाउनलोड बटन दबाकर APK फ़ाइल सेव करें। फ़ाइल आपके "डाउनलोड" फोल्डर में मिलेगी।', 'Tap the download button above to save the APK. You will find it in your Downloads folder.'],
    i2Title: ['अज्ञात स्रोत की अनुमति दें', 'Allow unknown sources'],
    i2Desc: ['फ़ाइल खोलने पर Android पूछेगा — "इस स्रोत से इंस्टॉल की अनुमति दें" चुनें और इंस्टॉल दबाएँ।', 'Android will ask to "allow installs from this source". Accept and press Install.'],
    i3Title: ['खोलें और लॉगिन करें', 'Open & login'],
    i3Desc: ['इंस्टॉल होते ही ऐप खोलें, अपना मोबाइल नंबर व OTP डालें और अपना वर्कशॉप सेट करें।', 'Open the app, enter your number & OTP, set up your workshop and you are live.'],
    faqEyebrow: ['सवाल-जवाब', 'FAQ'],
    faqTitle: ['अक्सर पूछे जाने वाले सवाल', 'Frequently asked questions'],
    faqSub: ['कोई और सवाल है? हमें हमेशा सुनना पसंद है।', 'Still have a question? We would love to hear it.'],
    faq1q: ['क्या Craft Flow प्ले स्टोर पर है?', 'Is Craft Flow on the Play Store?'],
    faq1a: ['अभी अभी-अभी शुरू किया है, इसलिए सीधे इस पेज से APK डाउनलोड किया जा सकता है। जल्द ही Google Play और App Store पर भी उपलब्ध होगा।', 'We just launched, so for now the APK is downloaded directly from this page. It will soon be available on Google Play and the App Store.'],
    faq2q: ['क्या यह ऐप हिन्दी में काम करता है?', 'Does this app work in Hindi?'],
    faq2a: ['हाँ, बिल्कुल! आप सेटिंग में हिन्दी या English अपनी पसंद से चुन सकते हैं। पूरा ऐप दोनों भाषाओं में उपलब्ध है — हिन्दी सपोर्ट Craft Flow v2 से शुरू हुआ है।', 'Yes, absolutely! You can choose Hindi or English from the settings. The entire app is available in both languages — and Hindi support arrives in Craft Flow v2.'],
    faq3q: ['क्या मेरा डेटा सुरक्षित है?', 'Is my data safe?'],
    faq3a: ['हाँ। आपका व्यवसाय डेटा सुरक्षित सर्वर पर रहता है और सिर्फ़ आपकी अनुमति से ही साझा होता है। डेटा कभी दूसरे किसी के साथ नहीं।', 'Yes. Your business data stays on secure servers and is only ever shared with your permission. It is never given to anyone else.'],
    faq4q: ['कितने लोग इस ऐप का इस्तेमाल कर सकते हैं?', 'How many people can use this app?'],
    faq4a: ['Craft Flow एक वर्कशॉप मालिक (admin) के लिए बना हुआ है। आप अपने सारे कारीगर, ग्राहक और ऑर्डर एक ही जगह मैनेज कर सकते हैं।', 'Craft Flow is built for a single workshop owner (admin). You can manage all your karigars, customers and orders from one place.'],
    faq5q: ['क्या मुझे इंटरनेट ज़रूरी है?', 'Do I need internet?'],
    faq5a: ['डेटा सिंक के लिए मोबाइल डेटा/Wi-Fi ज़रूरी है, लेकिन ऐप हल्का है और कम नेटवर्क पर भी ठीक चलता है।', 'Mobile data or Wi-Fi is needed to sync data, but the app is lightweight and works fine even on a weak network.'],
    ctaTitle: ['आज ही अपने हिसाब को डिजिटल बनाएँ', 'Go digital with your books today'],
    ctaSub: ['पेपर खाता-बही अब ऐप में। मुफ़्त में शुरू करें, जब चाहे अपग्रेड करें।', 'Your paper khata, now in an app. Start free, upgrade whenever you are ready.'],
    ctaBtn: ['अभी डाउनलोड करें', 'Download now'],
    footerAbout: ['वर्कशॉप मालिकों, फर्नीचर मास्टर्स और फैब्रिकेशन शॉप्स के लिए एक पूरा व्यवसाय प्रबंधन साथी — हिन्दी में। सहारनपुर, रुड़की, हरिद्वार, देहरादून।', 'A complete business companion for workshop owners, furniture masters and fabrication shops — in Hindi. Saharanpur, Roorkee, Haridwar, Dehradun.'],
    footerProd: ['उत्पाद', 'Product'],
    footerFeatures: ['विशेषताएँ', 'Features'],
    footerHow: ['कैसे काम करता है', 'How it works'],
    footerDownload: ['डाउनलोड', 'Download'],
    footerFaq: ['सवाल-जवाब', 'FAQ'],
    footerContact: ['संपर्क व सहायता', 'Contact & Support'],
    footerWhatsapp: ['WhatsApp सहायता', 'WhatsApp support'],
    footerFeedback: ['फीडबैक दें', 'Give feedback'],
    footerRights: ['सर्वाधिकार सुरक्षित', 'All rights reserved'],
    footerFor: ['वर्कशॉप मालिकों के लिए', 'For workshop owners']
  };

  var langBtns = document.querySelectorAll('.lang-btn');
  function applyLang(lang) {
    var idx = lang === 'en' ? 1 : 0;
    Object.keys(dict).forEach(function (key) {
      document.querySelectorAll('[data-i18n="' + key + '"]').forEach(function (el) {
        el.textContent = dict[key][idx];
      });
    });
    langBtns.forEach(function (b) {
      b.classList.toggle('active', b.getAttribute('data-lang') === lang);
    });
    document.documentElement.lang = lang;
  }
  langBtns.forEach(function (btn) {
    btn.addEventListener('click', function () { applyLang(btn.getAttribute('data-lang')); });
  });

  // hero title has three parts; keep the <br> intact by handling A/B/C spans
  function applyHero(lang) {
    var idx = lang === 'en' ? 1 : 0;
    document.querySelectorAll('#heroTitleA,#heroTitleB,#heroTitleC').forEach(function (el) {
      el.textContent = dict[el.id][idx];
    });
  }
  var origApply = applyLang;
  applyLang = function (lang) {
    origApply(lang);
    applyHero(lang);
  };
  // default language: English
  applyLang('en');

})();
</script>
</body>
</html>
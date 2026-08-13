<?php

namespace App\Infrastructure\Installers\Support;

final class ShowcaseStyles
{
    public static function css(): string
    {
        return <<<'CSS'
:root {
  --ink: #12263a;
  --paper: #eef2f6;
  --copper: #b4532a;
  --fog: #5d6b7a;
  --rule: #c5cdd6;
  --white: #ffffff;
  --display: "Syne", sans-serif;
  --body: "Figtree", sans-serif;
  --mono: "IBM Plex Mono", ui-monospace, monospace;
}

* { box-sizing: border-box; }
html, body { margin: 0; min-height: 100%; }
body {
  background: var(--paper);
  color: var(--ink);
  font-family: var(--body);
  line-height: 1.5;
}
a { color: inherit; }
.wrap { width: min(1080px, calc(100% - 2.5rem)); margin: 0 auto; }
.site-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 0;
  border-bottom: 1px solid var(--rule);
}
.mark {
  font-family: var(--display);
  font-weight: 800;
  letter-spacing: -0.04em;
  font-size: 1.15rem;
  text-decoration: none;
}
nav { display: flex; flex-wrap: wrap; gap: 0.75rem 1.1rem; }
nav a {
  font-family: var(--mono);
  font-size: 0.72rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  text-decoration: none;
  color: var(--fog);
}
nav a:hover, nav a[aria-current="page"] { color: var(--copper); }
.hero { padding: 3.5rem 0 2.5rem; }
.kicker {
  font-family: var(--mono);
  font-size: 0.72rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--copper);
  margin: 0 0 0.75rem;
}
h1 {
  font-family: var(--display);
  font-size: clamp(2.2rem, 5vw, 4.2rem);
  line-height: 0.95;
  letter-spacing: -0.05em;
  margin: 0 0 1rem;
  max-width: 14ch;
}
.lede { max-width: 40rem; color: var(--fog); margin: 0 0 1.75rem; }
.actions { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.btn {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  border: 1px solid var(--ink);
  background: var(--ink);
  color: var(--white);
  padding: 0.7rem 1.1rem;
  font-size: 0.9rem;
  font-weight: 600;
}
.btn.ghost { background: transparent; color: var(--ink); }
.title-block {
  margin: 0 0 3rem;
  border: 1px solid var(--rule);
  background: var(--white);
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
}
.title-block div {
  padding: 0.9rem 1rem;
  border-right: 1px solid var(--rule);
  border-bottom: 1px solid var(--rule);
}
.title-block dt {
  font-family: var(--mono);
  font-size: 0.65rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--fog);
}
.title-block dd { margin: 0.2rem 0 0; font-weight: 600; }
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  padding-bottom: 4rem;
}
.card {
  background: var(--white);
  border: 1px solid var(--rule);
  padding: 1.2rem 1.25rem;
  text-decoration: none;
  display: block;
}
.card h2, .card h3 { font-family: var(--display); margin: 0 0 0.35rem; letter-spacing: -0.03em; }
.card p { margin: 0; color: var(--fog); font-size: 0.92rem; }
.sheet {
  width: min(420px, 100%);
  margin: 3rem auto 4rem;
  background: var(--white);
  border: 1px solid var(--rule);
  padding: 1.75rem;
}
.sheet h1 { font-size: 2rem; max-width: none; }
label { display: block; font-size: 0.85rem; font-weight: 600; margin: 1rem 0 0.35rem; }
input {
  width: 100%;
  border: 1px solid var(--rule);
  background: var(--paper);
  padding: 0.7rem 0.75rem;
  font: inherit;
}
.hint { color: var(--fog); font-size: 0.85rem; margin-top: 1rem; }
.site-footer {
  border-top: 1px solid var(--rule);
  padding: 1.25rem 0 2rem;
  color: var(--fog);
  font-size: 0.85rem;
}
@media (max-width: 720px) {
  .site-header { flex-direction: column; align-items: flex-start; }
}
CSS;
    }

    public static function fontLinks(): string
    {
        return <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600&family=IBM+Plex+Mono:wght@400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet">
HTML;
    }
}

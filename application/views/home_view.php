<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghanshyam Murtibhandar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rozha+One&family=Mukta:wght@400;500;600&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ivory: #FBF3E7;
            --ivory-deep: #F1E3C9;
            --maroon: #7A1F2B;
            --maroon-deep: #5A1520;
            --gold: #C6952F;
            --gold-light: #EAD08B;
            --ink: #2A1E17;
            --ink-soft: #6B5B4E;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            min-height: 100vh;
            font-family: 'Mukta', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 50% -10%, rgba(198, 149, 47, 0.18), transparent 55%),
                radial-gradient(circle at 50% 110%, rgba(122, 31, 43, 0.10), transparent 55%),
                var(--ivory);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            position: relative;
            overflow: hidden;
        }

        /* faint repeating diamond lattice, like carved stone tracery */
        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(45deg, rgba(122, 31, 43, 0.035) 25%, transparent 25%, transparent 75%, rgba(122, 31, 43, 0.035) 75%),
                linear-gradient(45deg, rgba(122, 31, 43, 0.035) 25%, transparent 25%, transparent 75%, rgba(122, 31, 43, 0.035) 75%);
            background-size: 64px 64px;
            background-position: 0 0, 32px 32px;
            pointer-events: none;
        }

        .home {
            position: relative;
            width: min(720px, 100%);
            text-align: center;
            padding: 56px 40px 48px;
            animation: rise 0.9s ease-out both;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ---------- signature: temple-arch frame with diya ---------- */
        .arch-wrap {
            position: relative;
            width: 172px;
            height: 172px;
            margin: 0 auto 26px;
        }

        .arch-wrap svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .flame {
            transform-origin: center bottom;
            animation: flicker 2.6s ease-in-out infinite;
        }

        @keyframes flicker {

            0%,
            100% {
                transform: scaleY(1) scaleX(1) rotate(0deg);
                opacity: 1;
            }

            25% {
                transform: scaleY(1.08) scaleX(0.94) rotate(-2deg);
                opacity: 0.92;
            }

            50% {
                transform: scaleY(0.94) scaleX(1.05) rotate(1.5deg);
                opacity: 1;
            }

            75% {
                transform: scaleY(1.05) scaleX(0.97) rotate(-1deg);
                opacity: 0.95;
            }
        }

        .brand-mark {
            position: absolute;
            left: 50%;
            top: 58%;
            transform: translate(-50%, -50%);
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(155deg, var(--maroon) 0%, var(--maroon-deep) 100%);
            color: var(--gold-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Rozha One', serif;
            font-size: 30px;
            letter-spacing: 1px;
            box-shadow:
                0 0 0 3px var(--ivory),
                0 0 0 5px var(--gold),
                0 20px 40px rgba(122, 31, 43, 0.28);
        }

        .eyebrow {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3.5px;
            text-transform: uppercase;
            color: var(--maroon);
            margin-bottom: 14px;
        }

        h1 {
            font-family: 'Rozha One', serif;
            font-weight: 400;
            font-size: clamp(36px, 6.2vw, 62px);
            line-height: 1.08;
            letter-spacing: 0.3px;
            color: var(--ink);
            margin-bottom: 18px;
        }

        h1 span {
            display: block;
            background: linear-gradient(90deg, var(--maroon) 0%, #9A2C3A 50%, var(--maroon) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        p.tagline {
            color: var(--ink-soft);
            font-size: clamp(16px, 2.2vw, 19px);
            line-height: 1.75;
            max-width: 520px;
            margin: 0 auto;
            font-weight: 400;
        }

        /* ---------- brass bead-chain divider ---------- */
        .bead-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 32px 0 30px;
        }

        .bead-divider .line {
            height: 1px;
            width: 64px;
            background: linear-gradient(90deg, transparent, var(--gold));
        }

        .bead-divider .line.right {
            background: linear-gradient(90deg, var(--gold), transparent);
        }

        .bead-divider .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
        }

        .bead-divider .dot.center {
            width: 9px;
            height: 9px;
            background: var(--maroon);
            box-shadow: 0 0 0 3px var(--gold-light);
        }

        /* ---------- category strip ---------- */
        .categories {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px 18px;
            font-family: 'Poppins', sans-serif;
        }

        .categories .cat {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: var(--maroon-deep);
            padding: 8px 4px;
            position: relative;
        }

        .categories .sep {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--gold);
            opacity: 0.8;
        }

        @media (prefers-reduced-motion: reduce) {
            .home {
                animation: none;
            }

            .flame {
                animation: none;
            }
        }

        @media (max-width:480px) {
            .home {
                padding: 44px 22px 36px;
            }

            .arch-wrap {
                width: 140px;
                height: 140px;
            }

            .brand-mark {
                width: 72px;
                height: 72px;
                font-size: 24px;
            }
        }

        :focus-visible {
            outline: 2px solid var(--maroon);
            outline-offset: 3px;
        }
    </style>
</head>

<body>
    <main class="home">

        <div class="arch-wrap" aria-hidden="true">
            <svg viewBox="0 0 172 172" xmlns="http://www.w3.org/2000/svg">
                <!-- temple gopuram-style arch -->
                <path d="M20 168 L20 92 C20 45 48 14 86 14 C124 14 152 45 152 92 L152 168"
                    fill="none" stroke="var(--gold)" stroke-width="2.5" stroke-linecap="round" />
                <path d="M10 168 L10 96 C10 40 42 4 86 4 C130 4 162 40 162 96 L162 168"
                    fill="none" stroke="var(--gold)" stroke-width="1" stroke-opacity="0.55" stroke-linecap="round" />
                <!-- diya flame at the apex -->
                <g class="flame" transform="translate(86,-2)">
                    <path d="M0 -18 C6 -12 8 -4 3 2 C1 4 -1 4 -3 2 C-8 -4 -6 -12 0 -18 Z"
                        fill="var(--gold)" />
                    <path d="M0 -12 C3 -8 4 -3 1 0 C0 1 -1 1 -1.5 0 C-4 -3 -3 -8 0 -12 Z"
                        fill="var(--maroon)" />
                </g>
            </svg>
            <div class="brand-mark">GM</div>
        </div>

        <p class="eyebrow">Est. in devotion &amp; craft</p>

        <h1>Ghanshyam<span>Murtibhandar</span></h1>

        <p class="tagline">Traditional murti craftsmanship, devotional essentials, and trusted service for homes, temples, and spiritual spaces.</p>

        <div class="bead-divider" aria-hidden="true">
            <span class="line"></span>
            <span class="dot"></span>
            <span class="dot center"></span>
            <span class="dot"></span>
            <span class="line right"></span>
        </div>

        <div class="categories">
            <span class="cat">Idols</span>
            <span class="sep"></span>
            <span class="cat">Puja Essentials</span>
            <span class="sep"></span>
            <span class="cat">Home Temples</span>
        </div>

    </main>
</body>

</html>
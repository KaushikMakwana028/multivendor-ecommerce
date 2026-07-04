<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> - Ghanshyam Murtibhandar</title>
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
            padding: 48px 20px 60px;
            position: relative;
            overflow-x: hidden;
        }

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

        .page {
            position: relative;
            width: min(820px, 100%);
            margin: 0 auto;
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

        .header {
            text-align: center;
            margin-bottom: 30px;
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
            font-size: clamp(32px, 5.6vw, 54px);
            line-height: 1.1;
            letter-spacing: 0.3px;
            color: var(--ink);
            margin-bottom: 14px;
        }

        .updated {
            font-family: 'Poppins', sans-serif;
            color: var(--ink-soft);
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        .bead-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 26px 0 34px;
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

        .content-card {
            position: relative;
            background: #fff;
            border: 1px solid var(--ivory-deep);
            border-radius: 14px;
            padding: clamp(28px, 5vw, 48px);
            box-shadow: 0 24px 55px rgba(122, 31, 43, 0.10);
        }

        .content-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 8%;
            right: 8%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        #pageContent {
            color: var(--ink);
        }

        #pageContent h2 {
            font-family: 'Rozha One', serif;
            font-weight: 400;
            font-size: clamp(20px, 3vw, 25px);
            color: var(--maroon-deep);
            margin: 30px 0 12px;
        }

        #pageContent h2:first-child {
            margin-top: 0;
        }

        #pageContent p {
            font-size: 16px;
            line-height: 1.85;
            color: var(--ink-soft);
            margin-bottom: 8px;
        }

        #pageContent ul,
        #pageContent ol {
            margin: 6px 0 14px 22px;
            color: var(--ink-soft);
            line-height: 1.8;
        }

        #pageContent li {
            margin-bottom: 10px;
            padding-left: 4px;
        }

        #pageContent li strong {
            color: var(--maroon-deep);
            font-weight: 600;
        }

        #pageContent strong {
            color: var(--ink);
        }

        #pageContent a {
            color: var(--maroon);
            text-decoration: underline;
            text-decoration-color: var(--gold);
        }

        .state-message {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--ink-soft);
            text-align: center;
            padding: 20px 0;
        }

        .state-message.error {
            color: #b91c1c;
        }

        .skeleton-line {
            height: 14px;
            border-radius: 6px;
            background: linear-gradient(90deg, var(--ivory-deep) 25%, #fff 50%, var(--ivory-deep) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            margin-bottom: 12px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .page {
                animation: none;
            }

            .skeleton-line {
                animation: none;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 34px 14px 44px;
            }
        }

        :focus-visible {
            outline: 2px solid var(--maroon);
            outline-offset: 3px;
        }
    </style>
</head>

<body>
    <main class="page">

        <div class="header">
            <p class="eyebrow">Ghanshyam Murtibhandar</p>
            <h1 id="pageTitle"><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="updated">Last updated: <?= date('F j, Y') ?></div>
        </div>

        <div class="bead-divider" aria-hidden="true">
            <span class="line"></span>
            <span class="dot"></span>
            <span class="dot center"></span>
            <span class="dot"></span>
            <span class="line right"></span>
        </div>

        <section class="content-card">
            <div id="pageContent">
                <div class="skeleton-line" style="width: 92%;"></div>
                <div class="skeleton-line" style="width: 78%;"></div>
                <div class="skeleton-line" style="width: 85%;"></div>
            </div>
        </section>
    </main>

    <script>
        fetch("<?= htmlspecialchars($api_url, ENT_QUOTES, 'UTF-8') ?>")
            .then(function(response) {
                return response.json();
            })
            .then(function(result) {
                var data = result && result.data ? result.data : {};
                document.getElementById('pageTitle').textContent = data.title || "<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>";

                var content = document.getElementById('pageContent');
                if (data.content) {
                    // API returns HTML markup (h2/ul/li/strong), so render as markup, not plain text
                    content.innerHTML = data.content;
                } else {
                    content.innerHTML = '<p class="state-message">Content is not available right now.</p>';
                }
            })
            .catch(function() {
                var content = document.getElementById('pageContent');
                content.innerHTML = '<p class="state-message error">Unable to load this page content right now. Please try again later.</p>';
            });
    </script>
</body>

</html>
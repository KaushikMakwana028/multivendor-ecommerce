<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - Ghanshyam Murtibhandar</title>
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
            margin-bottom: 20px;
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
            margin-bottom: 18px;
        }

        .intro {
            color: var(--ink-soft);
            font-size: clamp(15px, 2vw, 17px);
            line-height: 1.8;
            max-width: 620px;
            margin: 0 auto;
        }

        .bead-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 30px 0 34px;
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

        .panel {
            position: relative;
            background: #fff;
            border: 1px solid var(--ivory-deep);
            border-radius: 14px;
            padding: clamp(28px, 5vw, 48px);
            box-shadow: 0 24px 55px rgba(122, 31, 43, 0.10);
        }

        .panel::before {
            content: "";
            position: absolute;
            top: 0;
            left: 8%;
            right: 8%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        h2 {
            font-family: 'Rozha One', serif;
            font-weight: 400;
            font-size: clamp(20px, 3vw, 25px);
            color: var(--maroon-deep);
            margin-bottom: 22px;
        }

        .steps {
            list-style: none;
            counter-reset: step;
            margin-bottom: 30px;
        }

        .steps li {
            counter-increment: step;
            position: relative;
            padding: 12px 0 12px 46px;
            font-size: 16px;
            line-height: 1.7;
            color: var(--ink);
            border-bottom: 1px dashed var(--ivory-deep);
        }

        .steps li:last-child {
            border-bottom: none;
        }

        .steps li::before {
            content: counter(step);
            position: absolute;
            left: 0;
            top: 10px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(155deg, var(--maroon) 0%, var(--maroon-deep) 100%);
            color: var(--gold-light);
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 14px rgba(122, 31, 43, 0.22);
        }

        .note {
            position: relative;
            display: flex;
            gap: 14px;
            border-left: 3px solid var(--maroon);
            background: linear-gradient(90deg, rgba(122, 31, 43, 0.06), transparent);
            border-radius: 0 8px 8px 0;
            padding: 18px 20px;
            margin-top: 30px;
        }

        .note .note-icon {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            margin-top: 2px;
        }

        .note p {
            color: var(--ink-soft);
            font-size: 15px;
            line-height: 1.75;
        }

        .note strong {
            color: var(--maroon-deep);
        }

        @media (prefers-reduced-motion: reduce) {
            .page {
                animation: none;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 34px 14px 44px;
            }

            .steps li {
                padding-left: 40px;
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
            <h1>Delete Account</h1>
            <p class="intro">You can request account deletion from inside the Ghanshyam Murtibhandar app. Deleting your account removes your saved cart, delivery addresses, and disables your user profile.</p>
        </div>

        <div class="bead-divider" aria-hidden="true">
            <span class="line"></span>
            <span class="dot"></span>
            <span class="dot center"></span>
            <span class="dot"></span>
            <span class="line right"></span>
        </div>

        <section class="panel">
            <h2>Steps to delete your account</h2>

            <ol class="steps">
                <li>Open the Ghanshyam Murtibhandar mobile app.</li>
                <li>Login with your registered mobile number.</li>
                <li>Go to your profile or account section.</li>
                <li>Select the delete account option.</li>
                <li>Confirm the action when the app asks for final confirmation.</li>
            </ol>

            <div class="note">
                <svg class="note-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 9v4M12 16.5h.01M10.29 3.86 1.82 18a1.5 1.5 0 0 0 1.29 2.25h17.78A1.5 1.5 0 0 0 22.18 18L13.71 3.86a1.5 1.5 0 0 0-2.42 0Z"
                        stroke="#7A1F2B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p><strong>Please note:</strong> after confirmation, your account is marked inactive and your cart and saved delivery addresses are removed. This action is intended to be permanent.</p>
            </div>
        </section>
    </main>

</body>

</html>
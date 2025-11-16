<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Vujade Project System</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@700&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="https://vujadesa.com/img/favicon.png"/>
    <style>
        body {
            background: linear-gradient(135deg, #190A2C 0%, #3B2667 100%);
            min-height: 100vh;
            font-family: 'Nunito', 'Rubik', Arial, sans-serif;
            margin: 0;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            position: relative;
        }

        .vujade-logo {
            width: 110px;
            margin-bottom: 2rem;
            filter: drop-shadow(0 2px 10px rgba(59,38,103,0.16));
            animation: float 2.6s ease-in-out infinite alternate;
        }

        @keyframes float {
            to { transform: translateY(-12px);}
            from { transform: translateY(0);}
        }

        h1 {
            font-family: 'Rubik', sans-serif;
            font-size: 2.7rem;
            font-weight: 700;
            margin-bottom: .5rem;
            letter-spacing: .01em;
            color: #fff;
            background: linear-gradient(100deg, #a770ef 20%, #f6d365 60%, #fccb90 85%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle {
            font-size: 1.15rem;
            font-weight: 400;
            color: #ffffffcc;
            margin-bottom: 2.4rem;
            line-height: 1.6;
            letter-spacing: 0.02em;
        }

        .cta-buttons {
            display: flex;
            gap: 1.3rem;
        }
        .btn {
            padding: .85rem 2.2rem;
            font-size: 1.08rem;
            font-weight: bold;
            border-radius: 34px;
            border: none;
            background: linear-gradient(90deg, #34e8f7 0%, #a770ef 60%, #fccb90 110%);
            color: #222;
            text-decoration: none;
            transition: box-shadow .22s, transform .15s;
            box-shadow: 0 4px 24px 0 rgba(58,35,89,0.20);
            outline: none;
            cursor: pointer;
        }
        .btn:hover {
            box-shadow: 0 8px 36px 0 rgba(50,35,71,0.22);
            transform: translateY(-3px) scale(1.03);
        }

        .footer-link {
            margin-top: 3.8rem;
            font-size: .94rem;
            color: #d4ceef;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity .19s;
            letter-spacing: 0.01em;
        }
        .footer-link:hover {
            opacity: 1;
            color: #fff3d1;
            text-shadow: 0 1px 5px #a770ef88;
        }
        .bubbles {
            position: absolute;
            left: 0; top: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .bubble {
            position: absolute;
            border-radius: 50%;
            opacity: 0.11;
            filter: blur(1.3px);
            animation: rise 14s infinite linear;
        }
        @keyframes rise {
            from { transform: translateY(0) scale(1);}
            to   { transform: translateY(-180px) scale(1.08);}
        }
    </style>
</head>
<body>
    <div class="bubbles">
        <div class="bubble" style="width:130px;height:130px;left:12vw;top:60vh;background:#a770ef;animation-delay:0s;"></div>
        <div class="bubble" style="width:90px;height:90px;left:55vw;top:72vh;background:#34e8f7;animation-delay:2s;"></div>
        <div class="bubble" style="width:65px;height:65px;left:78vw;top:66vh;background:#fccb90;animation-delay:1s;"></div>
        <div class="bubble" style="width:120px;height:120px;left:30vw;top:80vh;background:#f6d365;animation-delay:3.2s;"></div>
        <div class="bubble" style="width:80px;height:80px;left:87vw;top:85vh;background:#a770ef;animation-delay:5s;"></div>
    </div>
    <main style="z-index:2;position:relative;text-align:center;">
        <img class="vujade-logo" src="https://vujadesa.com/img/vujade-logo.svg" alt="Vujade Logo" />
        <h1>Welcome to Vujade</h1>
        <div class="subtitle">
            Reimagining project collaboration.<br>
            The next generation project and idea platform by <a href="https://vujadesa.com" style="color:#34e8f7;font-weight:700;text-decoration:none;" target="_blank">Vujadesa</a>.
        </div>
        <div class="cta-buttons">
            <a href="{{ route('login') }}" class="btn">Sign In</a>
            <a href="{{ route('register') }}" class="btn" style="background:linear-gradient(90deg,#fccb90 0%,#a770ef 100%);color:#432b67;">Get Started</a>
            <a href="https://vujadesa.com" class="btn" style="background:linear-gradient(100deg,#f6d365,#fccb90);color:#634f25;" target="_blank">Learn More</a>
        </div>
        <a class="footer-link" href="https://vujadesa.com" target="_blank">
            &copy; {{ date('Y') }} Vujadesa — Explore ideas. Unlock progress.
        </a>
    </main>
</body>
</html>

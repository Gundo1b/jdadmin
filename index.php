<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('APP_INIT', true);
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to JD Tutoring</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0d6efd;
            --accent: #c014e2;
            --text-main: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
            overflow: hidden;
            position: relative;
        }

        body::before,
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            z-index: -1;
        }

        body::before {
            background: var(--primary);
            top: -100px;
            left: -100px;
            animation: move 15s infinite alternate;
        }

        body::after {
            background: var(--accent);
            bottom: -100px;
            right: -100px;
            animation: move 20s infinite alternate-reverse;
        }

        @keyframes move {
            to { transform: translate(100px, 100px); }
        }

        .glass-card {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            padding: 50px;
            width: 420px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        }

        .logo-container {
            width: 140px;
            height: 140px;
            background: white;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .status-text {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 25px;
        }

        .progress-container {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 5s ease;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 11px;
            color: #94a3b8;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="glass-card">
        <div class="logo-container">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="JD Tutoring Logo" style="max-width:100%;">
        </div>

        <h1>JD TUTORING</h1>
        <div class="status-text" id="status">Initializing...</div>

        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <div class="footer-note">Learning to Accelerate</div>
    </div>

    <script>
        const status = document.getElementById('status');
        const bar = document.getElementById('progressBar');

        const messages = [
            "Connecting to secure server...",
            "Fetching academic resources...",
            "Syncing curriculum data...",
            "Preparing your workspace...",
            "Finalizing dashboard...",
            "Welcome back!"
        ];

        let i = 0;
        const interval = setInterval(() => {
            if (i < messages.length) status.innerText = messages[i++];
        }, 800);

        setTimeout(() => bar.style.width = '100%', 100);

        setTimeout(() => {
            clearInterval(interval);
            window.location.href = "<?= BASE_URL ?>admin/dashboard.php";
        }, 5500);
    </script>
</body>
</html>

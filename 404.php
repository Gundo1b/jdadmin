<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | JD Tutoring</title>
    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --accent-color: #6610f2;
        }

        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            position: relative;
        }

        .error-code {
            font-size: 10rem;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0;
            filter: drop-shadow(0 10px 20px rgba(13, 110, 253, 0.2));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .error-message {
            font-size: 1.5rem;
            color: #495057;
            margin: 20px 0 30px;
            font-weight: 500;
        }

        .btn-home {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
        }

        .decoration {
            position: absolute;
            z-index: -1;
            opacity: 0.1;
        }

        .circle-1 {
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: var(--primary-color);
        }

        .circle-2 {
            bottom: -80px;
            right: -20px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: var(--accent-color);
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="decoration circle-1"></div>
        <div class="decoration circle-2"></div>

        <h1 class="error-code">404</h1>
        <p class="error-message">Oops! The page you're looking for has vanished into thin air.</p>

        <div class="d-flex flex-column align-items-center gap-3">
            <a href="/jdtoturing/admin/dashboard.php" class="btn btn-primary btn-home">
                <i class="bi bi-house-door-fill me-2"></i> Take Me Home
            </a>
            <a href="javascript:history.back()" class="btn btn-link link-secondary text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Go Back
            </a>
        </div>
    </div>
</body>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #121212;
            font-family: Arial, sans-serif;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background-color: #1c1c1c;
            border-radius: 8px;
            padding: 20px;
            width: 80%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #ffcc00;
        }

        p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        a {
            color: #00b7ff;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    @include('sweetalert::alert')
    <div class="card">
        <h1>Please log in to the Telegram bot to get your login link:</h1>
        <p>Log in to the video bot: <a href="https://t.me/videoweb00_bot" target="_blank">Video bot</a></p>
        <p>Log in to the photo bot: <a href="https://t.me/photoweb00_bot" target="_blank">Photo bot</a></p>
    </div>
</body>
</html>

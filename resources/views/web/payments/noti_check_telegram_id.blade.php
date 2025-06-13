<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Đặt nền đen cho toàn bộ trang */
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

        /* Card chứa nội dung */
        .card {
            background-color: #1c1c1c;
            border-radius: 8px;
            padding: 20px;
            width: 80%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        /* Tiêu đề */
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #ffcc00;
        }

        /* Thông tin và liên kết */
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
        <h1>Vui lòng đăng nhập vào bot Telegram để lấy link đăng nhập:</h1>
        <p>Đăng nhập vào bot video: <a href="https://t.me/videoweb00_bot" target="_blank">Video bot</a></p>
        <p>Đăng nhập vào bot photo: <a href="https://t.me/photoweb00_bot" target="_blank">Photo bot</a></p>
    </div>
</body>
</html>

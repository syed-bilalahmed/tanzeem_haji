<?php
include 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "برائے مہربانی یوزر نیم اور پاس ورڈ درج کریں۔";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "غلط یوزر نیم یا پاس ورڈ۔";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لاگ ان - تنظیم اولاد حضرت حاجی بہادر</title>
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Jameel+Noori+Nastaleeq&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            /* Professional Gradient Background */
            background: linear-gradient(rgba(0, 77, 64, 0.9), rgba(0, 77, 64, 0.8)), url('background.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Jameel Noori Nastaleeq', 'Poppins', sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            text-align: center;
            border-top: 5px solid #ffd700; /* Gold Line */
        }
        .login-logo {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffd700;
            padding: 2px;
            background: white;
            margin-top: -80px; /* Pull logo up */
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ccc;
            background: #f8f9fa;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #004d40;
            background: #fff;
        }
        .btn-primary {
            background: #004d40;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #00382e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 77, 64, 0.3);
        }
        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 20px;
            display: inline-block;
        }
        .back-link:hover {
            color: #004d40;
        }
    </style>
</head>
<body>
    <div class="login-card animate__animated animate__fadeInUp">
        <img src="logo.jpeg" alt="Logo" class="login-logo">
        <h3 class="mb-4 fw-bold text-dark">ایڈمن لاگ ان</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger rounded-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 text-end">
                <label for="username" class="form-label small text-muted">یوزر نیم</label>
                <input type="text" class="form-control" id="username" name="username" required placeholder="User Name">
            </div>
            <div class="mb-4 text-end">
                <label for="password" class="form-label small text-muted">پاس ورڈ</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Password">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">لاگ ان (Login)</button>
            </div>
        </form>
        
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> واپس مرکزی صفحہ پر جائیں</a>
    </div>
</body>
</html>

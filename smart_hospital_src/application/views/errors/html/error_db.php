<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$base_url = config_item('base_url');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Error</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .fallback-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            max-width: 500px;
            width: 90%;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .fallback-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 5px;
            background: linear-gradient(90deg, #d63031, #ff7675);
        }
        .icon-container {
            width: 80px; height: 80px;
            background: #ffeaa7;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            color: #d63031;
        }
        .icon-container svg { width: 40px; height: 40px; }
        h1 { font-size: 24px; font-weight: 700; color: #2d3436; margin-bottom: 16px; }
        .message { font-size: 15px; color: #636e72; line-height: 1.6; margin-bottom: 32px; word-wrap: break-word; text-align: left; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee; overflow-x: auto; }
        .message p { margin-bottom: 8px; }
        .message p:last-child { margin-bottom: 0; }
        .actions { display: flex; gap: 16px; justify-content: center; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: all 0.2s ease; border: none;
        }
        .btn-primary {
            background: #d63031; color: white;
            box-shadow: 0 4px 15px rgba(214, 48, 49, 0.3);
        }
        .btn-primary:hover {
            background: #c0392b; transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(214, 48, 49, 0.4);
        }
        .btn-secondary { background: #dfe6e9; color: #2d3436; }
        .btn-secondary:hover { background: #b2bec3; transform: translateY(-2px); }
        @media (max-width: 480px) {
            .actions { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="fallback-container">
        <div class="icon-container">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m-4 5h4m-4 4h4"></path>
            </svg>
        </div>
        <h1><?php echo $heading; ?></h1>
        <div class="message">
            <?php echo $message; ?>
        </div>
        <div class="actions">
            <button class="btn btn-secondary" onclick="window.history.back()">Go Back</button>
            <a href="<?php echo $base_url; ?>" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Smart Timetable Generator</title>
    @include('partials.styles')
    <style>
        body, html {
            margin: 0; padding: 0; height: 100%;
            font-family: 'Segoe UI', sans-serif;
            background-color: #1b2a41;
        }
        .login-wrapper {
            display: flex;
            height: 100vh;
        }
        .login-left {
            flex: 1;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #1b2a41;
        }
        .brand-name {
            position: absolute;
            top: 20px;
            left: 40px;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            user-select: none;
            opacity: 0;
            animation: fadeSlideIn 1.2s forwards;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .brand-name::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background-color: rgba(255,255,255,0.4);
            margin-top: 5px;
            border-radius: 2px;
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-card {
            width: 100%;
            max-width: 350px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            color: #000;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }
        .login-card .logo {
            width: 50px;
            margin-bottom: 10px;
        }
        .login-card h2, .login-card p, .login-card label, .login-card a {
            color: #000;
        }
        .login-card h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .login-card p {
            margin-bottom: 20px;
            opacity: 0.9;
        }
        .login-card .form-group {
            margin-bottom: 15px;
        }
        .login-card input[type="email"],
        .login-card input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
            color: #000;
        }
        .login-card input::placeholder {
            color: rgba(0,0,0,0.6);
        }
        .login-card button {
            width: 100%;
            padding: 10px;
            background-color: #1b2a41;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-card button:hover {
            background-color: #243a5a;
        }
        .login-right {
            flex: 1;
            background: url('{{ asset('images/Time.jpg') }}') no-repeat center center;
            background-size: cover;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-left">
        <div class="brand-name">Smart Timetable Generator</div>
        <div class="login-card">
            <img src="{{ asset('images/Tau.logo.jpeg') }}" alt="Logo" class="logo">
            <h2>Reset Password</h2>
            <p>Enter your email to reset your password</p>
            <form method="POST" action="{{ URL::to('/request_reset') }}">
                {!! csrf_field() !!}
                @include('errors.form_errors')

                <div class="form-group">
                    <input type="email" name="email" placeholder="Your email" required>
                </div>

                @if (!empty($user->security_question))
                <div class="form-group">
                    <p>{{ $user->security_question->question }}</p>
                </div>
                <div class="form-group">
                    <input type="text" name="security_question_answer" placeholder="Your answer" required>
                </div>
                @endif

                <button type="submit">Submit</button>
                <div style="text-align:center; margin-top: 15px;">
    <a href="{{ url('/login') }}" style="color:#1b2a41; text-decoration:none; font-size:14px;">
        &larr; Back to login
    </a>
</div>

            </form>
        </div>
    </div>
    <div class="login-right"></div>
</div>
@include('partials.scripts')
</body>
</html>

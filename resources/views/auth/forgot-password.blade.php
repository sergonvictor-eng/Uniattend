<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - UniAttend</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-weight: 500;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 0;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1200px;
            min-height: 100vh;
            background: white;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            border-radius: 20px;
            overflow: hidden;
            margin: 20px;
        }

        .login-image-section {
            flex: 1;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .login-image-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
        }

        .login-form-section {
            flex: 1;
            padding: 3rem;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            color: #001f3f;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
        }

        .logo p {
            color: #666;
            font-size: 0.9rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-text h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-family: 'Montserrat', sans-serif;
        }

        .welcome-text p {
            color: #666;
            font-size: 0.9rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
        }

        .form-group input:focus {
            outline: none;
            border-color: #001f3f;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 0.75rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: #001f3f;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-submit:hover {
            background-color: #003366;
        }

        .back-to-login {
            text-align: center;
            margin-top: 2rem;
        }

        .back-to-login a {
            color: #001f3f;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
        }

        .back-to-login a:hover {
            color: #003366;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            
            .login-image-section {
                min-height: 200px;
            }
            
            .login-form-section {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            .login-form-section {
                padding: 1.5rem;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-image-section">
            <img src="https://picsum.photos/seed/university-student-campus/800/1200.jpg" alt="University Campus">
        </div>
        
        <div class="login-form-section">
            <div class="login-container">
                <div class="logo">
                    <h1>UniAttend</h1>
                    <p>University Attendance Management System</p>
                </div>

                <div class="welcome-text">
                    <h2>Forgot Password?</h2>
                    <p>Enter your email address and we'll reset your password to the default password.</p>
                </div>

                @if(session('success'))
                    <div class="success-message">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="error-message">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            autofocus
                            value="{{ old('email') }}"
                            placeholder="Enter your registered email address"
                        >
                    </div>

                    <button type="submit" class="btn-submit">Reset Password</button>
                </form>

                <div class="back-to-login">
                    <a href="{{ route('login') }}">← Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

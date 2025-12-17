<!DOCTYPE html>
<html lang="id" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - Ruang Lari</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    <link href="{{ asset('vendor/bootstrap-select/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <div class="text-center mb-3">
                                    <a href="{{ route('login') }}"><img src="{{ asset('images/logo-full.png') }}" alt="Ruang Lari" width="250px"></a>
                                    </div>
                                    <h4 class="text-center mb-4">Daftar Akun Baru</h4>
                                    
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('register') }}">
                                        @csrf
                                        
                                        <div class="form-group">
                                            <label class="mb-1 form-label">Nama Lengkap</label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="mb-1 form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="mb-1 form-label">Password</label>
                                            <input type="password" id="dz-password" name="password" class="form-control" required>
                                            <span class="show-pass eye">
                                                <i class="fa fa-eye-slash"></i>
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="mb-1 form-label">Konfirmasi Password</label>
                                            <input type="password" id="dz-password-confirm" name="password_confirmation" class="form-control" required>
                                            <span class="show-pass eye">
                                                <i class="fa fa-eye-slash"></i>
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="mb-1 form-label">Daftar Sebagai</label>
                                            <select name="role" class="form-control" required>
                                                <option value="runner" {{ (old('role', $role ?? 'runner')) == 'runner' ? 'selected' : '' }}>Runner</option>
                                                <option value="coach" {{ (old('role', $role ?? '')) == 'coach' ? 'selected' : '' }}>Coach</option>
                                                <option value="eo" {{ (old('role', $role ?? '')) == 'eo' ? 'selected' : '' }}>Event Organizer</option>
                                            </select>
                                            <small class="form-text text-muted">Admin hanya bisa dibuat melalui seeder</small>
                                        </div>
                                        
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary light btn-block">Daftar</button>
                                        </div>
                                    </form>
                                    <div class="new-account mt-3">
                                        <p>Sudah punya akun? <a class="text-primary" href="{{ route('login') }}">Masuk</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
</body>
</html>


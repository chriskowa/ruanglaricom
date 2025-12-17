@extends('layouts.app')

@section('title', 'Profile')

@section('page-title', 'Profile')

@push('styles')
    <link href="{{ asset('vendor/lightgallery/dist/css/lightgallery.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/lightgallery/dist/css/lg-thumbnail.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/lightgallery/dist/css/lg-zoom.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(auth()->user()->role . '.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active"><a href="javascript:void(0)">Profile</a></li>
    </ol>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="profile card card-body px-3 pt-3 pb-0">
            <div class="profile-head">
                <div class="photo-content">
                    <div class="cover-photo" style="background-image: url('{{ $user->banner ? asset('storage/' . $user->banner) : asset('images/profile/1.jpg') }}'); background-size: cover; background-position: center; height: 200px;"></div>
                </div>
                <div class="profile-info">
                    <div class="profile-photo">
                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/profile/profile.png') }}" class="img-fluid rounded-circle" alt="">
                    </div>
                    <div class="profile-details">
                        <div class="profile-name px-3 pt-2">
                            <h4 class="text-primary mb-0">{{ $user->name }}</h4>
                            <p>{{ ucfirst($user->role) }}</p>
                        </div>
                        <div class="profile-email px-2 pt-2">
                            <h4 class="text-muted mb-0">{{ $user->email }}</h4>
                            <p>Email</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card h-auto">
            <div class="card-body">
                <div class="profile-statistics mb-5">
                    <div class="text-center">
                        <div class="row">
                            <div class="col">
                                <h3 class="m-b-0">{{ $user->wallet ? number_format($user->wallet->balance, 0, ',', '.') : 0 }}</h3>
                                <span>Saldo Wallet</span>
                            </div>
                            <div class="col">
                                <h3 class="m-b-0">{{ $user->city ? $user->city->name : '-' }}</h3>
                                <span>Kota</span>
                            </div>
                            <div class="col">
                                <h3 class="m-b-0">{{ ucfirst($user->package_tier) }}</h3>
                                <span>Package</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($user->profile_images && count($user->profile_images) > 0)
                <div class="profile-interest mb-5">
                    <h4 class="text-primary d-inline">Profile Images</h4>
                    <div class="row mt-3" id="lightgallery">
                        @foreach($user->profile_images as $index => $profileImage)
                            <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                                <a href="{{ asset('storage/' . $profileImage) }}" data-exthumbimage="{{ asset('storage/' . $profileImage) }}" data-src="{{ asset('storage/' . $profileImage) }}" class="lg-item">
                                    <img src="{{ asset('storage/' . $profileImage) }}" alt="Profile Image {{ $index + 1 }}" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-xl-8">
        <div class="card h-auto">
            <div class="card-body">
                <div class="profile-tab">
                    <div class="custom-tab-1">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a href="#about-me" data-bs-toggle="tab" class="nav-link active show">About Me</a></li>
                            <li class="nav-item"><a href="#profile-settings" data-bs-toggle="tab" class="nav-link">Setting</a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="about-me" class="tab-pane fade active show">
                                <div class="profile-about-me">
                                    <div class="pt-4 border-bottom-1 pb-3">
                                        <h4 class="text-primary">About Me</h4>
                                        <p class="mb-2">{{ $user->address ?? 'Belum ada informasi alamat.' }}</p>
                                    </div>
                                </div>
                                <div class="profile-personal-info mt-4">
                                    <h4 class="text-primary mb-4">Personal Information</h4>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Nama <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ $user->name }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Email <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ $user->email }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Phone <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ $user->phone ?? '-' }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Tanggal Lahir <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ $user->date_of_birth ? $user->date_of_birth->format('d M Y') : '-' }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Jenis Kelamin <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ $user->gender ? ($user->gender == 'male' ? 'Laki-laki' : 'Perempuan') : '-' }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Kota <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ $user->city ? $user->city->name : '-' }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Alamat <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ $user->address ?? '-' }}</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-3 col-5">
                                            <h5 class="f-w-500">Role <span class="pull-right">:</span></h5>
                                        </div>
                                        <div class="col-sm-9 col-7"><span>{{ ucfirst($user->role) }}</span></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="profile-settings" class="tab-pane fade">
                                <div class="pt-3">
                                    <div class="settings-form">
                                        <h4 class="text-primary">Account Setting</h4>
                                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Nama</label>
                                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Email</label>
                                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Phone</label>
                                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                                                    @error('phone')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Tanggal Lahir</label>
                                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}" class="form-control @error('date_of_birth') is-invalid @enderror">
                                                    @error('date_of_birth')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Jenis Kelamin</label>
                                                    <select name="gender" class="form-control default-select @error('gender') is-invalid @enderror">
                                                        <option value="">Pilih Jenis Kelamin</option>
                                                        <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                                        <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                                                    </select>
                                                    @error('gender')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Alamat</label>
                                                <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Kota</label>
                                                <select name="city_id" class="form-control default-select @error('city_id') is-invalid @enderror">
                                                    <option value="">Pilih Kota</option>
                                                    @foreach($cities as $city)
                                                        <option value="{{ $city->id }}" {{ old('city_id', $user->city_id) == $city->id ? 'selected' : '' }}>
                                                            {{ $city->name }}, {{ $city->province->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('city_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Avatar</label>
                                                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                                                @error('avatar')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if($user->avatar)
                                                    <small class="text-muted">Avatar saat ini: <a href="{{ asset('storage/' . $user->avatar) }}" target="_blank">Lihat</a></small>
                                                @endif
                                                <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF. Maksimal 5MB. Otomatis di-compress 75% dan convert ke WebP.</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Banner</label>
                                                <input type="file" name="banner" class="form-control @error('banner') is-invalid @enderror" accept="image/*">
                                                @error('banner')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if($user->banner)
                                                    <div class="mt-2">
                                                        <img src="{{ asset('storage/' . $user->banner) }}" alt="Banner" class="img-fluid rounded" style="max-height: 200px;">
                                                        <small class="text-muted d-block mt-1">Banner saat ini: <a href="{{ asset('storage/' . $user->banner) }}" target="_blank">Lihat</a></small>
                                                    </div>
                                                @endif
                                                <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF. Maksimal 5MB. Otomatis di-compress 75% dan convert ke WebP.</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Profile Images (Maksimal 3 gambar)</label>
                                                <input type="file" name="profile_images[]" class="form-control @error('profile_images.*') is-invalid @enderror" accept="image/*" multiple>
                                                @error('profile_images.*')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if($user->profile_images && count($user->profile_images) > 0)
                                                    <div class="row mt-2">
                                                        @foreach($user->profile_images as $index => $profileImage)
                                                            <div class="col-md-4 mb-2">
                                                                <img src="{{ asset('storage/' . $profileImage) }}" alt="Profile Image {{ $index + 1 }}" class="img-fluid rounded" style="max-height: 150px;">
                                                                <small class="text-muted d-block mt-1">Image {{ $index + 1 }}</small>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF. Maksimal 5MB per gambar. Otomatis di-compress 75% dan convert ke WebP. Maksimal 3 gambar.</small>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                                    @error('password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Konfirmasi Password</label>
                                                    <input type="password" name="password_confirmation" class="form-control">
                                                </div>
                                            </div>
                                            <button class="btn btn-primary" type="submit">Update Profile</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/lightgallery/dist/lightgallery.min.js') }}"></script>
    <script src="{{ asset('vendor/lightgallery/dist/plugins/thumbnail/lg-thumbnail.min.js') }}"></script>
    <script src="{{ asset('vendor/lightgallery/dist/plugins/zoom/lg-zoom.min.js') }}"></script>
    <script>
        // Initialize LightGallery for profile images
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('lightgallery')) {
                lightGallery(document.getElementById('lightgallery'), {
                    plugins: [lgThumbnail, lgZoom],
                    speed: 500,
                });
            }
        });
    </script>
@endpush


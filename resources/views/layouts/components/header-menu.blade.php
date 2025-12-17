<ul class="navbar-nav header-right">
    <li class="nav-item">
        <form>
            <div class="input-group search-area d-lg-inline-flex d-none me-3">
                <span class="input-group-text" id="header-search">
                    <button class="bg-transparent border-0" type="button" aria-label="header-search">
                        <i class="flaticon-381-search-2"></i>
                    </button>
                </span>
                <input type="text" class="form-control" placeholder="Search here" aria-label="Username" aria-describedby="header-search">
            </div>
        </form>
    </li>
    <li class="nav-item dropdown notification_dropdown">
        <a class="nav-link bell dz-theme-mode" href="javascript:void(0);" aria-label="theme-mode">
            <i id="icon-light" class="fas fa-sun"></i>
            <i id="icon-dark" class="fas fa-moon"></i>
        </a>
    </li>
    <li class="nav-item dropdown notification_dropdown">
        <a class="nav-link ai-icon" href="javascript:void(0)" aria-label="bell" role="button" data-bs-toggle="dropdown" id="notification-dropdown">
            @include('layouts.components.svg-bell')
            <div class="pulse-css"></div>
            <span class="badge badge-danger notification-count d-none">0</span>
        </a>
        <div class="dropdown-menu rounded dropdown-menu-end" id="notification-dropdown-menu">
            <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3 height380">
                <ul class="timeline" id="notification-list">
                    <li class="text-center p-3">
                        <p class="text-muted mb-0">Memuat notifikasi...</p>
                    </li>
                </ul>
            </div>
            <a class="all-notification" href="{{ route('notifications.index') }}">Lihat semua notifikasi <i class="ti-arrow-right"></i></a>
        </div>
    </li>
    <li class="nav-item dropdown notification_dropdown">
        <a class="nav-link bell bell-link" href="javascript:void(0)" aria-label="Chat" id="chatbox-toggle">
            @include('layouts.components.svg-chat')
            <div class="pulse-css"></div>
        </a>
        <div class="dropdown-menu dropdown-menu-end rounded">
            <div id="DZ_W_TimeLine11Home" class="widget-timeline dz-scroll style-1 p-3 height370">
                <ul class="timeline">
                    <li>
                        <div class="timeline-badge primary"></div>
                        <a class="timeline-panel text-muted" href="#">
                            <span>10 minutes ago</span>
                            <h6 class="mb-0">Youtube, a video-sharing website, goes live <strong class="text-primary">$500</strong>.</h6>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </li>
    <li class="nav-item dropdown header-profile">
        <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
            <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/profile/17.jpg') }}" width="20" alt="{{ auth()->user()->name }}">
            <div class="header-info">
                <span class="text-black"><strong>{{ auth()->user()->name }}</strong></span>
                <p class="fs-12 mb-0">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="{{ route('profile.show') }}" class="dropdown-item ai-icon">
                @include('layouts.components.svg-user')
                <span class="ms-2">Profile</span>
            </a>
            <a href="{{ route('chat.index') }}" class="dropdown-item ai-icon">
                @include('layouts.components.svg-inbox')
                <span class="ms-2">Inbox</span>
            </a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();" class="dropdown-item ai-icon">
                @include('layouts.components.svg-logout')
                <span class="ms-2">Logout</span>
            </a>
            <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </li>
</ul>


<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="{{ route('home') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">{{ __('nav.home') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">{{ __('nav.contact') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('kvkk') }}">{{ __('nav.kvkk') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('terms') }}">{{ __('nav.terms') }}</a></li>
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">{{ __('nav.dashboard') }}</a></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-primary ms-lg-3">{{ __('nav.logout') }}</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('nav.login') }}</a></li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-3" href="{{ route('register') }}">{{ __('nav.register') }}</a>
                    </li>
                @endauth
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        {{ strtoupper(app()->getLocale()) }}
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <form method="POST" action="{{ route('locale.switch', 'tr') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">Türkçe</button>
                            </form>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('locale.switch', 'en') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">English</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<footer class="bg-white border-top py-4 mt-5">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <p class="mb-2 mb-md-0 text-muted">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('footer.rights') }}</p>
        <div class="d-flex gap-3">
            <a href="{{ route('kvkk') }}" class="text-decoration-none text-muted">{{ __('nav.kvkk') }}</a>
            <a href="{{ route('terms') }}" class="text-decoration-none text-muted">{{ __('nav.terms') }}</a>
            <a href="mailto:destek@interaktifkredi.com.tr" class="text-decoration-none text-muted">destek@interaktifkredi.com.tr</a>
        </div>
    </div>
</footer>

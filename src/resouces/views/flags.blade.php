@translationsolutioneasyCss()
@php($transFlags = __('flags.'. LaravelLocalization::getCurrentLocale()))
<li class="nav-item dropdown flags">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownFlags" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <img src="{{ asset("/vendor/gsferro/translationsolutioneasy/flags/". LaravelLocalization::getCurrentLocale().".png") }}"
             alt="bandeira {{ $transFlags }}"
        >
        {{ $transFlags }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownFlags">
        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
            @if ($localeCode != app()->getLocale())
                <a rel="alternate" hreflang="{{ $localeCode }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                    <img src="{{ asset("/vendor/gsferro/translationsolutioneasy/flags/{$localeCode}.png") }}"
                         alt="bandeira {{ $transFlags }}"
                    >
                    {{  __('flags.' . $localeCode) }}
                </a>
            @endif
        @endforeach
    </div>
</li>
{{--
    Contact form fields only (no section wrapper or card chrome).

    Expected locals:
      - $profile — public profile array (needs slug)
--}}
@php
    $slug = $profile['slug'] ?? '';
    $onSubdomain = app(\App\Services\Profile\ProfileDomain::class)
        ->slugFromHost(request()->getHost()) === $slug;
    $contactAction = $onSubdomain
        ? url('/contact')
        : route('public-profile.contact', $slug);
@endphp

@if(session('status'))
    <div class="cv-contact__alert cv-contact__alert--success" role="status">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="cv-contact__alert cv-contact__alert--error" role="alert">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        <ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

@if($slug !== '')
    <form method="POST" action="{{ $contactAction }}" class="cv-contact__form" novalidate>
        @csrf

        <div style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true">
            <label for="website-hp">Leave this empty</label>
            <input type="text" name="website" id="website-hp" tabindex="-1" autocomplete="off">
        </div>

        <div class="cv-contact__row">
            <div class="cv-contact__field">
                <label for="cf-name" class="cv-contact__label">{{ __('messages.public_profile.contact.name') }}</label>
                <input id="cf-name" name="name" type="text" required maxlength="120" autocomplete="name" value="{{ old('name') }}" class="cv-contact__input">
            </div>
            <div class="cv-contact__field">
                <label for="cf-email" class="cv-contact__label">{{ __('messages.public_profile.contact.email') }}</label>
                <input id="cf-email" name="email" type="email" required maxlength="191" autocomplete="email" inputmode="email" value="{{ old('email') }}" class="cv-contact__input">
            </div>
        </div>
        <div class="cv-contact__field">
            <label for="cf-subject" class="cv-contact__label">{{ __('messages.public_profile.contact.subject') }}</label>
            <input id="cf-subject" name="subject" type="text" maxlength="180" value="{{ old('subject') }}" class="cv-contact__input">
        </div>
        <div class="cv-contact__field">
            <label for="cf-message" class="cv-contact__label">{{ __('messages.public_profile.contact.message') }}</label>
            <textarea id="cf-message" name="message" required minlength="10" maxlength="4000" rows="5" class="cv-contact__input cv-contact__input--textarea">{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="cv-contact__submit">{{ __('messages.public_profile.contact.send') }}</button>
    </form>
@endif

@once
    <style>
        .cv-contact__alert { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 14px; line-height: 1.5; margin-bottom: 12px; }
        .cv-contact__alert svg { flex-shrink: 0; margin-top: 2px; }
        .cv-contact__alert--success { background: #e6f5ec; color: #1f7a4f; border: 1px solid #c2e7d3; }
        .cv-contact__alert--error { background: #fdecea; color: #c0392b; border: 1px solid #f5c6c0; }
        .cv-contact__alert ul { margin: 0; padding-inline-start: 18px; }
    </style>
@endonce

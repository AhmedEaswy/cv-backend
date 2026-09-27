{{--
    Default wrapped contact form for public profile templates.

    Expected locals:
      - $profile — array from PublicProfileDataMapper::formatPublicProfileResponse
--}}
@php
    $enableContact = (bool) ($profile['enable_contact_form'] ?? false);
@endphp

@if($enableContact)
    <section id="contact-form" class="cv-contact" aria-labelledby="cv-contact-heading">
        <div class="cv-contact__head">
            <span class="cv-contact__eyebrow">{{ __('messages.public_profile.contact.eyebrow') }}</span>
            <h2 id="cv-contact-heading" class="cv-contact__title">{{ __('messages.public_profile.contact.title') }}</h2>
            <p class="cv-contact__text">{{ __('messages.public_profile.contact.text') }}</p>
        </div>

        @include('components.public-profile._contact_fields', ['profile' => $profile])
    </section>

    <style>
        .cv-contact { margin: 48px auto; max-width: 640px; padding: 28px; border-radius: 18px; background: var(--paper, #fbfaf7); border: 1px solid var(--line, #e8e3d7); font-family: 'Inter', 'Figtree', system-ui, sans-serif; }
        html[dir="rtl"] .cv-contact { font-family: var(--font-ar-sans, 'Cairo', 'IBM Plex Sans Arabic', sans-serif); }
        .cv-contact__head { margin-bottom: 18px; }
        .cv-contact__eyebrow { display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--brand-primary, #5c17e7); margin-bottom: 6px; }
        .cv-contact__title { font-size: 1.4rem; font-weight: 700; color: var(--ink, #1a1623); margin: 0 0 6px; letter-spacing: -0.01em; }
        .cv-contact__text { color: var(--ink-soft, #4a4458); font-size: 14.5px; line-height: 1.6; margin: 0; }
        .cv-contact__form { display: flex; flex-direction: column; gap: 14px; position: relative; }
        .cv-contact__row { display: grid; gap: 14px; grid-template-columns: 1fr; }
        @media (min-width: 560px) { .cv-contact__row { grid-template-columns: 1fr 1fr; } }
        .cv-contact__field { display: flex; flex-direction: column; gap: 6px; }
        .cv-contact__label { font-size: 13px; font-weight: 600; color: var(--ink, #1a1623); }
        .cv-contact__input { width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line, #e8e3d7); background: #fff; color: var(--ink, #1a1623); font-size: 14.5px; font-family: inherit; }
        .cv-contact__input:focus { outline: none; border-color: var(--brand-primary, #5c17e7); box-shadow: 0 0 0 4px rgba(92,23,231,0.16); }
        .cv-contact__input--textarea { min-height: 110px; resize: vertical; }
        .cv-contact__submit { margin-top: 6px; padding: 12px 20px; border: 0; border-radius: 12px; background: var(--brand-primary, #5c17e7); color: #fff; font-weight: 600; font-size: 14.5px; cursor: pointer; align-self: flex-start; }
        .cv-contact__submit:hover { background: var(--brand-primary-dark, #4a10c0); }
        .cv-contact__submit:focus-visible { outline: 3px solid rgba(92,23,231,0.4); outline-offset: 2px; }
        .cv-contact__input:focus-visible { outline: 3px solid rgba(92,23,231,0.3); outline-offset: 1px; }
        @media (prefers-reduced-motion: reduce) { .cv-contact *, .cv-contact *::before, .cv-contact *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; } }
        .cv-contact__alert { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 14px; line-height: 1.5; margin-bottom: 12px; }
        .cv-contact__alert svg { flex-shrink: 0; margin-top: 2px; }
        .cv-contact__alert--success { background: #e6f5ec; color: #1f7a4f; border: 1px solid #c2e7d3; }
        .cv-contact__alert--error { background: #fdecea; color: #c0392b; border: 1px solid #f5c6c0; }
        .cv-contact__alert ul { margin: 0; padding-inline-start: 18px; }
    </style>
@endif

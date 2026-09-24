{{-- Loaded after template <style> blocks so Arabic stacks beat Latin-only font-family rules. --}}
<style>
    html[dir="rtl"] body,
    html[lang="ar"] body {
        font-family: var(--font-ar-sans);
        letter-spacing: 0;
    }

    html[dir="rtl"] :is(
        h1, h2, h3, h4, h5, h6,
        .name, .rail-name, .job-title, .subject, .signature,
        .masthead h1, .hero h1, .hero-text h1, .bar h1,
        .panel h2, .band h2, .section h2, .col h2, .item h3,
        .about, .deck, .rail-h
    ),
    html[lang="ar"] :is(
        h1, h2, h3, h4, h5, h6,
        .name, .rail-name, .job-title, .subject, .signature,
        .masthead h1, .hero h1, .hero-text h1, .bar h1,
        .panel h2, .band h2, .section h2, .col h2, .item h3,
        .about, .deck, .rail-h
    ) {
        font-family: var(--font-ar-display);
        letter-spacing: 0;
    }

    /* Editorial / serif accents in Arabic */
    html[dir="rtl"] :is(.about, .deck em, .signature, blockquote, em),
    html[lang="ar"] :is(.about, .deck em, .signature, blockquote, em) {
        font-family: var(--font-ar-serif);
    }

    /* Inline Latin font-family on quotes / accents */
    html[dir="rtl"] [style*="font-family"],
    html[lang="ar"] [style*="font-family"] {
        font-family: var(--font-ar-serif) !important;
    }
</style>

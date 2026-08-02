<?php

namespace App\Support;

class PublicProfileTemplateData
{
    public function __construct(
        public readonly string $fullName,
        public readonly ?string $jobTitle,
        public readonly ?string $headline,
        public readonly ?string $about,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $photo,
        public readonly ?string $coverImage,
        public readonly ?string $website,
        public readonly ?string $pronouns,
        public readonly string $language,
        public readonly array $socialLinks,
        public readonly array $experiences,
        public readonly array $educations,
        public readonly array $projects,
        public readonly array $skills,
        public readonly array $languages,
        public readonly array $services,
        public readonly array $testimonials,
        public readonly array $certifications,
        public readonly array $achievements,
        public readonly ?array $availability,
        public readonly ?array $cta,
        public readonly ?array $seo,
        public readonly array $sectionsOrder,
        public readonly array $contactParts,
    ) {
    }

    public static function from(array $profile): self
    {
        $userData = $profile['user_data'] ?? [];
        $firstName = $userData['firstName'] ?? '';
        $lastName = $userData['lastName'] ?? '';
        $fullName = trim($firstName.' '.$lastName) ?: 'Profile';

        $email = $userData['email'] ?? null;
        $phone = $userData['phone'] ?? null;
        $website = $userData['website'] ?? null;
        $city = $userData['city'] ?? null;
        $country = $userData['country'] ?? null;
        $address = $userData['address'] ?? null;

        $location = trim(implode(', ', array_filter([$city, $country])));
        if ($location === '' && $address) {
            $location = $address;
        }

        $contactParts = array_values(array_filter([
            $email,
            $phone,
            $website,
            $location ?: null,
        ]));

        $defaultOrder = [
            'about', 'services', 'experiences', 'projects', 'skills',
            'educations', 'certifications', 'achievements', 'languages',
            'testimonials', 'availability',
        ];

        return new self(
            fullName: $fullName,
            jobTitle: $userData['jobTitle'] ?? null,
            headline: $profile['headline'] ?? null,
            about: $profile['about'] ?? null,
            email: $email,
            phone: $phone,
            address: $address,
            city: $city,
            country: $country,
            photo: $userData['photo'] ?? null,
            coverImage: $userData['coverImage'] ?? null,
            website: $website,
            pronouns: $userData['pronouns'] ?? null,
            language: $profile['language'] ?? 'en',
            socialLinks: $userData['socialLinks'] ?? [],
            experiences: $userData['experiences'] ?? [],
            educations: $userData['educations'] ?? [],
            projects: $userData['projects'] ?? [],
            skills: $userData['skills'] ?? [],
            languages: $userData['languages'] ?? [],
            services: $userData['services'] ?? [],
            testimonials: $userData['testimonials'] ?? [],
            certifications: $userData['certifications'] ?? [],
            achievements: $userData['achievements'] ?? [],
            availability: $userData['availability'] ?? null,
            cta: $userData['cta'] ?? null,
            seo: $userData['seo'] ?? null,
            sectionsOrder: $profile['sections_order'] ?? $defaultOrder,
            contactParts: $contactParts,
        );
    }

    public function location(): ?string
    {
        $parts = array_filter([$this->city, $this->country]);
        if ($parts) {
            return implode(', ', $parts);
        }

        return $this->address;
    }

    public static function formatMonthYear(?string $date): string
    {
        if (! $date) {
            return '';
        }

        try {
            return \Carbon\Carbon::createFromFormat('Y-m', substr($date, 0, 7))->format('M Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    public static function dateRange(?string $from, ?string $to, bool $current = false): string
    {
        $start = self::formatMonthYear($from);
        if ($current) {
            return $start ? $start.' – Present' : 'Present';
        }
        $end = self::formatMonthYear($to);

        return trim($start.($end ? ' – '.$end : ''));
    }
}

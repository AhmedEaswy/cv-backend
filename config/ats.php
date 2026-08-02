<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scoring blend when a job description is provided
    |--------------------------------------------------------------------------
    */
    'structural_weight' => 0.7,
    'keyword_weight' => 0.3,

    /*
    |--------------------------------------------------------------------------
    | Grade bands (score >= threshold)
    |--------------------------------------------------------------------------
    */
    'grades' => [
        'A' => 85,
        'B' => 70,
        'C' => 55,
        'D' => 40,
        // below D => F
    ],

    /*
    |--------------------------------------------------------------------------
    | Thresholds
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'min_summary_length' => 50,
        'min_experience_description_length' => 40,
        'min_skills' => 3,
        'min_experiences' => 1,
        'min_educations' => 1,
        'min_pdf_text_length' => 80,
        'min_chars_per_kb' => 15,
        'max_special_char_ratio' => 0.25,
        'max_keyword_terms' => 40,
        'min_keyword_term_length' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Check weights by id
    |--------------------------------------------------------------------------
    */
    'weights' => [
        // completeness
        'has_name' => 8,
        'has_job_title' => 6,
        'has_summary' => 8,
        'has_experience' => 10,
        'has_education' => 8,
        'has_skills' => 8,
        'has_experience_dates' => 6,

        // contact
        'has_email' => 10,
        'has_phone' => 6,
        'has_address' => 3,

        // content
        'experience_detail' => 8,
        'action_verbs' => 5,
        'no_first_person' => 4,

        // ats_format (shared / structured)
        'photo_soft_warning' => 2,

        // ats_format (pdf)
        'pdf_parseable' => 20,
        'pdf_has_email' => 8,
        'pdf_has_phone' => 5,
        'pdf_section_headings' => 10,
        'pdf_text_density' => 6,
        'pdf_special_chars' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Section headings detected in PDF text (lowercase)
    |--------------------------------------------------------------------------
    */
    'section_headings' => [
        'experience',
        'work experience',
        'employment',
        'education',
        'skills',
        'summary',
        'profile',
        'projects',
        'languages',
        'certifications',
        'الخبرة',
        'التعليم',
        'المهارات',
        'الملخص',
        'المشاريع',
        'deneyim',
        'eğitim',
        'beceriler',
        'özet',
        'projeler',
    ],

    /*
    |--------------------------------------------------------------------------
    | English action verbs (lowercase) for experience text heuristic
    |--------------------------------------------------------------------------
    */
    'action_verbs' => [
        'achieved', 'built', 'created', 'designed', 'developed', 'delivered',
        'led', 'managed', 'improved', 'increased', 'reduced', 'implemented',
        'launched', 'optimized', 'collaborated', 'coordinated', 'analyzed',
        'architected', 'automated', 'configured', 'deployed', 'engineered',
        'established', 'executed', 'facilitated', 'generated', 'maintained',
        'mentored', 'negotiated', 'organized', 'oversaw', 'planned',
        'produced', 'resolved', 'spearheaded', 'streamlined', 'supported',
        'trained', 'transformed', 'wrote',
    ],

    /*
    |--------------------------------------------------------------------------
    | First-person pronouns to flag (English)
    |--------------------------------------------------------------------------
    */
    'first_person' => ['i ', "i'm", "i've", 'my ', 'me ', 'myself'],

    /*
    |--------------------------------------------------------------------------
    | Stopwords stripped from job descriptions
    |--------------------------------------------------------------------------
    */
    'stopwords' => [
        'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'as', 'is', 'are', 'was', 'were', 'be',
        'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will',
        'would', 'could', 'should', 'may', 'might', 'must', 'shall', 'can',
        'this', 'that', 'these', 'those', 'it', 'its', 'we', 'you', 'your',
        'our', 'their', 'they', 'them', 'he', 'she', 'his', 'her', 'who',
        'which', 'what', 'when', 'where', 'how', 'why', 'all', 'each', 'every',
        'both', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
        'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just',
        'about', 'into', 'through', 'during', 'before', 'after', 'above',
        'below', 'between', 'under', 'again', 'further', 'then', 'once',
        'here', 'there', 'any', 'also', 'able', 'across', 'role', 'job',
        'position', 'team', 'work', 'working', 'experience', 'years', 'year',
        'required', 'requirements', 'preferred', 'responsibilities', 'including',
        'include', 'etc', 'using', 'use', 'used', 'well', 'strong', 'good',
        'looking', 'seek', 'seeking', 'candidate', 'candidates', 'company',
        'please', 'apply', 'application',
    ],

];

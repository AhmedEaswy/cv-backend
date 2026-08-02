<?php

return [

    'checked_successfully' => 'ATS check completed successfully.',

    'errors' => [
        'profile_or_user_data_required' => 'Provide either profile_id or user_data.',
        'pdf_unreadable' => 'Unable to read the uploaded PDF file.',
    ],

    'checks' => [
        'has_name' => [
            'pass' => 'Name is present.',
            'fail' => 'Name is missing.',
            'tip' => 'Add your first and last name.',
        ],
        'has_job_title' => [
            'pass' => 'Job title is present.',
            'fail' => 'Job title is missing.',
            'tip' => 'Add a clear target job title near the top of your CV.',
        ],
        'has_summary' => [
            'pass' => 'Professional summary looks sufficient.',
            'fail' => 'Professional summary is missing or too short.',
            'tip' => 'Write a summary of at least 50 characters describing your background.',
        ],
        'has_experience' => [
            'pass' => 'Work experience is present.',
            'fail' => 'No work experience found.',
            'tip' => 'Add at least one work experience entry with role and company.',
        ],
        'has_education' => [
            'pass' => 'Education is present.',
            'fail' => 'No education found.',
            'tip' => 'Add at least one education entry.',
        ],
        'has_skills' => [
            'pass' => 'Skills list looks sufficient.',
            'fail' => 'Not enough skills listed.',
            'tip' => 'List at least three relevant skills.',
        ],
        'has_experience_dates' => [
            'pass' => 'Experience entries include start dates.',
            'fail' => 'Some experience entries are missing dates.',
            'tip' => 'Add a start date (YYYY-MM) for each role.',
        ],
        'has_email' => [
            'pass' => 'Valid email found.',
            'fail' => 'Valid email is missing.',
            'tip' => 'Add a professional email address ATS systems can parse.',
        ],
        'has_phone' => [
            'pass' => 'Phone number found.',
            'fail' => 'Phone number is missing.',
            'tip' => 'Add a phone number with country code if possible.',
        ],
        'has_address' => [
            'pass' => 'Location / address found.',
            'fail' => 'Location / address is missing.',
            'tip' => 'Add a city or address so recruiters know your location.',
        ],
        'experience_detail' => [
            'pass' => 'Experience descriptions have enough detail.',
            'fail' => 'Experience descriptions are too short or missing.',
            'tip' => 'Describe achievements in each role (at least a few sentences).',
        ],
        'action_verbs' => [
            'pass' => 'Experience text uses strong action verbs.',
            'fail' => 'Experience text lacks clear action verbs.',
            'tip' => 'Start bullets with verbs like developed, led, improved, or delivered.',
        ],
        'no_first_person' => [
            'pass' => 'No first-person pronouns detected.',
            'fail' => 'First-person pronouns detected (I, my, me).',
            'tip' => 'Rewrite in a concise third-person / bullet style without “I” or “my”.',
        ],
        'photo_soft_warning' => [
            'pass' => 'No photo attached (often safer for ATS parsing).',
            'fail' => 'A photo is attached.',
            'tip' => 'Some ATS parsers struggle with photo-heavy layouts; consider an ATS-classic template without a photo.',
        ],
        'pdf_parseable' => [
            'pass' => 'PDF text was extracted successfully.',
            'fail' => 'PDF appears unscannable (little or no extractable text).',
            'tip' => 'Upload a text-based PDF, not a scanned image. Export from your CV builder or Word as PDF.',
        ],
        'pdf_has_email' => [
            'pass' => 'Email address found in PDF text.',
            'fail' => 'No email address found in PDF text.',
            'tip' => 'Make sure your email is plain selectable text, not inside an image.',
        ],
        'pdf_has_phone' => [
            'pass' => 'Phone number found in PDF text.',
            'fail' => 'No phone number found in PDF text.',
            'tip' => 'Include a phone number as plain text in the header.',
        ],
        'pdf_section_headings' => [
            'pass' => 'Standard section headings detected.',
            'fail' => 'Few or no standard section headings detected.',
            'tip' => 'Use clear headings like Experience, Education, and Skills.',
        ],
        'pdf_text_density' => [
            'pass' => 'Text density looks healthy for file size.',
            'fail' => 'Low text density — PDF may be image-heavy or scanned.',
            'tip' => 'Avoid image-only pages; use real text layers.',
        ],
        'pdf_special_chars' => [
            'pass' => 'Special character density looks normal.',
            'fail' => 'Unusually high special-character density.',
            'tip' => 'Simplify formatting; avoid decorative symbols and complex tables.',
        ],
    ],

];

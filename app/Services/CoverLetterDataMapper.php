<?php

namespace App\Services;

use App\Models\CoverLetter;

class CoverLetterDataMapper
{
    public function mapUserDataToCoverLetter(array $userData): array
    {
        $info = [];

        if (isset($userData['firstName'])) {
            $info['firstName'] = $userData['firstName'];
        }
        if (isset($userData['lastName'])) {
            $info['lastName'] = $userData['lastName'];
        }
        if (isset($userData['email'])) {
            $info['email'] = $userData['email'];
        }
        if (isset($userData['phone'])) {
            $info['phone'] = $userData['phone'];
        }
        if (isset($userData['address'])) {
            $info['address'] = $userData['address'];
        }
        if (isset($userData['jobTitle'])) {
            $info['jobTitle'] = $userData['jobTitle'];
        }
        if (isset($userData['companyName'])) {
            $info['companyName'] = $userData['companyName'];
        }
        if (isset($userData['recipientName'])) {
            $info['recipientName'] = $userData['recipientName'];
        }
        if (isset($userData['recipientTitle'])) {
            $info['recipientTitle'] = $userData['recipientTitle'];
        }
        if (isset($userData['recipientCompany'])) {
            $info['recipientCompany'] = $userData['recipientCompany'];
        }
        if (isset($userData['subject'])) {
            $info['subject'] = $userData['subject'];
        }
        if (isset($userData['body'])) {
            $info['body'] = $userData['body'];
        }
        if (isset($userData['closing'])) {
            $info['closing'] = $userData['closing'];
        }

        $mapped = [];

        if ($info) {
            $mapped['info'] = $info;
        }

        if (isset($userData['experiences'])) {
            $mapped['experiences'] = $userData['experiences'];
        }

        return $mapped;
    }

    public function formatCoverLetterResponse(CoverLetter $coverLetter): array
    {
        $userData = [];

        if ($coverLetter->info) {
            foreach ($coverLetter->info as $key => $value) {
                $userData[$key] = $value;
            }
        }

        if ($coverLetter->experiences) {
            $userData['experiences'] = $coverLetter->experiences;
        }

        return [
            'id' => $coverLetter->id,
            'user_id' => $coverLetter->user_id,
            'name' => $coverLetter->name,
            'language' => $coverLetter->language,
            'is_public' => $coverLetter->is_public,
            'sections_order' => $coverLetter->sections_order,
            'cover_letter_template_id' => $coverLetter->cover_letter_template_id,
            'user_data' => $userData,
            'created_at' => $coverLetter->created_at?->toIso8601String(),
            'updated_at' => $coverLetter->updated_at?->toIso8601String(),
        ];
    }
}

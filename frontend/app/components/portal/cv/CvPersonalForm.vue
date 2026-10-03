<script setup lang="ts">
import type { CvUserData } from '~/composables/usePortalApi';
import {
    AlignLeftIcon,
    Briefcase01Icon,
    Link01Icon,
    Location01Icon,
    Mail01Icon,
    UserIcon,
} from '@hugeicons/core-free-icons';

const userData = defineModel<CvUserData>({ required: true });
</script>

<template>
    <div class="cv-fields">
        <div class="field">
            <label class="field-label" for="cv-first-name">{{ $t('portal.cvs.field.first_name') }}</label>
            <FieldIcon :icon="UserIcon">
                <input id="cv-first-name" v-model="userData.firstName" type="text" class="input" maxlength="255" autocomplete="given-name" :placeholder="$t('portal.cvs.field.first_name_placeholder')" />
            </FieldIcon>
        </div>
        <div class="field">
            <label class="field-label" for="cv-last-name">{{ $t('portal.cvs.field.last_name') }}</label>
            <FieldIcon :icon="UserIcon">
                <input id="cv-last-name" v-model="userData.lastName" type="text" class="input" maxlength="255" autocomplete="family-name" :placeholder="$t('portal.cvs.field.last_name_placeholder')" />
            </FieldIcon>
        </div>
        <div class="field">
            <label class="field-label" for="cv-job-title">{{ $t('portal.cvs.field.job_title') }}</label>
            <FieldIcon :icon="Briefcase01Icon">
                <input id="cv-job-title" v-model="userData.jobTitle" type="text" class="input" maxlength="255" :placeholder="$t('portal.cvs.field.job_title_placeholder')" />
            </FieldIcon>
        </div>
        <div class="field">
            <label class="field-label" for="cv-email">{{ $t('portal.cvs.field.email') }}</label>
            <FieldIcon :icon="Mail01Icon">
                <input id="cv-email" v-model="userData.email" type="email" class="input" maxlength="255" autocomplete="email" :placeholder="$t('portal.cvs.field.email_placeholder')" />
            </FieldIcon>
        </div>
        <div class="field">
            <label class="field-label" for="cv-phone">{{ $t('portal.cvs.field.phone') }}</label>
            <PhoneInput
                id="cv-phone"
                v-model="userData.phone"
                :placeholder="$t('portal.cvs.field.phone_placeholder')"
            />
        </div>
        <div class="field">
            <label class="field-label" for="cv-birthdate">{{ $t('portal.cvs.field.birthdate') }}</label>
            <DatePicker id="cv-birthdate" v-model="userData.birthdate" mode="date" :placeholder="$t('portal.cvs.field.birthdate_placeholder')" />
        </div>
        <div class="field field--full">
            <label class="field-label" for="cv-address">{{ $t('portal.cvs.field.address') }}</label>
            <FieldIcon :icon="Location01Icon">
                <input id="cv-address" v-model="userData.address" type="text" class="input" maxlength="500" autocomplete="street-address" :placeholder="$t('portal.cvs.field.address_placeholder')" />
            </FieldIcon>
        </div>
        <div class="field field--full">
            <label class="field-label" for="cv-portfolio">{{ $t('portal.cvs.field.portfolio') }}</label>
            <FieldIcon :icon="Link01Icon">
                <input id="cv-portfolio" v-model="userData.portfolioUrl" type="url" class="input" maxlength="500" :placeholder="$t('portal.cvs.field.portfolio_placeholder')" />
            </FieldIcon>
        </div>
        <div class="field field--full" style="margin-bottom: 0">
            <label class="field-label" for="cv-summary">{{ $t('portal.cvs.field.summary') }}</label>
            <FieldIcon :icon="AlignLeftIcon">
                <textarea id="cv-summary" v-model="userData.summary" class="textarea" rows="5" :placeholder="$t('portal.cvs.field.summary_placeholder')" />
            </FieldIcon>
        </div>
    </div>
</template>

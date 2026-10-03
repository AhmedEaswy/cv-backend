<script setup lang="ts">
const props = defineProps<{
    submitTicket: (body: {
        name: string;
        email: string;
        subject: string;
        body: string;
    }) => Promise<boolean>;
}>();

const { t } = useI18n();

const form = reactive({
    name: '',
    email: '',
    subject: '',
    body: '',
});
const sending = ref(false);
const sent = ref(false);

async function onSubmit() {
    sending.value = true;
    const ok = await props.submitTicket({
        name: form.name.trim(),
        email: form.email.trim(),
        subject: form.subject.trim(),
        body: form.body.trim(),
    });
    sending.value = false;
    if (ok) {
        sent.value = true;
        form.name = '';
        form.email = '';
        form.subject = '';
        form.body = '';
    }
}
</script>

<template>
    <section id="support-contact" class="support-section support-contact" aria-labelledby="support-contact-title">
        <header class="support-section__head">
            <h2 id="support-contact-title">{{ t('support.contact.title') }}</h2>
            <p class="support-section__lede">{{ t('support.contact.lede') }}</p>
            <p class="support-contact__private">
                <Icon name="lock" :size="16" aria-hidden="true" />
                {{ t('support.contact.private_notice') }}
            </p>
        </header>

        <div v-if="sent" class="support-contact__success" role="status">
            <p>{{ t('support.contact.success') }}</p>
            <Button variant="secondary" size="sm" @click="sent = false">{{ t('support.contact.send_another') }}</Button>
        </div>

        <form v-else class="support-form support-contact__form" @submit.prevent="onSubmit">
            <div class="support-form__grid">
                <label class="support-form__field">
                    <span>{{ t('support.contact.name') }} <em class="support-required">*</em></span>
                    <input v-model="form.name" class="input" required minlength="2" maxlength="120" autocomplete="name" />
                </label>
                <label class="support-form__field">
                    <span>{{ t('support.contact.email') }} <em class="support-required">*</em></span>
                    <input v-model="form.email" type="email" class="input" required autocomplete="email" />
                </label>
            </div>
            <label class="support-form__field">
                <span>{{ t('support.contact.subject') }} <em class="support-required">*</em></span>
                <input v-model="form.subject" class="input" required minlength="3" maxlength="200" />
            </label>
            <label class="support-form__field">
                <span>{{ t('support.contact.message') }} <em class="support-required">*</em></span>
                <textarea v-model="form.body" class="input support-form__textarea" required minlength="10" maxlength="8000" rows="5" />
            </label>
            <div class="support-form__actions">
                <Button type="submit" variant="primary" :loading="sending">
                    {{ t('support.contact.send') }}
                </Button>
            </div>
        </form>
    </section>
</template>

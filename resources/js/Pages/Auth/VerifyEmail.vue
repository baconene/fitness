<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <p class="ui-eyebrow">You’re almost ready</p>
        <h1 class="ui-title mt-3">Check your inbox.</h1>
        <p class="ui-description mt-3">
            Follow the link in your welcome email to verify your email address
            and start your FitTrack journey. Can’t find it? Send another below.
        </p>

        <div class="ui-status mt-6" v-if="verificationLinkSent" role="status">
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form
            class="auth-form"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <PrimaryButton class="w-full" :disabled="form.processing">
                {{
                    form.processing
                        ? 'Sending verification email...'
                        : 'Resend verification email'
                }}
            </PrimaryButton>
        </form>

        <p class="auth-switch">
            Need to use a different account?
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="ui-link"
            >
                Log out
            </Link>
        </p>
    </GuestLayout>
</template>

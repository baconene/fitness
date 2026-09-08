<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <p class="ui-eyebrow">Get back on track</p>
        <h1 class="ui-title mt-3">A fresh start.</h1>
        <p class="ui-description mt-3">
            Enter your email address and we’ll send you a link to reset your
            password. Your progress will be here when you return.
        </p>

        <div v-if="status" class="ui-status mt-6" role="status">
            {{ status }}
        </div>

        <form
            class="auth-form"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="email" value="Email address" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                    :aria-invalid="Boolean(form.errors.email)"
                    :aria-describedby="
                        form.errors.email ? 'email-error' : undefined
                    "
                />

                <InputError
                    id="email-error"
                    class="mt-2"
                    :message="form.errors.email"
                />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{
                    form.processing
                        ? 'Sending reset link...'
                        : 'Send reset link'
                }}
            </PrimaryButton>
        </form>

        <p class="auth-switch">
            Remembered your password?
            <Link :href="route('login')" class="ui-link">Back to log in</Link>
        </p>
    </GuestLayout>
</template>

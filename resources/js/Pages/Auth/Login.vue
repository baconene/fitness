<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout
        eyebrow="Keep showing up"
        headline="Pick up where"
        accent="you left off."
        description="Every workout is a step forward. Your next one starts here."
    >
        <Head title="Log in" />

        <p class="ui-eyebrow">Your progress is waiting</p>
        <h1 class="ui-title mt-3">Welcome back.</h1>
        <p class="ui-description mt-3">
            Log in, find your rhythm, and keep building on your progress.
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

            <div>
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    :aria-invalid="Boolean(form.errors.password)"
                    :aria-describedby="
                        form.errors.password ? 'password-error' : undefined
                    "
                />

                <InputError
                    id="password-error"
                    class="mt-2"
                    :message="form.errors.password"
                />
            </div>

            <div class="auth-options">
                <label
                    class="flex cursor-pointer items-center gap-2 text-sm text-muted"
                >
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span>Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="ui-link text-sm"
                >
                    Forgot password?
                </Link>
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Logging in...' : 'Log in' }}
            </PrimaryButton>
        </form>

        <p class="auth-switch">
            New to FitTrack?
            <Link :href="route('register')" class="ui-link">
                Start your journey
            </Link>
        </p>
    </GuestLayout>
</template>

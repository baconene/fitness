<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout
        eyebrow="Make room for a stronger you"
        headline="Big progress."
        accent="Small beginnings."
        description="Start where you are. Build a routine you love, one workout at a time."
    >
        <Head title="Register" />

        <p class="ui-eyebrow">Every rep starts somewhere</p>
        <h1 class="ui-title mt-3">Your day one starts here.</h1>
        <p class="ui-description mt-3">
            Create your account and give your fitness goals a place to grow.
        </p>

        <form
            class="auth-form"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="name" value="Full name" />

                <TextInput
                    id="name"
                    type="text"
                    class="block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    :aria-invalid="Boolean(form.errors.name)"
                    :aria-describedby="
                        form.errors.name ? 'name-error' : undefined
                    "
                />

                <InputError
                    id="name-error"
                    class="mt-2"
                    :message="form.errors.name"
                />
            </div>

            <div>
                <InputLabel for="email" value="Email address" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    required
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
                    autocomplete="new-password"
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

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    :aria-invalid="Boolean(form.errors.password_confirmation)"
                    :aria-describedby="
                        form.errors.password_confirmation
                            ? 'password-confirmation-error'
                            : undefined
                    "
                />

                <InputError
                    id="password-confirmation-error"
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{
                    form.processing
                        ? 'Creating your account...'
                        : 'Create my account'
                }}
            </PrimaryButton>
        </form>

        <p class="auth-switch">
            Already part of FitTrack?
            <Link :href="route('login')" class="ui-link"> Log in </Link>
        </p>
    </GuestLayout>
</template>

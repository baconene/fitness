<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <p class="ui-eyebrow">Ready for your next chapter</p>
        <h1 class="ui-title mt-3">Reset your password.</h1>
        <p class="ui-description mt-3">
            Choose a new password and get back to building your momentum.
        </p>

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
                <InputLabel for="password" value="New password" />

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
                    value="Confirm new password"
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
                    form.processing ? 'Resetting password...' : 'Reset password'
                }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>

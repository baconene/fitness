<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <p class="ui-eyebrow">One quick check</p>
        <h1 class="ui-title mt-3">Confirm it’s you.</h1>
        <p class="ui-description mt-3">
            Enter your password to continue to this secure area of your account.
        </p>

        <form
            class="auth-form"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
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

            <PrimaryButton class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Confirming...' : 'Confirm password' }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>

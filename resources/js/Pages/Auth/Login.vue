<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';

const route = inject('route');
const emailInput = ref(null);
const passwordInput = ref(null);

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
    if (form.processing) return;
    form.post(route('login', undefined, false), {
        onError: () => form.errors.email ? emailInput.value?.focus() : passwordInput.value?.focus(),
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout
    eyebrow="SYSTEM / RETURNING HUNTER"
    headline="YOUR NEXT"
    accent="ASCENT AWAITS."
    description="Reconnect with your missions. Continue your training. Every completed objective brings your next level closer."
    system-message="Your story continues with your next mission."
    >
    <Head title="Fitbakes — Log in to System" />
    <p class="ui-eyebrow">IDENTITY CHECK / LOGIN</p>
    <h1 class="ui-title mt-3">Enter the System.</h1>
    <p class="ui-description mt-3">
        Log in to access your hunter profile and today's mission.
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
        ref="emailInput"
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
        <PasswordInput
        id="password"
        ref="passwordInput"
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
    :href="route('password.request', undefined, false)"
    class="ui-link text-sm"
    >
    Forgot password?
</Link>
</div>
<PrimaryButton class="w-full" :disabled="form.processing">
{{ form.processing ? 'Authenticating...' : 'Log in to System' }}
<span aria-hidden="true">{{ form.processing ? '…' : '↗' }}</span>
</PrimaryButton>
</form>
<p class="system-feedback" :class="{ 'is-error': form.hasErrors }" role="status">
{{ form.processing ? 'SYSTEM: Checking your credentials.' : form.hasErrors ? 'ACCESS NOT COMPLETED. Check the highlighted fields and try again.' : 'SYSTEM: Awaiting your credentials.' }}
</p>
<p class="auth-switch">
New hunter?
<Link :href="route('register', undefined, false)" class="ui-link">
Create a Fitbakes account
</Link>
</p>
</GuestLayout>
</template>

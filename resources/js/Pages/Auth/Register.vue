<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, inject, ref } from 'vue';

const route = inject('route');
const nameInput = ref(null);
const emailInput = ref(null);
const passwordInput = ref(null);
const confirmationInput = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const requirements = computed(() => [
    { label: 'Name entered', complete: Boolean(form.name.trim()) },
    { label: 'Email entered', complete: Boolean(form.email.trim()) },
    { label: '8+ character password', complete: form.password.length >= 8 },
    { label: 'Passwords match', complete: Boolean(form.password) && form.password === form.password_confirmation },
]);
const completedRequirements = computed(() => requirements.value.filter((item) => item.complete).length);

const submit = () => {
    if (form.processing) return;
    form.post(route('register', undefined, false), {
        onError: () => {
            const fields = { name: nameInput, email: emailInput, password: passwordInput, password_confirmation: confirmationInput };
            fields[Object.keys(form.errors)[0]]?.value?.focus();
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout
    eyebrow="SYSTEM / NEW HUNTER DETECTED"
    headline="YOUR STORY."
    accent="YOUR AWAKENING."
    description="Build a hunter profile around your real-world effort. Train with live missions, complete quests, and turn consistency into progression."
    system-message="No rank required to begin. Your first step counts."
    >
    <Head title="Fitbakes — Create your account" />
    <p class="ui-eyebrow">AWAKENING PROTOCOL / REGISTER</p>
    <h1 class="ui-title mt-3">Become a hunter.</h1>
    <p class="ui-description mt-3">
        Create your Fitbakes account. Your goals and training setup come next.
    </p>
    <div class="setup-progress">
        <div class="setup-heading">
            <span>ACCOUNT SETUP</span>
            <span>{{ completedRequirements }} / 4 READY</span>
        </div>
        <progress :value="completedRequirements" max="4" aria-label="Account setup checklist">
        </progress>
        <ul aria-label="Account setup requirements">
            <li v-for="item in requirements" :key="item.label" :class="{ ready: item.complete }">
                <span aria-hidden="true">{{ item.complete ? '✓' : '○' }}</span>
                <span class="sr-only">{{ item.complete ? 'Complete: ' : 'Pending: ' }}</span>{{ item.label }}</li>
        </ul>
    </div>
    <form
    class="auth-form"
    :aria-busy="form.processing"
    @submit.prevent="submit"
    >
    <div>
        <InputLabel for="name" value="Full name" />
        <TextInput
        id="name"
        ref="nameInput"
        type="text"
        class="block w-full"
        v-model="form.name"
        required
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
        autocomplete="new-password"
        :aria-invalid="Boolean(form.errors.password)"
        :aria-describedby="
        form.errors.password ? 'password-error password-hint' : 'password-hint'
        "
        />
        <p id="password-hint" class="password-hint">Use at least 8 characters. A unique password is best.</p>
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
        <PasswordInput
        id="password_confirmation"
        ref="confirmationInput"
        label="confirm password"
        class="block w-full"
        v-model="form.password_confirmation"
        required
        autocomplete="new-password"
        :aria-invalid="Boolean(form.errors.password_confirmation)"
        :aria-describedby="
        form.errors.password_confirmation
        ? 'password-confirmation-error'
        : 'confirmation-hint'
        "
        />
        <p id="confirmation-hint" class="password-hint" aria-live="polite">{{ form.password_confirmation ? (form.password === form.password_confirmation ? 'Passwords match.' : 'Passwords do not match yet.') : 'Enter your password again to confirm.' }}</p>
        <InputError
        id="password-confirmation-error"
        class="mt-2"
        :message="form.errors.password_confirmation"
        />
    </div>
    <PrimaryButton class="w-full" :disabled="form.processing">
        {{
        form.processing
        ? 'Initializing your account...'
        : 'Create account'
        }}
        <span aria-hidden="true">{{ form.processing ? '…' : '↗' }}</span>
    </PrimaryButton>
</form>
<p class="system-feedback" :class="{ 'is-error': form.hasErrors }" role="status">
    {{ form.processing ? 'SYSTEM: Creating your hunter account.' : form.hasErrors ? 'SETUP NEEDS ATTENTION. Check the highlighted fields.' : 'Your progression begins after account creation.' }}
</p>
<p class="auth-switch">
    Already a hunter?
    <Link :href="route('login', undefined, false)" class="ui-link"> Log in to System </Link>
</p>
</GuestLayout>
</template>

<style scoped>
.setup-progress {
    margin-top: 22px;
    padding-block: 17px;
    border-block: 1px solid #657db72b;
}
.setup-heading {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 8px;
    letter-spacing: .12em;
    color: #9badcf;
}
.setup-progress progress {
    display: block;
    width: 100%;
    height: 3px;
    margin-top: 12px;
    appearance: none;
    border: 0;
    background: #182237;
}
.setup-progress progress::-webkit-progress-bar {
    background: #182237;
}
.setup-progress progress::-webkit-progress-value {
    background: #9f8cff;
    transition: width .2s;
}
.setup-progress progress::-moz-progress-bar {
    background: #9f8cff;
}
.setup-progress ul {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 9px 10px;
    margin-top: 14px;
}
.setup-progress li {
    font-size: 10px;
    color: #9aa9bf;
}
.setup-progress li>span:first-child {
    display: inline-block;
    width: 17px;
}
.setup-progress li.ready {
    color: #7de0bd;
}
.password-hint {
    font-size: 11px;
    color: #9aa9bf;
    margin-top: 8px;
    line-height: 1.6;
}
@media (prefers-reduced-motion: reduce) {
    .setup-progress progress::-webkit-progress-value {
        transition: none;
    }
}
</style>

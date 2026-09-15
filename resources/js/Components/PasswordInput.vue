<script setup>
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';

defineOptions({ inheritAttrs: false });
defineProps({ label: { type: String, default: 'password' } });
const model = defineModel({ type: String, required: true });
const visible = ref(false);
const capsLock = ref(false);
const input = ref(null);
watch(model, (value) => { if (!value) visible.value = false; });
defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="password-field">
        <div class="password-control">
            <TextInput ref="input" v-bind="$attrs" v-model="model" :type="visible ? 'text' : 'password'" class="password-input block w-full" @keydown="capsLock = $event.getModifierState('CapsLock')" @keyup="capsLock = $event.getModifierState('CapsLock')" @blur="capsLock = false" />
            <button type="button" class="password-toggle" :aria-label="`${visible ? 'Hide' : 'Show'} ${label}`" :aria-pressed="visible" :aria-controls="$attrs.id" @click="visible = !visible">{{ visible ? 'HIDE' : 'SHOW' }}</button>
        </div>
        <p v-if="capsLock" class="caps-warning" role="status">Caps Lock is on.</p>
    </div>
</template>

<style scoped>
.password-control {
    position: relative;
}
.password-input {
    padding-right: 72px;
}
.password-toggle {
    position: absolute;
    right: 3px;
    top: 3px;
    bottom: 3px;
    min-width: 60px;
    min-height: 44px;
    padding: 8px;
    color: #b6a9f4;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: .08em;
    border-radius: 2px;
}
.password-toggle:hover {
    background: #7b61ff18;
}
.password-toggle:focus-visible {
    outline: 2px solid #9f8cff;
    outline-offset: -3px;
}
.caps-warning {
    font-size: 11px;
    color: #ffb86b;
    margin-top: 8px;
}
</style>

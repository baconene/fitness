<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message" role="alert">
        <p class="ui-error">
            {{ message }}
        </p>
    </div>
</template>

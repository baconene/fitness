<template>
    <div class="bg-edge rounded-lg p-6 text-center">
        <h2 class="text-2xl font-bold text-content mb-2">{{ exercise.name }}</h2>
        <p class="text-edge text-sm">{{ exercise.difficulty }}</p>
        <div v-if="exercise.instructions" class="mt-4">
            <p class="text-content text-sm text-left">{{ exercise.instructions }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    exercise: Object,
});
</script>

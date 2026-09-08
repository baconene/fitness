<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AccountStep from '@/Pages/Onboarding/Steps/AccountStep.vue';
import BodyProfileStep from '@/Pages/Onboarding/Steps/BodyProfileStep.vue';
import FitnessExperienceStep from '@/Pages/Onboarding/Steps/FitnessExperienceStep.vue';
import GoalStep from '@/Pages/Onboarding/Steps/GoalStep.vue';
import AvailabilityStep from '@/Pages/Onboarding/Steps/AvailabilityStep.vue';
import EquipmentStep from '@/Pages/Onboarding/Steps/EquipmentStep.vue';
import LimitationsStep from '@/Pages/Onboarding/Steps/LimitationsStep.vue';
import AwakeningStep from '@/Pages/Onboarding/Steps/AwakeningStep.vue';

const props = defineProps({
    progress: Object,
});

const stepData = ref(props.progress.step_data || {});
const currentStep = ref(props.progress.current_step || 'Account');
const isLoading = ref(false);

const steps = [
    { id: 'Account', label: 'Account', component: AccountStep },
    { id: 'BodyProfile', label: 'Body Profile', component: BodyProfileStep },
    { id: 'FitnessExperience', label: 'Experience', component: FitnessExperienceStep },
    { id: 'Goal', label: 'Goal', component: GoalStep },
    { id: 'Availability', label: 'Availability', component: AvailabilityStep },
    { id: 'Equipment', label: 'Equipment', component: EquipmentStep },
    { id: 'Limitations', label: 'Limitations', component: LimitationsStep },
    { id: 'Awakening', label: 'Awakening', component: AwakeningStep },
];

const currentStepIndex = computed(() => {
    return steps.findIndex((s) => s.id === currentStep.value);
});

const currentStepComponent = computed(() => {
    const step = steps[currentStepIndex.value];
    return step?.component || null;
});

const progressPercent = computed(() => {
    return ((currentStepIndex.value + 1) / steps.length) * 100;
});

const handleStepComplete = async (data) => {
    stepData.value[currentStep.value] = data;
    isLoading.value = true;

    router.post(
        route('onboarding.store'),
        {
            step: currentStep.value,
            data: data,
        },
        {
            onSuccess: (page) => {
                const nextStep = page.props.progress.current_step;
                currentStep.value = nextStep;
            },
            onError: (errors) => {
                console.error('Failed to save step', errors);
                isLoading.value = false;
            },
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};

const handleAwakeningComplete = async () => {
    isLoading.value = true;

    router.post(
        route('onboarding.complete'),
        {},
        {
            onSuccess: () => {
                // Redirect to dashboard handled by Laravel
            },
            onError: () => {
                console.error('Failed to complete onboarding');
            },
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <GuestLayout>
        <Head title="Onboarding" />

        <div class="min-h-screen flex flex-col items-center justify-center bg-canvas px-4 py-12">
            <div class="w-full max-w-2xl">
                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex justify-between mb-2">
                        <h1 class="text-2xl font-bold text-content">
                            {{ steps[currentStepIndex]?.label || 'Loading...' }}
                        </h1>
                        <span class="text-sm text-content-secondary">
                            {{ currentStepIndex + 1 }} / {{ steps.length }}
                        </span>
                    </div>
                    <div class="w-full h-2 bg-edge rounded-full overflow-hidden">
                        <div
                            class="h-full bg-brand transition-all duration-300"
                            :style="{ width: progressPercent + '%' }"
                        />
                    </div>
                </div>

                <!-- Step Component -->
                <div class="ui-panel p-8">
                    <component
                        v-if="currentStepComponent"
                        :is="currentStepComponent"
                        :step-data="stepData[currentStep] || {}"
                        :is-loading="isLoading"
                        @complete="
                            currentStep === 'Awakening'
                                ? handleAwakeningComplete()
                                : handleStepComplete($event)
                        "
                    />
                </div>

                <!-- Navigation Hints -->
                <div class="mt-6 text-center text-sm text-content-secondary">
                    <p v-if="currentStep !== 'Awakening'">
                        {{ currentStepIndex + 1 }} of {{ steps.length - 1 }} steps
                    </p>
                    <p v-else>Final step - Complete your awakening</p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

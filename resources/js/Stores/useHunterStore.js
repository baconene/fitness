import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useHunterStore = defineStore('hunter', () => {
    const profile = ref(null);
    const stats = ref(null);
    const streak = ref(null);

    const isAwakened = computed(() => profile.value?.awakened_at !== null);

    const xpToNextLevel = computed(() => {
        if (!profile.value) return 0;
        return Math.floor(100 * profile.value.current_level ** 1.35);
    });

    const xpProgress = computed(() => {
        if (!profile.value) return 0;
        return Math.min(100, (profile.value.current_xp / xpToNextLevel.value) * 100);
    });

    const setProfile = (data) => {
        profile.value = data;
    };

    const setStats = (data) => {
        stats.value = data;
    };

    const setStreak = (data) => {
        streak.value = data;
    };

    const updateXp = (amount) => {
        if (profile.value) {
            profile.value.current_xp += amount;
            profile.value.total_xp_earned += amount;

            // Check for level up
            if (profile.value.current_xp >= xpToNextLevel.value) {
                profile.value.current_xp = 0;
                profile.value.current_level += 1;
            }
        }
    };

    const updateStat = (stat, amount) => {
        if (stats.value) {
            stats.value[stat] += amount;
        }
    };

    const initialize = (hunterData, statsData, streakData) => {
        setProfile(hunterData);
        setStats(statsData);
        setStreak(streakData);
    };

    return {
        profile,
        stats,
        streak,
        isAwakened,
        xpToNextLevel,
        xpProgress,
        setProfile,
        setStats,
        setStreak,
        updateXp,
        updateStat,
        initialize,
    };
});

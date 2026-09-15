/** Turns stored slugs such as `upper_back` into readable words. */
export const readable = (value) => String(value ?? '').replaceAll('_', ' ');

/**
 * Exercise types present in a list, alphabetically, with how many exercises each has.
 *
 * @returns {Array<{ value: string, label: string, count: number }>}
 */
export const exerciseTypesIn = (exercises) => {
    const counts = new Map();

    for (const exercise of exercises) {
        if (exercise.type) {
            counts.set(exercise.type, (counts.get(exercise.type) ?? 0) + 1);
        }
    }

    return [...counts.entries()]
        .sort(([first], [second]) => first.localeCompare(second))
        .map(([value, count]) => ({
            value,
            label: readable(value).replace(/^\w/, (letter) => letter.toUpperCase()),
            count,
        }));
};

/**
 * Narrows the catalogue by exercise type and a free-text search over the name,
 * category, muscles and equipment. Excluded ids are never offered.
 */
export const filterExercises = (exercises, { search = '', type = 'all', excludedIds = [] } = {}) => {
    const term = readable(search).trim().toLowerCase();
    const excluded = new Set(excludedIds.map(Number));

    return exercises.filter((exercise) => {
        if (excluded.has(Number(exercise.id))) {
            return false;
        }

        if (type !== 'all' && exercise.type !== type) {
            return false;
        }

        if (!term) {
            return true;
        }

        return [
            exercise.name,
            exercise.category,
            ...(exercise.primaryMuscles ?? []),
            ...(exercise.secondaryMuscles ?? []),
            ...(exercise.equipment ?? []),
        ].some((value) => readable(value).toLowerCase().includes(term));
    });
};

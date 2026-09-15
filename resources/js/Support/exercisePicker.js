/** Turns stored slugs such as `upper_back` into readable words. */
export const readable = (value) => String(value ?? '').replaceAll('_', ' ');

/**
 * Distinct values, alphabetically, with readable labels and how often each occurs.
 *
 * @returns {Array<{ value: string, label: string, count: number }>}
 */
const countedOptions = (values) => {
    const counts = new Map();

    for (const value of values) {
        if (value) {
            counts.set(value, (counts.get(value) ?? 0) + 1);
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

/** Exercise types present in a list, with how many exercises each has. */
export const exerciseTypesIn = (exercises) => countedOptions(exercises.map((exercise) => exercise.type));

/** Primary target muscles present in a list, with how many exercises work each. */
export const musclesIn = (exercises) =>
    countedOptions(exercises.flatMap((exercise) => [...new Set(exercise.primaryMuscles ?? [])]));

/**
 * Narrows the catalogue by exercise type, primary target muscle and a free-text
 * search over the name, category, muscles and equipment. Excluded ids are never offered.
 */
export const filterExercises = (exercises, { search = '', type = 'all', muscle = 'all', excludedIds = [] } = {}) => {
    const term = readable(search).trim().toLowerCase();
    const excluded = new Set(excludedIds.map(Number));

    return exercises.filter((exercise) => {
        if (excluded.has(Number(exercise.id))) {
            return false;
        }

        if (type !== 'all' && exercise.type !== type) {
            return false;
        }

        if (muscle !== 'all' && !(exercise.primaryMuscles ?? []).includes(muscle)) {
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

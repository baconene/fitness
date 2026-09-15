import { test } from 'node:test';
import assert from 'node:assert/strict';
import { exerciseTypesIn, filterExercises, musclesIn } from '../../resources/js/Support/exercisePicker.js';

const catalogue = [
    { id: 1, name: 'Bench Press', category: 'Chest', type: 'strength', primaryMuscles: ['chest'], secondaryMuscles: ['triceps'], equipment: ['barbell', 'bench'] },
    { id: 2, name: 'Treadmill Run', category: 'Cardio', type: 'cardio', primaryMuscles: ['quadriceps'], secondaryMuscles: [], equipment: ['treadmill'] },
    { id: 3, name: 'Push-Up', category: 'Chest', type: 'bodyweight', primaryMuscles: ['chest'], secondaryMuscles: ['upper_back'], equipment: [] },
    { id: 4, name: 'Barbell Row', category: 'Back', type: 'strength', primaryMuscles: ['upper_back'], secondaryMuscles: ['biceps'], equipment: ['barbell'] },
];

const names = (exercises) => exercises.map((exercise) => exercise.name);

test('search matches names, muscles and equipment case-insensitively, including multi-word slugs', () => {
    assert.deepEqual(names(filterExercises(catalogue, { search: 'CHEST' })), ['Bench Press', 'Push-Up']);
    assert.deepEqual(names(filterExercises(catalogue, { search: 'treadmill' })), ['Treadmill Run']);
    assert.deepEqual(names(filterExercises(catalogue, { search: 'upper back' })), ['Push-Up', 'Barbell Row']);
    assert.deepEqual(names(filterExercises(catalogue, { search: 'swimming' })), []);
});

test('type and muscle filters combine with search and excluded exercises are never offered', () => {
    assert.deepEqual(names(filterExercises(catalogue, { search: 'chest', type: 'bodyweight' })), ['Push-Up']);
    assert.deepEqual(names(filterExercises(catalogue, { search: 'chest', excludedIds: [1] })), ['Push-Up']);
    assert.deepEqual(names(filterExercises(catalogue, { type: 'strength', muscle: 'upper_back' })), ['Barbell Row']);
});

test('the muscle filter only matches primary target muscles', () => {
    assert.deepEqual(names(filterExercises(catalogue, { muscle: 'upper_back' })), ['Barbell Row']);
});

test('types and muscles are listed alphabetically with readable labels and counts', () => {
    assert.deepEqual(exerciseTypesIn(catalogue), [
        { value: 'bodyweight', label: 'Bodyweight', count: 1 },
        { value: 'cardio', label: 'Cardio', count: 1 },
        { value: 'strength', label: 'Strength', count: 2 },
    ]);
    assert.deepEqual(musclesIn(catalogue), [
        { value: 'chest', label: 'Chest', count: 2 },
        { value: 'quadriceps', label: 'Quadriceps', count: 1 },
        { value: 'upper_back', label: 'Upper back', count: 1 },
    ]);
});

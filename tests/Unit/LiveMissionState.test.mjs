import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createPinia, setActivePinia } from 'pinia';
import { createRenderer, h } from 'vue';
import { useLiveWorkoutStore } from '../../resources/js/Stores/useLiveWorkoutStore.js';
import { useRestTimer } from '../../resources/js/Composables/useRestTimer.js';

test('empty missions and exercises without sets are not complete', () => {
    setActivePinia(createPinia());
    const store = useLiveWorkoutStore();
    store.setWorkout({ workout_exercises: [] });
    assert.equal(store.isWorkoutComplete, false);
    store.setWorkout({ workout_exercises: [{ workout_sets: [] }] });
    assert.equal(store.isWorkoutComplete, false);
});

test('out-of-order completion wraps to unfinished exercises without mutating server props', () => {
    setActivePinia(createPinia());
    const store = useLiveWorkoutStore();
    const workout = { workout_exercises: [
        { id: 1, workout_sets: [{ id: 11, is_completed: false }] },
        { id: 2, workout_sets: [{ id: 21, is_completed: true }] },
        { id: 3, workout_sets: [{ id: 31, is_completed: false }] },
    ] };
    store.setWorkout(workout, 2);
    store.markSetCompleted(31, { xp_awarded: 20 });
    store.moveToNextExercise();
    assert.equal(store.currentExercise.id, 1);
    assert.equal(store.currentSet.id, 11);
    assert.equal(workout.workout_exercises[2].workout_sets[0].is_completed, false);
    store.markSetCompleted(11, { xp_awarded: 15 });
    assert.equal(store.isWorkoutComplete, true);
    assert.equal(store.currentSet, undefined);
});

test('rest timer handles background time, extension, pause, resume and cleanup', (context) => {
    context.mock.timers.enable({ apis: ['Date', 'setInterval'], now: 1000 });
    const renderer = createRenderer({
        createElement: () => ({}), insert() {}, remove() {}, patchProp() {},
        setElementText() {}, createText: () => ({}), setText() {},
        createComment: () => ({}), parentNode: () => null, nextSibling: () => null,
    });
    let timer;
    const app = renderer.createApp({ setup() { timer = useRestTimer(); return () => h('div'); } });
    app.mount({});
    timer.start(90);
    context.mock.timers.setTime(31000);
    context.mock.timers.tick(250);
    assert.equal(timer.timeRemaining.value, 60);
    timer.addTime(30);
    assert.equal(timer.timeRemaining.value, 90);
    timer.pause();
    context.mock.timers.tick(10000);
    assert.equal(timer.timeRemaining.value, 90);
    timer.resume();
    context.mock.timers.tick(90000);
    assert.equal(timer.timeRemaining.value, 0);
    assert.equal(timer.isActive.value, false);
    timer.start(20);
    app.unmount();
    context.mock.timers.tick(5000);
    assert.equal(timer.timeRemaining.value, 20);
});

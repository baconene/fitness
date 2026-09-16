/**
 * Renders the exercise demonstration artwork into public/images/exercises.
 *
 * Run with: node resources/js/Support/generateExerciseDemos.mjs [slug ...]
 *
 * Writes `<slug>.gif` (the looping demonstration) and `<slug>.png` (the still
 * poster shown before playback, and under prefers-reduced-motion). Passing
 * slugs regenerates only those; passing none regenerates everything.
 *
 * This is a build-time tool, not part of the app bundle.
 */
import { createCanvas } from '@napi-rs/canvas';
import GIFEncoder from 'gif-encoder-2';
import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { drawExerciseDemo } from './exerciseDemoScene.js';
import { ARCHETYPES } from './exerciseArchetypes.js';
import { EXERCISE_DEMOS } from './exerciseDemos.js';

const WIDTH = 400;
const HEIGHT = 240;
const FRAMES = 32;
const FRAME_DELAY_MS = 100;

/** The poster frame: a little into the movement reads better than the start. */
const POSTER_PHASE = 0.25;

const outputDirectory = resolve(dirname(fileURLToPath(import.meta.url)), '../../../public/images/exercises');

const render = (slug) => {
    const canvas = createCanvas(WIDTH, HEIGHT);
    const encoder = new GIFEncoder(WIDTH, HEIGHT, 'neuquant', false);

    encoder.setDelay(slug === 'burpee' ? 80 : slug === 'jump-rope' ? 35 : slug === 'kettlebell-swing' ? 70 : FRAME_DELAY_MS);
    encoder.setRepeat(0);
    encoder.setQuality(3);
    encoder.start();

    const frames = slug === 'burpee' ? 64 : FRAMES;
    for (let frame = 0; frame < frames; frame++) {
        drawExerciseDemo(canvas, slug, frame / frames);
        encoder.addFrame(canvas.getContext('2d'));
    }

    encoder.finish();
    writeFileSync(resolve(outputDirectory, `${slug}.gif`), encoder.out.getData());

    drawExerciseDemo(canvas, slug, POSTER_PHASE);
    writeFileSync(resolve(outputDirectory, `${slug}.png`), canvas.toBuffer('image/png'));
};

const requested = process.argv.slice(2);
const slugs = requested.length
    ? requested
    : [...new Set([...Object.keys(EXERCISE_DEMOS), ...Object.keys(ARCHETYPES)])].sort();

mkdirSync(outputDirectory, { recursive: true });

let done = 0;
const failures = [];

for (const slug of slugs) {
    try {
        render(slug);
        done++;
        process.stdout.write(`\r${done}/${slugs.length} rendered`);
    } catch (error) {
        failures.push(`${slug}: ${error.message}`);
    }
}

process.stdout.write('\n');

if (failures.length) {
    console.error(`${failures.length} failed:\n  ${failures.join('\n  ')}`);
    process.exit(1);
}

console.log(`Wrote ${done} demonstrations to public/images/exercises`);

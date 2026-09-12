/**
 * Hunter rank → palette class.
 *
 * The class names are written out in full on purpose: Tailwind tree-shakes the
 * `@layer components` rules, so a name built by interpolation (`sys-rank-${x}`)
 * would be stripped from the bundle.
 */
const RANK_CLASSES = {
    E: 'sys-rank-e',
    D: 'sys-rank-d',
    C: 'sys-rank-c',
    B: 'sys-rank-b',
    A: 'sys-rank-a',
    S: 'sys-rank-s',
};

/** First character of the rank, uppercased; anything unknown reads as E. */
export const rankLetter = (rank) => {
    const letter = String(rank ?? '').toUpperCase().charAt(0);

    return letter in RANK_CLASSES ? letter : 'E';
};

/** Scopes `--rank-rgb`, which the sigil, rank text and rank pills all read. */
export const rankClass = (rank) => RANK_CLASSES[rankLetter(rank)];

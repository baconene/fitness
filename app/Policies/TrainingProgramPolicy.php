<?php

namespace App\Policies;

use App\Models\TrainingProgram;
use App\Models\User;

class TrainingProgramPolicy
{
    /**
     * System programs are shared with everyone; custom ones only with their author.
     */
    public function view(User $user, TrainingProgram $program): bool
    {
        return $program->is_system_program || $program->created_by_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->hunterProfile;
    }

    /**
     * Seeded system programs stay read-only: users copy them before editing.
     *
     * Rebuilding the structure detaches any workouts already generated from it
     * (`program_day_id` is null-on-delete), which would strand an in-flight
     * enrollment, so an actively enrolled program is locked too.
     */
    public function update(User $user, TrainingProgram $program): bool
    {
        return ! $program->is_system_program
            && $program->created_by_user_id === $user->id
            && ! $program->enrollments()->where('status', 'active')->exists();
    }

    public function delete(User $user, TrainingProgram $program): bool
    {
        return $this->update($user, $program);
    }

    public function duplicate(User $user, TrainingProgram $program): bool
    {
        return $this->create($user) && $this->view($user, $program);
    }
}

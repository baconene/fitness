<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function timezone(): string
    {
        $timezone = data_get($this->onboardingProgress?->step_data, 'Account.timezone');

        return is_string($timezone) && in_array($timezone, \DateTimeZone::listIdentifiers(), true)
            ? $timezone
            : config('app.timezone', 'UTC');
    }

    public function hunterProfile()
    {
        return $this->hasOne(HunterProfile::class);
    }

    public function onboardingProgress()
    {
        return $this->hasOne(OnboardingProgress::class);
    }

    public function healthMeasurements()
    {
        return $this->hasMany(HealthMeasurement::class);
    }

    public function waterLogs()
    {
        return $this->hasMany(WaterLog::class);
    }

    public function foodLogs()
    {
        return $this->hasMany(FoodLog::class);
    }

    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class);
    }

    public function stepLogs()
    {
        return $this->hasMany(StepLog::class);
    }

    public function fitnessGoals()
    {
        return $this->hasMany(FitnessGoal::class);
    }

    public function trainingPreference()
    {
        return $this->hasOne(TrainingPreference::class);
    }

    public function equipmentProfile()
    {
        return $this->hasOne(EquipmentProfile::class);
    }

    public function healthRestrictions()
    {
        return $this->hasMany(HealthRestriction::class);
    }

    public function workouts()
    {
        return $this->hasMany(Workout::class);
    }

    public function userQuests()
    {
        return $this->hasMany(UserQuest::class);
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function bossEncounters()
    {
        return $this->hasMany(BossEncounter::class);
    }

    public function streak()
    {
        return $this->hasOne(Streak::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

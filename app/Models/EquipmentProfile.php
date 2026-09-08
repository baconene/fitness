<?php

namespace App\Models;

use Database\Factories\EquipmentProfileFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentProfile extends Model
{
    /** @use HasFactory<EquipmentProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location',
        'available_equipment',
    ];

    protected function casts(): array
    {
        return [
            'available_equipment' => AsArrayObject::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingGoal extends Model
{
    protected $fillable = ['user_id', 'name', 'target_amount', 'current_amount', 'deadline'];

    protected function casts(): array
    {
        return ['deadline' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_name',
        'email',
        'homepage',
        'text',
        'parent_id',
    ];

    // Зв'язок з батьківським коментарем
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Зв'язок з дочірніми коментарями
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('children');
    }
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

}

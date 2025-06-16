<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['file_url', 'type'];

    public function mediable()
    {
        return $this->morphTo();
    }
}


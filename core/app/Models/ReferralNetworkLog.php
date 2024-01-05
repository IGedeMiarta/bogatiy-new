<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralNetworkLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
      public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }
}

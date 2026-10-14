<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $table = 'a_surveys';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'photo',
        'latitude',
        'longitude',
        'address',
        'qr_code',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

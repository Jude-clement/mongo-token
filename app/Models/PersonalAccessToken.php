<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalAccessToken extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'personal_access_tokens';

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable()
    {
        return $this->belongsTo(UserModel::class, 'tokenable_id');
    }

    public static function findToken($token)
    {
        return static::where('token', hash('sha256', $token))->first();
    }

    public function can($ability)
    {
        return in_array('*', $this->abilities) ||
               in_array($ability, $this->abilities);
    }

    public function cant($ability)
    {
        return !$this->can($ability);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserModel extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $connection = 'mongodb';
    protected $collection = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function tokens()
    {
        return $this->hasMany(PersonalAccessToken::class, 'tokenable_id');
    }

    public function createToken(string $name, array $abilities = ['*'], $expiresAt = null)
    {
        $plainTextToken = Str::random(40);

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'tokenable_type' => get_class($this),
            'tokenable_id' => $this->getKey(),
            'expires_at' => $expiresAt
        ]);

        return new \Laravel\Sanctum\NewAccessToken($token, $token->id . '|' . $plainTextToken);
    }
    
    public function currentAccessToken()
    {
        return $this->accessToken;
    }

    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        
        return $this;
    }
}
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserJwt extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'jwt-mac.users'; // Indica que el modelo usa la tabla 'users'

    // Si tus columnas no siguen las convenciones de Laravel, define las columnas principales
    protected $primaryKey = 'id'; // Suponiendo que la columna primaria es 'id'

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_personal',
        'idcentro_mac',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

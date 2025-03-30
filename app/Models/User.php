<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'role',
        'department',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Accessor para retornar o nome completo
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Accessor para compatibilidade com o campo name padrão do Laravel
    public function getNameAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->getFullNameAttribute();
    }

    // Mutator para preencher os campos first_name e last_name ao definir name
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;

        // Se name for definido e first_name ou last_name estiverem vazios,
        // tenta dividir name em first_name e last_name
        if (!empty($value) && (empty($this->first_name) || empty($this->last_name))) {
            $parts = explode(' ', $value, 2);
            if (count($parts) >= 1) {
                $this->attributes['first_name'] = $parts[0];
            }
            if (count($parts) >= 2) {
                $this->attributes['last_name'] = $parts[1];
            }
        }
    }

    // Verificar se o usuário tem um papel específico
    public function hasRole($role)
    {
        // Usar o método do trait para verificar a role
        if (is_string($role)) {
            // Verificar usando o método do Spatie
            return parent::hasRole($role);
        }

        // Fallback para a verificação antiga
        return $this->role === $role;
    }

    // Verificar se o usuário está ativo
    public function isActive()
    {
        return $this->is_active;
    }

    // Relacionamentos
    public function tasksAssigned()
    {
        return $this->hasMany(MaintenanceTask::class, 'assigned_to');
    }

    public function plansAssigned()
    {
        return $this->hasMany(MaintenancePlan::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(MaintenanceTask::class, 'created_by');
    }

    public function updatedTasks()
    {
        return $this->hasMany(MaintenanceTask::class, 'updated_by');
    }

    public function createdPlans()
    {
        return $this->hasMany(MaintenancePlan::class, 'created_by');
    }

    public function updatedPlans()
    {
        return $this->hasMany(MaintenancePlan::class, 'updated_by');
    }
}

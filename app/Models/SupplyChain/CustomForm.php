<?php

namespace App\Models\SupplyChain;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sc_custom_forms';

    protected $fillable = [
        'name',
        'description',
        'entity_type',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtém os campos associados ao formulário
     */
    public function fields()
    {
        return $this->hasMany(CustomFormField::class, 'form_id')->orderBy('order');
    }

    /**
     * Obtém os envios deste formulário
     */
    public function submissions()
    {
        return $this->hasMany(CustomFormSubmission::class, 'form_id');
    }

    /**
     * Obtém o usuário que criou este formulário
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtém as shipping notes que usam este formulário
     */
    public function shippingNotes()
    {
        return $this->hasMany(ShippingNote::class, 'custom_form_id');
    }

    /**
     * Verifica se o formulário tem submissões
     */
    public function hasSubmissions()
    {
        return $this->submissions()->exists();
    }
}

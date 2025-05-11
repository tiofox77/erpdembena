<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomFormAttachment extends Model
{
    use HasFactory;

    protected $table = 'sc_custom_form_attachments';

    protected $fillable = [
        'field_value_id',
        'filename',
        'original_filename',
        'mime_type',
        'path',
        'size',
    ];

    /**
     * Obtém o valor do campo ao qual este anexo pertence
     */
    public function fieldValue()
    {
        return $this->belongsTo(CustomFormFieldValue::class, 'field_value_id');
    }

    /**
     * Obtém a URL completa para o arquivo
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    /**
     * Obtém o tamanho formatado do arquivo (KB, MB)
     */
    public function getFormattedSizeAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $i = 0;
        
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Obtém o ícone apropriado para o tipo de arquivo
     */
    public function getIconAttribute()
    {
        $mimeType = strtolower($this->mime_type);
        
        if (strpos($mimeType, 'image/') === 0) {
            return 'fa-file-image';
        } elseif (strpos($mimeType, 'application/pdf') === 0) {
            return 'fa-file-pdf';
        } elseif (strpos($mimeType, 'application/msword') === 0 || strpos($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml') === 0) {
            return 'fa-file-word';
        } elseif (strpos($mimeType, 'application/vnd.ms-excel') === 0 || strpos($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml') === 0) {
            return 'fa-file-excel';
        } elseif (strpos($mimeType, 'application/vnd.ms-powerpoint') === 0 || strpos($mimeType, 'application/vnd.openxmlformats-officedocument.presentationml') === 0) {
            return 'fa-file-powerpoint';
        } elseif (strpos($mimeType, 'text/') === 0) {
            return 'fa-file-alt';
        } elseif (strpos($mimeType, 'video/') === 0) {
            return 'fa-file-video';
        } elseif (strpos($mimeType, 'audio/') === 0) {
            return 'fa-file-audio';
        } elseif (strpos($mimeType, 'application/zip') === 0 || strpos($mimeType, 'application/x-rar') === 0) {
            return 'fa-file-archive';
        } else {
            return 'fa-file';
        }
    }
}

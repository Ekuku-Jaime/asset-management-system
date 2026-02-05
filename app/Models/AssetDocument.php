<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'filename', 'original_name', 'mime_type', 
        'size', 'path', 'document_type', 'description'
    ];
    
    protected $casts = [
        'size' => 'integer',
    ];
    
    /**
     * Relacionamento com Asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
    
    /**
     * Accessors
     */
    public function getFormattedSizeAttribute()
    {
        if ($this->size >= 1073741824) {
            return number_format($this->size / 1073741824, 2) . ' GB';
        } elseif ($this->size >= 1048576) {
            return number_format($this->size / 1048576, 2) . ' MB';
        } elseif ($this->size >= 1024) {
            return number_format($this->size / 1024, 2) . ' KB';
        } else {
            return $this->size . ' bytes';
        }
    }
    
    public function getFileIconAttribute()
    {
        $mimeType = strtolower($this->mime_type);
        
        if (str_contains($mimeType, 'pdf')) return 'fas fa-file-pdf text-danger';
        if (str_contains($mimeType, 'image')) return 'fas fa-file-image text-success';
        if (str_contains($mimeType, 'word') || str_contains($mimeType, 'document')) 
            return 'fas fa-file-word text-primary';
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) 
            return 'fas fa-file-excel text-success';
        if (str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') || str_contains($mimeType, '7z')) 
            return 'fas fa-file-archive text-warning';
        
        return 'fas fa-file text-secondary';
    }
}
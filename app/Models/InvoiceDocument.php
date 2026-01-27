<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 
        'filename', 
        'original_name', 
        'mime_type', 
        'size', 
        'path'
    ];

    /**
     * Relacionamento com fatura
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
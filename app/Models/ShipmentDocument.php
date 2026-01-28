<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id', 
        'filename', 
        'original_name', 
        'mime_type', 
        'size', 
        'path'
    ];

    /**
     * Relacionamento com remessa
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
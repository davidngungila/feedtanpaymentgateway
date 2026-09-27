<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderApiLog extends Model
{
    protected $fillable = ['provider', 'transaction_id', 'operation', 'http_status', 'duration_ms', 'attempt', 'success', 'request_headers_encrypted', 'request_body_encrypted', 'response_headers_encrypted', 'response_body_encrypted', 'error'];

    /**
     * Bodies/headers are encrypted at rest and hidden from serialization
     * so credentials can never leak into views, APIs or dumps.
     */
    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'request_headers_encrypted' => 'encrypted:array',
            'request_body_encrypted' => 'encrypted',
            'response_headers_encrypted' => 'encrypted:array',
            'response_body_encrypted' => 'encrypted',
        ];
    }

    protected $hidden = ['request_headers_encrypted', 'request_body_encrypted', 'response_headers_encrypted', 'response_body_encrypted'];
}

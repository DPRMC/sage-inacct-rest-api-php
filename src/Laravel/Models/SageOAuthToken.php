<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Models;

use Illuminate\Database\Eloquent\Model;

final class SageOAuthToken extends Model
{
    protected $table = 'sage_intacct_oauth_tokens';

    protected $guarded = [];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'datetime',
        'scope' => 'string',
    ];
}

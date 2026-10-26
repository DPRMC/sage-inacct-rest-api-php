<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SageReportRun extends Model
{
    protected $table = 'sage_intacct_report_runs';

    protected $guarded = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'request_payload' => 'array',
        'response_checksum' => 'string',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(SageReportRecord::class, 'run_id');
    }
}

<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SageReportRecord extends Model
{
    protected $table = 'sage_intacct_report_records';

    protected $guarded = [];

    protected $casts = [
        'source_payload' => 'array',
        'normalized_payload' => 'array',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(SageReportRun::class, 'run_id');
    }
}

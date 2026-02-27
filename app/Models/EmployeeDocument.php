<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'name',
        'path',
        'mime_type',
        'size',
        'notes',
    ];

    protected $casts = [
        'type' => DocumentType::class,
        'size' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        if (! $this->size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 1) . ' ' . $units[$unit];
    }

    public function getDownloadUrl(): ?string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (! $this->path || ! $disk->exists($this->path)) {
            return null;
        }

        return $disk->temporaryUrl($this->path, now()->addMinutes(30));
    }
}

<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class FileUpload extends Field
{
    protected string $component = 'file-field';

    protected ?string $disk = null;

    protected ?string $directory = null;

    protected bool $image = false;

    /** @var array<int, string> Accepted MIME extensions for the mimes rule, e.g. ['pdf', 'docx']. */
    protected array $acceptedTypes = [];

    protected ?int $maxSize = null;

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function image(bool $image = true): static
    {
        $this->image = $image;

        return $this;
    }

    /** @param array<int, string> $mimes */
    public function acceptedTypes(array $mimes): static
    {
        $this->acceptedTypes = array_values($mimes);

        return $this;
    }

    /** @param int $kilobytes Maximum file size in kilobytes (Laravel's `max` rule unit for files). */
    public function maxSize(int $kilobytes): static
    {
        $this->maxSize = $kilobytes;

        return $this;
    }

    /** Resolve lazily so per-app `saddle.uploads.disk` config is honored when unset. */
    protected function resolveDisk(): string
    {
        return $this->disk ?? config('saddle.uploads.disk');
    }

    /** Resolve lazily so per-app `saddle.uploads.directory` config is honored when unset. */
    protected function resolveDirectory(): string
    {
        return $this->directory ?? config('saddle.uploads.directory');
    }

    protected function typeRules(): array
    {
        $rules = ['file'];

        if ($this->image) {
            $rules[] = 'image';
        }

        if ($this->acceptedTypes !== []) {
            $rules[] = 'mimes:'.implode(',', $this->acceptedTypes);
        }

        if ($this->maxSize !== null) {
            $rules[] = 'max:'.$this->maxSize;
        }

        return $rules;
    }

    public function fill(Model $record, mixed $value): void
    {
        if ($value instanceof UploadedFile) {
            $record->{$this->name} = $value->store($this->resolveDirectory(), $this->resolveDisk());

            return;
        }

        if ($value === null) {
            $record->{$this->name} = null;

            return;
        }

        // Defensive: the `file` rule keeps a non-file string out of validated(),
        // so this never fires from the normal flow. fill() is public API, so a
        // stray string is ignored rather than written as a bogus path.
    }

    protected function meta(): array
    {
        return ['accept' => $this->accept()];
    }

    protected function accept(): ?string
    {
        if ($this->image) {
            return 'image/*';
        }

        if ($this->acceptedTypes !== []) {
            return collect($this->acceptedTypes)
                ->map(fn (string $type) => '.'.ltrim($type, '.'))
                ->implode(',');
        }

        return null;
    }
}

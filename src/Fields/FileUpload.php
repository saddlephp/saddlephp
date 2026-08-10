<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Extensions that execute or render as script when served back from the
     * application's own origin.
     *
     * Laravel derives the stored extension from the file's *content*, not its
     * name, so an attacker uploads "notes.txt" containing HTML and it lands as
     * <random>.html on the public disk. An admin opening the record clicks a
     * same-origin page under attacker control, with their session.
     *
     * @var list<string>
     */
    public const DENIED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'pht',
        'html', 'htm', 'xhtml', 'shtml', 'svg', 'xml', 'js', 'mjs', 'swf', 'htaccess',
    ];

    /** Fallback when a host publishes a partial `uploads` config block. */
    public const DEFAULT_DISK = 'public';

    /** Fallback when a host publishes a partial `uploads` config block. */
    public const DEFAULT_DIRECTORY = 'saddle';

    /**
     * Resolve lazily so per-app `saddle.uploads.disk` config is honored when unset.
     *
     * mergeConfigFrom is a shallow array_merge, so a host that publishes only
     * `uploads.disk` replaces the whole sub-array and leaves `directory` null.
     * Without the fallback that is a TypeError on every upload.
     */
    protected function resolveDisk(): string
    {
        return $this->disk ?? config('saddle.uploads.disk') ?? self::DEFAULT_DISK;
    }

    /** Resolve lazily so per-app `saddle.uploads.directory` config is honored when unset. */
    protected function resolveDirectory(): string
    {
        return $this->directory ?? config('saddle.uploads.directory') ?? self::DEFAULT_DIRECTORY;
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

        // Neither an image constraint nor an explicit allowlist was declared,
        // so fall back to a safe default set rather than accepting anything.
        //
        // `mimes` and not `extensions`: `extensions` compares the name the
        // client supplied, which the attacker chooses. `mimes` resolves the
        // extension from the file's content, the same way store() picks the one
        // it writes to disk, so the check and the filename agree.
        if (! $this->image && $this->acceptedTypes === []) {
            $rules[] = 'mimes:'.implode(',', self::allowedExtensions());
        }

        // Always bound the size. An unrestricted field was also an unbounded
        // upload, which is a cheap way to fill someone's disk.
        $rules[] = 'max:'.($this->maxSize ?? (int) config('saddle.uploads.max_size', 10240));

        return $rules;
    }

    /**
     * The default accepted set, expressed as an allowlist because Laravel ships
     * no "reject these types" rule. Override with `saddle.uploads.allowed_extensions`.
     *
     * Deliberately excludes everything in DENIED_EXTENSIONS.
     *
     * @return list<string>
     */
    protected static function allowedExtensions(): array
    {
        /** @var list<string> $configured */
        $configured = config('saddle.uploads.allowed_extensions', []);

        if ($configured !== []) {
            return $configured;
        }

        return [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'tiff', 'ico',
            'pdf', 'txt', 'csv', 'md', 'rtf',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp',
            'zip', 'gz', 'tar', '7z',
            'mp3', 'wav', 'ogg', 'mp4', 'webm', 'mov',
        ];
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

    protected function displayType(): string
    {
        return 'file';
    }

    protected function displayValue(?Model $record): mixed
    {
        if ($record === null) {
            return null;
        }

        $path = $this->resolve($record);

        return $path === null ? null : Storage::disk($this->resolveDisk())->url($path);
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

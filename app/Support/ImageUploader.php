<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Helper upload gambar untuk konten CMS
 * (hero, about, partners, projects, gallery).
 */
class ImageUploader
{
    /** Maksimum ukuran file: 5 MB. */
    public const MAX_KILOBYTES = 5120;

    /** Ekstensi/format gambar yang diizinkan. */
    public const ALLOWED_MIME = 'jpg,jpeg,png,webp';

    /**
     * Aturan validasi upload gambar (dipakai di Form Request / controller).
     *
     * @return array<string, mixed>
     */
    public static function rules(bool $required = true): array
    {
        return [
            'image' => [
                $required ? 'required' : 'nullable',
                'file',
                'image',
                'mimes:'.self::ALLOWED_MIME,
                'extensions:'.self::ALLOWED_MIME,
                'max:'.self::MAX_KILOBYTES,
            ],
        ];
    }

    /**
     * Simpan gambar ke disk publik dan kembalikan path relatifnya.
     */
    public static function store(UploadedFile $file, string $folder = 'images'): string
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'image' => 'File gagal diunggah, silakan coba lagi.',
            ]);
        }

        $folder = trim($folder, '/');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $allowed = explode(',', self::ALLOWED_MIME);

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'image' => 'Format gambar tidak didukung.',
            ]);
        }

        $name = Str::ulid().'.'.$extension;

        return $file->storeAs($folder, $name, 'public');
    }

    /**
     * Hapus gambar lama (abaikan jika kosong).
     */
    public static function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Ubah path relatif menjadi URL publik absolut.
     */
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // Sudah berupa URL absolut atau aset eksternal.
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

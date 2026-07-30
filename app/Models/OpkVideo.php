<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpkVideo extends Model
{
    protected $table = 'opk_videos';

    protected $fillable = [
        'laporan_id', 'nama_file', 'path', 'link_eksternal', 'keterangan',
    ];

    public function laporan()
    {
        return $this->belongsTo(OpkLaporan::class, 'laporan_id');
    }

    public function isLocal(): bool
    {
        return ! empty($this->path);
    }

    public function isExternal(): bool
    {
        return ! empty($this->link_eksternal);
    }

    public function embedProvider(): ?string
    {
        if (! $this->link_eksternal) {
            return null;
        }

        $host = parse_url($this->link_eksternal, PHP_URL_HOST) ?? '';

        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            return 'youtube';
        }
        if (str_contains($host, 'drive.google.com')) {
            return 'googledrive';
        }

        return null;
    }

    public function embedUrl(): ?string
    {
        $provider = $this->embedProvider();
        if (! $provider) {
            return null;
        }

        if ($provider === 'youtube') {
            return $this->youtubeEmbedUrl();
        }

        if ($provider === 'googledrive') {
            return $this->googleDriveEmbedUrl();
        }

        return null;
    }

    private function youtubeEmbedUrl(): ?string
    {
        $url = $this->link_eksternal;

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';

        if (str_contains($path, '/embed/')) {
            return $url;
        }

        if (str_contains($path, '/shorts/')) {
            $id = explode('/', trim($path, '/'))[1] ?? null;

            return $id ? "https://www.youtube.com/embed/{$id}" : null;
        }

        if (str_contains($parsed['host'] ?? '', 'youtu.be')) {
            return 'https://www.youtube.com/embed/'.trim($path, '/');
        }

        parse_str($parsed['query'] ?? '', $query);

        return isset($query['v']) ? "https://www.youtube.com/embed/{$query['v']}" : null;
    }

    private function googleDriveEmbedUrl(): ?string
    {
        $url = $this->link_eksternal;

        if (preg_match('#/file/d/([^/]+)#', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        if (isset($query['id'])) {
            return "https://drive.google.com/file/d/{$query['id']}/preview";
        }

        return null;
    }
}

<?php
namespace App\Domain\Media;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateImageDerivatives
{
    public function execute(string $disk, string $sourcePath, string $mime): array
    {
        $bytes = Storage::disk($disk)->get($sourcePath);
        [$width,$height] = @getimagesizefromstring($bytes) ?: [null,null];

        $base = 'media/derived/'.pathinfo($sourcePath, PATHINFO_FILENAME);
        $targets = [
            'thumb' => [480, 360],
            'web' => [1600, 1200],
            'signage' => [1920, 1080],
        ];

        $paths = [];
        foreach ($targets as $name => [$maxW,$maxH]) {
            $path = $base.'-'.$name.'.jpg';
            $out = $this->resizeToJpeg($bytes, $mime, $maxW, $maxH);
            if ($out === null) {
                // Safe functional fallback when GD is unavailable: preserve the
                // validated original bytes rather than silently failing upload.
                $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'img');
                $path = $base.'-'.$name.'.'.$ext;
                $out = $bytes;
            }
            Storage::disk($disk)->put($path, $out);
            $paths[$name.'_path'] = $path;
        }

        return $paths + ['width'=>$width, 'height'=>$height];
    }

    private function resizeToJpeg(string $bytes, string $mime, int $maxW, int $maxH): ?string
    {
        if (!function_exists('imagecreatetruecolor')) return null;
        $src = @imagecreatefromstring($bytes);
        if (!$src) throw new RuntimeException('Não foi possível processar a imagem.');

        $w = imagesx($src); $h = imagesy($src);
        $scale = min(1, $maxW/$w, $maxH/$h);
        $nw = max(1, (int)round($w*$scale)); $nh = max(1, (int)round($h*$scale));
        $dst = imagecreatetruecolor($nw,$nh);
        $white = imagecolorallocate($dst,255,255,255);
        imagefill($dst,0,0,$white);
        imagecopyresampled($dst,$src,0,0,0,0,$nw,$nh,$w,$h);
        ob_start(); imagejpeg($dst,null,86); $out=ob_get_clean();
        imagedestroy($src); imagedestroy($dst);
        return $out === false ? null : $out;
    }
}

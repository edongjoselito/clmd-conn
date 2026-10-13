<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Signature_image
{
    public function encode($path)
    {
        if (!is_file($path) || filesize($path) > 1048576 || filesize($path) === 0) {
            throw new InvalidArgumentException('Upload a PNG or JPEG signature of at most 1 MB.');
        }
        $info = @getimagesize($path);
        if (!$info || !in_array($info[2], array(IMAGETYPE_PNG, IMAGETYPE_JPEG), true)
            || $info[0] > 3000 || $info[1] > 3000 || $info[0] * $info[1] > 4000000) {
            throw new InvalidArgumentException('Use a valid PNG or JPEG image, no larger than 3000 × 3000 pixels and 4 megapixels.');
        }
        if (!function_exists('imagecreatefromstring')) {
            throw new RuntimeException('Image processing is unavailable. Please contact the administrator.');
        }
        $source = @imagecreatefromstring(file_get_contents($path));
        if (!$source) { throw new InvalidArgumentException('The signature image could not be read.'); }
        $scale = min(1, 1000 / $info[0], 500 / $info[1]);
        $width = max(1, (int) round($info[0] * $scale)); $height = max(1, (int) round($info[1] * $scale));
        $clean = imagecreatetruecolor($width, $height);
        imagealphablending($clean, false); imagesavealpha($clean, true);
        imagefilledrectangle($clean, 0, 0, $width, $height, imagecolorallocatealpha($clean, 255, 255, 255, 127));
        imagecopyresampled($clean, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
        ob_start(); imagepng($clean); $png = ob_get_clean();
        imagedestroy($source); imagedestroy($clean);
        if (strlen($png) > 1048576) { throw new InvalidArgumentException('The signature image is too large. Please use a smaller image.'); }
        return 'data:image/png;base64,' . base64_encode($png);
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile_picture
{
    public function encode($path)
    {
        if (!is_file($path) || filesize($path) === 0 || filesize($path) > 2097152) {
            throw new InvalidArgumentException('Choose a PNG or JPEG profile picture up to 2 MB.');
        }
        $info = @getimagesize($path);
        if (!$info || !in_array($info[2], array(IMAGETYPE_PNG, IMAGETYPE_JPEG), true)
            || $info[0] > 4000 || $info[1] > 4000 || $info[0] * $info[1] > 8000000) {
            throw new InvalidArgumentException('Use a valid PNG or JPEG, up to 4000 pixels per side and 8 megapixels.');
        }
        if (!function_exists('imagecreatefromstring')) { throw new RuntimeException('Image processing is unavailable.'); }
        $source = @imagecreatefromstring(file_get_contents($path));
        if (!$source) { throw new InvalidArgumentException('The profile picture could not be read.'); }
        $scale = min(1, 400 / max($info[0], $info[1]));
        $width = max(1, (int) round($info[0] * $scale));
        $height = max(1, (int) round($info[1] * $scale));
        $clean = imagecreatetruecolor($width, $height);
        imagefilledrectangle($clean, 0, 0, $width, $height, imagecolorallocate($clean, 255, 255, 255));
        imagecopyresampled($clean, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
        ob_start(); imagejpeg($clean, null, 85); $jpeg = ob_get_clean();
        imagedestroy($source); imagedestroy($clean);
        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }
}

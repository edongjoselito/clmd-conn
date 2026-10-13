<?php
// Run: php -n tests/monitoring_signature_test.php (requires GD).
require __DIR__ . '/monitoring_assessment_test.php';
require APPPATH . 'libraries/Signature_image.php';
$encoder = new Signature_image;
$path = tempnam(sys_get_temp_dir(), 'clmd-signature-');
try {
    $image = imagecreatetruecolor(1200, 600);
    imagealphablending($image, false); imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 255, 255, 127));
    imageline($image, 10, 80, 400, 90, imagecolorallocate($image, 0, 0, 0));
    imagepng($image, $path);
    file_put_contents($path, '<?php echo "unsafe"; ?>', FILE_APPEND);
    $signature = $encoder->encode($path);
    $binary = base64_decode(substr($signature, strlen('data:image/png;base64,')), true);
    $info = getimagesizefromstring($binary);
    check($info[0] === 1000 && $info[1] === 500, 'Signature resized to bounded dimensions');
    check(strpos($binary, '<?php') === false, 'Trailing payload stripped by re-encoding');
    $decoded = imagecreatefromstring($binary);
    check((imagecolorat($decoded, 0, 0) >> 24 & 0x7f) === 127, 'PNG transparency preserved');
    imagedestroy($decoded);
    imagejpeg($image, $path); check(strpos($encoder->encode($path), 'data:image/png;base64,') === 0, 'JPEG converted to PNG');
    imagedestroy($image);
    foreach (array('<svg onload="alert(1)"></svg>', '<?php echo 1;', str_repeat('x', 1048577), '') as $invalid) {
        file_put_contents($path, $invalid); clearstatcache(true, $path);
        $rejected = false; try { $encoder->encode($path); } catch (InvalidArgumentException $e) { $rejected = true; }
        check($rejected, 'Invalid/oversized image rejected');
    }
    $payload['monitor_signature'] = $signature;
    $payload['monitor_user_id'] = 7;
    $payload['details']['monitor_one'] = 'Sample Monitor';
    $print = $render->render($payload, true);
    check(strpos($print, 'Sample Monitor') !== false && strpos($print, html_escape($signature)) !== false, 'Print contains monitor name and signature');
    $html = $render->render($payload, false);
    check(strpos($html, 'name="monitor_one"') === false, 'Monitor identity is not editable');
} finally { unlink($path); }
class CI_Controller { public $session, $db, $load, $User_signature_model; }
require APPPATH . 'controllers/Pages.php';
class IdentityDb {
    public function where($key, $value) { return $this; }
    public function get($table) { return $this; }
    public function row() { return (object) array('fname' => 'Logged', 'mname' => null, 'lname' => 'User'); }
}
class IdentityLoad { public function model($name) {} }
class IdentitySignature { public function initialize() {} public function get($id) { return 'profile-signature'; } }
$controller = (new ReflectionClass('Pages'))->newInstanceWithoutConstructor();
$controller->session = (object) array('id' => 7);
$controller->db = new IdentityDb; $controller->load = new IdentityLoad; $controller->User_signature_model = new IdentitySignature;
$identity = new ReflectionMethod('Pages', 'monitoring_monitor_identity'); $identity->setAccessible(true);
$new = $identity->invoke($controller, array('details' => array('monitor_one' => 'Forged name')));
check($new['details']['monitor_one'] === 'Logged User' && $new['monitor_user_id'] === 7 && $new['monitor_signature'] === 'profile-signature', 'Use authenticated profile identity and signature');
$new['monitor_signature'] = 'saved-signature';
check($identity->invoke($controller, $new)['monitor_signature'] === 'saved-signature', 'Preserve saved signature after profile replacement');
$new['monitor_user_id'] = 8; $new['details']['monitor_one'] = 'Original Monitor';
check($identity->invoke($controller, $new) === $new, 'Another editor cannot replace original monitor identity');
echo "PASS: PNG/JPEG validation, size limits, sanitization, transparency, signature rendering, authenticated identity, and saved signature preservation.\n";

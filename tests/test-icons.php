<?php
/**
 * Icon registry tests.
 *
 * Runs without WordPress: the few core functions the class touches are stubbed
 * below. Run with `php tests/test-icons.php` — it exits non-zero on failure.
 */

define('ABSPATH', true);
function __($s,$d=''){return $s;}
function esc_attr($s){return htmlspecialchars((string)$s,ENT_QUOTES);}
function sanitize_key($s){return strtolower(preg_replace('/[^a-z0-9_\-]/i','',(string)$s));}
function apply_filters($t,$v){return $v;}
require __DIR__ . '/../restaurant-menu-builder/includes/class-icons.php';
use RestaurantMenuBuilder\Icons;

$fail = 0;
function check($label, $got, $want) { global $fail; if ($got !== $want) { $fail++; echo "FAIL $label\n  got:  $got\n  want: $want\n"; } else { echo "ok   $label\n"; } }

// Unknown keys are rejected.
check('unknown key', Icons::sanitize('does-not-exist'), '');
check('legacy key kept', Icons::sanitize('starter'), 'starter');
check('render unknown', Icons::render('nope'), '');

// A hostile filter cannot smuggle markup through path data or circles.
$evil = array( 'paths' => array( 'M0 0"/><script>alert(1)</script><path d="M1 1' ), 'circles' => array( '1 2 3"/><script>x</script>' ) );
$paths = implode('|', Icons::paths_of($evil));
check('path has no markup', (int) ( strpbrk($paths, '<>"') === false ), 1);
check('circle rejected', count(Icons::circles_of($evil)), 0);

// 1.0 single-path format still renders.
$legacy = array( 'path' => 'M3 12h18' );
check('legacy path format', implode('|', Icons::paths_of($legacy)), 'M3 12h18');

// Every registered icon produces markup and belongs to a known group.
$groups = Icons::groups();
foreach (Icons::all() as $key => $icon) {
    if (Icons::render($key) === '') { echo "FAIL empty render: $key\n"; $fail++; }
    if (!isset($groups[$icon['group'] ?? ''])) { echo "FAIL bad group: $key\n"; $fail++; }
    if (!isset($icon['label']) || $icon['label'] === '') { echo "FAIL no label: $key\n"; $fail++; }
}
foreach (Icons::ui() as $key => $icon) {
    if (Icons::render_ui($key) === '') { echo "FAIL empty ui render: $key\n"; $fail++; }
}
echo $fail === 0 ? "\nALL PASS (" . count(Icons::all()) . " icons)\n" : "\n$fail FAILURES\n";
exit($fail === 0 ? 0 : 1);

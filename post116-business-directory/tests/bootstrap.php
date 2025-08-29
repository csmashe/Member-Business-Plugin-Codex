<?php
// Minimal bootstrap: attempt to load WordPress test suite if available.
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    fwrite(STDERR, "WP_TESTS_DIR not set; skipping WordPress bootstrap.\n");
    return;
}
require_once $_tests_dir . '/includes/functions.php';
tests_add_filter('muplugins_loaded', function () {
    require dirname(__DIR__) . '/post116-business-directory.php';
});
require $_tests_dir . '/includes/bootstrap.php';


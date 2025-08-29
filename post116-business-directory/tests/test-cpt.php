<?php
use PHPUnit\Framework\TestCase;

class Test_CPT extends TestCase {
    public function test_constants_exist() {
        if (!class_exists('P116BD\\CPT')) {
            $this->markTestSkipped('Plugin not loaded.');
        }
        $this->assertTrue(defined('P116BD_VERSION'));
        $this->assertTrue(defined('P116BD_PLUGIN_DIR'));
        $this->assertTrue(defined('P116BD_PLUGIN_URL'));
        $this->assertNotEmpty(\P116BD\CPT::POST_TYPE);
        $this->assertNotEmpty(\P116BD\CPT::TAXONOMY);
    }
}


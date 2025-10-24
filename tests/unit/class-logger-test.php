<?php
namespace OlogyBrewing\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OlogyBrewing\Logger;

class LoggerTest extends TestCase {
    private $logger;
    
    protected function setUp(): void {
        $this->logger = new Logger();
    }
    
    public function test_log_writes_to_file() {
        $this->logger->info('Test message');
        
        $log_file = WP_CONTENT_DIR . '/logs/ology-brewing/info.log';
        $this->assertFileExists($log_file);
        
        $content = file_get_contents($log_file);
        $this->assertStringContainsString('Test message', $content);
    }
    
    public function test_log_writes_to_transient() {
        $this->logger->info('Test message');
        
        $recent_logs = get_transient('ology_brewing_recent_logs');
        $this->assertIsArray($recent_logs);
        $this->assertCount(1, $recent_logs);
        $this->assertEquals('Test message', $recent_logs[0]['message']);
    }
    
    public function test_log_levels() {
        $this->logger->error('Error message');
        $this->logger->warning('Warning message');
        $this->logger->info('Info message');
        $this->logger->debug('Debug message');
        
        $recent_logs = get_transient('ology_brewing_recent_logs');
        $this->assertCount(4, $recent_logs);
        
        $levels = array_column($recent_logs, 'level');
        $this->assertContains('error', $levels);
        $this->assertContains('warning', $levels);
        $this->assertContains('info', $levels);
        $this->assertContains('debug', $levels);
    }
    
    public function test_debug_disabled() {
        update_option('ology_debug_enabled', false);
        
        $this->logger->debug('Debug message');
        
        $recent_logs = get_transient('ology_brewing_recent_logs');
        $this->assertEmpty($recent_logs);
        
        update_option('ology_debug_enabled', true);
    }
    
    public function test_log_rotation() {
        // Create a large log file
        $log_file = WP_CONTENT_DIR . '/logs/ology-brewing/info.log';
        $large_content = str_repeat('A', 10485761); // 10MB + 1 byte
        file_put_contents($log_file, $large_content);
        
        $this->logger->info('New message');
        
        // Check if old file was renamed
        $rotated_files = glob(WP_CONTENT_DIR . '/logs/ology-brewing/info.log.*');
        $this->assertNotEmpty($rotated_files);
    }
    
    public function test_clear_logs() {
        $this->logger->info('Test message');
        $this->logger->clear_logs();
        
        $recent_logs = get_transient('ology_brewing_recent_logs');
        $this->assertEmpty($recent_logs);
    }
}

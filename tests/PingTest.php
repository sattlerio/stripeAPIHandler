<?php
namespace Tests;

class HomepageTest extends BaseTestCase
{
    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testGetHomepageWithoutName()
    {
        $response = $this->runApp('GET', '/ping');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('pong', (string)$response->getBody());
    }
}
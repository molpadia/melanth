<?php

namespace Melanth\Tests\Foundation;

use UnexpectedValueException;
use Melanth\Foundation\Application;
use Melanth\Foundation\Config;
use Melanth\Foundation\Bootstrap\ConfigurationLoader;
use Melanth\Foundation\Bootstrap\RegisterFacades;
use Melanth\Foundation\Bootstrap\RegisterProviders;
use Melanth\Foundation\Bootstrap\BootProviders;

class BootstrapperTest extends TestCase
{
    private $configPath;

    public function setUp() : void
    {
        parent::setUp();

        $this->configPath = __DIR__.'/config';

        @mkdir($this->configPath);
    }

    public function tearDown() : void
    {
        @rmdir($this->configPath);

        parent::tearDown();
    }

    public function testBootstrapConfiguration()
    {
        $app = new Application(__DIR__);

        fclose(fopen("{$this->configPath}/app.php", 'w'));

        $this->assertNull((new ConfigurationLoader)->bootstrap($app));
        @unlink("{$this->configPath}/app.php");
    }

    public function testBootstrapConfigurationWithUndefinedPath()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unable to load app configuration file.');

        $app = new Application(realpath(__DIR__));

        $this->assertNull((new ConfigurationLoader)->bootstrap($app));
    }

    public function testRegisterServiceProviders()
    {
        $config = new Config(['app' => ['providers' => ['foo']]]);
        $mock = $this->createMock(Application::class);
        $mock->expects($this->once())
            ->method('offsetGet')
            ->will($this->returnValue($config));

        $mock->expects($this->once())
            ->method('register')
            ->with($this->equalTo('foo'));

        $this->assertNull((new RegisterProviders)->bootstrap($mock));
    }

    public function testBootstrapProviders()
    {
        $mock = $this->createMock(Application::class);
        $mock->expects($this->once())
            ->method('boot');

        $this->assertNull((new BootProviders)->bootstrap($mock));
    }

    public function testBootstrapFacades()
    {
        $this->assertNull((new RegisterFacades)->bootstrap(new Application));
    }
}

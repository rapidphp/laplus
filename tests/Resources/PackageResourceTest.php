<?php

namespace Rapid\Laplus\Tests\Resources;

use Rapid\Laplus\Resources\PackageResource;
use Rapid\Laplus\Resources\SharedPackageResource;
use Rapid\Laplus\Tests\TestCase;

class PackageResourceTest extends TestCase
{
    public function test_shared_package_resource_path()
    {
        $resource = new SharedPackageResource('my-package', 'models', 'travels');

        $this->assertSame('my-package', $resource->packageName);
        $this->assertSame(database_path('migrations/vendor/my-package'), $resource->migrations);
        $this->assertSame(database_path('migrations/vendor/my-package/dev_generated'), $resource->devMigrations);
    }

    public function test_dev_package_work_on_developing_package()
    {
        $resource = new PackageResource('my-package', 'models', __DIR__, __DIR__, 'travels');

        $this->assertSame('my-package', $resource->packageName);
        $this->assertSame(true, $resource->shouldGenerate());
    }

    public function test_dev_package_not_generate_when_used_as_installed_package()
    {
        $example = __DIR__ . '/../../vendor/bin';
        $resource = new PackageResource('my-package', 'models', $example, $example, 'travels');

        $this->assertSame('my-package', $resource->packageName);
        $this->assertSame(false, $resource->shouldGenerate());
    }
}
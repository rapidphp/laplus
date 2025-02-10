<?php

namespace Rapid\Laplus\Present\Generate\Testing;

use PHPUnit\Framework\Assert;
use Rapid\Laplus\Present\Generate\MigrationExporter;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\Generate\Structure\MigrationFileListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationFileState;

class ExportedTesting
{
    public function __construct(
        public MigrationGenerator     $generator,
        public MigrationExporter      $exporter,
        public MigrationFileListState $files,
    )
    {
    }

    public function assertCreateTable(string $table)
    {
        Assert::assertTrue(
            collect($this->files->files)->contains(function (MigrationFileState $file, string $key) use ($table) {
                return str_ends_with($key, "_create_{$table}_table") &&
                    str_contains($file->up[0] ?? '', "Schema::create('{$table}'");
            }),
            "Table `{$table}` is not created.",
        );

        return $this;
    }

    public function assertModifyTable(string $table)
    {
        Assert::assertTrue(
            collect($this->files->files)->contains(function (MigrationFileState $file, string $key) use ($table) {
                return str_contains($file->up[0] ?? '', "Schema::table('{$table}'");
            }),
            "Table `{$table}` is not modified.",
        );

        return $this;
    }

    public function assertDropTable(string $table)
    {
        Assert::assertTrue(
            collect($this->files->files)->contains(function (MigrationFileState $file, string $key) use ($table) {
                return str_ends_with($key, "_drop_{$table}_table") &&
                    str_contains($file->up[0] ?? '', "Schema::drop('{$table}');");
            }),
            "Table `{$table}` is not dropped.",
        );

        return $this;
    }

    public function assertContainsFile(string $name)
    {
        Assert::assertTrue(
            collect($this->files->files)->keys()->contains(function (string $key) use ($name) {
                return str_ends_with($key, '_' . $name);
            }),
            "Migration `{$name}` is not generated.",
        );

        return $this;
    }

    public function assertFileNames(array $names)
    {
//        dd($this->files);
        $this->assertFileCount(count($names));
        Assert::assertTrue(
            !collect($this->files->files)->keys()->contains(function (string $key, int $index) use ($names) {
                return !str_ends_with($key, '_' . $names[$index]);
            }),
            "Generated migrations is not the same as expected.",
        );

        return $this;
    }

    public function assertFiles(array $contents)
    {
        $index = 0;

        $this->assertFileCount(count($contents));

        foreach ($this->files->files as $key => $file) {
            $expected = $contents[$index++];

            if (isset($expected['up'])) {
                Assert::assertSame($expected['up'], $file->up, "Migration up command on `$key` is not correct.");
            }

            if (isset($expected['down'])) {
                Assert::assertSame($expected['down'], $file->down, "Migration down command on `$key` is not correct.");
            }

            if (isset($expected['up.table'])) {
                Assert::assertTrue(
                    str_starts_with($file->up[0] ?? '', "Schema::create(") ||
                    str_starts_with($file->up[0] ?? '', "Schema::table("),
                    "Migration `$key` is not modifying the table.",
                );
                Assert::assertSame("});", end($file->up) ?: '');

                Assert::assertSame($expected['up.table'], array_map('trim', array_slice($file->up, 1, -1)), "Migration up command on `$key` is not correct.");
            }
        }

        return $this;
    }

    public function assertFileCount(int $count)
    {
        Assert::assertSame(
            $count,
            count($this->files->files),
            "Generated migrations size is not the same as expected.",
        );

        return $this;
    }
}
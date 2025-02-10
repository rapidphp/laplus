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

    public function assertCreateTable(string $table): void
    {
        Assert::assertTrue(
            collect($this->files->files)->contains(function (MigrationFileState $file, string $key) use ($table) {
                return str_ends_with($key, "_create_{$table}_table") &&
                    str_contains($file->up[0] ?? '', "Schema::create('{$table}'");
            }),
            "Table `{$table}` is not created.",
        );
    }

    public function assertModifyTable(string $table): void
    {
        Assert::assertTrue(
            collect($this->files->files)->contains(function (MigrationFileState $file, string $key) use ($table) {
                return str_ends_with($key, "_modify_{$table}_table") &&
                    str_contains($file->up[0] ?? '', "Schema::table('{$table}'");
            }),
            "Table `{$table}` is not modified.",
        );
    }

    public function assertContainsFile(string $name): void
    {
        Assert::assertTrue(
            collect($this->files->files)->keys()->contains(function (string $key) use ($name) {
                return str_ends_with($key, '_' . $name);
            }),
            "Migration `{$name}` is not generated.",
        );
    }

    public function assertFileNames(array $names): void
    {
        Assert::assertTrue(
            count($this->files->files) === count($names) &&
            !collect($this->files->files)->keys()->contains(function (string $key, int $index) use ($names) {
                return !str_ends_with($key, '_' . $names[$index]);
            }),
            "Generated migrations is not the same as expected.",
        );
    }
}
<?php

namespace Rapid\Laplus\Commands;

class LaplusRegenerateCommand extends LaplusGenerateCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:regenerate {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name} {--all : Remove all snapshots and regenerate}';
    protected $aliases = ['regenerate+'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old migrations and Re-generate the migrations using presents';

    public function handle()
    {
        if ($this->option('all') && !$this->output->confirm("All the migrations will be deleted! Are you sure?", false)) {
            return 1;
        } elseif (!$this->option('all') && !$this->output->confirm("All the migrations after last snapshot will be deleted! Are you sure?", false)) {
            return 1;
        }

        return parent::handle();
    }

    public function generate(string $modelPath, string $migrationPath)
    {
        // Detect target migrations
        $target = collect(@scandir($migrationPath) ?: [])
            ->filter(fn($file) => is_file($migrationPath . '/' . $file) && !str_starts_with($file, '0001_01_01'));

        // Filter snapshot
        if (!$this->option('all')) {
            if ($lastSnapshot = $target->last(fn($file) => str_ends_with($file, '.snapshot'))) {
                $target = $target->slice($target->values()->search($lastSnapshot) + 1);
            }
        }

        // Delete old migrations
        foreach ($target as $file) {
            @unlink($migrationPath . '/' . $file);
        }

        return parent::generate($modelPath, $migrationPath);
    }

}
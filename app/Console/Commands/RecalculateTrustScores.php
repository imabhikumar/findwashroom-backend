<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TrustService;
use Illuminate\Console\Command;

class RecalculateTrustScores extends Command
{
    protected $signature = 'trust:recalculate';
    protected $description = 'Recalculate trust scores for all users';

    public function handle(TrustService $trustService)
    {
        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $trustService->recalculateAndSaveScore($user->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Trust scores recalculated for ' . $users->count() . ' users.');
    }
}
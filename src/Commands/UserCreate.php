<?php

namespace Zakalajo\ApiGenerator\Commands;

use App\Models\User as ModelsUser;
use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;

class UserCreate extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaff:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user';

    /**
     * Execute the console command.
     */
    public function handle() {
        $user = ModelsUser::factory()->create();

        $this->info('Generated successfully');

        $this->info('Email is: ' . $user->email);
    }
}

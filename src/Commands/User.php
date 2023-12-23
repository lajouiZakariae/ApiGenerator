<?php

namespace Zakalajo\ApiGenerator\Commands;

use App\Models\User as ModelsUser;
use Illuminate\Console\Command;

class User extends Command {
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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        ModelsUser::factory()->create();
    }
}

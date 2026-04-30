<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Task;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123') ,
        ]);

        $cat1 = Category::create([
            'name' => 'Developement'
        ]);

        $cat2 = Category::create([
            'name' => 'Maintenanc'
        ]);

        $cat3 = Category::create([
            'name' => 'Reunion'
        ]);

        $cat4 = Category::create([
            'name' => 'Formation'
        ]);

        $cat5 = Category::create([
            'name' => 'Marketing'
        ]);

        $cat6 = Category::create([
            'name' => 'Deploiement'
        ]);


        Task::create([
            'title'=> 'Developement API utilisateurs' , 
            'description' => 'Cree les endpoint pour CRUD utilisateurs' , 
            'status' => 'done' , 
            'category_id' => $cat1->id ,
            'due_date' => '2026-04-30' ,    
            'user_id' => 1,
        ]);

        Task::create([
            'title' => 'Corriger bug login',
            'description' => 'Les utilisateurs ne peuvent pas se connecter',
            'status' => 'todo',
            'due_date' => '2026-05-01',
            'category_id' => $cat2->id,
            'user_id' => 1
        ]);

        Task::create([
            'title' => 'Reunion equipe projet',
            'description' => 'Point sur l\'avancement du projet',
            'status' => 'in_progress',
            'due_date' => '2026-04-15',
            'category_id' => $cat3->id,
            'user_id' => 1
        ]);

        Task::create([
            'title' => 'Formation sur Laravel 13',
            'description' => 'Apprendre les bases de Laravel 13',
            'status' => 'todo',
            'due_date' => '2026-04-30',
            'category_id' => $cat4->id,
            'user_id' => 1
        ]);

        Task::create([
            'title' => 'Déployer en production',
            'description' => 'Mise en ligne de la nouvelle version',
            'status' => 'todo',
            'due_date' => '2026-04-10',
            'category_id' => $cat1->id,
            'user_id' => 1
        ]);



    }
}
   
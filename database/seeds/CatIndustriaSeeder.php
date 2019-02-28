<?php

use Illuminate\Database\Seeder;

class CatIndustriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cat_industrias')->insert(['nombre' => 'Industria pesada', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Siderurgícas', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Metalúrgicas', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Cementeras', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Químicas de base', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Petroquímicas', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Automovilísticas', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Industria ligera', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Alimentación', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Peletera', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Textil', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Farmacéutica', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Armamentística', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Robótica', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Informática', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Astronáutica', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Mecánica', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Aeroespacial', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cat_industrias')->insert(['nombre' => 'Industria punta', 'created_at' => now(), 'updated_at' => now()]);
    }
}

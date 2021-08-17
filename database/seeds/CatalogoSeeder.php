<?php

use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Categorias fuentes
        DB::table('cat_fuentes')->insert(['nombre'=>'Facebook','url'=>'https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/facebook.svg','status'=>1 ,'created_at'=> now()]);
        DB::table('cat_fuentes')->insert(['nombre'=>'Google','url'=>'https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/google.svg','status'=>1 ,'created_at'=> now()]);
        DB::table('cat_fuentes')->insert(['nombre'=>'Manual','url'=>'https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/manual.svg','status'=>1 ,'created_at'=> now()]);
        DB::table('cat_fuentes')->insert(['nombre'=>'Llamada','url'=>'https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/incoming-call2.svg','status'=>1 ,'created_at'=> now()]);
        
        //Categoria Medio Contacto
        DB::table('mediocontacto_catalogo')->insert(['nombre'=>'Nota','color'=>'#ffd505','created_at'=> now()]);
        DB::table('mediocontacto_catalogo')->insert(['nombre'=>'Llamada','color'=>'#297c35','created_at'=> now()]);
        DB::table('mediocontacto_catalogo')->insert(['nombre'=>'Whatsapp','color'=>'#33CC66','created_at'=> now()]);
        DB::table('mediocontacto_catalogo')->insert(['nombre'=>'Correo','color'=>'#408af9','created_at'=> now()]);
        DB::table('mediocontacto_catalogo')->insert(['nombre'=>'Reunión','color'=>'#f9ab3f','created_at'=> now()]);

    }
}

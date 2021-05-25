<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;


class PruebasController extends Controller
{
   public function index(){
       
       $titulo = "Animales";
       $animales = ['Perro', 'Gato', 'Tigre'];
       
       return view('index', array(
           'titulo' => $titulo,
           'animales' => $animales
       ));
   }
   
   public function testOrm(){
      
       
       $posts = Post::all();  
       //echo var_dump($posts);
       
       foreach($posts as $post){
           echo "<h1>".$post->title."</h1>";
           echo "<span style='color:gray;'>{$post->categorie->name}- {$post->user->name} </span>";
           echo "<p>".$post->content."</p>";
           echo '<hr>';
       }
        
         
     
       
       /*
       $categories = Category::all();
       foreach($categories as $category){
           echo  "<h1>{$category->name}</h1>";
           
           foreach($category as $post){
                echo "<h1>".$post->title."</h1>";
                echo "<span style='color:gray;'>{$post->user-> name} - {$category->user-> name} </span>";
                echo "<p>".$post->content."</p>";
                echo '<hr>';
            }
        * 
        */
       //}
      
        //die();
   }
    
}


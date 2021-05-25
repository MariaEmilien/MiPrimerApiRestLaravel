<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;


class PostController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except'=>[
            'index',
            'show', 
            'getImage',
            'getPostsByCategory',
            'getPostsByUser'
            ]]);
    }
    
    public function index(){
        $posts = Post::all();
        
        return response()->json([
            'code'   => 200,
            'status' => 'success',
            'posts'  => $posts
        ], 200 );
        
    }
    
    public function show($id){
        $post = Post::find($id);
        
        if(is_object($post)){
            $data = [
                'code'   => 200,
                'status' => 'success',
                'posts'  => $post
            ];
            
        }else{
            $data = [
                'code'   => 400,
                'status' => 'error',
                'message'  => 'La entrada no existe'
            ];
        }
        
        return response ()->json($data, $data['code']);
    }
    
    public function store(Request $request){
        //Recoger datos por Post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
        //Conseguir ususario identificado
         $user = $this->getIdentity($request);
        
        //Validar los datos
        $validate = \Validator::make($params_array, [
            'title'       => 'required',
            'content'     => 'required',
            'category_id' => 'required',
            'image'       => 'required'
        ]);//Datos obligatorios de entrada
        
        if($validate->fails()){
            $data = [
                'code'   => 400,
                'status' => 'error',
                'message'  => 'No se ha guardado el post, faltan datos'
            ];
        }else{
            //Guardar el artículo
            $post = new Post();
                /* @var $user type */
            $post->user_id = $user->sub;
            $post->category_id = $params->category_id;
            $post->title = $params->title;
            $post->content = $params->content;
            $post->image = $params->image;
            $post->save();// Esta línea hace la entrada en la base de datos
            
            $data = [
                'code'   => 200,
                'status' => 'success',
                'posts'  => $post
            ];
            
        }
        
            
        }else{
            $data = [
                'code'   => 400,
                'status' => 'error',
                'message'  => 'Envía los datos correctamente'
            ];
            
        }
        
        //Devolver respuesta
        return response()->json($data, $data['code']);
    }
    
    public function update($id, Request $request){
        //Recoger los datos por Post
        $json = $request->input('json', null);
        $params_array = json_decode($json,true);
        
        //Datos para devolver
        $data = [
                'code'   => 400,
                'status' => 'error',
                'message'  => 'Datos incorrectos'
            ]; 
        
        if(!empty($params_array)){
        
            //Validar datos
            $validate = \Validator::make($params_array,[
                'title'   => 'required',
                'content' => 'required',
                'category_id'=> 'required'
            ]);
            
            if($validate->fails()){
                $data['errors']= $validate->errors;
                return response()->json($data, $data['code']);
            }
            
            //Eliminar lo que no se quiere actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);
            
            //Conseguir usuario identificado
            $user = $this->getIdentity($request);
            
            //Conseguir el registro
            $post = Post::where('id',$id)
                  ->where('user_id', $user->sub)
                  ->first();
            
            if(!empty($post) && is_object($post)){
                
                //Actualizar el registro
                $post->update($params_array);
                
                //Devolver algo
                
                $data = [
                    'code'   => 200,
                    'status' => 'success',
                    'post'   => $post,
                    'changes'   => $params_array
                ]; 
            }
            
        }
            return response()->json($data, $data['code']);
    }
    
    public function destroy($id, Request $request){
        //Conseguir usuario identificado
        $user = $this->getIdentity($request);
        
        //Conseguir el registro
        $post = Post::where('id',$id)
                ->where('user_id', $user->sub)
                ->first();
        
        //Comprobar si existe el registro
        $post = Post::find($id);
        
        if(!empty($post)){
            
            //Borrarlo
            $post->delete();
        
            //Devolver algo
             $data = [
                'code'    => 200,
                'status'  => 'success',
                'post'    => $post
             ];
        }else{
            $data = [
                'code'  =>  404,
                'status'=> 'error',
                'message' => 'El post no existe'
            ];
        }
        
        return response()->json($data, $data['code']);
    }
    
    private function getIdentity($request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        
        return $user;
    }
    
    public function upload(Request $request){
        //Recoger la imagen de la petición
        $image = $request->file('file0'); //Depende de como se envían los datos desde el FrontEnd
        
        //Validar la imagen
        $validate = \Validator::make($request->all(),[
           'file0' => 'required|image|mimes:jpg,jpeg,png,gif' 
        ]);
        
        //Guardar la imagen
        if(!$image || $validate->fails()){
            $data = [
                'code'    => 400,
                'status'  => 'error',
                'message' => 'Error al subir la imagen'
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();
            // se concatena la función time para agregar un string que hace que la imagen sea única
            
            \Storage::disk('images')->put($image_name, \File::get($image));
            
            $data = [
                'code'    => 200,
                'status'  => 'success',
                'image'   => $image_name
            ];
        }
        
        //Devolver datos
        return response()->json($data, $data['code']);
        
    }
    
    public function getImage($filename){
        //Comprobar si existe el fichero
        $isset = \Storage::disk('image')->exists($filename);
        
        if($isset){
             //Conseguir la imagen
            $file = \Storage::disk('image')->get($filename);
            
             //Devolver la imagen
            return new Response($file, 200);
        }else{
             //Mostrar Error
            $data = [
                'code'   => 404,
                'status' => 'error',
                'message'=> 'La imagen no existe'
            ];
        }
          
         return response()->json($data, $data['code']);
       
    }
    
    //Listando artículos por usuario y categoría
    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();
        //Resultado de todos los posts listados por categoría
        
        return response()->json([
            'status'  => 'success',
            'posts'   => $posts
        ], 200);
    }
    
    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();
        
        return response()->json([
           'status'  => 'success',
            'posts'  => $posts
        ], 200);
    }
}


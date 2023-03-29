<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except'=>[
            'index',
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostsByUser'
        ]]);
    }
    public function index(){
        $posts = Post::all()->load('category');
        return response()->json([
            'code'      =>  200,
            'status'    =>  'success',
            'posts'=>  $posts
        ], 200);

    }
    public function show($id){
        $post = Post::find($id)->load('category')
                               ->load('user');
        if (is_object($post)){
            $data = [
                'code'      =>  200,
                'status'    =>  'success',
                'post'=>  $post
            ];
        }else{
            $data = [
                'code'      =>  404,
                'status'    =>  'error',
                'message'=>  'El post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }//////////////////fin show//////////////

     public function store(Request $request){
            //Recoger los datos por post
            $json = $request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);

            if (!empty($params_array)) {
            //Conseguir el usuario identificado
                $user = $this->getIdentity($request);

            //Validar los datos
                $validate = \Validator::make($params_array, [
                    'title'         => 'required',
                    'content'       => 'required',
                    'category_id'   => 'required',
                    'image'         => 'required'

                ]);


                if ($validate->fails()) {
                    $data = [
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'No se ha guardado el post, faltan datos'
                    ];
            //Guardar el post
                } else {
                    $post = new Post();
                    $post->user_id = $user->sub;
                    $post->category_id = $params->category_id;
                    $post->title = $params->title;
                    $post->content = $params->content;
                    $post->image = $params->image;
                    $post->save();

                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post
                    ];
                }
            } else {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No envia los datos correctamente'
                ];
            }
         //Devolver los datos
         return response()->json($data, $data['code']);
     }///////////////fin store////////////

    public function update($id, Request $request){
        //Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados de manera incorrecta'
        ];

        if (!empty($params_array)) {
            //Identificar el usuario
            $user = $this->getIdentity($request);
            //Validar datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if ($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            ///Eliminar lo que no queremos que se actualice
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            //Buscar registro
            $post = Post::where('id', $id)
                        ->where('user_id', $user->sub)
                        ->first();

            ///Devolver algo
            if (!empty($post) && is_object($post)){
                ///Actualizar registro concreto
                $post->update($params_array);
                $data = [
                    'code'      => 200,
                    'status'    => 'success',
                    'changes'   => $params_array,
                    'post'      => $post
                ];
            }else{
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No puedes actualizar este post'
                ];
            }
        }

        return response()->json($data, $data['code']);
    }///////////////fin update////////////

    public function destroy($id, Request $request){
        //Conseguir el usuario identificado
        $user = $this->getIdentity($request);

        //Recoger  el registro(post)
        $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)->first();

        if (!empty($post)){
            //Borrarlo
            $post->delete();

            //Devolver algo
            $data = [
                'code'      => 200,
                'status'    => 'success',
                'post'      => $post
            ];
        }else{
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'      => 'El post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }//////////fin destroy//////////
    public function upload(Request $request){
        //Recoger la imagen de la petición
        $image = $request->file('file0');

        //Validar imagen
        $validate = \Validator::make($request->all(),[
            'file0'=>'required|image|mimes:jpeg,jpg,png,gif'
        ]);

        // Guardar la imagen
        if (!$image || $validate->fails()){
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'      => 'Error al subir la imagen'
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code'      => 200,
                'status'    => 'success',
                'image'      => $image_name
            ];
        }
        // Devolver datos
        return response()->json($data, $data['code']);
    }//////////////////fin upload/////////////

    public function getImage($filename){
        //Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);

        if ($isset){
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
            return new Response($file, 200);
            // Devolver la imagen
        }else{
            $data = [
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'La imagen no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }////////////fin getImage/////////////

    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();
        return response()->json([
            'status'    => 'success',
            'posts'      => $posts
        ], 200);
    }
    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();
        return response()->json([
            'status'    => 'success',
            'posts'      => $posts
        ], 200);
    }
    private function getIdentity(Request $request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }
}

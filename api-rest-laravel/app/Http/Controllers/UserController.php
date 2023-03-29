<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\App;

class UserController extends Controller
{

    public function register(Request $request){

        //     * Recoger los datos del usuario por post
        $json = $request->input('json', null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json, true);//array

        if (!empty($params) && !empty($params_array)){
        //     *Limpiar datos
            $params_array = array_map('trim', $params_array);

        //     * Validar datos
            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users',//unique:users comprueba si el usuario existe
                'password'  => 'required'
             ]);

            if ($validate->fails()){//la validacion ha fallado
                $data = array(//default
                    'status'  => 'error',
                    'code'    => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors'  => $validate->errors()
                );
            }else{//la validacion ha pasado correctamente

            //     * Cifrar la contraseña

                $pwd_crypt = hash('sha256',$params->password);

            //     * Crear usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd_crypt;
//                var_dump($user);

            //     *Guardar el usuario
                $user->save();

                $data = array(
                    'status'  => 'success',
                    'code'    => 200,
                    'message' => 'El usuario se ha creado correctamente',
                );
            }
        }else{
            $data = array(//default
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        return response()->json($data, $data['code']);


    }/////////////////////////////////////////////fin register/////////////////////////////
    public function login(Request $request){

        $jwtAuth = new \JwtAuth();

        //  *Recibir datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //  *Validar esos datos
        $validate = \Validator::make($params_array, [
            'email'     => 'required|email',//unique:users comprueba si el usuario existe
            'password'  => 'required'
        ]);

        if ($validate->fails()){//la validacion ha fallado
            $signup = array(
                'status'  => 'error',
                'code'    => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors'  => $validate->errors()
            );
        }else{
        //  *Cifrar la contraseña
            $pwd_crypt = hash('sha256',$params->password);
        //  *Devolver token o datos
            $signup = $jwtAuth->signup($params->email, $pwd_crypt);
            if (!empty($params->getToken)){
                $signup = $jwtAuth->signup($params->email, $pwd_crypt, true);
            }
        }


        return response()->json($signup, 200);
    }/////////////////////////////////////////////fin login/////////////////////////////

    public function update(Request $request){
        // Comprobar que el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //  Recoger los datos por post

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);//para que llegue como un array

        if ($checkToken && !empty($params_array)){
            //  Actualizar usuario


            //  Sacar el usuario identificado
            $user = $jwtAuth->checkToken($token, true);
            //  Validar los datos

            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users,'.$user->sub,//unique:users comprueba si el usuario existe con la excepción del correo antiguo del usuario
            ]);
            //  Quitar los campos que no quiero actualizar nunca

            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //  Actualizar usuario en bbdd

            $user_update = User::where('id', $user->sub)->update($params_array);

            //  Devolver array con resultado

            $data = array(
                'code'=>200,
                'status'=>'success',
                'user'=>$user,
                'changes'=>$params_array
            );
        }else{
            $data = array(
                'code'=>400,
                'status'=>'error',
                'message'=>'El usuario no esta identificado'
            );
        }
        return response()->json($data, $data['code']);
    }/////////////////////////////////////////////fin update/////////////////////////////

    public function upload(Request $request){

        //  Recoger datos de la peticion
        $image = $request->file('file0');

        //  Validacion de la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpeg, jpg, png, gif'
        ]);

        //  Guardar imagen
        if (!$image||$validate->fails()){

            $data = array(
                'code'=>400,
                'status'=>'error',
                'message'=>'Error al subir imagen'
            );

        }else{

            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code'=>200,
                'status'=>'success',
                'image'=>$image_name
            );
        }

        return response()->json($data, $data['code']);
    }/////////////////////////////////////////////fin upload/////////////////////////////

    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
            //1678875864elbich0.jpeg
        }else{
            $data = array(
                'code'=>400,
                'status'=>'error',
                'message'=>'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }

    }/////////////////////////////////////////////fin getImage/////////////////////////////
    public function detail($id){
        $user = User::find($id);

        if (is_object($user)){
            $data = array(
                'code'=>200,
                'status'=>'success',
                'user'=>$user
            );

        }else{
            $data = array(
                'code'=>400,
                'status'=>'error',
                'message'=>'El usuario no existe'
            );
        }
        return response()->json($data, $data['code']);
    }

}

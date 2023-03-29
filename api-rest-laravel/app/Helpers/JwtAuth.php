<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;
use App\Models\User;


class JwtAuth{
    public $key;
    public function __construct(){
        $this->key = 'clave_super_secreta-14022002';
    }
    public function signup($email, $password, $getToken=null){
        //  *Buscar un usuario con sus credenciales
        $user = User::where([
            'email'     => $email,
            'password'  => $password
        ])->first();//para coger la primera opción que aparezca al buscar en la BD

        //  *Comprobar que son correctos(objeto)

            $signup = false;
            if(is_object($user)){
                $signup = true;
            }

        //  *Generar el token con los datos del usuario identificado

            if ($signup){
                $token = array(
                    'sub'        => $user->id,
                    'email'      => $user->email,
                    'name'       => $user->name,
                    'surname'    => $user->surname,
                    'description'=> $user->description,
                    'image'      => $user->image,
                    'iat'        => time(),
                //fecha expedición del token        //1 semana
                    'exp'        => time() + (7*24*60*60)//7dias*24h*60min*60sec
                );

                $jwt = JWT::encode($token, $this->key, 'HS256');
                $decoded = JWT::decode($jwt, new key($this->key, 'HS256'));
        //  *Devolver los datos decodificados o el token, en funcion de un parametro
                if (is_null($getToken)){
                    $data = $jwt;
                }else{
                    $data = $decoded;
                }
            }else{
                $data = array(
                    'status'    =>  'error',
                    'message'   =>  'Login incorrecto'
                );
            }

            return $data;

    }
    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, new key($this->key, 'HS256'));//new key($this->key, 'HS256')  el decode del video no funciona por la version de laravel
        }catch (\UnexpectedValueException $e){
            $auth = false;
        }catch (\DomainException $e){
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if ($getIdentity){
            return $decoded;
        }

        return $auth;

    }
}


'use strict'

var validator = require('validator');
var bcrypt = require('bcrypt');
var fs = require('fs');
var path = require('path');
var User = require('../models/user');
var jwt = require('../services/jwt');

var controller = {
    probando:function(req, res){
        return res.status(200).send({
            message: 'soy el metodo probando'
        });
    },
    testeando:function(req, res){
        return res.status(200).send({
            message: 'soy el metodo TESTEANDO'
        });
    },

    save: async function(req, res){
        //Recoger los parametros de la peticion
        var params = req.body;

        try{
            //Validar los datos
            var validate_name = !validator.isEmpty(params.name);
            var validate_surname = !validator.isEmpty(params.surname);
            var validate_email = !validator.isEmpty(params.email) && validator.isEmail(params.email);
            var validate_password = !validator.isEmpty(params.password);
        }catch (error){
            return res.status(400).send({
                message: 'faltan datos por enviar'
            });
        }

        /*console.log(validate_name);
        console.log(validate_surname);
        console.log(validate_email);
        console.log(validate_password);*/

        if (validate_name && validate_surname && validate_email && validate_password){
            //Crear objeto de usuario
            const user = new User();

            //Asignar valores al objeto
            user.name = params.name;
            user.surname = params.surname;
            user.email = params.email.toLowerCase();
            user.role = 'ROLE_USER';
            user.image = null;

            //comprobar q el objeto exista
            const issetUser = await User.findOne({email: user.email});
            console.log(issetUser);

            if (issetUser) {
                return res.status(500).send({
                    message: 'Error, el usuario ya esta registrado'
                });
            }else {
                //Si no existe, hashear la password
                bcrypt.hash(params.password, 1).then((hash)=>{
                    user.password = hash;

                }).catch(function(error){
                    console.log('Error saving user: ');
                    console.log(error);
                }).finally(() => {
                    //Guardar usuario
                    user.save();
                    //Devolver respuesta
                    return res.status(200).send({
                        message: 'El usuario se ha  registrado correctamente',
                        status: 'success',
                        user: user
                    });
                });
            }

        }else{//si no supera la validacion
            return res.status(400).send({
                message: 'La Validación es incorrecta, intentelo de nuevo'
            });
        }
    },//////////fin register///////////////


    login: async function (req, res){
        //Recoger param de la peticion
        var params = req.body;

        try{
            //Validar los datos
            var validate_email = !validator.isEmpty(params.email) && validator.isEmail(params.email);
            var validate_password = !validator.isEmpty(params.email);
        }catch (error){
            return res.status(400).send({
                message: 'faltan d atos por enviar'
            });
        }

        if (validate_email || validate_password){//si pasa la validacion
            let email = params.email.toLowerCase();
            //Buscar que el usuario coincida con el email
            const issetUser = await User.findOne({email: email});

            if (issetUser){//Si encuantra el usuario,
            //Comprobar contraseña(coincidencia email y pw | bcrypt)
                const result = await bcrypt.compare(params.password, issetUser.password)
                if (result){//Si la contraseña es correcta,
                    console.log('el check es correcto');
                //Generar el token jwt
                    if (params.getToken){
                        return res.status(200).send({
                            token: jwt.createToken(issetUser)
                        });
                    }else{
                        //Limpiar el objeto
                        issetUser.password = undefined;

                        // Devolver el usuario
                        console.log(issetUser);
                        return res.status(200).send({
                            message: 'Existe usuario',
                            status: 'success',
                            user: issetUser
                        });
                    }

                }else{
                    return res.status(400).send({
                        message: 'El email o contraseña no son correctos, intentelo de nuevo'
                    });
                }

            }if (!issetUser) {
                return res.status(400).send({
                    message: 'Error, no existe ningún usuario con ese email'
                });
            }else {
                return res.status(500).send({
                    message: 'Error en el login'
                });
            }

        }else {
            //Crear middleware para comprobar el jwt token

            return res.status(400).send({
                message: 'El email o contraseña mal escritos, intentelo de nuevo'
            });
        }

    },///////////fin login/////////////



    update: async function(req, res){
        //Recoger datos del usuario
        var params = req.body;

        //Validar los datos
        try{
            var validate_name = !validator.isEmpty(params.name);
            var validate_surname = !validator.isEmpty(params.surname);
            var validate_email = !validator.isEmpty(params.email) && validator.isEmail(params.email);
        }catch (error){
            return res.status(200).send({
                message: 'faltan datos por enviar',
            });
        }
        //Eliminar propiedades innecesarias
        delete params.password;

        var userId = req.user.sub;

        if(req.user.email != params.email){//si el usuario cambia el email
        //Comprobar que el email sea unico para evitar duplicados de emails
            const issetUser = await User.findOne({email: params.email});

            if (issetUser) {
                return res.status(500).send({
                    message: 'Error, ya hay un usuario con ese email'
                });
            }
        }
        //Buscar y actualizar documento
        const userUpdated = await User.findOneAndUpdate({_id: userId}, params, {new: true});

        if (userUpdated){
            //Devolver respuesta
            return res.status(200).send({
                message: 'Usuario actualizado correctamente',
                status: 'success',
                changes: params,
                user: userUpdated
            });
        }else{
            return res.status(400).send({
                message: 'Error al modificar el usuario'
            });
        }
    },///////////fin update/////////////



    upload: async function(req, res){
        //Configurar el modulo multiparty(middlware)

        //Recoger el fichero de la petición
        var file_err = 'Aun no subido';
        console.log(req.files);
        if (!req.files.file0){
            return res.status(404).send({
                message: file_err
            });
        }else{
            // Conseguir el nombre y la extensión del archivo
            var file_path = req.files.file0.path;
            // console.log(file_path)


            //Nombre del archivo
            var file_split = file_path.split('/');
            var file_name = file_split[2];
            //Extensión del archivo
            var ext_split = file_name.split('.');
            var file_ext = ext_split[1];

            // Comprobar extensión (solo admitir imagenes)
            if (file_ext != 'png' && file_ext != 'jpg' && file_ext != 'jpeg' && file_ext != 'gif'){
                fs.unlink(file_path, (err)=>{
                    if (err){
                        return res.status(404).send({
                            message: 'Error en el borrado',
                            status: 'error',
                        });
                    }else{
                        console.log('borrado con exito')
                    }
                });

            }else{
                // Sacar el id del usuario identificado
                var userId = req.user.sub;
                // Buscar y actualizar el documento de la bbdd
                const userUploaded = await User.findOneAndUpdate({_id: userId}, {image: file_name}, {new: true});
                if (userUploaded){
                    //Devolver respuesta
                    return res.status(200).send({
                        message: 'Avatar subido con exito',
                        status: 'success',
                        user: userUploaded,

                    });
                }else{
                    return res.status(404).send({
                        status: 'error',
                        message: 'Error al subir la imagen'
                    });
                }

            }
        }
    },////////////////fin upload/////////////////



    getAvatar: function(req, res){
        var fileName = req.params.fileName;
        var pathFile = './uploads/users/' + fileName;

        fs.exists(pathFile, (exists)=>{
            if (exists){//si existe la imagen
                return res.sendFile(path.resolve(pathFile));
            }else{
                return res.status(404).send({
                    status: 'error',
                    message: 'Error, la imagen no existe'
                });
            }
        });
    },//////////////////fin avatar usuario///////////////////



    getUsers: function(req, res){

    }
};

module.exports = controller;
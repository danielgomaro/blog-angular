'use strict'

var jwt = require('jwt-simple');
var moment = require('moment')
var secret = 'clave-secreta-para-generar-el-token-2023';

exports.auth = function(req, res, next){

    //Comprobar si llega la autorizacion
    if (!req.headers.authorization){
        return res.status(403).send({
            message: 'La petición no tiene la cabecera de autorizacion',
            status: 'error',
        });
    }
    //Limpiar token
    var token = req.headers.authorization.replace(/['"]+/g, '');

    try{
        //Decodificar token
        var payload = jwt.decode(token, secret);
        // Comprobar si el token ha expirado
        if (payload.exp<=moment().unix()){
            return res.status(404).send({
                message: 'El token ha expirado',
                status: 'error'
            });
        }
    }catch (ex){
        return res.status(404).send({
            message: 'El token no es válido',
            status: 'error'
        });
    }
    //Devolver el usuario identificado en la request
    req.user = payload;
    //Pasar a la accion

    console.log('middleware de autenticacion de usuarios');

    next();
}
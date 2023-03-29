'use strict'

//Requires
var express = require('express');
var bodyparser = require('body-parser');
const bodyParser = require("body-parser");

//Ejecutar express
var app = express();

//Cargar archivos de rutas
var user_routes = require('./routes/user');
// Middlewares
app.use(bodyParser.urlencoded({extended:false}));
app.use(bodyParser.json());

//CORS

//Reescribir rutas
app.use('/api', user_routes);
    //ruta prueba
    app.get('/prueba', (req, res)=>{
        return res.status(200).send('<h1>Hola mundo soy el back-end</h1>');
        return res.status(200).send({
            name: 'Daniel Gómez',
            message: 'hola mundo desde el back-end con Node'
        });
    })

//Exportar el modulo
module.exports = app;
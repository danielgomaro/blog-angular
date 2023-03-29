'use strict'

var express = require('express');
var UserController = require('../controllers/user');
var md_auth = require('../middlewares/autenticate');

var router = express.Router();
var multipart = require('connect-multiparty');
var md_upload = multipart({uploadDir: './uploads/users'});

//rutas de prueba
router.get('/probando', UserController.probando);
router.post('/testeando', UserController.testeando);

//rutas de usuarios
router.post('/register', UserController.save);
router.post('/login', UserController.login);
router.put('/update', md_auth.auth, UserController.update);
router.post('/upload',  [md_auth.auth, md_upload], UserController.upload);
router.get('/avatar/:fileName', UserController.getAvatar);

module.exports = router;
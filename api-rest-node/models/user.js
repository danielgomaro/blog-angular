'use strict'

var mongoose = require('mongoose');
var Schema = mongoose.Schema;

var UserSchema = Schema({
    name: String,
    surname: String,
    email: String,
    password: String,
    image: String,
    role: String
});

const User = mongoose.model('User', UserSchema);
module.exports = User;
//lowercase y plurarizar nombre
                                                         //users -> documentos(schema)
 
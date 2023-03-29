'use strict'

var mongoose = require('mongoose');
var Schema = mongoose.Schema;

//modelo de comment
var CommentSquema = Schema({
    content: String,
    date: {type: Date, default: Date.now},
    user: {type: Schema.objectId, ref: 'User'}
});
var comments = mongoose.model('Comment', CommentSquema);

//modelo de topic
var TopicSchema = Schema({
    title: String,
    content: String,
    code: String,
    lang: String,
    date: {type: Date, default: Date.now},
    user: {type: Schema.objectId, ref: 'User'},
    comments: [CommentSquema]
});

module.exports = mongoose.model('Topic', TopicSchema);//lowercase y plurarizar nombre
                                                           //topics -> documentos(schema)

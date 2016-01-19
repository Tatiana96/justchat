<?php

namespace tatiana96\justchat;

use yii\web\AssetBundle;

 // класс библиотек, которые все подгружаются с помощью yii2
class ChatLibAsset extends AssetBundle
{
	// Встроенный в YII2
    public $sourcePath = '@bower'; // Bower — нестандартный менеджер пакетов для клиентского JavaScript (упрощает установку и обновление сторонних библиотек в проекте)
    public $css = [ // стили
        'fontawesome/css/font-awesome.min.css', // Font Awesome - это язык иконок для web-проектов
        'pnotify/pnotify.core.css',
    ];
    public $js = [
        'underscore/underscore-min.js', // библиотека JavaScript, реализующая дополнительную функциональность для работы с массивами, объектами и функциями
        'backbone/backbone.js', // фреймворк для разработки приложений с использованием JavaScript
        'js-cookie/src/js.cookie.js', // Плагин jQuery cookie для работы с cookie
        'pnotify/pnotify.core.js', // PNotify - это плагин всплывающих уведомлений, написан на JavaScript
    ];

    public $depends = [ // настраиваем зависимости
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
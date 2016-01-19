<?php
namespace tatiana96\justchat;

use yii\web\AssetBundle;

// класс активов
// в директорию assets фреймворк Yii генерирует компактно стили (css) и js
class ChatAsset extends AssetBundle
{
    public $css = [ // стили
        'css/style.css'
    ];
    // список js файлов
    public $js = [
        'js/helper.js',
        'js/chat.js',
        'js/models/user.js',
        'js/models/room.js',
        'js/chat-room.js',
        'js/collections/users.js',
        'js/collections/rooms.js',
        'js/views/message.js',
        'js/views/chat.js',
        'js/views/room.js',
        'js/views/add_room.js',
        'js/views/rooms.js',
        'js/views/user.js',
        'js/views/add_user.js',
        'js/views/users.js',
        'js/main.js',
    ];

    public $depends = [ // настраиваем зависимости
        'tatiana96\justchat\ChatLibAsset'
    ];
	
	// функция подключения файлов
    public function init()
    {
        $this->sourcePath = __DIR__.'/assets/'; // путь к папке "assets"

		// настраиваем самую простую версию js scripts без debug
        if (!YII_DEBUG) {
            $this->js = ['js/chat.min.js'];
        }
        parent::init();
    }
}

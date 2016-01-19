<?php
namespace tatiana96\justchat;

use Yii;
use yii\base\Widget;
use yii\web\View;
use yii\helpers\Json;

 // Класс виджета чата (чат сделан как виджет, на основе фреймворка YII2)
 
class ChatWidget extends Widget
{

    public $auth = false; // настраивается в true при условии, что виджет запускает уже авторизированный пользователь
    public $user_id = null;
    public $view = 'index'; // начальная вьюшка
   
    public $port = 8080; // порт для подключения сокетов
    
    public $chatList = [ // массив комнат, которые были загружены по умолчанию (настраивается)
        'id' => 1,
        'title' => 'All'
    ];
    
    public $imgPath = '@vendor/tatiana96/justchat/assets/img'; // алиас пути к папке с картинками-аватарками

 
	// запускаем виджет
    public function run()
    {
        $this->registerJsOptions();
        Yii::$app->assetManager->publish($this->imgPath);
        return $this->render($this->view, ['auth' => $this->auth]);
    }

	// объявляем is переменные
    protected function registerJsOptions()
    {
        $opts = [
            'var currentUserId = '.($this->user_id ?: 0).';',
            'var port = '.$this->port.';',
            'var chatList = '.Json::encode($this->chatList).';',
            'var imgPath = "'.Yii::$app->assetManager->getPublishedUrl($this->imgPath).'";',
        ];
        $this->getView()->registerJs(implode(' ', $opts), View::POS_BEGIN);
    }
}
 

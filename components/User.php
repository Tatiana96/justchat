<?php
namespace tatiana96\justchat\components;

use Yii;
use yii\base\InvalidParamException;

 // класс пользователя
class User
{
    public $id;
    public $username;
    public $avatar_16; // картинка 16x16 - аватар
    public $avatar_32; // картинка 32x32 - аватар
    private $rid; // resource id, источник
    private $chat; // храним комнату чата \tatiana96\justchat\components\ChatRoom $chat
    private $modelClassName = null;
	 
	// properties - массив настроека для неавторизированных пользователей
    public function __construct($id = null, $modelClassName = null, array $props = [])
    {
        $this->id = $id;
        $this->modelClassName = $modelClassName;
        $this->init($props);
	}

	// инициализация: получаем атрибуты пользователя из кэша или загружаем их из базы - по ситуации
    private function init(array $props = [])
    {
        $cache = Yii::$app->cache;
        $cache->keyPrefix = 'user';
        if ($cache->exists($this->id)) {
            $attrs = $cache->get($this->id);
        } else {
            if ($this->modelClassName) {
                if (!in_array('findOne', (array)get_class_methods($this->modelClassName))) {
                    throw new InvalidParamException(Yii::t('app', 'Model class should implements `findOne()` method'));
                }
				
				// модель для записей в базу \yii\db\BaseActiveRecord $model
                $model = call_user_func_array([$this->modelClassName, 'findOne'], ['id' => $this->id]); // ищем пользователя в базе
                if (!$model) {
                    throw new InvalidParamException(Yii::t('app', 'User entity not found.'));
                }
                $attrs = $model->attributes;
            } else {
                $attrs = $props;
            }
            $cache->set($this->id, $attrs);
        }
        Yii::configure($this, $attrs);
    }

	// получаем id пользователя
    public function getId()
    {
        return $this->id;
    }

	// получаем id источника (resource id)
    public function getRid()
    {
        return $this->rid;
    }

	// настраиваем id источника (resource id)
    public function setRid($rid)
    {
        $this->rid = $rid;
    }

	// получаем комнату чата, в которую попал пользователь
    public function getChat()
    {
        return $this->chat;
    }

	// настраиваем комнату чата для пользователя
    public function setChat(ChatRoom $chat)
    {
        $this->chat = $chat;
        $this->chat->addUser($this);
    }
}
 
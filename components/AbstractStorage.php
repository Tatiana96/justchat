<?php
namespace tatiana96\justchat\components;

use Yii;
use tatiana96\justchat\collections\History;

 // создаем класс по паттерну "Абстрактная фабрика", порождающий шаблон проектирования
 // это основной класc для создания конструктора для хранения сообщений
abstract class AbstractStorage
{
    // создание экземпляра базы данных, чтобы хранить историю сообшений
	
	// статичная функция, которая будет возвращать экземпляр класса \tatiana96\justchat\components\AbstractStorage
    public static function factory($storage = null)
    {
        if (empty($storage)) {
            $components = Yii::$app->getComponents();
            $storage = !empty($components['mongodb']) ? 'mongodb' : Yii::$app->getDb()->driverName;
        }
        switch ($storage) {
            case 'mongodb':
                $class = new History(); // здесь будет храниться история
                break;
            default:
                $class = new DbStorage();
        }
        return $class;
    }
	 
	 // загружаем историю сообщений, которую будем возвращать массивом
    abstract public function getHistory($chatId, $limit = 10);

	 // функция для хранения сообщений
    abstract public function storeMessage(array $params);
}

<?php
namespace tatiana96\justchat\components;

use yii;
use yii\db\Query;
use yii\db\Exception;

// Класс базы данных для хранения сообщений, подкласс AbstractStorage
class DbStorage extends AbstractStorage
{
	// получаем имя таблицы
    public static function tableName()
    {
        return 'history'; // используем по умолчанию "history"
    }

	// получаем атрибуты - поля таблицы, возвращаем массив
    public function attributes()
    {
        return [
            'id', 'chat_id', 'chat_title', 'user_id', 'username', 'avatar_16',
            'avatar_32', 'timestamp', 'message'
        ];
    }

	// получаем историю сообщений
    public function getHistory($chatId, $limit = 10)
    {
        $query = new Query();
        $query->select(['user_id', 'username', 'message', 'timestamp', 'avatar_16', 'avatar_32'])
            ->from(self::tableName())
            ->where(['chat_id' => $chatId]);
        $query->orderBy(['timestamp' => SORT_DESC]);
        if ($limit) {
            $query->limit($limit);
        }
        return $query->all();
    }

	// сохраняем сообщения
    public function storeMessage(array $params)
    {
        try {
            Yii::$app->getDb()->createCommand()
                ->insert(self::tableName(), $params)
                ->execute();
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
        return true;
    }
}
 
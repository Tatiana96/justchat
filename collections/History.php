<?php
namespace tatiana96\justchat\collections;

use Yii;
use yii\mongodb\Exception;
use yii\mongodb\Query;
use tatiana96\justchat\components\AbstractStorage;

/*
	Class History ( tatiana96\justchat\collections )
	\MongoId $_id
	string $chat_id
	string $chat_title
	string $user_id
	string $username
	string $avatar_16
	string $avatar_32
	integer $timestamp
	string $message
 */
class History extends AbstractStorage
{
	// получаем имя коллекции в MongoDB (по умолчанию "history")
    public static function collectionName()
    {
        return 'history';
    }

	// получаем список атрибутов/полей
    public function attributes()
    {
        return [
            '_id', 'chat_id', 'chat_title', 'user_id', 'username', 'avatar_16',
            'avatar_32', 'timestamp', 'message'
        ];
    }

	// получаем историю
    public function getHistory($chatId, $limit = 10)
    {
        $query = new Query();
        $query->select(['user_id', 'username', 'message', 'timestamp', 'avatar_16', 'avatar_32'])
            ->from(self::collectionName())
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
            // \yii\mongodb\Collection $collection
            $collection = Yii::$app->mongodb->getCollection(self::collectionName());
            $collection->insert($params);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
        return true;
    }
}
 
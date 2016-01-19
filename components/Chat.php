<?php
namespace tatiana96\justchat\components;

use Yii;
use yii\helpers\Json;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

// класс создания чата
class Chat implements MessageComponentInterface 
{
    private $clients = []; // организуем подключение
   
    private $cm = null; // для класса \tatiana96\justchat\components\ChatManager
 
    private $requests = [ // список/массив активных запросов
        'auth', 'message'
    ];

    public function __construct(ChatManager $cm) // конструктор: настраиваем параметры класса \tatiana96\justchat\components\ChatManager
    {
        $this->cm = $cm;
    }

    public function onOpen(ConnectionInterface $conn) // устанавливаем соединение
    {
        $rid = $this->getResourceId($conn);
        $this->clients[$rid] = $conn;
        Yii::info('Connection is established: '.$rid, 'chat'); // соединение прошло успешно
    }
   
    public function onMessage(ConnectionInterface $from, $msg) // получение сообщений
    {
        $data = Json::decode($msg, true); // расшифровываем данные данные
        $rid = array_search($from, $this->clients);
        if (in_array($data['type'], $this->requests)) {
            call_user_func_array([$this, $data['type'].'Request'], [$rid, $data['data']]);
        }
    }

    public function onClose(ConnectionInterface $conn)  // разрываем соединение
    {
        $rid = array_search($conn, $this->clients);
        if ($this->cm->getUserByRid($rid)) {
            $this->closeRequest($rid);
        }
        unset($this->clients[$rid]);
        Yii::info('Connection is closed: '.$rid, 'chat'); // закрываем соединение
    }

    public function onError(ConnectionInterface $conn, \Exception $e) // обрабатываем ошибки
    {
        Yii::error($e->getMessage());
        $conn->send(Json::encode(['type' => 'error', 'data' => [
            'message' => Yii::t('app', 'Something wrong. Connection will be closed')
        ]]));
        $conn->close();
    }

	
    private function getResourceId(ConnectionInterface $conn) // получаем id соединения
    {
        return $conn->resourceId;
    }
	 
	// функция аутентификации, находит существующего пользователя (если его нет - предлагает создать)
    private function authRequest($rid, array $data)
    {
        $chatId = $data['cid'];
        Yii::info('Auth request from rid: '.$rid.' and chat: '.$chatId, 'chat');
        $userId = !empty($data['user']['id']) ? $data['user']['id'] : '';
		
		// если один и тот же пользователь уже подключен к чату, то нужно закрыть старое соединение
        if ($oldRid = $this->cm->isUserExistsInChat($userId, $chatId)) {
            $this->closeRequest($oldRid);
        }
        $this->cm->addUser($rid, $userId, $data['user']);
        $chat = $this->cm->findChat($chatId, $rid);
        $users = $chat->getUsers();
        $joinedUser = $this->cm->getUserByRid($rid);
        $response = [
            'user' => $joinedUser,
            'join' => true,
        ];
        foreach ($users as $user) {
			// отправляем сообщение остальным пользователям чата
            if ($userId != $user->getId()) {
                $conn = $this->clients[$user->getRid()];
                $conn->send(Json::encode(['type' => 'auth', 'data' => $response]));
            }
        }
		
		// отправляем ответ на аутентификацию присоединившегося пользователя
        $response = [
            'user' => $joinedUser,
            'users' => $users,
            'history' => $this->cm->getHistory($chat->getUid())
        ];
        $conn = $this->clients[$rid];
        $conn->send(Json::encode(['type' => 'auth', 'data' => $response]));
    }

	// отправляем сообщение: находим комнату, в которой сидит пользователь и отправляем сообщение другим пользователям
    private function messageRequest($rid, array $data)
    {
        Yii::info('Message from: '.$rid, 'chat');
        $chat = $this->cm->getUserChat($rid);
        if (!$chat) {
            return;
        }
        $data['message']['timestamp'] = time();
        $user = $this->cm->getUserByRid($rid);
        $this->cm->storeMessage($user, $chat, $data['message']);
        foreach ($chat->getUsers() as $user) {
			// чтобы не отправлять сообщение самому себе
            if ($user->getRid() == $rid) {
                continue;
            }
            $conn = $this->clients[$user->getRid()];
            $conn->send(Json::encode(['type' => 'message', 'data' => $data]));
        }
    }

	// закрываем соединение: находим чат пользователя, удаляем его из комнаты и отправляем сообщение остальным, что он покинул чат
    private function closeRequest($rid)
    {
		// находим пользователя, чтобы закрыть соединение
        $requestUser = $this->cm->getUserByRid($rid);
        $chat = $this->cm->getUserChat($rid);
        
		// удаляем пользователя из чата
        $this->cm->removeUserFromChat($rid);
        
		// отправляем уведомление остальным пользователям 
        $users = $chat->getUsers();
        $response = array(
            'type' => 'close',
            'data' => ['user' => $requestUser]
        );
        foreach ($users as $user) {
            $conn = $this->clients[$user->getRid()];
            $conn->send(Json::encode($response));
        }
    }
}
 

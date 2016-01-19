<?php
namespace tatiana96\justchat\components;

use Yii;
use tatiana96\justchat\collections\History;
 
// управление чатом
class ChatManager
{
 
    private $users = []; // получаем массив пользователей \tatiana96\justchat\components\User[]

    public $userClassName = null; // название класса, чтобы получить экземпляр класса пользователя

	 // поиск: проверяем есть ли нужный пользователь в чате: возвращаем его id, либо null
    public function isUserExistsInChat($id, $chatId)
    {
        foreach ($this->users as $rid => $user) {
            $chat = $user->getChat();
            if (!$chat) {
                continue;
            }
            if ($user->id == $id && $chat->getUid() == $chatId) {
                return $rid;
            }
        }
        return null;
    }

	// Добавляем нового пользователя в класс Manager
    public function addUser($rid, $id, array $props = [])
    {
        $user = new User($id, $this->userClassName, $props);
        $user->setRid($rid);
        $this->users[$rid] = $user;
    }

	// получаем комнату в чате для пользователя
    public function getUserChat($rid)
    {
        $user = $this->getUserByRid($rid);
        return $user ? $user->getChat() : null;
    }

	// находим комнату по id, если же ее нет - создаем новую
    public function findChat($chatId, $rid)
    {
        $chat = null;
        $storedUser = $this->getUserByRid($rid);
        foreach ($this->users as $user) {
            $userChat = $user->getChat();
            if (!$userChat) {
                continue;
            }
            if ($userChat->getUid() == $chatId) {
                $chat = $userChat;
                Yii::info('User('.$user->id.') will be joined to: '.$chatId, 'chat'); // пользователь добавился в комнату
                break;
            }
        }
        if (!$chat) {
            Yii::info('New chat room: '.$chatId.' for user: '.$storedUser->id, 'chat'); // новая комната создалась
            $chat = new ChatRoom();
            $chat->setUid($chatId);
        }
        $storedUser->setChat($chat);
        return $chat;
    }

	// получаем данные пользователя по его id
    public function getUserByRid($rid)
    {
        return !empty($this->users[$rid]) ? $this->users[$rid] : null;
    }

	// удаляем пользователя из чата: находим по id и удаляем
    public function removeUserFromChat($rid)
    {
        $user = $this->getUserByRid($rid);
        if (!$user) {
            return;
        }
        $chat = $user->getChat();
        if ($chat) {
            $chat->removeUser($user);
        }
        unset($this->users[$rid]);
    }

	// Сохраняем сообщения из чата
    public function storeMessage(User $user, ChatRoom $chat, $message)
    {
        $params = [
            'chat_id' => $chat->getUid(),
            'chat_title' => $chat->title,
            'user_id' => $user->getId(),
            'username' => $user->username,
            'avatar_16' => $user->avatar_16,
            'avatar_32' => $user->avatar_32,
            'message' => $message['message'],
            'timestamp' => $message['timestamp']
        ];
        AbstractStorage::factory()->storeMessage($params);
    }

	// подгружаем историю чата
    public function getHistory($chatId, $limit = 10)
    {
        $data = AbstractStorage::factory()->getHistory($chatId, $limit);
        return array_reverse($data);
    }
}
 
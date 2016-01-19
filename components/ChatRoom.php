<?php
namespace tatiana96\justchat\components;


// класс комнаты чата
class ChatRoom
{
    public $title; // название комнаты

    private $uid; // ее id (тип string)

    private $users = []; // для массива пользователей \tatiana96\justchat\components\User[] , которые сидят в комнате

	// настраиваем для комнаты чата уникальный id
    public function setUid($uid) 
    {
        $this->uid = $uid;
    }

	// получаем id комнаты
    public function getUid()
    {
        return $this->uid;
    }

	// получаем массив пользователей, которые в комнате
    public function getUsers()
    {
        return $this->users;
    }

	// добавляем пользователя в комнату
    public function addUser(User $user)
    {
        $this->users[$user->getId()] = $user;
    }

	// удаляем пользователя из комнаты
    public function removeUser(User $user)
    {
        unset($this->users[$user->getId()]);
    }
}
 
Chat.Room = function(options) { // Настраиваем параметры комнаты
    this.conn = null;
    this.options = $.extend({
        url: location.host,
        port: 8080,
        currentUserId: '',
        username: ''
    }, options);
    this.active = false;
    this.users = new Chat.Collections.Users();
    this.currentUser = null;
};
Chat.Room.prototype.init = function() { // инициализируем комнату
    var self = this;
    try {
        self.cid = $('#chat-room-list .active').attr('data-chat');
        if (self.cid) {
            
			// если id пользователя не настроено, то нужно его сгенерировать (в случае неавторизированных пользователей)
            if (!self.options.currentUserId) {
                self.options.currentUserId = Helper.uid();
                $.cookie('chatUserId', self.options.currentUserId);
            }
            self.conn = new WebSocket('ws://' + self.options.url + ':' + self.options.port);
            self.addConnectionHandlers();
            
			// настраиваем текущую комнату в чате, по умолчанию - ALL
            var timer = setInterval(function() {
                if (self.conn.readyState == 1) {
                    self.auth();
                    clearInterval(timer);
                }
            }, 200);
        } else {
            Helper.Message.error(Helper.t('Current room is not available'));
        }
    } catch (e) {
        Helper.Message.error(Helper.t('Connection error. Try to reload page'));
        console.log(e);
    }
    self.initLang();
    self.addEventsHandlers();
};
Chat.Room.prototype.initLang = function() { // инициализируем язык
    var lang = navigator.language || navigator.userLanguage;
    lang = lang.split('-');
    $.cookie('chatLang', lang[0], {expires: 1});
};
Chat.Room.prototype.addEventsHandlers = function() { // добавляем обработчики событий
    var self = this;
    Chat.vent.on('user:auth', function (data) {
        var user = new Chat.Models.User(data.user);
     
		//новый пользователь добавился в чат
        if (typeof data.join !== 'undefined' && data.join) {
            user.set('message', Helper.t('Connect to chat'));
            user.set('type', 'warning');
            Chat.vent.trigger('message:add', user);
        } else {
            
			// текущий пользователь получает ответ по авторизации
            self.fillUsers(data.users, data.user);
            self.currentUser = user;
            Chat.vent.trigger('user:setCurrent', self.currentUser);
            Chat.vent.trigger('history:load', data.history);
        }
        self.users.add(user);
    });
    Chat.vent.on('message:send', function (msg) {
        self.currentUser.set('message', msg);
        self.sendMessage(self.currentUser);
    });
    Chat.vent.on('user:remove', function (data) {
        var user = self.users.get(data.id);
        user.set({
            message: Helper.t('Left this chat'),
            timestamp: ''
        });
        Chat.vent.trigger('message:add', user);
        self.users.remove(user);
    });
};
Chat.Room.prototype.addConnectionHandlers = function() { // добавляем обработчики соединения
    var self = this;
    self.conn.onclose = function (e) {
        if (!self.active) {
            return;
        }
        if (self.isFunction(self.onClose)) {
            self.onClose(e);
        }
    };
    self.conn.onerror = function (e) {
        Helper.Message.error('Connection refused');
        self.conn.close();
    };
    self.conn.onmessage = function (e) {
        if (self.isFunction(self.onMessage)) {
            self.onMessage(e);
        }
    };

    $(window).unload(function () {
        self.conn.close();
    });
};
Chat.Room.prototype.isFunction = function(name) {
    return typeof name === 'function';
};
Chat.Room.prototype.onMessage = function(e) { // получение сообщений
    try {
        var response = JSON.parse(e.data);
        switch (response.type) {
            case 'auth':
                Chat.vent.trigger('user:auth', response.data);
                break;
            case 'message':
                var user = new Chat.Models.User(response.data.message);
                user.set('type', 'info');
                
				// копируем модель, чтобы избежать обработки сообщения
                this.showNotification(user.clone());
                Chat.vent.trigger('message:add', user);
                break;
            case 'close':
                Chat.vent.trigger('user:remove', response.data.user);
                break;
            case 'error':
                Helper.Message.error(Helper.t(response.data.message));
                break;
        }
    } catch (e) {
        console.log(e);
    }
};
Chat.Room.prototype.onClose = function (e) { // разрываем соединение
    console.log('close');
    console.log(e);
};
Chat.Room.prototype.sendMessage = function (data) { // отправляем сообщение
    this.send({type: 'message', data: {message: data}});
};
Chat.Room.prototype.auth = function() { // авторизация
    var user = new Chat.Models.User({id: this.options.currentUserId, username: this.options.username});
    this.send({type: 'auth', data: {user: user.toJSON(), cid: this.cid}});
};
Chat.Room.prototype.send = function(request) { // отправляем запрос
    this.conn.send(JSON.stringify(request));
};
Chat.Room.prototype.fillUsers = function(users, user) { // добавляем пользователей
    for (var key in users) {
        if (key !== user.id) {
            this.users.add(users[key]);
        }
    }
};
Chat.Room.prototype.showNotification = function(user) { // показываем уведомление
    var self = this;
    if (!('Notification' in window)) {
        return;
    }
    Notification.requestPermission(function () {
        var title = Helper.t('New message from') + ' ' + user.get('name');
        var msg = user.get('message');
        if (msg.length > 40) {
            msg = msg.substring(0, 40) + '...';
        }
        var notification = new Notification(title, {
            icon: user.get('avatar_32'),
            body: msg,
            lang: self.lang
        });
        notification.onshow = function () {
			
			// скрывать уведомление через 5 секунд
            setTimeout(function () {
                notification.close();
            }, 5000);
        };
    });
};
// переменная currentUserId настроена в классе ChatWidget по умолчанию в 0
// переменная port настроена в классе ChatWidget по умолчанию 8080
// массив chatList настроен в классе ChatWidget

$(document).ready(function() { // основная функция обработки событий (JQuery)
    $('body').tooltip({selector: '[data-toggle="tooltip"]'});
	// обрабатываем список комнат
    var rooms = new Chat.Collections.Rooms(chatList);
    var roomListView = new Chat.Views.ChatRoomList({collection: rooms});
    roomListView.render(); //создаем комнаты 

	// создаем чат после того, как комнаты подгрузятся
    currentUserId = currentUserId || $.cookie('chatUserId');
    var chat = new Chat.Room({port: port, currentUserId: currentUserId});
    if (!currentUserId) {
        var addUserView = new Chat.Views.AddUserView();
        addUserView.show();
        Chat.vent.on('user:set_username', function(username) {
            chat.options.username = username;
            chat.init();
        });
    } else {
        chat.init();
    }
    var chatView = new Chat.Views.ChatView();
    var userListView = new Chat.Views.UserListView({collection: chat.users});
});
define(['core/ajax', 'core/notification'], function(Ajax, Notification) {
    var BUTTON_ID = 'local-hello-load-messages';
    var LIST_ID = 'local-hello-ajax-list';

    var renderMessages = function(container, messages) {
        if (!Array.isArray(messages) || messages.length === 0) {
            container.textContent = container.dataset.emptyText || '';
            return;
        }

        var list = document.createElement('ul');
        messages.forEach(function(item) {
            var row = document.createElement('li');
            row.textContent = item.timecreated + ' - ' + item.message;
            list.appendChild(row);
        });

        container.innerHTML = '';
        container.appendChild(list);
    };

    var loadMessages = function(button, container) {
        var loadingText = button.dataset.loadingText || button.textContent;
        var defaultText = button.dataset.defaultText || button.textContent;

        button.disabled = true;
        button.textContent = loadingText;

        var request = Ajax.call([{
            methodname: 'local_hello_get_messages',
            args: {
                userid: 0
            }
        }])[0];

        request.then(function(messages) {
            renderMessages(container, messages);
            button.disabled = false;
            button.textContent = defaultText;
            return null;
        }).catch(function(error) {
            button.disabled = false;
            button.textContent = defaultText;
            Notification.exception(error);
        });
    };

    var init = function() {
        var button = document.getElementById(BUTTON_ID);
        var container = document.getElementById(LIST_ID);

        if (!button || !container) {
            return;
        }

        button.addEventListener('click', function(event) {
            event.preventDefault();
            loadMessages(button, container);
        });
    };

    return {
        init: init
    };
});

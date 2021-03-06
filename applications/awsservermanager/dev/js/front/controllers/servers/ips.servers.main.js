;(function ($) {
    "use strict";

    ips.controller.register('awsservermanager.front.servers.main', {
        stateTimeout: null,

        initialize: function () {
            $.proxy(this.getState, this)();

            this.scope.find('button[data-startServer]').on('click', $.proxy(this.startServer, this));
            this.scope.find('button[data-stopServer]').on('click', $.proxy(this.stopServer, this));
            this.scope.find('button[data-rebootServer]').on('click', $.proxy(this.rebootServer, this));
        },

        buildAjaxUrl: function (action) {
            const doAction = action !== '' ? '&do=' + action : '';
            const serverId = this.scope.data('serverid');
            return ips.getSetting('baseURL') + 'index.php?app=awsservermanager&module=servers&controller=ajax' + doAction + '&id=' + serverId;
        },

        handleResponse: function (response) {
            this.scope.find('li[data-serverInfo]').removeClass('ipsHide');
            this.scope.find('span[data-serverState]').text(response.state);
            if (response.steam) {
                this.scope.find('div[data-steamInfo]').removeClass('ipsHide');
                this.scope.find('div[data-noSteamInfo]').addClass('ipsHide');
                this.scope.find('span[data-serverName]').text(response.steam.name);
                this.scope.find('span[data-serverGame]').text(response.steam.game);
                this.scope.find('span[data-serverPlayers]').text(response.steam.players + '/' + response.steam.max_players);
            } else {
                this.scope.find('div[data-steamInfo]').addClass('ipsHide');
                this.scope.find('div[data-noSteamInfo]').removeClass('ipsHide');
            }

            const startButton = this.scope.find('button[data-startServer]');
            const stopButton = this.scope.find('button[data-stopServer]');
            const rebootButton = this.scope.find('button[data-rebootServer]');

            if (response.state === 'running') {
                startButton.prop('disabled', true);
                stopButton.prop('disabled', false);
                rebootButton.prop('disabled', false);
            } else if (response.state === 'stopped') {
                startButton.prop('disabled', false);
                stopButton.prop('disabled', true);
                rebootButton.prop('disbaled', true);
            } else {
                startButton.prop('disabled', true);
                stopButton.prop('disabled', true);
                rebootButton.prop('disbaled', true);
            }
        },

        getState: function (action) {
            if (action === undefined) {
                action = '';
            }
            clearTimeout(this.stateTimeout);

            this.scope.find('li[data-loading]').removeClass('ipsHide');
            this.scope.find('li[data-serverInfo]').addClass('ipsHide');
            this.scope.find('button[data-startServer]').prop('disabled', true);
            this.scope.find('button[data-stopServer]').prop('disabled', true);
            this.scope.find('button[data-rebootServer]').prop('disabled', true);

            ips.getAjax()(this.buildAjaxUrl(action))
                .done(this.handleResponse.bind(this))
                .always(function () {
                    this.scope.find('li[data-loading]').addClass('ipsHide');
                    this.stateTimeout = setTimeout($.proxy(this.getState, this), 60000);
                }.bind(this));
        },

        startServer: function () {
            this.getState('startServer');
        },

        stopServer: function () {
            this.getState('stopServer');
        },

        rebootServer: function () {
            this.getState('rebootServer');
        }
    });
}(jQuery));

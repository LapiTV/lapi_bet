function DrawWinner(idBet) {
    this.betId = idBet;

    this.allWinner = [];

    this.element = {
        table: $('.js--table-answer tbody'),
        formGetWinner: $('.js--get-winner'),
        answerInput: $('.js--get-winner').find('#answer'),
        dice: $('.js--display-dice'),
        winner: {
            mainDiv: $('.js--display-winner'),
            name: $('.js--name-winner'),
            messages: $('.js--message-of-winner'),
            time: $('.js--time-winner-draw'),
            refresh: $('.js--refresh-message')
        },
        choice: {
            all: $('.js--all-winners'),
            one: $('.js--one-winner')
        }
    };

    this.interval = {
        timeWinnerDraw: null
    };

    this.timeout = {
        getMessages: null
    };

    this.time = {
        winnerDraw: null,
        timeDbWinnerDraw: null
    };

    this.diceElement = '<div style="margin-left:auto;margin-right:auto;width:480px;height:360px;"><img src="https://media.giphy.com/media/6wRAJMOXqSGCk/giphy.gif"></div>';
    this.loadingTable = '<tr><td colspan="4"><div style="display:flex;justify-content:center;align-items:center;font-size: 30px;margin: 25px;width:100%;"><i class="fa fa-lg fa-spinner fa-spin"></i></div></td></tr>';

    this.minDistance = 0;
    this.currentWinner = '';
    this.current = 'one';
    this.alreadyDraw = false;
    this.winnerNumber = 0;
    this.lockLoadMessages = false;
    this.idDisplayed = [];

    var _this = this;
    this.element.formGetWinner.on('submit', function (e) {
        e.preventDefault();
        _this.reset();

        $.post('/ajax/bet/' + _this.betId + '/winner', {
            answer: _this.element.answerInput.val()
        }, function (data) {
            _this.allWinner = data.table;
            _this.minDistance = data.minDistance;

            _this.element.table.html(_this.loadingTable);
            _this.displayTable();

            if (_this.current !== 'one') {
                return;
            }
            _this.time.timeDbWinnerDraw = new Date(data.now);
            _this.element.dice.show();
            _this.element.dice.html(_this.diceElement);

            var timeoutDisplay = 1000;
            if (!_this.alreadyDraw) {
                _this.alreadyDraw = true;
                timeoutDisplay *= 10;
            }

            setTimeout(function () {
                _this.element.dice.hide();
                _this.element.winner.mainDiv.show();
                _this.time.winnerDraw = parseInt((new Date()).getTime() / 1000);

                _this.displayWinner();
            }, timeoutDisplay);
        });

        return false;
    });

    $('#oneWinner').on('change', function () {
        if ($(this).is(':checked')) {
            _this.current = 'one';
            _this.element.choice.one.show();
            _this.element.choice.all.hide();
        } else {
            _this.current = 'all';
            _this.element.choice.one.hide();
            _this.element.choice.all.show();
        }
    });

    $('.js--next-winner').on('click', function (e) {
        clearTimeout(_this.timeout.getMessages);
        _this.element.winner.messages.html('');
        _this.time.winnerDraw = parseInt((new Date()).getTime() / 1000);

        _this.displayWinner();
    });
}

DrawWinner.prototype.displayWinner = function () {
    var _this = this;

    if (!this.allWinner[this.winnerNumber]) {
        this.element.winner.name.html('Il n\'y a plus de gagnant :O #lapiRIP');
        return;
    }

    this.currentWinner = this.allWinner[this.winnerNumber].username;

    clearInterval(this.interval.timeWinnerDraw);
    this.interval.timeWinnerDraw = setInterval(function () {
        var currentTime = parseInt((new Date()).getTime() / 1000);
        _this.element.winner.time.text(currentTime - _this.time.winnerDraw + ' s');
    }, 1000);

    this.element.winner.name.html(
        'Bravo <strong>' + this.currentWinner + '</strong> ' +
        'avec ' + this.allWinner[this.winnerNumber].answer + ' !'
    );

    clearTimeout(this.timeout.getMessages);
    this.getMessageWinner();

    this.winnerNumber++;
};

DrawWinner.prototype.displayTable = function () {
    var _this = this;

    this.element.table.html('');
    if (this.allWinner) {
        this.allWinner.map(function (e) {
            _this.element.table.append('<tr style="background-color: #' + gradient(e.distance, _this.minDistance) + ';"><td>' + e.username + '</td><td>' + e.answer + '</td><td>' + e.distance + '</td><td>' + e.date + '</td></tr>');
        });
    }
};

DrawWinner.prototype.getMessageWinner = function () {
    var _this = this;

    if (this.lockLoadMessages) {
        return;
    }

    this.lockLoadMessages = true;
    this.element.winner.refresh.removeClass('refresh_me');

    $.post('/ajax/message', {winner: this.currentWinner, date: this.time.timeDbWinnerDraw}, function (data) {
        var messages = data.messages;
        var length = messages.length;

        for (var i = 0; i < length; i++) {
            if (_this.idDisplayed.indexOf(messages[i].id) === -1) {
                var date = new Date(messages[i].sent);
                _this.idDisplayed.push(messages[i].id);
                _this.element.winner.messages.prepend('<i>' + date.getHours() + ':' + date.getMinutes() + '</i> - ' + escapeHtml(messages[i].message) + '<br />');
            }
        }
    }).always(function () {
        _this.element.winner.refresh.addClass('refresh_me');
        _this.lockLoadMessages = false;
        _this.timeout.getMessages = setTimeout(function () {
            _this.getMessageWinner();
        }, 2000);
    });
}

DrawWinner.prototype.reset = function () {
    this.element.dice.hide();
    this.element.winner.mainDiv.hide();

    this.element.winner.mainDiv.hide();
    this.element.winner.name.text('');
    this.element.winner.messages.text('');

    this.currentWinner = '';
    this.winnerNumber = 0;

    clearInterval(this.interval.timeWinnerDraw);
    clearTimeout(this.timeout.getMessages);
};


function gradient(ratio, minDistance) {
    ratio -= minDistance;
    ratio /= 15;
    if (ratio > 1) {
        ratio = 1;
    }

    var color1 = 'd9534f';
    var color2 = '4cae4c';

    var r = Math.ceil(parseInt(color1.substring(0, 2), 16) * ratio + parseInt(color2.substring(0, 2), 16) * (1 - ratio));
    var g = Math.ceil(parseInt(color1.substring(2, 4), 16) * ratio + parseInt(color2.substring(2, 4), 16) * (1 - ratio));
    var b = Math.ceil(parseInt(color1.substring(4, 6), 16) * ratio + parseInt(color2.substring(4, 6), 16) * (1 - ratio));

    return hex(r) + hex(g) + hex(b);
}

function hex(x) {
    x = x.toString(16);
    return (x.length === 1) ? '0' + x : x;
}

var entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
};

function escapeHtml(string) {
    return String(string).replace(/[&<>"'`=\/]/g, function (s) {
        return entityMap[s];
    });
}
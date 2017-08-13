/**
 * Created by Francois on 21/06/2017.
 */

$(function () {
    timerBet.init();

    if (timerBet.betEnd != 0) {
        timerBet.updateText();
        timerBet.cursorInterval = setInterval(function () {
            timerBet.updateText();
        }, 1000);
    }
});

var timerBet = {
    betEnd: 0,
    target: $('.js--live-message'),

    messageType: '',

    init: function () {
        this.betEnd = moment.tz(parseInt(this.target.data('time') * 1000), 'Etc/UTC');
        this.messageType = this.target.data('type');

        var dateNow = moment.tz(parseInt(this.target.data('now') * 1000), 'Etc/UTC');
        var currentTime = moment.utc();

        this.differenceBetweenDateNow = moment.duration(dateNow.diff(currentTime));
    },

    updateText: function () {
        var currentTime = moment.utc().add(this.differenceBetweenDateNow);
        var duration = moment.duration(this.betEnd.diff(currentTime));

        var text = '';

        if (duration.asSeconds() <= 0) {
            text = (this.messageType === 'clock') ? '00:00:00' : 'Le pari est terminé.';
            clearInterval(this.cursorInterval);
        } else {
            if (this.messageType === 'clock') {
                text = pad_left_with_zeroes(duration.hours()) + ':' + pad_left_with_zeroes(duration.minutes()) + ':' + pad_left_with_zeroes(duration.seconds());
            } else {
                text = parseDurationText(duration);
            }
        }

        this.target.text(text);
    }
};

function pad_left_with_zeroes(str) {
    str = str + '';
    var pad = "00";
    return pad.substring(0, pad.length - str.length) + str;
}

function parseDurationText(duration) {
    var text = 'Il reste encore ';

    if (duration.hours() > 1) {
        text += duration.hours() + ' heures ';
    } else if (duration.hours() === 1) {
        text += duration.hours() + ' heure ';
    }

    if (duration.minutes() > 1) {
        text += duration.minutes() + ' minutes et ';
    } else if (duration.minutes() === 1) {
        text += duration.minutes() + ' minute et ';
    } else {
        text = 'Il ne reste plus que ';
    }

    if (duration.seconds() > 1) {
        text += duration.seconds() + ' secondes ';
    } else {
        text += duration.seconds() + ' seconde ';
    }

    text += 'pour le pari !';

    return text;
}
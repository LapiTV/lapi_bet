/**
 * Created by Francois on 21/06/2017.
 */

$(function() {
    timerInvasion.init();

    if(timerInvasion.betEnd != 0) {
        timerInvasion.updateText();
        timerInvasion.cursorInterval = setInterval(function () {
            timerInvasion.updateText();
        }, 1000);
    }
});

var timerInvasion = {

    betEnd: 0,
    message: $('.js--message'),

    init: function() {
        this.betEnd = moment.tz(parseInt($('#time').val() * 1000), 'Etc/UTC');
    },

    updateText: function() {
        var currentTime = moment.utc();

        var duration = moment.duration(this.betEnd.diff(currentTime));

        var text = '';

        if(duration.asSeconds() <= 0) {
            text = 'Le pari est terminé.';
            clearInterval(this.cursorInterval);
        } else {
            text = 'Il reste encore ';

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
        }

        this.message.text(text);
    }
};
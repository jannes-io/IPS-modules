;(function ($) {
    'use strict';

    ips.controller.register('penh.front.mission.main', {
        initialize: function () {
            this.scope.find('#form_mission_combat_record_entry').hide();
            this.scope.find('input[name=mission_create_combat_record_entry_checkbox]').on('change', $.proxy(this.handleCombatRecordToggle, this));
        },

        handleCombatRecordToggle: function (e) {
            if (e.target.checked) {
                this.scope.find('#form_mission_combat_record_entry').show();
            } else {
                this.scope.find('#form_mission_combat_record_entry').hide();
            }
        }
    });
}(jQuery));

;(function ($) {
    'use strict';

    ips.controller.register('penh.front.afteractionreport.main', {
        initialize: function () {
            const combatUnitField = document.getElementsByName('aar_combat_unit_id')[0];

            const handleCombatUnitChange = $.proxy(this.handleCombatUnitChange, this);
            const combatUnitObserver = new MutationObserver(function (mutations) {
                for (const mutation of mutations) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        handleCombatUnitChange(mutation.target.value);
                    }
                }
            });
            combatUnitObserver.observe(combatUnitField, {attributes: true});
            if (combatUnitField.value !== '') {
                handleCombatUnitChange(combatUnitField.value);
            }

            this.scope.find('form').on('submit', $.proxy(this.handleFormSubmit, this));
        },

        handleCombatUnitChange: function (newCombatUnit) {
            const ajaxUrl = ips.getSetting('baseURL')
                + 'index.php?app=penh&module=operations&controller=afteractionreport&do=personnel&id='
                + newCombatUnit;

            ips.getAjax()(ajaxUrl).done(this.buildAttendanceTable.bind(this));
        },

        buildAttendanceTable: function (soldiers) {
            let tbody = document.getElementById('attendance-table__body');
            if (tbody === null) {
                const table = document.createElement('table');
                table.id = 'attendance-table';
                table.classList.add('ipsTable', 'ipsTable_zebra');

                const thead = document.createElement('thead');
                const theadRow = document.createElement('tr');

                const thSoldier = document.createElement('th');
                thSoldier.innerText = 'Soldier';
                theadRow.append(thSoldier);
                for (const status of this.scope.data('status')) {
                    const th = document.createElement('th');
                    th.innerText = status;
                    theadRow.append(th);
                }
                thead.append(theadRow);
                table.append(thead);

                tbody = document.createElement('tbody');
                tbody.id = 'attendance-table__body';
                table.append(tbody);

                const label = document.createElement('label');
                label.classList.add('ipsFieldRow_label');
                label.innerText = 'Attendance';

                const tableContainer = document.createElement('div');
                tableContainer.classList.add('ipsFieldRow_content');
                tableContainer.append(table);

                const li = document.createElement('li');
                li.classList.add('ipsFieldRow', 'ipsClearfix');
                li.append(label);
                li.append(tableContainer);

                const aarField = document.getElementById('form_aar_attendance');
                const fieldList = aarField.parentNode;
                fieldList.insertBefore(li, aarField);
            }

            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            const attendanceField = document.getElementsByName('aar_attendance')[0];
            const attendance = JSON.parse(attendanceField.value);

            for (const soldier of soldiers) {
                const row = document.createElement('tr');
                row.id = 'attendance-soldier__' + soldier.id;

                const nameElem = document.createElement('td');

                if (soldier.rank.image_small) {
                    const rankImg = document.createElement('img');
                    rankImg.src = '/uploads/' + soldier.rank.image_small;
                    rankImg.width = 20;
                    nameElem.append(rankImg);
                } else if (soldier.rank.icon) {
                    const rankIcon = document.createElement('i');
                    rankIcon.classList.add('fa', 'fa-fw', 'fa-rank', soldier.rank.icon);
                }

                const name = document.createElement('span');
                name.innerText = ' ' + soldier.firstname + ' ' + soldier.lastname;
                nameElem.append(name);

                row.append(nameElem);

                for (const status of this.scope.data('status')) {
                    const checkBox = document.createElement('input');
                    checkBox.type = 'checkbox';
                    checkBox.setAttribute('data-status', status);
                    checkBox.addEventListener('change', function (e) {
                        const thisBox = $(e.target);
                        thisBox.parents('tr').find('input').not(thisBox).prop('checked', false);
                    });

                    if (attendance[soldier.id] !== undefined && attendance[soldier.id] === status) {
                        checkBox.setAttribute('checked', 'checked');
                    }

                    const data = document.createElement('td');
                    data.append(checkBox);

                    row.append(data);
                }

                tbody.append(row);
            }
        },

        handleFormSubmit: function (e) {
            e.preventDefault();

            const attendance = {};
            const attendanceRows = [].slice.call(document.getElementById('attendance-table__body').children);
            for (const row of attendanceRows) {
                const soldierId = row.id.split('__')[1];
                attendance[soldierId] = $(row).find('input:checked').attr('data-status');
            }
            $('#form_aar_attendance input').val(JSON.stringify(attendance));

            e.target.submit();
        }
    });
}(jQuery));

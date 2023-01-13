import $ from 'jquery';

const projectSettings = (function () {
    let _ = {
        $removeUserButton: $('.remove-user-button'),
        $removeUserModal: $('#removeUserModal'),
        $removeUserModalButton: $('.remove-modal-button'),
        idRemovedUser: 0,
        idProject: 0,

        showConfirmModal: {
            init: () => _.$removeUserButton.on('click', _.showConfirmModal.onClick),
            onClick: function () {
                _.idRemovedUser = $(this).data('id');
                _.idProject = $(this).data('project-id');
            }
        },
        pressRemoveModalButton: {
            init: () => _.$removeUserModalButton.on('click', _.pressRemoveModalButton.onClick),
            onClick: function () {
                $.ajax({
                    type: 'GET',
                    url: '/projects/removeUser/' + _.idProject + '/' + _.idRemovedUser,
                    success: function() {
                        window.location.reload();
                    }
                });
            }
        }
    }
    return {
        init: function () {
            _.showConfirmModal.init();
            _.pressRemoveModalButton.init();
        }
    }
});

$(function () {
    projectSettings().init();
})
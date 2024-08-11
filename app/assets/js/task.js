import { formatDuration } from './utils.js';
import { handleAjaxError } from './utils.js';

$(function() {
    
    $(document).on('click', '.update_button', function(event) {
        event.preventDefault();
        var form = $(this).closest('form');
        var id = form.attr('id').split('_').pop();
        var title = form.find('input[name="task[title]"]').val();
        var hours = form.find('input[name="task[duration][hours]"]').val();
        var minutes = form.find('input[name="task[duration][minutes]"]').val();
		var duration = formatDuration(hours, minutes);
        var confirmation = confirm("Êtes-vous sûr de vouloir modifier cette tâche ?");
        if (confirmation) {
            updateTask(id, title, duration);
        }
   });

   $(document).on('click', '.delete_button', function(event) {
        event.preventDefault();
        var form = $(this).closest('form');
        var id = form.attr('id').split('_').pop();
        var confirmation = confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?");
        if (confirmation) {
            deleteTask(id, form);
        }
   });

   $(document).on('click', '#task_save', function(event) {
        event.preventDefault();
        var form = $(this).closest('form');
        var title = form.find('input[name="task[title]"]').val();
        var hours = form.find('input[name="task[duration][hours]"]').val();
        var minutes = form.find('input[name="task[duration][minutes]"]').val();
		var duration = formatDuration(hours, minutes);
        var confirmation = confirm("Êtes-vous sûr de vouloir ajouter cette tâche ?");
        if (confirmation) {
            addTask(form, title, duration);
        }		
   });

   function updateTask(id, title, duration) {
        $.ajax({
            url: '/api/tasks/' + id,
            method: 'PATCH',
            contentType: 'application/merge-patch+json',
            data: JSON.stringify({
                title: title,
                duree: duration
            }),
            dataType: 'json',
        }).done(function(response) {
            alert("Tâche modifiée");
        }).fail(function(xhr) {
            handleAjaxError(xhr.responseJSON);
        });
   }

   function deleteTask(id, form) {
        $.ajax({
            url: '/api/tasks/' + id,
            method: 'DELETE',
            dataType: 'json',
        }).done(function(response) {
            alert("Tâche supprimée");
            form.remove();
            var separator=$('#separator_'+id);
            separator.remove();
        }).fail(function(xhr) {
            handleAjaxError(xhr.responseJSON);
        });
   }

   function addTask(form, title, duration) {
        $.ajax({
            url: '/ajax/task/add',
            method: 'POST',
            data: JSON.stringify({
                title: title,
                duration: duration
            }),
            dataType: 'html',
        }).done(function(response) {
            $('#task-list').append(response);
            form.find('input[name="task[title]"]').val('');
            alert("Tâche crée avec succès");
        }).fail(function(xhr) {
            handleAjaxError(JSON.parse(xhr.responseText));
        });
   }
});
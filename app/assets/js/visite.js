import { handleAjaxError } from './utils.js';

$(function() { 

    $(document).on('click', '.update_button', function(event) {
         event.preventDefault();
         var form = $(this).closest('form');
         var id = form.attr('id').split('_').pop();
         var formdate = form.find('input[name="visite[date]"]').val();
         var site = form.find('select[name="visite[site]"]').val();
         var user = form.find('select[name="visite[user]"]').val();
         var date = formatDate(formdate);
         var checklist = form.find('input[name="visite[checklist]"]').val();
         var confirmation = confirm("Êtes-vous sûr de vouloir modifier cette visite ?");
         if (confirmation) {
             updateVisite(id, date, site, user, checklist);
         }
    });
 
    $(document).on('click', '.delete_button', function(event) {
         event.preventDefault();
         var form = $(this).closest('form');
         var id = form.attr('id').split('_').pop();
 
         var confirmation = confirm("Êtes-vous sûr de vouloir supprimer cette visite ?");
         if (confirmation) {
             deleteVisite(id, form);
         }
    });
 
    $(document).on('click', '#visite_save', function(event) {
         event.preventDefault();
         var form = $(this).closest('form');
         var formdate = form.find('input[name="visite[date]"]').val();
         var site = form.find('select[name="visite[site]"]').val();
         var user = form.find('select[name="visite[user]"]').val();
         var date = formatDate(formdate);
         var checklist = form.find('select[name="visite[checklist]"]').val();
         var confirmation = confirm("Êtes-vous sûr de vouloir ajouter cette visite ?");
         if (confirmation) {
             addVisite(form, site,user,date, checklist);
         }		
    });
 
    function updateVisite(id, date, site, user, checklist) {
         $.ajax({
             url: '/api/visites/' + id,
             method: 'PATCH',
             contentType: 'application/merge-patch+json',
             data: JSON.stringify({
                 date: date,
                 site: site,
                 user:user,
                 checklist:checklist
             }),
             dataType: 'json',
         }).done(function(response) {
             alert("Visite modifiée");
		}).fail(function(xhr) {
			handleAjaxError(xhr.responseJSON);
		});
    }
 
 
     function formatDate(date){
         // Séparation de la chaîne en jour, mois et année
         var parts = date.split('/');
         var day = parts[0];
         var month = parts[1];
         var year = parts[2];
 
         // Création de la date au format ISO 8601
         var isoDateString = year + '-' + month + '-' + day + 'T00:00:00';
         var isoRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{3})?Z?$/;
         if (isoRegex.test(isoDateString)){
             return isoDateString;
         } else {
             return false;
         }
     }
 
    function deleteVisite(id, form){
         $.ajax({
             url: '/api/visites/' + id,
             method: 'DELETE',
             dataType: 'json'
         }).done(function(response) {
             alert("Visite supprimée");
             form.remove();
             var separator=$('#separator_'+id);
             separator.remove();
		}).fail(function(xhr) {
			handleAjaxError(xhr.responseJSON);
		});
    }
 
    function addVisite(form, site,user,date, checklist) {
         $.ajax({
             url: '/ajax/visite/add',
             method: 'POST',
             data: JSON.stringify({
                 date: date,
                 site: site,
                 user:user,
                 checklist:checklist
             }),
             dataType: 'html',
         }).done(function(response) {
             $('#visite-list').append(response);
             form.find('input[name="visite[titre]"]').val('');
             alert("Visite crée avec succès");
        }).fail(function(xhr) {
			handleAjaxError(JSON.parse(xhr.responseText));
        });
    }
 });
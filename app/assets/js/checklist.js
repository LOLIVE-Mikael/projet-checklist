// assets/js/checklist.js

import $ from 'jquery';
window.jQuery = $;
import { formatDuration } from './utils.js';
import { handleAjaxError } from './utils.js';

$(function() {

	//fonction de mise à jour de l'affichage des taches en fonction de la cheklist sélectionnée
    function handleChecklistChange()  {
        var checklistId = $('#form_checklist').val();
        
		if(checklistId !== ''){
			// Requête AJAX pour récupérer les données de la checklist sélectionnée
			$.ajax({
				url: '/ajax/checklist',
				method: 'GET',
				data: { checklistId: checklistId },
				dataType: 'html',
			}).done(function(response) {
					$('#task-list').html(response);
					$('#form_delete').prop('disabled', false);
					updateTaskSubmit();
			}).fail(function(xhr) {
				handleAjaxError(JSON.parse(xhr.responseText));
			});
		} else {
			$('#task-list').empty();
			$('#form_delete').prop('disabled', true);
		}
    }
	
    function deleteChecklist()  {
        var checklistId = $('#form_checklist').val();
		if(checklistId !== ''){
			var confirmation = confirm("Êtes-vous sûr de vouloir supprimer cette checklist ?");
			if (confirmation) {
				// Appel à l'API pour supprimer une checklist
				$.ajax({
					url: '/api/checklists/' + checklistId,
					method: 'DELETE',
					dataType: 'json'
				}).done(function(response) {
					$('#task-list').empty();
					$('#form_delete').prop('disabled', true);
					$('#form_checklist').val('');
					$('#form_checklist option[value="' + checklistId + '"]').remove();
				}).fail(function(xhr) {
					handleAjaxError(xhr.responseJSON);
				});
			}
		} else {
			alert('aucune checklist sélectionnée');
		}
    } 
	
	function addChecklist()  {
		var title = $('#checklist_title').val();
		var confirmation = confirm("Êtes-vous sûr de vouloir ajouter cette checklist ?");
		if (confirmation) {
			$.ajax({
				url: '/api/checklists',
				method: 'POST',
			    contentType: 'application/ld+json',
				data: JSON.stringify({
					title: title
				}),
				dataType: 'json',
			}).done(function(response) {
				// ajouter la nouvelle checklist dans la liste déroulante
			    $('#form_checklist').append($('<option>', {
					value: response.id,
					text: response.title
				}));
				// sélectionner la chacklist dans la liste déroulante
				$('#form_checklist').val(response.id);
				//on affiche les infos de la nouvelle checklist.
				handleChecklistChange();
				//on remet le formulaire à zéro.
				$('#checklist_title').val('');
				$('#checklist_creer').prop('disabled', true);
				updateTaskSubmit();
			}).fail(function(xhr) {
				handleAjaxError(xhr.responseJSON);
			});
		}
    } 
	
	//fonction pour retirer la tache.
	function removeTask(taskId) {
		var checklistId = $('#form_checklist').val();
		var confirmation = confirm("Êtes-vous sûr de vouloir dissocier cette tâche de la checklist ?");
		if (confirmation) {
			$.ajax({
				url: '/api/checklists/' + checklistId + '/tasks/' + taskId,
				method: 'DELETE',
				dataType: 'json'
			}).done(function(response) {
				console.log('Task dissociated successfully');
				//on ajoute la tache retirée de la checklist dans la liste déroulante des taches (pour éventuellement pouvoir la remettre).
				// Récupérer le titre de la tâche
				var taskTexte = $('#task-' + taskId + ' > div').contents().filter(function() {
					return this.nodeType === 3; // Filtrer uniquement les nœuds de texte
				}).text().trim()
				var taskTitle = taskTexte.substring(0, taskTexte.indexOf('Durée'));
				
				$('#task_task').append($('<option>', {
					value: taskId,
					text: taskTitle
				}));
				//retirer la tache de la liste des taches associées à la checklist
				$('#task-' + taskId).remove();
				refreshDuration();
			}).fail(function(xhr) {
				handleAjaxError(JSON.parse(xhr.responseText));
			});
		}
	}
	
	// Fonction pour rendre la création d'une nouchelle tache possible si aucune tache n'est sélectionnée.
	function updateTaskTitleField() {
		// Récupérer la valeur sélectionnée dans la liste déroulante task_task
		var selectedTaskValue = $('#task_task').val();
		// Désactiver la zone de texte si une tâche est sélectionnée dans task_task
		if (selectedTaskValue !== '') {
			$('#task_title').prop('disabled', true); 
			$('#task_duration_hours').prop('disabled', true);
			$('#task_duration_minutes').prop('disabled', true);
			$('#task_submit').prop('disabled', false);
		} else {
			$('#task_title').prop('disabled', false);
			$('#task_duration_hours').prop('disabled', false);
			$('#task_duration_minutes').prop('disabled', false);
			$('#task_submit').prop('disabled', true);
		}
	}	

	// Fonction pour rendre la sélection d'un tache possible si le titre d'une nouvelle tache est vide.
	function updateFormNewTask(){
		var newTaskValue = $('#task_title').val();
		if (newTaskValue !== '') {
			$('#task_task').prop('disabled', true);
			$('#task_submit').prop('disabled', false);
		} else {
			$('#task_task').prop('disabled', false);
			$('#task_submit').prop('disabled', true);
		}
	} 

	// Fonction activer/désactiver le bouton de création de checklist.
	function updateFormNewChecklist(){
		var newChecklistValue = $('#checklist_title').val();
		if (newChecklistValue !== '') {
			$('#checklist_creer').prop('disabled', false); 
		} else {
			$('#checklist_creer').prop('disabled', true); 
		}
	}

	// Fonction pour associer une nouvelle tache à la checklist.
	function addTask(){
		var checklistId = $('#form_checklist').val();
        var hours = $('#task_duration_hours').val();
        var minutes = $('#task_duration_minutes').val();
		var durationTask = formatDuration(hours, minutes);
		var taskId = $('#task_task').val();
		var title = $('#task_title').val();
		var durationChecklist  = $('#checklist_duration').text().trim();
		
		if (durationChecklist >6){
			var texteConfirmation = "La durée des tache dépasse les 7 H. ";
		} else {
			var texteConfirmation = "";
		}

		var confirmation = confirm(texteConfirmation + "Êtes-vous sûr de vouloir associer cette tâche à la checklist ?");
		if (confirmation) {
			$.ajax({
				url: '/ajax/checklist/addtask',
				method: 'POST',
				data: {
					taskId: taskId,
					checklistId: checklistId,
					title: title,
					duration: durationTask
				},
				dataType: 'html',
			}).done(function(response) {
				// Mettre à jour l'interface utilisateur ou faire d'autres actions si nécessaire
				console.log('Task associated successfully');
				//on ajoute la tache ajoutée à la liste.
				// Récupérer le titre de la tâche
				$('#ul-task-list').append(response);
				//et on la retire de la liste déroulante (si c'est une tache déja existante).
				if(taskId){
					$('#task_task option[value="' + taskId + '"]').remove();
				}
				//actualisation de la durée de la checklist
				refreshDuration();
				//reactulkisation des champs du formulaire d'ajout de tache.
				updateTaskTitleField();
			}).fail(function(xhr) {
				handleAjaxError(JSON.parse(xhr.responseText));
			});
		}
	}

	
	// Fonction permettant de raffraichir l'affichage de la durée théorique de la checklist.
	function refreshDuration(){
		var checklistId = $('#form_checklist').val();

		$.ajax({
			url: '/ajax/checklist/duration',
			method: 'GET',
			data: {
				checklistId: checklistId,
			},
			dataType: 'html',
		}).done(function(response) {
			console.log('Duration refreshed successfully');
			$('#duration_checklist').html(response); // Remplace le contenu de la div par la réponse de la requête				

		}).fail(function(xhr) {
			handleAjaxError(JSON.parse(xhr.responseText));
		});
	}

	function updateTaskSubmit(){
		// Récupérer la valeur sélectionnée dans la liste déroulante task_task
		var selectedTaskValue = $('#task_task').val();
		// Récupérer la valeur du champs titre de la nouvelle tache
		var newTaskValue = $('#task_title').val();
		// si l'es deux valeurs sont nulles, désactiver le bouton d'ajout d'une têche
		if (selectedTaskValue !== '' || newTaskValue !=='') {
			$('#task_submit').prop('disabled', false);
		} else {
			$('#task_submit').prop('disabled', true);
		}	
	};




	//ecouteur d'évènement en cas de sélection d'une nouvelle tache
	$(document).on('change', '#task_task', function() {
		updateTaskTitleField()
	});

	//ecouteur d'évènement en cas de saisis d'une nouvelle tâche
	$(document).on('input', '#task_title', function() {
		updateFormNewTask();
	});

	//ecouteur d'évènement en cas de saisis d'une nouvelle tâche
	$(document).on('input', '#checklist_title', function() {
		updateFormNewChecklist();
	});

	//ecouteur d'évènement d'ajout d'une tâche à la checklist
	$(document).on('click', '#task_submit', function(event) {
        // Empêcher le formulaire de se soumettre
        event.preventDefault();
		addTask();
	});

	//supprimer le bouton "voir", inutile avec le JS
    $('#form_select').hide();
	
    // Écouteur d'événement pour le changement de sélection de la checklist
    $('#form_checklist').on('change', handleChecklistChange); 

	// Écouteur d'événement en cas de clic sur un bouton pour retirer une tache	
	$(document).on('click', '.dissociate-button', function(event) {
        // Empêcher le formulaire de se soumettre
        event.preventDefault();
		var taskId = $(this).data('task-id');
		removeTask(taskId);
	});
	
	// Écouteur d'événement en cas de clic sur le bouton de suppression de la checklist	
	$(document).on('click', '#form_delete', function(event) {
        // Empêcher le formulaire de se soumettre
        event.preventDefault();
		deleteChecklist();
	}); 

	// Écouteur d'événement en cas de clic sur le bouton d'ajout d'une checklist	
	$(document).on('click', '#checklist_creer', function(event) {
        // Empêcher le formulaire de se soumettre
        event.preventDefault();
		addChecklist();
	});  

	// au démarrage, 
	// mise à jour de la page en fonction de la checklist sélectionnée.
    handleChecklistChange();
	// désactivation du bouton de creation de checklist s'il n'y aucun titre. 
	updateFormNewChecklist();
	// Activation (ou pas) du bouton d'ajout de checklist. 
	updateTaskSubmit();


	
});
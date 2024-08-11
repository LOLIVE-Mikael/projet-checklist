export function formatDuration(hours, minutes) {
    return 'PT' + hours + 'H' + minutes + 'M';
}

export function handleAjaxError(response) {
    var errorMessage = "Erreur : ";
    
    // Vérifier si la réponse contient des violations de validation
    if (response.violations && response.violations.length > 0) {
        // Utiliser le message de la première violation pour l'affichage
        errorMessage += response.violations[0].message;
    } else if (response.detail) {
        // Utiliser le champ 'detail' s'il est présent
        errorMessage += response.detail;
    } else if (response.message) {
        // Utiliser le champ 'message' s'il est présent
        errorMessage += response.message;
    } else {
        // Message générique si rien d'autre n'est disponible
        errorMessage += 'Une erreur inconnue est survenue.';
    }

    // Afficher le message d'erreur dans un pop-up
    alert(errorMessage);
}
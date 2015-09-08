<?php
/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Saisir fiche de frais"
 * @package default
 * @todo  RAS
 */
  $repInclude = './include/';
  require($repInclude . "_init.inc.php");

  // page inaccessible si visiteur non connecté
  if (!estVisiteurConnecte()) {
      header("Location: cSeConnecter.php");  
  }
  require($repInclude . "_entete.inc.html");
  require($repInclude . "_sommaire.inc.php");
  //Supprime le nbre de justificatif de -1
	$nbJustificatif=recuperationJustificatif($idConnexion, $_GET['mois'], $_GET['i']);
	$nbJustificatif = $nbJustificatif -1 ;
	ajoutNbJustificatif($idConnexion, $_GET['mois'], $_GET['i'],$nbJustificatif);
?>
  <!-- Division principale -->
 <div id="contenu">
<?php
		if(substr($_GET['libelle'],0,8) == "REFUSE :")
		{
			?>
			<p class="info">La fiche ne peut pas etre reportée</p>
			
			<?php
			header("Refresh:2, url=./cValiderFichesFrais.php");
		}
		else
		{
			$moisS = reporterMois($_GET['mois']);
			$existeFicheFrais = existeFicheFrais($idConnexion, $moisS, $_GET['i']);
			// si elle n'existe pas, on la crée avec les élets frais forfaitisés à 0
			if ( !$existeFicheFrais ) {
				//creation de la nouvelle fiches
				  ajouterFicheFrais($idConnexion, $moisS, $_GET['i']);	  
			}
			
			$nbJustificatif=recuperationJustificatif($idConnexion, $moisS, $_GET['i']);
			$nbJustificatif=$nbJustificatif+1;
			ajoutNbJustificatif($idConnexion,$moisS, $_GET['i'],$nbJustificatif);
			modifierLigneReportHF($idConnexion,$moisS, $_GET['id']);

			header("Refresh:2, url=./cValiderFichesFrais.php");
			?>
			<p class="info">La fiche a été reportée</p>
			
			<?php 
		}
		?>
  </div>
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 
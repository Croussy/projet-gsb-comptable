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
  // affectation du mois courant pour la saisie des fiches de frais
  $mois = sprintf("%04d%02d", date("Y"), date("m"));
  //Supprime la fiche hors forfait 
   	  $nbJustificatif=recuperationJustificatif($idConnexion, $mois, $_GET['id']);
	  $nbJustificatif = $nbJustificatif -1 ;
	  ajoutNbJustificatif($idConnexion, $mois, $_GET['id'],$nbJustificatif);
?>
  <!-- Division principale -->
  <div id="contenu">
		 
		<?php 
		
		if(substr($_GET['libelle'],0,6)=="REFUSE"){?>
			<p class="info">Le libelle a deja été modifié</p>
		<?php
		}
		else{
		
		$lib = "REFUSE : ".$_GET['libelle'];	
		refuserLigneHF($idConnexion, $_GET['i'], $lib);
		?>
		<p class="info">Le libelle a été modifié</p>
			
		<?php
		}
		header("Refresh:2, url=./cValiderFichesFrais.php");
		?>
   
  </div>
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 
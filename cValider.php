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
  
  
?>
  <!-- Division principale -->
  <div id="contenu">
   <p class="info">La fiche a été validée</p>
  <?php
		
	  validerFiche($idConnexion,$_POST['idVisiteur'],$_POST['moisSaisie'],date("y/m/d"));
	?>	
<?php
		header("Refresh:1, url=./cValiderFichesFrais.php");
      
 
?>          
      <form action="" method="post">
        
      </form>
  </div>
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 
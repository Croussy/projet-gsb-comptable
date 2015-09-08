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
  
  // vérification de l'existence de la fiche de frais pour ce mois courant
  $existeFicheFrais = existeFicheFrais($idConnexion, $mois, $_GET['i']);
  // si elle n'existe pas, on la crée avec les élets frais forfaitisés à 0
  if ( !$existeFicheFrais ) {
      ajouterFicheFrais($idConnexion, $mois, $_GET['i']);
  }
  // acquisition des données entrées
  // acquisition de l'étape du traitement 
  $etape=lireDonnee("etape","demanderSaisie");
  // acquisition des quantités des éléments forfaitisés 
  $tabQteEltsForfait=lireDonneePost("txtEltsForfait", "");
  // acquisition des données d'une nouvelle ligne hors forfait
  $idLigneHF = lireDonnee("idLigneHF", "");
  $dateHF = lireDonnee("txtDateHF", "");
  $libelleHF = lireDonnee("txtLibelleHF", "");
  $montantHF = lireDonnee("txtMontantHF", "");
  // structure de décision sur les différentes étapes du cas d'utilisation
  if ($etape == "validerSaisie") { 
      // l'utilisateur valide les éléments forfaitisés         
      // vérification des quantités des éléments forfaitisés
      $ok = verifierEntiersPositifs($tabQteEltsForfait);      
      if (!$ok) {
          ajouterErreur($tabErreurs, "Chaque quantité doit être renseignée et numérique positive.");
      }
      else { // mise à jour des quantités des éléments forfaitisés
          modifierEltsForfait($idConnexion, $_GET['date'], $_GET['i'],$tabQteEltsForfait);
      }
  }                                                       
  elseif ($etape == "validerSuppressionLigneHF") {
      supprimerLigneHF($idConnexion, $idLigneHF);
  }
  elseif ($etape == "validerAjoutLigneHF") {
      verifierLigneFraisHF($dateHF, $libelleHF, $montantHF, $tabErreurs);
      if ( nbErreurs($tabErreurs) == 0 ) {
          // la nouvelle ligne ligne doit être ajoutée dans la base de données
          ajouterLigneHF($idConnexion, $_GET['date'], $_GET['i'], $dateHF, $libelleHF, $montantHF);
      }
  }
  else { // on ne fait rien, étape non prévue 
  
  }                                  
?>
  <!-- Division principale -->
  <div id="contenu">
      <h2>Renseigner ma fiche de frais du mois de <?php echo obtenirLibelleMois(intval(substr($_GET['date'],4,2))) . " " . substr($mois,0,4); ?></h2>
	  
<?php
  if ($etape == "validerSaisie" || $etape == "validerAjoutLigneHF" || $etape == "validerSuppressionLigneHF") {
      if (nbErreurs($tabErreurs) > 0) {
          echo toStringErreurs($tabErreurs);
      } 
      else {
?>
      <p class="info">Les modifications de la fiche de frais ont bien été enregistrées</p>
	  
		
<?php
	header("Refresh:2, url=./cValiderFichesFrais.php");
      }   
  }
      ?>            
      <form action="" method="post">
      <div class="corpsForm">
          <input type="hidden" name="etape" value="validerSaisie" />
          <fieldset>
		  <!--input cache pour envoie de la date -->
		  <input type="hidden" name="date" value="<?php $_GET['date']?>" />
		  <!--input cache pour envoie de l'id -->
		  <input type="hidden" name="idVisiteur" value="<?php $_GET['i']?>" />
            <legend>Eléments forfaitisés
            </legend>
      <?php          
            // demande de la requête pour obtenir la liste des éléments 
            // forfaitisés du visiteur connecté pour le mois demandé
            $req = obtenirReqEltsForfaitFicheFrais($_GET['date'], $_GET['i']);
            $idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
            echo mysql_error($idConnexion);
            $lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
            while ( is_array($lgEltForfait) ) {
                $idFraisForfait = $lgEltForfait["idFraisForfait"];
                $libelle = $lgEltForfait["libelle"];
                $quantite = $lgEltForfait["quantite"];
				$lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait); 

            ?>
            <p>
			  <input type="hidden" name="idFraisForfait" value="<?php echo $idFraisForfait?>"/>
              <label for="<?php echo $idFraisForfait ?>">* <?php echo $libelle; ?> : </label>
              <input type="text" id="<?php echo $idFraisForfait ?>"
                    name="txtEltsForfait[<?php echo $idFraisForfait ?>]" 
                    size="10" maxlength="5"
                    title="Entrez la quantité de l'élément forfaitisé" 
                    value="<?php echo $quantite; ?>" />
					
			<input type="hidden" name="lib[<?php echo $idFraisForfait ?>]" value="<?php echo $quantite?>"/>
            </p>
            <?php                          
            }
            mysql_free_result($idJeuEltsFraisForfait);
            ?>
          </fieldset>
      </div>
      <div class="piedForm">
      <p>
        <input id="ok" type="submit" value="Valider" size="20" 
               title="Enregistrer les nouvelles valeurs des éléments forfaitisés" />
        <input id="annuler" type="reset" value="Effacer" size="20" />
      </p> 
      </div>
        
      </form>
  </div>
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 
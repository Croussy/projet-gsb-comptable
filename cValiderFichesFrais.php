<?php
/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Consulter une fiche de frais"
 * @package default
 * @todo  RAS
 */
  $repInclude = './include/';
  require($repInclude . "_init.inc.php");

  // page inaccessible si visiteur non connecté
  if ( ! estVisiteurConnecte() ) {
      header("Location: cSeConnecter.php");  
  }
  require($repInclude . "_entete.inc.html");
  require($repInclude . "_sommaire.inc.php");
  
  // acquisition des données entrées, ici le numéro de mois et l'étape du traitement
  $etape=lireDonnee("etape",""); 
  $etape2=lireDonnee("etape2","");
  $idVisiteurSaisie=lireDonnee("numVisiteur","");
  $moisSaisi=lireDonnee("lstMois", "");	
  
  // récupération des libellés des frais forfatisés 
  $lib1=LireDonneePost("libelle1", "");
  $lib2=LireDonneePost("libelle2", "");
  $lib3=LireDonneePost("libelle3", "");
  $lib4=LireDonneePost("libelle4", "");
  
  //recuperation des données venant de hors forfait 
  $idHF=LireDonnee("i","");
  $libelleHF=LireDonnee("libelle","");
  $dateHF=LireDonnee("date","");
  $montant=LireDonnee("montant","");
  $erreurLibelle=0;
  $erreurRefuser=0;
  $erreurReport=0;
  $montantValide=0;
  
  $tabEltsFraisForfaitModifie=array();
  $tabEltsMontantValide=array();
  
  
  
 $tabFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisSaisi, $idVisiteurSaisie);
  if ($etape != "demanderConsult" && $etape != "validerConsult") {
      // si autre valeur, on considère que c'est le début du traitement
      $etape = "demanderConsult";        
  } 
    if ($etape == "validerConsult") { // l'utilisateur valide ses nouvelles données
		
		//affichage de test : 
		//echo (" !!! ".$moisSaisi." !!! ".$visiteurSaisi." !!! ".$etape2);
	
	// vérification de l'existence de la fiche de frais pour le mois demandé
      $existeFicheFrais = existeFicheFrais($idConnexion, $moisSaisi, $idVisiteurSaisie);

		// test si une fiche de fais est existante avec les données sélectionnées
		if ( !$existeFicheFrais ) {
			ajouterErreur($tabErreurs, "Pas de fiche de frais pour ce visiteur ce mois !");
		}
		else {
			  // récupération des données sur la fiche de frais demandée 
			$tabFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisSaisi,$idVisiteurSaisie);
		}
	}
	/*l'utilisateur clique sur le bouton "modifier"
	 *on recupere les libellés de la table fraisfortfait de la base de données
	 *les libellés sont ajouté tant que il y a une cle dans le tableau 
	
	*/
	if ($etape2 =="modifierConsult"){
		$i=1;
		//on récupére les données de la fiche de frais pour le mettre dans un tableau 
		$req = obtenirReqEltsForfaitFicheFrais($moisSaisi, $idVisiteurSaisie);
		$idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
		echo mysql_error($idConnexion);
		$lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
		//on parcours le tableau 
		while ( is_array($lgEltForfait) ) {
			//verification si les données sont bien positives
			if (!estEntierPositif(${'lib'.$i})) {
				$erreurLibelle=1;
			}
			$tabEltsFraisForfaitModifie[$lgEltForfait["idFraisForfait"]]=${'lib'.$i};
			$lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
			
		$i++;
		}
		 mysql_free_result($idJeuEltsFraisForfait);
		 //verification si il n'y a pas eu d'erreur dans la procedure
		 if ($erreurLibelle==0){
			modifierEltsForfait($idConnexion,$moisSaisi,$idVisiteurSaisie,$tabEltsFraisForfaitModifie);
		 }
	}
	//action de l'utilisateur sur le bouton "refuser" et on verifie que le frais n'a pas ete deja refuser
	elseif($etape2=="RefuserLigneHF"){
		if(substr($libelleHF,0,6)=="REFUSE"){
			$erreurRefuser=1;
		}
		else{
			//on enleve -1 au nombre de justification de la fiche de frais 
			$nbJustificatif=recuperationJustificatif($idConnexion, $moisSaisi,$idVisiteurSaisie);
			$nbJustificatif = $nbJustificatif -1 ;
			ajoutNbJustificatif($idConnexion, $moisSaisi, $idVisiteurSaisie,$nbJustificatif);
			
			$newLib = "REFUSE : ".$libelleHF;
			refuserLigneHF($idConnexion, $idHF, $newLib);
		}
		
	}
	elseif($etape2=="ReporterHF"){
		if(substr($libelleHF,0,6)=="REFUSE"){
			$erreurReport=1;
		}
		else{
			//Supprime le nbre de justificatif de -1
			$nbJustificatif=recuperationJustificatif($idConnexion, $moisSaisi, $idVisiteurSaisie);
			$nbJustificatif = $nbJustificatif -1 ;
			ajoutNbJustificatif($idConnexion, $moisSaisi, $idVisiteurSaisie,$nbJustificatif);
			
			//on recuperer le mois courant pour le transformer en mois suivant
			$moisS=reporterMois($moisSaisi); 
			
			//on verifie que le fiche du mois suivant existe
			$existeFicheFrais = existeFicheFrais($idConnexion, $moisS, $idVisiteurSaisie);
			
			// si elle n'existe pas, on la crée avec les élets frais forfaitisés à 0
			if ( !$existeFicheFrais ) {
				//creation de la nouvelle fiches
				  ajouterFicheFrais($idConnexion, $moisS, $idVisiteurSaisie);	  
			}
			//on recupere le nbre de justificatif de la fiche du mois suivant
			$nbJustificatif=recuperationJustificatif($idConnexion, $moisS, $idVisiteurSaisie);
			
			//puis on l'on incremente de +1
			$nbJustificatif=$nbJustificatif+1;
			
			//puis on ajoute le nouveaux nbre de justificatif à le fiche suivant
			ajoutNbJustificatif($idConnexion,$moisS, $idVisiteurSaisie,$nbJustificatif);
			
			//enfin on modifie le mois du frais hors forfait
			modifierLigneReportHF($idConnexion,$moisS, $idHF);
		}
	}
	elseif($etape2=="validerFicheFrais"){
	
		//creation du tableau rassemblant les informations des frais forfaitisés 
		$req = obtenirReqEltsForfaitFicheFrais($moisSaisi, $idVisiteurSaisie);
		$idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
		echo mysql_error($idConnexion);
		$lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);		
		
		//on parcours le tableau des données 
		while ( is_array($lgEltForfait) ) {
		//	on ajoute quantité forfatisée multiplier par le montant dans un tableau qui a pour clé le libelle 
			$tabEltsMontantValide[$lgEltForfait["libelle"]] = $lgEltForfait["quantite"]*$lgEltForfait["montant"];
			$lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
		}
		mysql_free_result($idJeuEltsFraisForfait);
		//on parcours le tableau pour chaque categories on additionne les montants 
		foreach ( $tabEltsMontantValide as $unLibelle => $uneQuantite ) {
			$montantValide=$montantValide+$uneQuantite ;	
		}

		
		//pour ca on recupere la requete des elements hors forfait
		$reqEltsHF=obtenirReqEltsHorsForfaitFicheFrais($moisSaisi, $idVisiteurSaisie);
		$idJeuEltsHF=mysql_query($reqEltsHF,$idConnexion);
		//creation du tableau rassemblant les informations hors forfait
		$lgEltHF=mysql_fetch_assoc($idJeuEltsHF);
		
		//parcours des éléments HF
		while(is_array($lgEltHF)){
			//on test si la ligne HF n'est pas refusée et on le montant de la ligne HF au montant total
			if(substr($lgEltHF["libelle"], 0, 6)<>"REFUSE") {
				$montantValide=$montantValide+$lgEltHF["montant"];
			}
			$lgEltHF=mysql_fetch_assoc($idJeuEltsHF);
		}
		mysql_free_result($idJeuEltsHF);
		
		//Enfin on valide la fiche frais 
		validerFiche($idConnexion,$idVisiteurSaisie,$moisSaisi,$montantValide);
	}
	$tabFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisSaisi, $idVisiteurSaisie);
	?>	
  <!-- Division principale -->
  <div id="contenu">
      <h2>Valider les fiches frais </h2>
      <h3>Sélectionner le mois et le visiteur: </h3>
      <form action="?" method="post">
      <div class="corpsForm">
          <input type="hidden" name="etape" value="validerConsult" />
		  <input type="hidden" name="etape2" value="null" />
      <p>
		 <!--Menu deroulant des visiteurs --> 
		<label for="lstVisiteur">Visiteur :</label>
		<select id="numVisiteur" name="numVisiteur" title="Selectionnez le visiteur">
			<?php
				$req = "select id,nom,prenom , mois from visiteur v ,fichefrais f where id = idVisiteur and idEtat='CL' GROUP BY id,nom,prenom";;
				$idJeuVisiteur = mysql_query($req,$idConnexion);
				while  ($lgVisiteur = mysql_fetch_array($idJeuVisiteur)) {
					$idVisiteur = $lgVisiteur['id'];
					$nomVisiteur = $lgVisiteur['nom'];
					$pnomVisiteur = $lgVisiteur['prenom'];?>
				<option value="<?php echo $idVisiteur; ?>"<?php if ($idVisiteurSaisie == $idVisiteur) { ?> selected="selected"<?php } ?>><?php echo $nomVisiteur . " " . $pnomVisiteur; ?></option>
				 <?php  
					}
				?>
		
		</select>
		</p>
		<p>
        <label for="lstMois">Mois : </label>
        <select id="lstMois" name="lstMois" title="Sélectionnez le mois souhaité pour la fiche de frais">
            <?php
                // on propose tous les mois pour lesquels le visiteur a une fiche de frais
                $req = "select distinct mois from fichefrais where idEtat = 'CL'";
                $idJeuMois = mysql_query($req, $idConnexion);
                $lgMois = mysql_fetch_assoc($idJeuMois);
                while ( is_array($lgMois) ) {
                    $mois = $lgMois["mois"];
                    $noMois = intval(substr($mois, 4, 2));
                    $annee = intval(substr($mois, 0, 4));
            ?>    
            <option value="<?php echo $mois; ?>"<?php if ($moisSaisi == $mois) { ?> selected="selected"<?php } ?>><?php echo obtenirLibelleMois($noMois) . " " . $annee; ?></option>
            <?php
                    $lgMois = mysql_fetch_assoc($idJeuMois);        
                }
                mysql_free_result($idJeuMois);
            ?>
        </select>
      </p>
      </div>
      <div class="piedForm">
      <p>
        <input id="ok" type="submit" value="Valider" size="20"
               title="Demandez à consulter cette fiche de frais" />
      </p> 
      </div>
        
      </form>
<?php      

// demande et affichage des différents éléments (forfaitisés et non forfaitisés)
// de la fiche de frais demandée, uniquement si pas d'erreur détecté au contrôle
    if ( $etape == "validerConsult" ) {
        if ( nbErreurs($tabErreurs) > 0 ) {
            echo toStringErreurs($tabErreurs) ;
        }
        else {	
		
?>
    <h3>Fiche de frais du mois de <?php echo obtenirLibelleMois(intval(substr($moisSaisi,4,2))) . " " . substr($moisSaisi,0,4); ?> : 
    <em><?php echo $tabFicheFrais["libelleEtat"]; ?> </em>
    depuis le <em><?php echo $tabFicheFrais["dateModif"]; ?></em></h3>
    <div class="encadre">
    <p>Montant validé : <?php echo $tabFicheFrais["montantValide"] ;
        ?>              
	</p>
		<form action="?" method="post">
		<div class="corpsForm">
          <input type="hidden" name="etape2" value="modifierConsult" />
          <input type="hidden" name="etape" value="validerConsult" />
		  <input type="hidden" name="lstMois" value="<?php echo $moisSaisi; ?>" />
		  <input type="hidden" name="numVisiteur" value="<?php echo$idVisiteurSaisie; ?>" />
	<p>
<?php          
            // demande de la requête pour obtenir la liste des éléments 
            // forfaitisés du visiteur connecté pour le mois demandé
            $req = obtenirReqEltsForfaitFicheFrais($moisSaisi, $idVisiteurSaisie);
            $idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
            echo mysql_error($idConnexion);
            $lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
            // parcours des frais forfaitisés du visiteur connecté
            // le stockage intermédiaire dans un tableau est nécessaire
            // car chacune des lignes du jeu d'enregistrements doit être doit être
            // affichée au sein d'une colonne du tableau HTML
            $tabEltsFraisForfait = array();
            while ( is_array($lgEltForfait) ) {
                $tabEltsFraisForfait[$lgEltForfait["libelle"]] = $lgEltForfait["quantite"];
				$lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
            }
            mysql_free_result($idJeuEltsFraisForfait);
            ?>
  	<table class="listeLegere">
  	   <caption>Quantités des éléments forfaitisés</caption>
        <tr>
		
            <?php
            // premier parcours du tableau des frais forfaitisés du visiteur connecté
            // pour afficher la ligne des libellés des frais forfaitisés
            foreach ( $tabEltsFraisForfait as $unLibelle => $uneQuantite ) {
            ?>
                <th><?php echo $unLibelle ; ?></th>
            <?php
            }
            ?>
        </tr>
        <tr>
            <?php
			$i=1;
            // second parcours du tableau des frais forfaitisés du visiteur connecté
            // pour afficher la ligne des quantités des frais forfaitisés
            foreach ( $tabEltsFraisForfait as $unLibelle => $uneQuantite ) {
            ?>
                <td class="qteForfait"><input type="text" value="<?php echo $uneQuantite ; ?>" name="libelle<?php echo ($i);?>" size="10"></td>
				
            <?php
			$i++;
            }
            ?>
			
        </tr>
    </table>
	<?php
	if ($etape2 == "modifierConsult") {
		if (  $erreurLibelle==1 ) {
		?>
		     
			<p class="erreur">Attention vous ne pouvez que saisir des nombres positifs !</p>
		<?php
		}
		else
		{
			?>
			  <p class="info">Les modifications de la fiche de frais ont bien été enregistrées</p>        
			<?php
		}
		}
		?>
		<input id="ok" type="submit" value="Modifier" size="20" title="Modifier les éléments hors forfait saisis" />
		</p> 
		</form>	
		<form action="?" method="post">
        <input type="hidden" name="numVisiteur" value="<?php echo $idVisiteurSaisie; ?>" />
		<input type="hidden" name="lstMois" value="<?php echo $moisSaisi; ?>" />
		<?php
			if ($etape2 == "RefuserLigneHF") {
				if($erreurRefuser==1){
				?>
				<p class="erreur">La ligne hors forfait a deja été refusé</p>
				<?php
				}
				elseif($erreurRefuser==0){
				?>
				<p class="info">La ligne hors forfait a été refusé !</p>    
				<?php
				}
			}
			elseif ($etape2== "validerSuppressionLigneHF"){
				if($erreurReport==0)
				?>
				    <p class="info">La ligne hors forfait a été reporté au moins suivant !</p>     
				<?php
				}
				elseif($erreurReport==1){
				?>
					<p class="erreur">La ligne hors forfait a été refusé, vous ne pouvez pas la reporter !</p>  
				<?php
				}
			
		?>		
  	<table class="listeLegere">
  	   <caption>Descriptif des éléments hors forfait - <?php echo $tabFicheFrais["nbJustificatifs"]; ?> justificatifs reçus -</caption>
             <tr>
                <th class="date">Date</th>
                <th class="libelle">Libellé</th>
                <th class="montant">Montant</th>  	
				<th>Refuser</th>
				<th>Reporter</th>
             </tr>
			<?php          
            // demande de la requête pour obtenir la liste des éléments hors
            // forfait du visiteur connecté pour le mois demandé
            $req = obtenirReqEltsHorsForfaitFicheFrais($moisSaisi, $idVisiteurSaisie);
            $idJeuEltsHorsForfait = mysql_query($req, $idConnexion);
            $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
            
            // parcours des éléments hors forfait 
            while ( is_array($lgEltHorsForfait) ) {
            ?>
                <tr>
                   <td><?php echo $lgEltHorsForfait["date"] ; ?></td>
                   <td><?php echo filtrerChainePourNavig($lgEltHorsForfait["libelle"]) ; ?></td>
                   <td><?php echo $lgEltHorsForfait["montant"] ; ?></td>
				   
				   <td><a href="?etape=validerConsult&etape2=RefuserLigneHF&numVisiteur=<?php echo $idVisiteurSaisie?>&lstMois=<?php echo $moisSaisi?>&libelle=<?php echo $lgEltHorsForfait["libelle"]?>&i=<?php echo $lgEltHorsForfait["id"] ;?>"><img src="./images/index.jpg"/></a></td>
				   <td><a href="?etape=validerConsult&etape2=ReporterHF&numVisiteur=<?php echo $idVisiteurSaisie?>&lstMois=<?php echo $moisSaisi?>&i=<?php echo $lgEltHorsForfait["id"] ;?>&date=<?php echo $lgEltHorsForfait["date"] ;?>&montant=<?php echo $lgEltHorsForfait["montant"];?>&libelle=<?php echo $lgEltHorsForfait["libelle"]?>"><img src="./images/reporter.png"/></a></td>
				   
                </tr>
            <?php
                $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
            }
            mysql_free_result($idJeuEltsHorsForfait);
  ?>
    </table>
	  <p>
		<input type="hidden" name="etape2" value="validerFicheFrais"/>
        <input id="ok" type="submit" value="Valider la fiche fais " size="20"
               title="Valider la fiche frais  " />
      </p> 
  </div>
  </div>
  </form>
 
<?php
        }
    }

?>    
  </div>
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 
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
  ?>
  
  <!-- Division principale -->
  <div id="contenu">
      <h2>Suivre les fiches de frais</h2>
      <h3>Sélectionner une fiche : </h3>	
      <form action="" method="post">
      <div >

		<table class="listeLegere" >

             <tr>
                <th class="date">Visiteur</th>
                <th class="libelle">mois</th>
                <th class="montant">Montant</th>  	
				<th>Voir Detail</th>
             </tr>
			 <?php
				$req = obtenirReqFicheFrais();
				$idFiches = mysql_query($req, $idConnexion);
				$lgFiches = mysql_fetch_assoc($idFiches);
				while (is_array($lgFiches)) {
			?>
			    <tr>
                   <td><?php echo $lgFiches["nom"]." "	.$lgFiches["prenom"]; ?></td>
                   <td><?php echo obtenirLibelleMois(intval(substr($lgFiches["mois"],4,2))) . " " . substr($lgFiches["mois"],0,4); ?></td>
                   <td><?php echo $lgFiches["montantValide"] ; ?></td>
				   
				   <td><a href="cRembourse.php?visiteur=<?php echo $lgFiches["idVisiteur"];?>&mois=<?php echo $lgFiches["mois"] ;?>">Details<img src="./images/reporter.png"/></a></td>			   
                </tr>
            <?php
                $lgFiches = mysql_fetch_assoc($idFiches);
            }
            mysql_free_result($idFiches);
  ?>
    </table>
		</p>
      </div>
        
      </form>

</div>	
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 
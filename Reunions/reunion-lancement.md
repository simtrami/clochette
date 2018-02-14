# Réunion de lancement

Projet Clochette
------

## Ordre du jour

- Cahier des charges
- Organisation des périodes de travail
- Bonnes pratiques de gestion de projet et travail en groupe

## Concernant le projet

### Cahier des charges

L&#39;appli actuelle est archaïque (seulement base de données et indication du montant de la transaction). Il faut de nouvelles fonctionnalités : Historiques des tenues, gestion du stock et de la trésorerie et préparation des ventes.

- Le but est d&#39;optimiser le temps de travail au niveau du stock et de la trésorerie et aussi de prévenir les erreurs.
- Gestion prévisionnel, profilage des étudiants (afin de contrôler leur consommation d&#39;alcool si jamais leur comportement se dégradent et afin de prévenir tout problème avec l&#39;administration) → voir <a href="#GDPR">GDPR</a> (General Data Protection Regulation)

Caisse enregistreuse : On ne souhaite conserver que le tiroir-caisse et remplacer la caisse par le PC sur l&#39;appli. **Cette question doit trouver une réponse en priorité.**

## Organisation

Il faut définir l&#39;objectif final en termes de temps et de qualité.

Voir comment on peut séparer individuellement les tâches.

- Par module ?
- En suivant la méthode Agile ?

Il faut avoir conscience qu&#39;au niveau de l&#39;organisation il faut préparer les deadlines (soutenances et rendus) selon les dispos de chacun (<a href="#retroplanning">retroplanning</a>). Pas besoin de soigner ou passer plus de temps que nécessaire sur les outils de performance comme le Gant.

Afin de respecter les délais dans la mesure du possible, il faut trier les tâches, i.e. représenter les tâches sous forme d&#39;une arborescence guidées par la <a href="#methAgile">méthode agile</a>.

Nous avons défini une réunion bi-hebdomadaire avec le tuteur. On se fixe donc des jalons tous les 15 jours c&#39;est-à-dire des sprints (<a href="#i">plus... (i)</a>).

Il faut organiser visuellement l&#39;avancement du projet :

- Post-it (à faire, en cours, faits) plutôt sur version informatiques faute de locale ?
- Trello/Kanboard ?

Organisation conseillée pour ce type d&#39;appli : Equipe projet technique qui connait bien le cahier des charges, le manager (<a href="#scrum">scrum</a> master) qui veille à la fluidité dans l&#39;achèvement des tâches.


## ToDo List

Dans les 15 prochains jours :

- Définir quel type de méthodologie on emploiera
- Revoir le cahier des charges pour pouvoir le verrouiller
- Clarifier notamment tout ce qui touche aux profiling des clients qui peut poser des problèmes éthiques
- Démarrage du Sprint 0
- Etudier pourquoi les autres projets de caisse n&#39;ont pas été mis en place <a href="#ii">plus... (ii)</a>
- Continuer l&#39;état de l&#39;art en prenant en compte les précédentes applis <a href="#iii">plus... (iii)</a>
- Déterminer une solution pour la caisse
- Appréhender les outils de gestion de projet <a href="#iv">plus... (iv)</a>
- Préconcevoir la base de données sur laquelle se concentrera le premier sprint.


## Références


##### <span id="GDPR">GDPR</span>
 Nouveau règlement sur la protection des données qui va s&#39;appliquer à tout le monde. Il sera de le creuser dans notre cas car les données que l&#39;on prévoit de stocker et traiter sont sensibles (éthique et permet garder une certaine confidentialité)


##### <span id="retroplanning">Retroplanning</span>
 Définition : [http://jamaity.org/2014/11/le-retroplanning-et-le-budget-previsionnel/](http://jamaity.org/2014/11/le-retroplanning-et-le-budget-previsionnel/)


##### <span id="methAgile">Méthode Agile</span>
 Cette page peut très bien servir de guide [https://www.agiliste.fr/exemple-dorganisation-projet-agile/](https://www.agiliste.fr/exemple-dorganisation-projet-agile/)


##### <span id="scrum">Scrum</span>
 C&#39;est un schéma d&#39;organisation de développement de produits complexes. Il est défini par ses créateurs comme un « cadre de travail holistique itératif qui se concentre sur les buts communs en livrant de manière productive et créative des produits de la plus grande valeur possible ».
 Source : [https://fr.wikipedia.org/wiki/Scrum\_(d%C3%A9veloppement)](https://fr.wikipedia.org/wiki/Scrum_(d%C3%A9veloppement))


## Remarques :

 <span id="i">i</span> :
 Le principe est qu&#39;en arrivant à chaque réunion, un objet du cahier des charges (ou sous objets) ait été réalisé depuis le dernière réunion.

<span id="ii">ii</span> :
 Il faut s&#39;intéresser aussi aux versions « ratées » de l&#39;application afin de voir si certaines parties sont exploitables pour nous faire gagner du temps.

<span id="iii">iii</span> :
 On peut également se pencher sur d&#39;autres applis caisse dispos en open-source et éventuellement passer un Sprint, non pas à coder mais à les étudier et récupérer les parties de code-source qui nous intéressent.

<span id="iv">iv</span> :
 Pour rédiger des comptes-rendus plus efficacement, Olivier Berger nous conseille [Orgmode](https://orgmode.org/fr/index.html). Les comptes-rendus pourront être gittés.
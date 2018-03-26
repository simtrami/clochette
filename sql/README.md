# Informations sur la conception de la BDD
- Les 4 tables sont comptes, articles, commandes (historique) et stocks
- Dans stock, il y a la colonne type, qui est un varchar(15), à défaut de pouvoir utiliser un enum. Donc il faudra au moment de la création du site et de ses commandes, restreindre les valeurs que peut prendre cette colonne à ‘bouteille’ et ‘fut’.
- Ensuite, il faudra des commandes différentes à chaque type. On diminue le stock de fut de 1 quand un fut est changé, donc on appelle ici l’intervention d’un membre du bureau pour aller appuyer sur un bouton prévu à cet effet.
- Alors que pour les bouteilles, à chaque vente, on diminue de 1 la quantité automatiquement.
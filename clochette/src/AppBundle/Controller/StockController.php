<?php
// src/AppBundle/Controller/StockController.php
namespace AppBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use AppBundle\Entity\Stocks;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class StockController extends Controller {

    private $types = [
        [   'type'  => 'draft',
            'nom'   => 'Fût'
        ],
        [   'type'  => 'bottle',
            'nom'   => 'Bouteille'
        ],
        [   'type'  => 'article',
            'nom'   => 'Nourriture ou autre'
        ]
    ];

    /**
    * @Route("/stock", name="stock")
    **/
    public function showIndex(){
        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');

        $drafts = $repo_stocks->findByType('draft');
        $bottles = $repo_stocks->findByType('bottle');
        $article = $repo_stocks->findByType('article');

        $data=[];
        $data['drafts'] = $drafts;
        $data['bottles'] = $bottles;
        $data['article'] = $article;


        return $this->render("stock/index.html.twig", $data);
    }

    /**
     * @Route("/stock/details/{id_article}", name="details_article")
     */
    public function showDetails(Request $request, $id_article){

        $data = [];

        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        
        $data['mode'] = 'modify';
        $data['types'] = $this->types;
        $data['form'] = [];

        $form = $this->createFormBuilder()
            ->add('nom', TextType::class)
            ->add('type', TextType::class)
            ->add('prixAchat', MoneyType::class)
            ->add('prixVente', MoneyType::class)
            ->add('quantite', IntegerType::class)
            ->add('volume', NumberType::class, array(
                'required'      => false,
            ))
            ->getForm()
        ;

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $form_data = $form->getData();
            $data['form'] = [];
            $data['form'] = $form_data;
            $article = $repo_stocks->find($id_article);

            $article->setNom($form_data['nom']);
            $article->setType($form_data['type']);
            $article->setPrixAchat($form_data['prixAchat']);
            $article->setPrixVente($form_data['prixVente']);
            $article->setQuantite($form_data['quantite']);
            $article->setVolume($form_data['volume']);
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('stock');
            
        } else
        {
            $article = $repo_stocks->find($id_article);

            $article_data['idarticle'] = $article->getIdarticle();
            $article_data['nom'] = $article->getNom();
            $article_data['type'] = $article->getType();
            $article_data['prixAchat'] = $article->getPrixAchat();
            $article_data['prixVente'] = $article->getPrixVente();
            $article_data['quantite'] = $article->getQuantite();
            $article_data['volume'] = $article->getVolume();

            $article_data['types'] = $this->types;

            $data['form'] = $article_data;
        }

        return $this->render("stock/ajout.html.twig", $data);
    }
    
    /**
     * @Route("/stock/ajout", name="ajout_article")
     */
    public function ajoutArticleAction(Request $request){

        $data = [];
        $data['mode'] = 'new_article';
        $data['types'] = $this->types;
        $data['form'] = [];
        $data['form']['type'] = '';

        $form = $this->createFormBuilder()
            ->add('nom', TextType::class)
            ->add('type', TextType::class)
            ->add('prixAchat', MoneyType::class)
            ->add('prixVente', MoneyType::class)
            ->add('quantite', IntegerType::class)
            ->add('volume', NumberType::class, array(
                'required'      => false,
            ))
            ->getForm()
        ;

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $form_data = $form->getData();
            $data['form'] = [];
            $data['form'] = $form_data;

            $em = $this->getDoctrine()->getManager();
            $article = new Stocks();
            $article->setNom($form_data['nom']);
            $article->setType($form_data['type']);
            $article->setPrixAchat($form_data['prixAchat']);
            $article->setPrixVente($form_data['prixVente']);
            $article->setQuantite($form_data['quantite']);
            $article->setVolume($form_data['volume']);
            
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('ajout_article');
        }


        return $this->render('stock/ajout.html.twig', $data);
    }

    /**
    * @Route("/stock/supprimer", name="suppr_article")
    **/
    public function supprArticleAction(Request $request){
        $idarticle = $request->query->get('idarticle');

        $em = $this->getDoctrine()->getEntityManager();
        $article = $em->getRepository('AppBundle:Stocks')->find($idarticle);
        
        if (!$article) {
            throw $this->createNotFoundException("Article non trouvé pour l'id ".$idarticle);
        }

        $em->remove($article);
        $em->flush();
    
        return $this->redirectToRoute('stock');
    }

}